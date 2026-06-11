<?php

namespace App\Http\Requests\Audit;

class ExportAuditLogsRequest extends AuditLogFilterRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.activity-audit-logs.export') === true;
    }
}
