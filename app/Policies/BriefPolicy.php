<?php

namespace App\Policies;

use App\Models\Brief;
use App\Models\User;

class BriefPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Brief $brief): bool
    {
        // Пользователь может просматривать свой собственный бриф
        if ($brief->user_id === $user->id) {
            return true;
        }

        // Администраторы, координаторы и партнеры могут просматривать любые брифы
        if (in_array($user->status, ['admin', 'coordinator', 'partner'])) {
            return true;
        }

        // Проверяем, связан ли пользователь с брифом через сделку
        if ($brief->deal_id && $brief->deal) {
            // Если пользователь является координатором сделки
            if ($brief->deal->coordinator_id === $user->id) {
                return true;
            }

            // Если пользователь является ответственным за сделку
            if ($brief->deal->user_id === $user->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Все аутентифицированные пользователи могут создавать брифы
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Brief $brief): bool
    {
        // Пользователь может редактировать свой собственный бриф
        if ($brief->user_id === $user->id) {
            return true;
        }

        // Администраторы и координаторы могут редактировать любые брифы
        if (in_array($user->status, ['admin', 'coordinator'])) {
            return true;
        }

        // Координатор сделки может редактировать бриф
        if ($brief->deal_id && $brief->deal && $brief->deal->coordinator_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Brief $brief): bool
    {
        // Пользователь может удалить свой собственный бриф
        if ($brief->user_id === $user->id) {
            return true;
        }

        // Только администраторы могут удалять чужие брифы
        if ($user->status === 'admin') {
            return true;
        }

        return false;
    }
}
