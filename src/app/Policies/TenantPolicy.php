<?php

namespace App\Policies;

use App\Models\Central\Tenant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TenantPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Central\Tenant  $tenant
     * @return bool
     */
    public function view(User $user, Tenant $tenant): bool
    {
        return $user->hasRole('Super Admin');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Central\Tenant  $tenant
     * @return bool
     */
    public function update(User $user, Tenant $tenant): bool
    {
        return $user->hasRole('Super Admin');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Central\Tenant  $tenant
     * @return bool
     */
    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->hasRole('Super Admin');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Central\Tenant  $tenant
     * @return bool
     */
    public function restore(User $user, Tenant $tenant): bool
    {
        return $user->hasRole('Super Admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Central\Tenant  $tenant
     * @return bool
     */
    public function forceDelete(User $user, Tenant $tenant): bool
    {
        return $user->hasRole('Super Admin');
    }
}
