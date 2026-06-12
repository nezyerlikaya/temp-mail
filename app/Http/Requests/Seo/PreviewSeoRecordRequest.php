<?php

namespace App\Http\Requests\Seo;

class PreviewSeoRecordRequest extends UpdateSeoRecordRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.seo-growth-center.preview') ?? false;
    }
}
