<?php

namespace Database\Seeders;

use App\DTO\UserDTO;
use App\Enums\Status;
use App\Services\UserService;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(UserService $userService): void
    {
        $users = [
            [
                'name' => 'user_first',
                'phone' => '+7 (999) 888-77-66',
                'status' => Status::USER,
                'password' => 'user_password123',
            ],
            [
                'name' => 'user_second',
                'phone' => '+7 (999) 111-33-55',
                'status' => Status::USER,
                'password' => 'user_password123',
            ],
            [
                'name' => 'user_third',
                'phone' => '+7 (999) 555-33-11',
                'status' => Status::USER,
                'password' => 'user_password',
            ]
        ];

        foreach ($users as $user) {
            $userService->updateOrCreate(UserDTO::fromArray($user));
        }
    }
}
