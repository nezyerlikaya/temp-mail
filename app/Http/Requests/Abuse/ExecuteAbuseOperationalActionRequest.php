<?php

namespace App\Http\Requests\Abuse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ExecuteAbuseOperationalActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('execute abuse operational actions') ?? false;
    }

    public function rules(): array
    {
        return [
            'operational_action' => ['required', Rule::in(['lock_mailbox', 'expire_mailbox', 'suspend_user', 'trash_comment', 'block_sender_email', 'block_sender_domain', 'block_recipient_email', 'block_recipient_domain', 'block_ip_hash'])],
            'block_value' => ['nullable', 'required_if:operational_action,block_sender_email,block_sender_domain,block_recipient_email,block_recipient_domain,block_ip_hash', 'string', 'max:255'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
            'confirm_action' => ['accepted'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $action = (string) $this->input('operational_action');
            $value = (string) $this->input('block_value');

            if (in_array($action, ['block_sender_email', 'block_recipient_email'], true) && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                $validator->errors()->add('block_value', 'Enter a valid email address.');
            }

            if (in_array($action, ['block_sender_domain', 'block_recipient_domain'], true)
                && ! preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i', $value)) {
                $validator->errors()->add('block_value', 'Enter a valid domain name without a URL path.');
            }

            if ($action === 'block_ip_hash' && filter_var($value, FILTER_VALIDATE_IP) === false && ! preg_match('/^[a-f0-9]{64}$/i', $value)) {
                $validator->errors()->add('block_value', 'Enter an IP address or a 64-character SHA-256 hash.');
            }
        }];
    }
}
