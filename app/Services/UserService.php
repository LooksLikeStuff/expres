<?php

namespace App\Services;

use App\DTO\UserDTO;
use App\Models\User;

class UserService
{
    public function updateOrCreate(UserDTO $userDTO)
    {
        return User::updateOrCreate([
            'phone' => $userDTO->phone,
        ], $userDTO->toArray());
    }

    public function getByName(string $name)
    {

    }

    public function getIdByName(string $name): ?int
    {
        return User::select(['id'])
            ->where('name', $name)
            ->first()
            ?->id;
    }
}
