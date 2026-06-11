<?php

namespace App\Http\Requests\Updates;

use Illuminate\Foundation\Http\FormRequest;

class UploadManualUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.update-center.manual-upload') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'package' => ['required', 'file', 'mimes:zip', 'max:65536'],
            'expected_checksum' => ['required', 'string', 'size:64'],
            'signature' => ['nullable', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'package' => 'manual update package',
            'expected_checksum' => 'expected checksum',
        ];
    }
}
