<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserSearchService
{
    public function __construct(
        private readonly UserProfileService $profiles,
        private readonly UserStatusService $statuses,
    ) {}

    public function search(Request $request): LengthAwarePaginator
    {
        $search = trim((string) $request->query('search'));
        $role = (string) $request->query('role');
        $status = (string) $request->query('status');
        $createdFrom = (string) $request->query('created_from');
        $createdTo = (string) $request->query('created_to');

        return User::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $identityQuery) use ($search): void {
                    $identityQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('display_name', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when(array_key_exists($role, $this->profiles->roles()), fn (Builder $query) => $query->where('role', $role))
            ->when($this->statuses->isValid($status), fn (Builder $query) => $query->where('status', $status))
            ->when($this->validDate($createdFrom), fn (Builder $query) => $query->whereDate('created_at', '>=', $createdFrom))
            ->when($this->validDate($createdTo), fn (Builder $query) => $query->whereDate('created_at', '<=', $createdTo))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();
    }

    private function validDate(string $date): bool
    {
        if ($date === '') {
            return false;
        }

        $parsed = date_create_from_format('Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
