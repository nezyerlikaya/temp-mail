<?php

namespace App\Services\Users;

use App\Enums\UserRole;
use App\Models\User;
use DateTimeZone;

class UserProfileService
{
    public function __construct(private readonly UserStatusService $statuses) {}

    /**
     * @return array<string, mixed>
     */
    public function formData(User $user): array
    {
        return [
            'profileUser' => $user,
            'statuses' => $this->statuses->statuses(),
            'roles' => $this->roles(),
            'timezones' => DateTimeZone::listIdentifiers(),
            'languages' => $this->languages(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function roles(): array
    {
        return collect(UserRole::cases())->mapWithKeys(fn (UserRole $role): array => [
            $role->value => $role->label(),
        ])->all();
    }

    /**
     * @return array<string, string>
     */
    public function languages(): array
    {
        return [
            'en' => 'English',
            'tr' => 'Turkish',
            'de' => 'German',
            'fr' => 'French',
            'es' => 'Spanish',
        ];
    }
}
