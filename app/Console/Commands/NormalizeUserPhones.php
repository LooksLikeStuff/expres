<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Symfony\Component\Console\Command\Command as CommandAlias;

class NormalizeUserPhones extends Command
{
    protected $signature = 'users:normalize-phones {--force : Применить даже если номер уже нормализован}';
    protected $description = 'Нормализовать все номера телефонов пользователей (включая soft-deleted)';

    public function handle()
    {
        $this->info('Начинаем нормализацию телефонов...');

        $updated = 0;

        User::withoutGlobalScopes()
            ->withTrashed() // если есть SoftDeletes
            ->chunkById(200, function ($users) use (&$updated) {
                foreach ($users as $user) {
                    if ($user->id === 1231) dd($user);
                    if (! $user->phone) {
                        continue;
                    }

                    $normalized = normalizePhone($user->getRawOriginal('phone')); // берём именно сырое значение из БД

                    if ($this->option('force') || $normalized !== $user->getRawOriginal('phone')) {
                        $user->forceFill(['phone' => $normalized])->saveQuietly();
                        $updated++;
                    }
                }

            });

        $this->info("Готово ✅ Обновлено {$updated} номеров.");
        return CommandAlias::SUCCESS;
    }
}
