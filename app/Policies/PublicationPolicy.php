<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Publication;
use App\Enums\RoleEnum;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Publication $publication)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Publication $publication)
    {
        if ($user->can('publication.edit') &&
            ($user->role->name != RoleEnum::VENDOR || $user->id == $publication->created_by_id)) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Publication $publication)
    {
        if ($user->can('publication.destroy') &&
            ($user->role->name != RoleEnum::VENDOR || $user->id == $publication->created_by_id)) {
            return true;
        }
    }

    /**
     * Determine whether the user can republication the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Publication $publication)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Publication $publication)
    {
        //
    }
}
