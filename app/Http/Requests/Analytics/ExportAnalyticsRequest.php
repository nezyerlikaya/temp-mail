<?php

namespace App\Http\Requests\Analytics;

class ExportAnalyticsRequest extends AnalyticsFilterRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('export analytics') ?? false;
    }
}
