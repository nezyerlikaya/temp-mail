<?php

namespace App\Services\Mail;

use App\Models\InboundMailConnection;
use Throwable;

class ImapConnectionTester
{
    public function __construct(private readonly InboundMailExtensionChecker $extensions) {}

    /** @return array{status: string, message: string, checks: array<string, array{status: string, message: string}>} */
    public function test(InboundMailConnection $connection): array
    {
        if (! $this->extensions->check()['ready']) {
            return $this->failure('PHP IMAP extension is required before this connection can be tested.', [
                'dns' => $this->pending('Not checked because PHP IMAP is unavailable.'),
                'socket' => $this->pending('Not checked because PHP IMAP is unavailable.'),
                'authentication' => $this->pending('Not checked because PHP IMAP is unavailable.'),
                'mailbox' => $this->pending('Not checked because PHP IMAP is unavailable.'),
            ]);
        }

        $dns = $this->dnsCheck($connection->host);
        if ($dns['status'] !== 'passed') {
            return $this->failure('The inbound host could not be resolved.', [
                'dns' => $dns,
                'socket' => $this->pending('Waiting for DNS resolution.'),
                'authentication' => $this->pending('Waiting for a socket connection.'),
                'mailbox' => $this->pending('Waiting for authentication.'),
            ]);
        }

        $socket = $this->socketCheck($connection);
        if ($socket['status'] !== 'passed') {
            return $this->failure('The inbound host did not accept a safe socket connection.', [
                'dns' => $dns,
                'socket' => $socket,
                'authentication' => $this->pending('Waiting for a socket connection.'),
                'mailbox' => $this->pending('Waiting for authentication.'),
            ]);
        }

        return $this->imapCheck($connection, $dns, $socket);
    }

    /** @return array{status: string, message: string} */
    private function dnsCheck(string $host): array
    {
        try {
            $addresses = gethostbynamel($host);

            return $addresses
                ? ['status' => 'passed', 'message' => 'Host resolves to '.count($addresses).' address(es).']
                : ['status' => 'failed', 'message' => 'No address records were found for the inbound host.'];
        } catch (Throwable) {
            return ['status' => 'failed', 'message' => 'DNS resolution could not be completed on this server.'];
        }
    }

    /** @return array{status: string, message: string} */
    private function socketCheck(InboundMailConnection $connection): array
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
            $socket = @stream_socket_client(
                $transport.'://'.$connection->host.':'.$connection->port,
                $errorCode,
                $errorMessage,
                $connection->connection_timeout,
                STREAM_CLIENT_CONNECT,
                $context,
            );

            if (! is_resource($socket)) {
                return ['status' => 'failed', 'message' => 'Socket connection failed. Check the host, port, encryption, and firewall.'];
            }

            fclose($socket);

            return ['status' => 'passed', 'message' => 'The inbound socket accepted a connection.'];
        } catch (Throwable) {
            return ['status' => 'failed', 'message' => 'Socket connection failed safely without exposing provider details.'];
        }
    }

    /** @return array{status: string, message: string, checks: array<string, array{status: string, message: string}>} */
    private function imapCheck(InboundMailConnection $connection, array $dns, array $socket): array
    {
        $flags = match ($connection->encryption) {
            'ssl' => '/imap/ssl',
            'tls' => '/imap/tls',
            default => '/imap/notls',
        };

        if (! $connection->validate_certificate) {
            $flags .= '/novalidate-cert';
        }

        $mailbox = sprintf('{%s:%d%s}%s', $connection->host, $connection->port, $flags, $connection->mailbox);

        try {
            $stream = @imap_open($mailbox, $connection->username, (string) $connection->encrypted_password, OP_READONLY, 1);

            if ($stream === false) {
                imap_errors();

                return $this->failure('Authentication or mailbox selection failed. Verify the credentials and folder name.', [
                    'dns' => $dns,
                    'socket' => $socket,
                    'authentication' => ['status' => 'failed', 'message' => 'The server rejected authentication or mailbox selection.'],
                    'mailbox' => $this->pending('Mailbox availability could not be confirmed.'),
                ]);
            }

            imap_close($stream);

            return [
                'status' => 'connected',
                'message' => 'Inbound mail connection is ready. No messages were imported or changed.',
                'checks' => [
                    'dns' => $dns,
                    'socket' => $socket,
                    'authentication' => ['status' => 'passed', 'message' => 'Authentication succeeded in read-only mode.'],
                    'mailbox' => ['status' => 'passed', 'message' => 'The configured mailbox is available in read-only mode.'],
                ],
            ];
        } catch (Throwable) {
            return $this->failure('The IMAP readiness test failed safely. Review the connection settings.', [
                'dns' => $dns,
                'socket' => $socket,
                'authentication' => ['status' => 'failed', 'message' => 'Authentication could not be completed.'],
                'mailbox' => $this->pending('Mailbox availability could not be confirmed.'),
            ]);
        }
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
