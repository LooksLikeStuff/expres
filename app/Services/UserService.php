<?php

namespace App\Services;

use App\DTO\UserDTO;
use App\Models\Chats\UserChat;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    public function findByPhone(string $phone): ?User
    {
        return User::where('phone', $phone)->first();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function create(UserDTO $userDTO, string $password): User
    {
        return User::create([
            ...$userDTO->toArray(),
            'password' => bcrypt($password),
            'avatar_url' => $userDTO->avatarUrl ?? '/storage/icon/profile.svg',
        ]);
    }

    public function update(int $id, UserDTO $userDTO): bool
    {
        return User::where('id', $id)->update($userDTO->toArray());
    }

    public function delete(int $id): bool
    {
        return User::destroy($id) > 0;
    }

    public function findByStatus(string $status): Collection
    {
        return User::where('status', $status)->get();
    }

    public function searchByPhone(string $phone): Collection
    {
        return User::where('phone', 'LIKE', "%{$phone}%")->get();
    }

    public function existsByPhone(string $phone): bool
    {
        return User::where('phone', $phone)->exists();
    }

    public function getByChatId(int $chatId): Collection
    {
        return User::select(['id', 'name', 'avatar_url'])
            ->whereIn('id', UserChat::select(['user_id'])
                ->where('chat_id', $chatId)
                ->pluck('user_id')
                ->toArray()
            )
            ->get();
    }

    public function getAll(): Collection
    {
        return User::select(['id', 'name', 'avatar_url'])->get();
    }

    public function getByClientPhone(string $phone): ?User
    {
        return User::where('phone', $phone)->first();
    }

    public function updateOrCreate(UserDTO $userDTO): User
    {
        return User::updateOrCreate([
            'phone' => $userDTO->phone,
        ], $userDTO->toArray());
    }
}
