<?php

namespace App\Services;

use App\DTO\UserDTO;
use App\Models\Chats\UserChat;
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

    public function getById(int $userId)
    {
        return User::find($userId);
    }

    public function getIdByName(string $name): ?int
    {
        return User::select(['id'])
            ->where('name', $name)
            ->first()
            ?->id;
    }

    public function getByChatId(int $chatId)
    {
        return User::select(['id', 'name'])
            ->whereIn('id', UserChat::select(['user_id'])
                ->where('chat_id', $chatId)
                ->pluck('user_id')
                ->toArray()
            )
            ->get();
    }

    public function getAll()
    {
        return User::select(['id', 'name'])->get();
    }

    public function getByClientPhone(string $phone): ?User
    {
        return User::where('phone', $phone)
            ->firstOrFail();
    }
}
