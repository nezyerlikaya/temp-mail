<?php

namespace App\Http\Requests\Installer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DatabaseSetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'connection' => ['required', Rule::in(['mysql', 'mariadb', 'sqlite'])],
            'host' => [Rule::requiredIf(fn (): bool => $this->input('connection') !== 'sqlite'), 'nullable', 'string', 'max:255'],
            'port' => [Rule::requiredIf(fn (): bool => $this->input('connection') !== 'sqlite'), 'nullable', 'integer', 'between:1,65535'],
            'database' => ['required', 'string', 'max:255'],
            'username' => [Rule::requiredIf(fn (): bool => $this->input('connection') !== 'sqlite'), 'nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'database.required' => 'Enter the database name your host gave you.',
            'host.required' => 'Enter the database host.',
            'port.required' => 'Enter the database port.',
            'username.required' => 'Enter the database username.',
        ];
    }
}
