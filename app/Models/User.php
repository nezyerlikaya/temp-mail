<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Services\Analytics\AnalyticsEventTracker;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    'public_author_slug',
    'author_bio',
    'social_links',
    'author_profile_active',
    'featured_author',
    'avatar_color',
    'current_plan_reference',
    'membership_status',
    'premium_starts_at',
    'premium_ends_at',
    'membership_granted_by',
    'is_admin',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected static function booted(): void
    {
        static::created(function (User $user): void {
            app(AnalyticsEventTracker::class)->trackSafely('user.registered', [
                'user' => $user,
                'metadata' => [
                    'source' => $user->hasAdminAccess() ? 'admin' : 'product',
                    'status' => (string) $user->status,
                ],
            ]);
        });
    }

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
            'social_links' => 'array',
            'author_profile_active' => 'boolean',
            'featured_author' => 'boolean',
            'premium_starts_at' => 'datetime',
            'premium_ends_at' => 'datetime',
            'membership_granted_by' => 'integer',
            'is_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function hasAdminAccess(): bool
    {
        return (UserRole::tryFrom((string) $this->role) ?? UserRole::Member)->hasAdminAccess();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }
}
