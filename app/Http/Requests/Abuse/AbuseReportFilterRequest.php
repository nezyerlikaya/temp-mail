<?php

namespace App\Http\Requests\Abuse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AbuseReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view abuse reports') ?? false;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:190'],
            'report_type' => ['nullable', Rule::in(['all', 'phishing', 'spam', 'malware', 'impersonation', 'illegal_content', 'privacy_violation', 'copyright_dmca', 'abusive_mailbox', 'abusive_domain', 'other'])],
            'status' => ['nullable', Rule::in(['all', 'new', 'reviewing', 'awaiting_information', 'resolved', 'rejected', 'archived'])],
            'priority' => ['nullable', Rule::in(['all', 'low', 'normal', 'high', 'critical'])],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'date' => ['nullable', 'date'],
        ];
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        return [
            'q' => $this->validated('q'),
            'report_type' => $this->validated('report_type', 'all'),
            'status' => $this->validated('status', 'all'),
            'priority' => $this->validated('priority', 'all'),
            'assigned_to' => $this->validated('assigned_to'),
            'date' => $this->validated('date'),
        ];
    }
}
