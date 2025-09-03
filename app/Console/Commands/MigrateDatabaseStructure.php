<?php

namespace App\Console\Commands;

use App\Enums\VerificationType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Deal;
use Exception;

class MigrateDatabaseStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:database-structure 
                            {--source-db= : Название исходной базы данных}
                            {--target-db= : Название целевой базы данных}
                            {--dry-run : Выполнить тестовый запуск без записи данных}
                            {--chunk-size=1000 : Размер порции для обработки данных}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Перенос данных из старой структуры БД в новую рефакторизированную структуру';

    private $sourceConnection;
    private $targetConnection;
    private $isDryRun = false;
    private $chunkSize = 1000;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Начинаем миграцию структуры базы данных...');
        
        // Получение параметров
        $sourceDb = $this->option('source-db');
        $targetDb = $this->option('target-db');
        $this->isDryRun = $this->option('dry-run');
        $this->chunkSize = intval($this->option('chunk-size') ?? 1000);

        if (!$sourceDb || !$targetDb) {
            $this->error('❌ Необходимо указать исходную и целевую базы данных');
            $this->info('Пример: php artisan migrate:database-structure --source-db=old_db --target-db=new_db');
            return 1;
        }

        if ($this->isDryRun) {
            $this->warn('🧪 Выполняется тестовый запуск (данные не будут записаны)');
        }

        try {
            // Настройка подключений к базам данных
            $this->setupDatabaseConnections($sourceDb, $targetDb);
            
            // Проверка доступности баз данных
            $this->validateDatabases();
            
            // Выполнение миграции
            $this->performMigration();
            
            $this->info('✅ Миграция успешно завершена!');
            return 0;
            
        } catch (Exception $e) {
            $this->error('❌ Ошибка при выполнении миграции: ' . $e->getMessage());
            $this->error('Стек ошибки: ' . $e->getTraceAsString());
            return 1;
        }
    }

    private function setupDatabaseConnections($sourceDb, $targetDb)
    {
        $this->info("🔧 Настройка подключений к БД: {$sourceDb} -> {$targetDb}");
        
        $currentConfig = config('database.connections.mysql');
        
        config([
            'database.connections.source_mysql' => array_merge($currentConfig, ['database' => $sourceDb]),
            'database.connections.target_mysql' => array_merge($currentConfig, ['database' => $targetDb])
        ]);
        
        $this->sourceConnection = DB::connection('source_mysql');
        $this->targetConnection = DB::connection('target_mysql');
    }

    private function validateDatabases()
    {
        $this->info('🔍 Проверка доступности баз данных...');
        
        try {
            $this->sourceConnection->getPdo();
            $this->info('✅ Исходная база данных доступна');
        } catch (Exception $e) {
            throw new Exception("Не удалось подключиться к исходной БД: " . $e->getMessage());
        }
        
        try {
            $this->targetConnection->getPdo();
            $this->info('✅ Целевая база данных доступна');
        } catch (Exception $e) {
            throw new Exception("Не удалось подключиться к целевой БД: " . $e->getMessage());
        }
        
        foreach (['users', 'deals', 'news', 'ratings'] as $table) {
            if (!Schema::connection('source_mysql')->hasTable($table)) {
                throw new Exception("Таблица {$table} не найдена в исходной БД");
            }
        }
        
        foreach (['verification_codes', 'user_sessions', 'deal_clients', 'news', 'ratings'] as $table) {
            if (!Schema::connection('target_mysql')->hasTable($table)) {
                throw new Exception("Таблица {$table} не найдена в целевой БД");
            }
        }
    }

    private function performMigration()
    {
        $this->info('📊 Начинаем перенос данных...');
        
        $this->migrateUsers();
        $this->migrateDeals();
        $this->migrateDealClients();
        $this->migrateNews();
        $this->migrateRatings();
        $this->migrateVerificationCodes();
        $this->migrateUserSessions();
        
        $this->info('🎉 Все данные успешно перенесены!');
    }

    private function migrateUsers()
    {
        $this->info('👥 Переносим пользователей...');
        
        $totalUsers = $this->sourceConnection->table('users')->count();
        $this->info("Найдено пользователей: {$totalUsers}");
        
        if ($totalUsers === 0) {
            $this->warn('⚠️ Пользователи не найдены в исходной БД');
            return;
        }
        
        $progressBar = $this->output->createProgressBar($totalUsers);
        $migratedCount = 0;
        
        $this->sourceConnection->table('users')
            ->orderBy('id')
            ->chunk($this->chunkSize, function ($users) use (&$migratedCount, $progressBar) {
                foreach ($users as $user) {
                    $userData = (array) $user;
                    
                    // Удаляем поля, которые теперь в отдельных таблицах или были удалены
                    $excludeFields = [
                        'temp_phone', 'cod', 'verification_code', 'verification_code_expires_at', 
                        'verification_code_used_at', 'email_verification_code', 'phone_verification_code',
                        'last_seen_at', 'last_login_at', 'ip_address', 'user_agent', 'avatar_yandex_path',
                        'fcm_token', 'active_projects_count', 'firebase_token', 
                    ];
                    
                    // Удаляем только существующие поля для избежания ошибок
                    foreach ($excludeFields as $field) {
                        if (isset($userData[$field])) {
                            unset($userData[$field]);
                        }
                    }
                    
        
                    if (!$this->isDryRun) {
                        User::updateOrCreate(
                            ['id' => $user->id],
                            $userData
                        );
                    }
                    
                    $migratedCount++;
                    $progressBar->advance();
                }
            });
        
        $progressBar->finish();
        $this->newLine();
        $this->info("✅ Перенесено пользователей: {$migratedCount}");
    }

    private function migrateNews()
    {
        $this->info('📰 Переносим новости...');
        
        $totalNews = $this->sourceConnection->table('news')->count();
        $this->info("Найдено новостей: {$totalNews}");
        
        if ($totalNews === 0) {
            $this->warn('⚠️ Новости не найдены в исходной БД');
            return;
        }
        
        $progressBar = $this->output->createProgressBar($totalNews);
        $migratedCount = 0;
        
        $this->sourceConnection->table('news')
            ->orderBy('id')
            ->chunk($this->chunkSize, function ($newsItems) use (&$migratedCount, $progressBar) {
                foreach ($newsItems as $news) {
                    $newsData = (array) $news;
                    
                    if (!$this->isDryRun) {
                        $this->targetConnection->table('news')->updateOrInsert(
                            ['id' => $news->id],
                            $newsData
                        );
                    }
                    
                    $migratedCount++;
                    $progressBar->advance();
                }
            });
        
        $progressBar->finish();
        $this->newLine();
        $this->info("✅ Перенесено новостей: {$migratedCount}");
    }

    private function migrateRatings()
    {
        $this->info('⭐ Переносим рейтинги...');
        
        $totalRatings = $this->sourceConnection->table('ratings')->count();
        $this->info("Найдено рейтингов: {$totalRatings}");
        
        if ($totalRatings === 0) {
            $this->warn('⚠️ Рейтинги не найдены в исходной БД');
            return;
        }
        
        $progressBar = $this->output->createProgressBar($totalRatings);
        $migratedCount = 0;
        
        $this->sourceConnection->table('ratings')
            ->orderBy('id')
            ->chunk($this->chunkSize, function ($ratings) use (&$migratedCount, $progressBar) {
                foreach ($ratings as $rating) {
                    $ratingData = (array) $rating;
                    
                    // Проверяем, существуют ли связанные сделка и пользователи
                    $dealExists = $this->targetConnection->table('deals')->where('id', $rating->deal_id)->exists();
                    $ratedUserExists = $this->targetConnection->table('users')->where('id', $rating->rated_user_id)->exists();
                    $raterUserExists = $this->targetConnection->table('users')->where('id', $rating->rater_user_id)->exists();
                    
                    if (!$dealExists || !$ratedUserExists || !$raterUserExists) {
                        $this->warn("⚠️ Пропускаем рейтинг ID {$rating->id} - отсутствуют связанные записи");
                        $progressBar->advance();
                        continue;
                    }
                    
                    if (!$this->isDryRun) {
                        $this->targetConnection->table('ratings')->updateOrInsert(
                            ['id' => $rating->id],
                            $ratingData
                        );
                    }
                    
                    $migratedCount++;
                    $progressBar->advance();
                }
            });
        
        $progressBar->finish();
        $this->newLine();
        $this->info("✅ Перенесено рейтингов: {$migratedCount}");
    }

    private function migrateVerificationCodes()
    {
        $this->info('🔐 Переносим коды верификации...');
        
        $userColumns = Schema::connection('source_mysql')->getColumnListing('users');
        $verificationFields = array_intersect($userColumns, [
            'verification_code', 'email_verification_code', 'phone_verification_code',
            'verification_code_expires_at', 'verification_code_used_at', 'temp_phone', 'cod'
        ]);
        
        if (empty($verificationFields)) {
            $this->warn('⚠️ Поля кодов верификации не найдены в старой структуре users');
            return;
        }
        
        $usersWithCodes = $this->sourceConnection->table('users')
            ->where(function($query) use ($verificationFields) {
                if (in_array('verification_code', $verificationFields)) {
                    $query->whereNotNull('verification_code');
                }
                if (in_array('email_verification_code', $verificationFields)) {
                    $query->orWhereNotNull('email_verification_code');
                }
                if (in_array('phone_verification_code', $verificationFields)) {
                    $query->orWhereNotNull('phone_verification_code');
                }
                if (in_array('temp_phone', $verificationFields)) {
                    $query->orWhereNotNull('temp_phone');
                }
                if (in_array('cod', $verificationFields)) {
                    $query->orWhereNotNull('cod');
                }
            })
            ->get();
            
        $this->info("Найдено пользователей с кодами верификации: " . $usersWithCodes->count());
        
        $migratedCount = 0;
        foreach ($usersWithCodes as $user) {
            // Проверяем, существует ли пользователь в целевой БД
            $userExists = $this->targetConnection->table('users')->where('id', $user->id)->exists();
            if (!$userExists) {
                $this->warn("⚠️ Пропускаем коды верификации для пользователя ID {$user->id} - пользователь не существует в целевой БД");
                continue;
            }
            
            // Email verification code
            if (in_array('email_verification_code', $verificationFields) && !empty($user->email_verification_code)) {
                $this->insertVerificationCode($user, $user->email_verification_code, VerificationType::EMAIL);
                $migratedCount++;
            }
            
            // Phone verification code  
            if (in_array('phone_verification_code', $verificationFields) && !empty($user->phone_verification_code)) {
                $this->insertVerificationCode($user, $user->phone_verification_code, VerificationType::PHONE);
                $migratedCount++;
            }
            
            // Generic verification code
            if (in_array('verification_code', $verificationFields) && !empty($user->verification_code) && 
                (!isset($user->email_verification_code) || empty($user->email_verification_code)) && 
                (!isset($user->phone_verification_code) || empty($user->phone_verification_code))) {
                $this->insertVerificationCode($user, $user->verification_code, VerificationType::LOGIN);
                $migratedCount++;
            }
            
            // Temporary phone verification (cod field for temp_phone)
            if (in_array('temp_phone', $verificationFields) && in_array('cod', $verificationFields) && 
                !empty($user->temp_phone) && !empty($user->cod)) {
                $this->insertPhoneVerificationCode($user, $user->cod, $user->temp_phone);
                $migratedCount++;
            }
        }
        
        $this->info("✅ Перенесено кодов верификации: {$migratedCount}");
    }

    private function insertVerificationCode($user, $code, $type)
    {
        if ($this->isDryRun) return;
        
        $this->targetConnection->table('verification_codes')->insert([
            'user_id' => $user->id,
            'code' => $code,
            'type' => $type->value,
            'expires_at' => (isset($user->verification_code_expires_at) ? $user->verification_code_expires_at : null) ?? now()->addHours(24),
            'used_at' => (isset($user->verification_code_used_at) ? $user->verification_code_used_at : null),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    }

    private function insertPhoneVerificationCode($user, $code, $tempPhone)
    {
        if ($this->isDryRun) return;
        
        $this->targetConnection->table('verification_codes')->insert([
            'user_id' => $user->id,
            'code' => $code,
            'type' => VerificationType::PHONE_UPDATE->value,
            'expires_at' => (isset($user->verification_code_expires_at) ? $user->verification_code_expires_at : null) ?? now()->addHours(24),
            'used_at' => (isset($user->verification_code_used_at) ? $user->verification_code_used_at : null),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
        
        // Можно добавить дополнительную запись с информацией о временном телефоне
        // Но для этого нужно расширить схему verification_codes или создать отдельную таблицу
    }

    private function migrateUserSessions()
    {
        $this->info('💻 Переносим пользовательские сессии...');
        
        $userColumns = Schema::connection('source_mysql')->getColumnListing('users');
        $sessionFields = array_intersect($userColumns, ['last_seen_at', 'last_login_at', 'ip_address', 'user_agent']);
        
        if (empty($sessionFields)) {
            $this->warn('⚠️ Поля сессий не найдены в старой структуре users');
            return;
        }
        
        $usersWithSessions = $this->sourceConnection->table('users')
            ->where(function($query) use ($sessionFields) {
                if (in_array('last_seen_at', $sessionFields)) {
                    $query->whereNotNull('last_seen_at');
                }
                if (in_array('last_login_at', $sessionFields)) {
                    $query->orWhereNotNull('last_login_at');
                }
            })
            ->get();
            
        $this->info("Найдено пользователей с данными сессий: " . $usersWithSessions->count());
        
        $migratedCount = 0;
        foreach ($usersWithSessions as $user) {
            // Проверяем, существует ли пользователь в целевой БД
            $userExists = $this->targetConnection->table('users')->where('id', $user->id)->exists();
            if (!$userExists) {
                $this->warn("⚠️ Пропускаем сессию для пользователя ID {$user->id} - пользователь не существует в целевой БД");
                continue;
            }
            
            if (!$this->isDryRun) {
                $this->targetConnection->table('user_sessions')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'user_id' => $user->id,
                        'last_seen_at' => (isset($user->last_seen_at) ? $user->last_seen_at : null) ?? 
                                         (isset($user->last_login_at) ? $user->last_login_at : null) ?? 
                                         $user->updated_at,
                        'device_info' => null,
                        'ip_address' => (isset($user->ip_address) ? $user->ip_address : null),
                        'user_agent' => (isset($user->user_agent) ? $user->user_agent : null),
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ]
                );
            }
            $migratedCount++;
        }
        
        $this->info("✅ Перенесено пользовательских сессий: {$migratedCount}");
    }

    private function migrateDeals()
    {
        $this->info('💼 Переносим сделки...');
        
        $totalDeals = $this->sourceConnection->table('deals')->count();
        $this->info("Найдено сделок: {$totalDeals}");
        
        if ($totalDeals === 0) {
            $this->warn('⚠️ Сделки не найдены в исходной БД');
            return;
        }
        
        $progressBar = $this->output->createProgressBar($totalDeals);
        $migratedCount = 0;
        
        $this->sourceConnection->table('deals')
            ->orderBy('id')
            ->chunk($this->chunkSize, function ($deals) use (&$migratedCount, $progressBar) {
                foreach ($deals as $deal) {
                    $dealData = (array) $deal;
                    
                    // Remove client fields that are now in separate table
                    $clientFields = ['client_name', 'client_phone', 'client_email', 'client_city', 'client_timezone', 'client_info', 'client_account_link'];
                    foreach ($clientFields as $field) {
                        unset($dealData[$field]);
                    }
                    
                    // Проверяем foreign key constraints для всех связей с пользователями
                    $userFieldsToCheck = [
                        'user_id', 
                        'coordinator_id', 
                        'designer_id', 
                        'architect_id', 
                        'visualizer_id', 
                        'office_partner_id',
                        'client_id',
                        'deleted_user_id'
                    ];
                    $skipDeal = false;
                    
                    foreach ($userFieldsToCheck as $field) {
                        if (!empty($deal->$field)) {
                            $userExists = $this->targetConnection->table('users')->where('id', $deal->$field)->exists();
                            if (!$userExists) {
                                $this->warn("⚠️ Пропускаем сделку ID {$deal->id} - пользователь {$field}={$deal->$field} не существует");
                                $skipDeal = true;
                                break;
                            }
                        }
                    }
                    
                    if ($skipDeal) {
                        $progressBar->advance();
                        continue;
                    }
                    
                    if (!$this->isDryRun) {
                        
                        Deal::withoutEvents(function () use ($deal, $dealData) {
                            // Убираем chat_id, если он есть
                            unset($dealData['chat_id']);
                        
                            return Deal::updateOrCreate(
                                ['id' => $deal->id],
                                $dealData
                            );
                        });
                    }
                    
                    $migratedCount++;
                    $progressBar->advance();
                }
            });
        
        $progressBar->finish();
        $this->newLine();
        $this->info("✅ Перенесено сделок: {$migratedCount}");
    }

    private function migrateDealClients()
    {
        $this->info('👤 Переносим данные клиентов из сделок...');
        
        $dealColumns = Schema::connection('source_mysql')->getColumnListing('deals');
        $clientFields = array_intersect($dealColumns, ['client_name', 'client_phone', 'client_email', 'client_city', 'client_timezone', 'client_info', 'client_account_link']);
        
        if (empty($clientFields)) {
            $this->warn('⚠️ Клиентские поля не найдены в старой структуре deals');
            return;
        }
        
        $dealsWithClients = $this->sourceConnection->table('deals')
            ->where(function($query) {
                $query->whereNotNull('client_name')->orWhereNotNull('client_phone');
            })
            ->get();
            
        $this->info("Найдено сделок с данными клиентов: " . $dealsWithClients->count());
        
        $migratedCount = 0;
        foreach ($dealsWithClients as $deal) {
            if (empty($deal->client_name) && empty($deal->client_phone)) {
                continue;
            }
            
            // Проверяем, существует ли сделка в целевой БД
            $dealExists = $this->targetConnection->table('deals')->where('id', $deal->id)->exists();
            if (!$dealExists) {
                $this->warn("⚠️ Пропускаем клиента для сделки ID {$deal->id} - сделка не существует в целевой БД");
                continue;
            }
            
            if (!$this->isDryRun) {
                $this->targetConnection->table('deal_clients')->updateOrInsert(
                    ['deal_id' => $deal->id],
                    [
                        'deal_id' => $deal->id,
                        'name' => $deal->client_name ?? 'Неизвестный клиент',
                        'phone' => $deal->client_phone ?? '',
                        'email' => $deal->client_email,
                        'city' => $deal->client_city,
                        'timezone' => $deal->client_timezone,
                        'info' => $deal->client_info,
                        'account_link' => $deal->client_account_link,
                        'created_at' => $deal->created_at,
                        'updated_at' => $deal->updated_at,
                    ]
                );
            }
            $migratedCount++;
        }
        
        $this->info("✅ Перенесено данных клиентов: {$migratedCount}");
    }
}
