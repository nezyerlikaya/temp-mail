<?php

namespace App\Http\Requests\Updates;

use App\Services\Updates\UpdateChannelResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckForUpdatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.update-center.check') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'channel' => ['required', 'string', Rule::in(app(UpdateChannelResolver::class)->keys())],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'channel' => 'update channel',
        ];
    }
}
