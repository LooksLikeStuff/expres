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


    public function getIdByName(string $name): ?int
    {
        return User::select('id')
            ->where('name', trim($name))
            ->first()?->id;
    }

    /**
     * Поиск пользователя по телефону клиента из DealClient
     */
    public function getByClientPhone(string $phone): ?User
    {
        // Нормализуем телефон
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Сначала ищем в основной таблице users
        $user = $this->findByPhone($phone);
        if ($user) {
            return $user;
        }
        
        // Если не найден, ищем через DealClient
        $dealClient = \App\Models\DealClient::where('phone', 'LIKE', '%' . $normalizedPhone . '%')->first();
        if ($dealClient && $dealClient->deal && $dealClient->deal->user) {
            return $dealClient->deal->user;
        }
        
        return null;
    }

    /**
     * Поиск всех пользователей связанных с телефоном (включая через DealClient)
     */
    public function findAllByPhone(string $phone): Collection
    {
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);
        $users = collect();
        
        // Ищем в основной таблице users
        $directUsers = User::where('phone', 'LIKE', '%' . $normalizedPhone . '%')->get();
        $users = $users->merge($directUsers);
        
        // Ищем через DealClient
        $dealClients = \App\Models\DealClient::where('phone', 'LIKE', '%' . $normalizedPhone . '%')
            ->with('deal.user')
            ->get();
            
        foreach ($dealClients as $dealClient) {
            if ($dealClient->deal && $dealClient->deal->user) {
                $users->push($dealClient->deal->user);
            }
        }
        
        // Убираем дубликаты по ID
        return $users->unique('id');
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



    public function updateOrCreate(UserDTO $userDTO): User
    {
        return User::updateOrCreate([
            'phone' => $userDTO->phone,
        ], $userDTO->toArray());
    }
}
