<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['group', 'payload', 'updated_by'])]
class ApiSetting extends Model
{
    protected function casts(): array
    {
        return ['payload' => 'array', 'updated_by' => 'integer'];
    }
}
