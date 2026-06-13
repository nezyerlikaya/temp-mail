<?php

namespace App\Http\Requests\Abuse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitAbuseReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_type' => ['required', Rule::in(['phishing', 'spam', 'malware', 'impersonation', 'illegal_content', 'privacy_violation', 'copyright_dmca', 'abusive_mailbox', 'abusive_domain', 'other'])],
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'critical'])],
            'reporter_name' => ['required', 'string', 'min:2', 'max:120'],
            'reporter_email' => ['required', 'email:rfc', 'max:190'],
            'subject' => ['required', 'string', 'min:5', 'max:190'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'related_url' => ['nullable', 'url:http,https', 'max:500'],
        ];
    }
}
