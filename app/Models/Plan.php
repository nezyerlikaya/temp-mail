<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'key',
    'name',
    'description',
    'is_active',
    'monthly_price',
    'yearly_price',
    'currency',
    'sort_order',
    'billing_provider',
    'updated_by',
])]
class Plan extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function limits(): HasOne
    {
        return $this->hasOne(PlanLimit::class);
    }
}
