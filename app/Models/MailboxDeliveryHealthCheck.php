<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['status', 'summary', 'checked_at', 'checked_by'])]
class MailboxDeliveryHealthCheck extends Model
{
    protected function casts(): array
    {
        return ['summary' => 'array', 'checked_at' => 'datetime'];
    }
}
