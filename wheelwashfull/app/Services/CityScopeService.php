<?php

namespace App\Services;

use App\Constants\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CityScopeService
{
    public function apply(Builder $query, User $user): Builder
    {
        $cityId = $this->allowedCityId($user, request('service_city_id'));

        if ($cityId === null) {
            return $query;
        }

        return $query->where('service_city_id', $cityId);
    }

    public function applyViaUser(Builder $query, User $user): Builder
    {
        $cityId = $this->allowedCityId($user, request('service_city_id'));

        if ($cityId === null) {
            return $query;
        }

        return $query->whereHas('user', fn (Builder $userQuery) => $userQuery->where('service_city_id', $cityId));
    }

    public function allowedCityId(User $user, mixed $requestedCityId = null): ?int
    {
        if ($this->isSuperAdmin($user)) {
            return $requestedCityId ? (int) $requestedCityId : null;
        }

        if ($this->isCityAdmin($user)) {
            return $user->service_city_id ? (int) $user->service_city_id : 0;
        }

        return $user->service_city_id ? (int) $user->service_city_id : null;
    }

    public function ensureCanAccessCity(User $user, mixed $cityId): void
    {
        if ($this->isSuperAdmin($user)) {
            return;
        }

        if (! $cityId || (int) $cityId !== (int) $user->service_city_id) {
            throw new HttpException(403, 'You cannot access data from another city.');
        }
    }

    public function ensureCanAccessModel(User $user, Model $model): void
    {
        if (! array_key_exists('service_city_id', $model->getAttributes())) {
            return;
        }

        $this->ensureCanAccessCity($user, $model->service_city_id);
    }

    public function isSuperAdmin(User $user): bool
    {
        return UserRole::isSuperAdminRole($user->role);
    }

    public function isCityAdmin(User $user): bool
    {
        return $user->role === UserRole::CITY_ADMIN;
    }
}
