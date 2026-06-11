<?php

namespace App\Models;

use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'display_name',
    'username',
    'email',
    'password',
    'status',
    'role',
    'timezone',
    'language_preference',
    'bio',
    'website',
    'avatar_media_id',
    'is_admin',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'avatar_media_id' => 'integer',
            'is_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function hasAdminAccess(): bool
    {
        return (UserRole::tryFrom((string) $this->role) ?? UserRole::Member)->hasAdminAccess();
    }
}
