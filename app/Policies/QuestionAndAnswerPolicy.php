<?php

namespace App\Policies;

use App\Models\User;
use App\Models\QuestionAndAnswer;

class QuestionAndAnswerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, QuestionAndAnswer $question_and_answer)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, QuestionAndAnswer $question_and_answer)
    {
        if ($user->can('question_and_answer.edit')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, QuestionAndAnswer $question_and_answer)
    {
        if ($user->can('question_and_answer.destroy')) {
            return true;
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, QuestionAndAnswer $question_and_answer)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, QuestionAndAnswer $question_and_answer)
    {
        //
    }
}
