<?php

namespace App\Http\Requests\PublicSite;

use App\Services\Security\BotProtectionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreatePublicMailboxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'domain_id' => ['required', 'integer'],
            'alias' => ['nullable', 'string', 'max:64', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/'],
            'bot_protection_token' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $bot = app(BotProtectionService::class);

                if (! $bot->shouldProtect('mailbox_creation')) {
                    return;
                }

                $readiness = $bot->readiness();
                if (! $readiness['ready']) {
                    $validator->errors()->add('bot_protection_token', $readiness['message']);

                    return;
                }

                if (blank($this->input('bot_protection_token'))) {
                    $validator->errors()->add('bot_protection_token', 'Complete the configured bot protection challenge before creating an inbox.');
                }
            },
        ];
    }

    public function attributes(): array
    {
        return ['domain_id' => 'receiving domain', 'alias' => 'custom alias'];
    }
}
