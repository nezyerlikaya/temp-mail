<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class DashboardLiveMetricsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view live metrics') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
