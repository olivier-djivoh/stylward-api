<?php

namespace App\Policies;

use App\Models\ClothingModel;
use App\Models\User;

class ClothingModelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStylist() || $user->isAdmin();
    }

    public function view(User $user, ClothingModel $model): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $model->shop->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isStylist() || $user->isAdmin();
    }

    public function update(User $user, ClothingModel $model): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $model->shop->user_id;
    }

    public function delete(User $user, ClothingModel $model): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $model->shop->user_id;
    }
}
