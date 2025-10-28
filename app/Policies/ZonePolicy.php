<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Auth\Access\Response;

class ZonePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Zone $zone): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can('zone.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Zone $zone): bool
    {
        if ($user->can('zone.edit') &&
            ($user->role->name != RoleEnum::VENDOR || $user->id == $zone->created_by_id)) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Zone $zone): bool
    {
        if ($user->can('zone.destroy') &&
            ($user->role->name != RoleEnum::VENDOR || $user->id == $zone->created_by_id)) {
            return true;
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Zone $zone): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Zone $zone): bool
    {
        //
    }
}
