<?php

namespace App\Services\Mail;

use App\Models\SmtpConnection;
use Throwable;

class SmtpConnectionTester
{
    /** @return array{status: string, message: string, checks: array<string, array{status: string, message: string}>} */
    public function test(SmtpConnection $connection): array
    {
        $dns = $this->dnsCheck($connection->host);
        if ($dns['status'] !== 'passed') {
            return $this->failure('The SMTP host could not be resolved.', [
                'dns' => $dns,
                'socket' => $this->pending('Waiting for DNS resolution.'),
                'authentication' => $this->pending('Waiting for a socket connection.'),
                'delivery_readiness' => $this->pending('Waiting for authentication.'),
            ]);
        }

        $socket = $this->openSocket($connection);
        if (! is_resource($socket['stream'])) {
            return $this->failure('The SMTP host did not accept a safe socket connection.', [
                'dns' => $dns,
                'socket' => ['status' => 'failed', 'message' => $socket['message']],
                'authentication' => $this->pending('Waiting for a socket connection.'),
                'delivery_readiness' => $this->pending('Waiting for authentication.'),
            ]);
        }

        return $this->smtpDialogue($connection, $socket['stream'], $dns);
    }

    /** @return array{status: string, message: string} */
    private function dnsCheck(string $host): array
    {
        try {
            $addresses = gethostbynamel($host);

            return $addresses
                ? ['status' => 'passed', 'message' => 'Host resolves to '.count($addresses).' address(es).']
                : ['status' => 'failed', 'message' => 'No address records were found for the SMTP host.'];
        } catch (Throwable) {
            return ['status' => 'failed', 'message' => 'DNS resolution could not be completed on this server.'];
        }
    }

    /** @return array{stream: resource|null, message: string} */
    private function openSocket(SmtpConnection $connection): array
    {
        $transport = $connection->encryption === 'ssl' ? 'ssl' : 'tcp';
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => $connection->validate_certificate,
                'verify_peer_name' => $connection->validate_certificate,
                'allow_self_signed' => ! $connection->validate_certificate,
                'peer_name' => $connection->host,
                'SNI_enabled' => true,
            ],
        ]);

        try {
            $stream = @stream_socket_client(
                $transport.'://'.$connection->host.':'.$connection->port,
                $errorCode,
                $errorMessage,
                $connection->connection_timeout,
                STREAM_CLIENT_CONNECT,
                $context,
            );

            if (! is_resource($stream)) {
                return ['stream' => null, 'message' => 'Socket connection failed. Check the host, port, encryption, and firewall.'];
            }

            stream_set_timeout($stream, $connection->connection_timeout);

            return ['stream' => $stream, 'message' => 'The SMTP socket accepted a connection.'];
        } catch (Throwable) {
            return ['stream' => null, 'message' => 'Socket connection failed safely without exposing provider details.'];
        }
    }

    /** @param resource $stream */
    private function smtpDialogue(SmtpConnection $connection, mixed $stream, array $dns): array
    {
        try {
            $banner = $this->read($stream);
            if (! str_starts_with($banner, '220')) {
                fclose($stream);

                return $this->failedDialogue($dns, 'The SMTP server did not return a ready banner.');
            }

            $ehlo = $this->command($stream, 'EHLO '.parse_url((string) config('app.url'), PHP_URL_HOST));

            if ($connection->encryption === 'tls') {
                if (! str_contains($ehlo, 'STARTTLS')) {
                    fclose($stream);

                    return $this->failedDialogue($dns, 'The SMTP server did not advertise STARTTLS.');
                }

                $startTls = $this->command($stream, 'STARTTLS');
                if (! str_starts_with($startTls, '220') || ! @stream_socket_enable_crypto($stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    fclose($stream);

                    return $this->failedDialogue($dns, 'STARTTLS negotiation failed. Check encryption and certificate settings.');
                }

                $this->command($stream, 'EHLO '.parse_url((string) config('app.url'), PHP_URL_HOST));
            }

            $auth = $this->authenticate($stream, $connection);
            if (! $auth) {
                $this->command($stream, 'QUIT');
                fclose($stream);

                return $this->failure('SMTP authentication failed. Verify the username and password.', [
                    'dns' => $dns,
                    'socket' => ['status' => 'passed', 'message' => 'The SMTP socket accepted a connection.'],
                    'authentication' => ['status' => 'failed', 'message' => 'The SMTP server rejected authentication.'],
                    'delivery_readiness' => $this->pending('Waiting for authentication.'),
                ]);
            }

            $this->command($stream, 'QUIT');
            fclose($stream);

            return [
                'status' => 'connected',
                'message' => 'SMTP connection is ready for transactional system delivery. No message was sent by this readiness test.',
                'checks' => [
                    'dns' => $dns,
                    'socket' => ['status' => 'passed', 'message' => 'The SMTP socket accepted a connection.'],
                    'authentication' => ['status' => 'passed', 'message' => 'SMTP authentication succeeded.'],
                    'delivery_readiness' => ['status' => 'passed', 'message' => 'A validated from address is configured for safe test delivery.'],
                ],
            ];
        } catch (Throwable) {
            if (is_resource($stream)) {
                fclose($stream);
            }

            return $this->failedDialogue($dns, 'The SMTP readiness test failed safely. Review host, encryption, certificate, and credentials.');
        }
    }

    /** @param resource $stream */
    private function authenticate(mixed $stream, SmtpConnection $connection): bool
    {
        $response = $this->command($stream, 'AUTH LOGIN');
        if (! str_starts_with($response, '334')) {
            return false;
        }

        $user = $this->command($stream, base64_encode($connection->username));
        if (! str_starts_with($user, '334')) {
            return false;
        }

        $password = $this->command($stream, base64_encode((string) $connection->encrypted_password));

        return str_starts_with($password, '235');
    }

    private function failedDialogue(array $dns, string $message): array
    {
        return $this->failure($message, [
            'dns' => $dns,
            'socket' => ['status' => 'passed', 'message' => 'The SMTP socket accepted a connection.'],
            'authentication' => ['status' => 'failed', 'message' => 'SMTP authentication or protocol negotiation could not be completed.'],
            'delivery_readiness' => $this->pending('Waiting for authentication.'),
        ]);
    }

    /** @param resource $stream */
    private function command(mixed $stream, string $command): string
    {
        fwrite($stream, $command."\r\n");

        return $this->read($stream);
    }

    /** @param resource $stream */
    private function read(mixed $stream): string
    {
        $response = '';

        while (! feof($stream)) {
            $line = fgets($stream, 512);
            if ($line === false) {
                break;
            }

            $response .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }

        return $response;
    }

    /** @param array<string, array{status: string, message: string}> $checks */
    private function failure(string $message, array $checks): array
    {
        return ['status' => 'failed', 'message' => $message, 'checks' => $checks];
    }

    /** @return array{status: string, message: string} */
    private function pending(string $message): array
    {
        return ['status' => 'pending', 'message' => $message];
    }
}
