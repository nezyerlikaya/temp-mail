<?php

namespace App\Services\EmailTemplates;

class SystemEmailLayoutResolver
{
    /** @return array<string, mixed> */
    public function readiness(): array
    {
        return [
            'brand_logo_ready' => false,
            'support_links_ready' => filled(config('mail.from.address')),
            'legal_links_ready' => false,
            'header_locked' => true,
            'footer_locked' => true,
            'brand_name' => (string) config('app.name', 'Temp Mail Cloud'),
            'support_email' => (string) config('mail.from.address', 'support@example.test'),
        ];
    }

    public function wrap(string $body, ?string $preheader = null): string
    {
        $brand = e((string) config('app.name', 'Temp Mail Cloud'));
        $support = e((string) config('mail.from.address', 'support@example.test'));
        $preheaderHtml = filled($preheader)
            ? '<div style="display:none;max-height:0;overflow:hidden;">'.e((string) $preheader).'</div>'
            : '';

        return $preheaderHtml.'
<div data-system-email-layout="locked" style="margin:0;background:#f5f5f4;padding:24px;font-family:Arial,sans-serif;color:#1c1917;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e7e5e4;border-radius:12px;overflow:hidden;">
        <tr>
            <td style="padding:24px;border-bottom:1px solid #e7e5e4;">
                <strong style="font-size:18px;">'.$brand.'</strong>
            </td>
        </tr>
        <tr>
            <td style="padding:24px;font-size:15px;line-height:1.6;">'.$body.'</td>
        </tr>
        <tr>
            <td style="padding:20px 24px;border-top:1px solid #e7e5e4;color:#78716c;font-size:12px;">
                This system message was sent by '.$brand.'. For support, contact '.$support.'.
            </td>
        </tr>
    </table>
</div>';
    }
}
