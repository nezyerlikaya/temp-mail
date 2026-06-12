<?php

namespace App\Services\EmailTemplates;

class EmailTemplateVariableRegistry
{
    /** @return array<string, string> */
    public function variables(): array
    {
        return [
            'app_name' => 'Application name',
            'user_name' => 'User name',
            'reset_url' => 'Password reset URL',
            'login_url' => 'Login URL',
            'verification_url' => 'Email verification URL',
            'premium_ends_at' => 'Premium expiration date',
            'support_email' => 'Support email',
            'abuse_email' => 'Abuse email',
        ];
    }

    /** @return array<string, array<int, string>> */
    public function requiredByKey(): array
    {
        return [
            'password_reset' => ['app_name', 'user_name', 'reset_url'],
            'email_verification' => ['app_name', 'user_name', 'verification_url'],
            'admin_invite' => ['app_name', 'user_name', 'login_url'],
            'login_alert' => ['app_name', 'user_name', 'login_url'],
            'premium_expiring' => ['app_name', 'user_name', 'premium_ends_at'],
            'premium_expired' => ['app_name', 'user_name', 'support_email'],
            'security_alert' => ['app_name', 'user_name', 'login_url', 'support_email'],
            'backup_failed' => ['app_name', 'support_email'],
            'abuse_report_received' => ['app_name', 'abuse_email'],
            'contact_form_received' => ['app_name', 'support_email'],
        ];
    }

    /** @return array<int, string> */
    public function invalidVariables(string $content): array
    {
        preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);

        return collect($matches[1] ?? [])
            ->reject(fn (string $variable): bool => array_key_exists($variable, $this->variables()))
            ->unique()
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    public function missingRequired(string $templateKey, string $content): array
    {
        preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);
        $present = collect($matches[1] ?? [])->unique();

        return collect($this->requiredByKey()[$templateKey] ?? [])
            ->reject(fn (string $variable): bool => $present->contains($variable))
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    public function usedVariables(string $content): array
    {
        preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);

        return collect($matches[1] ?? [])->unique()->values()->all();
    }
}
