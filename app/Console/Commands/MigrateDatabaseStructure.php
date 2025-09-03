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
        
        foreach (['users', 'deals', 'news', 'ratings', 'commons', 'commercials'] as $table) {
            if (!Schema::connection('source_mysql')->hasTable($table)) {
                throw new Exception("Таблица {$table} не найдена в исходной БД");
            }
        }
        
        foreach (['verification_codes', 'user_sessions', 'deal_clients', 'news', 'ratings', 'briefs', 'brief_questions', 'brief_answers', 'brief_rooms', 'brief_documents'] as $table) {
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
        $this->migrateCommons();
        $this->migrateCommercials();
        $this->migrateVerificationCodes();
        $this->migrateUserSessions();
        
        // Настраиваем auto_increment для корректной работы новых записей
        if (!$this->isDryRun) {
            $this->updateAutoIncrementValues();
        }
        
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
                    
                    if (!$this->isDryRun) {
                        try {
                            // Получаем список полей в целевой таблице
                            $targetColumns = Schema::connection('target_mysql')->getColumnListing('users');
                            
                            // Фильтруем данные - оставляем только поля, которые существуют в целевой таблице
                            $filteredData = array_intersect_key($userData, array_flip($targetColumns));
                            
                            // Сохраняем оригинальный ID из исходной БД
                            $filteredData['id'] = $user->id;
                            
                            $this->targetConnection->table('users')->updateOrInsert(
                                ['id' => $user->id],
                                $filteredData
                            );
                        } catch (\Exception $e) {
                            $this->error("❌ Ошибка при переносе пользователя ID {$user->id}: " . $e->getMessage());
                            $progressBar->advance();
                            continue;
                        }
                    }
                    
                    $migratedCount++;
                    $progressBar->advance();
                }
            });
        
        $progressBar->finish();
        $this->newLine();
        $this->info("✅ Перенесено пользователей: {$migratedCount}");
        
        // Проверяем, есть ли пропущенные пользователи
        $skippedCount = $totalUsers - $migratedCount;
        if ($skippedCount > 0) {
            $this->warn("⚠️ Пропущено пользователей: {$skippedCount}");
        }
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
                        try {
                            // Получаем список полей в целевой таблице
                            $targetColumns = Schema::connection('target_mysql')->getColumnListing('deals');
                            
                            // Фильтруем данные - оставляем только поля, которые существуют в целевой таблице
                            $filteredData = array_intersect_key($dealData, array_flip($targetColumns));
                            
                            // Сохраняем оригинальный ID из исходной БД
                            $filteredData['id'] = $deal->id;
                            
                            $this->targetConnection->table('deals')->updateOrInsert(
                                ['id' => $deal->id],
                                $filteredData
                            );
                        } catch (\Exception $e) {
                            $this->error("❌ Ошибка при переносе сделки ID {$deal->id}: " . $e->getMessage());
                            $progressBar->advance();
                            continue;
                        }
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

    private function updateAutoIncrementValues()
    {
        $this->info('🔧 Настраиваем значения AUTO_INCREMENT...');
        
        $tables = ['users', 'deals', 'news', 'ratings', 'briefs'];
        
        foreach ($tables as $table) {
            try {
                // Получаем максимальный ID из таблицы
                $maxId = $this->targetConnection->table($table)->max('id');
                
                if ($maxId) {
                    $nextAutoIncrement = $maxId + 1;
                    
                    // Устанавливаем следующее значение AUTO_INCREMENT
                    $this->targetConnection->statement("ALTER TABLE {$table} AUTO_INCREMENT = {$nextAutoIncrement}");
                    
                    $this->info("✅ {$table}: AUTO_INCREMENT установлен на {$nextAutoIncrement}");
                }
            } catch (\Exception $e) {
                $this->warn("⚠️ Не удалось обновить AUTO_INCREMENT для таблицы {$table}: " . $e->getMessage());
            }
        }
    }

    private function migrateCommons()
    {
        $this->info('📄 Переносим общие брифы (Commons)...');
        
        $commons = $this->sourceConnection->table('commons')->orderBy('id')->get();
        $totalCommons = $commons->count();
        
        if ($totalCommons === 0) {
            $this->info('ℹ️ Общие брифы не найдены в исходной БД');
            return;
        }
        
        $this->info("📊 Найдено общих брифов: {$totalCommons}");
        $progressBar = $this->output->createProgressBar($totalCommons);
        $progressBar->start();
        
        $migratedCount = 0;
        $skippedCount = 0;
        
        foreach ($commons as $common) {
            // Проверяем существование пользователя
            $userExists = $this->targetConnection->table('users')->where('id', $common->user_id)->exists();
            if (!$userExists) {
                $this->warn("⚠️ Пропускаем общий бриф ID {$common->id} - пользователь {$common->user_id} не существует");
                $skippedCount++;
                $progressBar->advance();
                continue;
            }
            
            if (!$this->isDryRun) {
                try {
                    // Находим сделку, которая ссылается на этот Common бриф
                    $dealId = $this->findDealByCommonId($common->id);
                    
                    // Создаем бриф в новой структуре
                    $briefData = [
                        'id' => $common->id,
                        'user_id' => $common->user_id,
                        'deal_id' => $dealId,
                        'type' => \App\Enums\Briefs\BriefType::COMMON->value,
                        'title' => $common->title,
                        'description' => $common->description,
                        'status' => \App\Enums\Briefs\BriefStatus::fromLabel($common->status),
                        'article' => $common->article,
                        'zones' => null, // Общие брифы не используют зоны
                        'total_area' => null,
                        'price' => $this->processPriceValue($common->price),
                        'preferences' => null, // Общие брифы не используют preferences в JSON
                        'created_at' => $common->created_at,
                        'updated_at' => $common->updated_at,
                    ];
                    
                    $this->targetConnection->table('briefs')->updateOrInsert(
                        ['id' => $common->id],
                        $briefData
                    );
                    
                    // Создаем комнаты из DEFAULT_ROOMS для вопросов страницы 3
                    $this->createBriefRooms($common->id, $common);
                    
                    // Переносим все ответы на вопросы
                    $this->migrateCommonAnswers($common);
                    
                    // Переносим документы (references) в BriefDocument
                    $this->migrateCommonDocuments($common);
                    
                } catch (\Exception $e) {
                    $this->error("❌ Ошибка при переносе общего брифа ID {$common->id}: " . $e->getMessage());
                    $skippedCount++;
                    $progressBar->advance();
                    continue;
                }
            }
            
            $migratedCount++;
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine();
        $this->info("✅ Перенесено общих брифов: {$migratedCount}");
        if ($skippedCount > 0) {
            $this->warn("⚠️ Пропущено общих брифов: {$skippedCount}");
        }
    }

    private function createBriefRooms($briefId, $common)
    {
        $roomIdCounter = 1;
        
        // Создаем комнаты из поля rooms (основные комнаты брифа)
        if (!empty($common->rooms)) {
            $rooms = json_decode($common->rooms, true);
            if (is_array($rooms)) {
                foreach ($rooms as $roomKey => $roomTitle) {
                    $this->targetConnection->table('brief_rooms')->updateOrInsert(
                        ['brief_id' => $briefId, 'key' => $roomKey],
                        [
                            'brief_id' => $briefId,
                            'key' => $roomKey,
                            'title' => $roomTitle,
                            'created_at' => \Carbon\Carbon::now(),
                            'updated_at' => \Carbon\Carbon::now(),
                        ]
                    );
                    $roomIdCounter++;
                }
            }
        }
        
        // Обрабатываем кастомные комнаты из поля custom_rooms
        if (!empty($common->custom_rooms)) {
            $customRooms = json_decode($common->custom_rooms, true);
            if (is_array($customRooms)) {
                foreach ($customRooms as $index => $customRoom) {
                    $customKey = 'custom_room_' . ($index + 1);
                    $customTitle = is_array($customRoom) ? 
                        ($customRoom['title'] ?? 'Кастомная комната ' . ($index + 1)) : 
                        (string)$customRoom;
                        
                    $this->targetConnection->table('brief_rooms')->updateOrInsert(
                        ['brief_id' => $briefId, 'key' => $customKey],
                        [
                            'brief_id' => $briefId,
                            'key' => $customKey,
                            'title' => $customTitle,
                            'created_at' => \Carbon\Carbon::now(),
                            'updated_at' => \Carbon\Carbon::now(),
                        ]
                    );
                    $roomIdCounter++;
                }
            }
        }
    }

    private function migrateCommonAnswers($common)
    {
        // Получаем все поля question_X_Y из Common
        $commonData = (array) $common;
        
        // Получаем список комнат и их ID для привязки ответов 3-й страницы
        $roomsMapping = $this->getCommonRoomsMapping($common);
        
        foreach ($commonData as $field => $value) {
            if (preg_match('/^question_(\d+)_(\d+)$/', $field, $matches) && !empty($value)) {
                $page = (int) $matches[1];
                $order = (int) $matches[2];
                $questionKey = $this->getCommonQuestionKey($page, $order);
                
                if ($questionKey) {
                    // Для question_3_X (комнаты) привязываем к соответствующей комнате
                    if ($page === 3) {
                        // Находим комнату по порядку среди существующих комнат
                        $roomId = $this->getRoomIdByOrder($roomsMapping, $order);
                        
                        if ($roomId) {
                            $this->targetConnection->table('brief_answers')->updateOrInsert(
                                [
                                    'brief_id' => $common->id,
                                    'room_id' => $roomId,
                                    'question_key' => 'room' // Все вопросы комнат используют ключ 'room'
                                ],
                                [
                                    'answer_text' => $value,
                                    'created_at' => \Carbon\Carbon::now(),
                                    'updated_at' => \Carbon\Carbon::now(),
                                ]
                            );
                        }
                    } else {
                        // Для остальных вопросов без привязки к комнате
                        $this->targetConnection->table('brief_answers')->updateOrInsert(
                            [
                                'brief_id' => $common->id,
                                'room_id' => null,
                                'question_key' => $questionKey
                            ],
                            [
                                'answer_text' => $value,
                                'created_at' => \Carbon\Carbon::now(),
                                'updated_at' => \Carbon\Carbon::now(),
                            ]
                        );
                    }
                }
            }
        }
        
        // Обрабатываем кастомные ответы комнат из custom_room_answers
        $this->migrateCustomRoomAnswers($common, $roomsMapping);
    }

    private function getCommonRoomsMapping($common)
    {
        $roomsMapping = [];
        $roomIdCounter = 1;
        
        // Получаем основные комнаты из поля rooms
        if (!empty($common->rooms)) {
            $rooms = json_decode($common->rooms, true);
            if (is_array($rooms)) {
                foreach ($rooms as $roomKey => $roomTitle) {
                    // Получаем реальный ID комнаты из БД
                    $room = $this->targetConnection->table('brief_rooms')
                        ->where('brief_id', $common->id)
                        ->where('key', $roomKey)
                        ->first();
                    
                    if ($room) {
                        $roomsMapping[] = [
                            'id' => $room->id,
                            'key' => $roomKey,
                            'title' => $roomTitle,
                            'order' => $roomIdCounter
                        ];
                    }
                    $roomIdCounter++;
                }
            }
        }
        
        // Получаем кастомные комнаты
        if (!empty($common->custom_rooms)) {
            $customRooms = json_decode($common->custom_rooms, true);
            if (is_array($customRooms)) {
                foreach ($customRooms as $index => $customRoom) {
                    $customKey = 'custom_room_' . ($index + 1);
                    $customTitle = is_array($customRoom) ? 
                        ($customRoom['title'] ?? 'Кастомная комната ' . ($index + 1)) : 
                        (string)$customRoom;
                    
                    // Получаем реальный ID кастомной комнаты из БД
                    $room = $this->targetConnection->table('brief_rooms')
                        ->where('brief_id', $common->id)
                        ->where('key', $customKey)
                        ->first();
                    
                    if ($room) {
                        $roomsMapping[] = [
                            'id' => $room->id,
                            'key' => $customKey,
                            'title' => $customTitle,
                            'order' => $roomIdCounter,
                            'is_custom' => true
                        ];
                    }
                    $roomIdCounter++;
                }
            }
        }
        
        return $roomsMapping;
    }

    private function getRoomIdByOrder($roomsMapping, $order)
    {
        // Находим комнату по порядку (question_3_1 соответствует первой комнате и т.д.)
        foreach ($roomsMapping as $room) {
            if ($room['order'] === $order) {
                return $room['id'];
            }
        }
        return null;
    }

    private function migrateCustomRoomAnswers($common, $roomsMapping)
    {
        if (empty($common->custom_room_answers)) {
            return;
        }
        
        $customRoomAnswers = json_decode($common->custom_room_answers, true);
        if (!is_array($customRoomAnswers)) {
            return;
        }
        
        // Находим кастомные комнаты в mapping
        $customRooms = array_filter($roomsMapping, function($room) {
            return isset($room['is_custom']) && $room['is_custom'];
        });
        
        $customIndex = 0;
        foreach ($customRoomAnswers as $answer) {
            if ($customIndex < count($customRooms)) {
                $room = array_values($customRooms)[$customIndex];
                
                if (!empty($answer)) {
                    $this->targetConnection->table('brief_answers')->updateOrInsert(
                        [
                            'brief_id' => $common->id,
                            'room_id' => $room['id'],
                            'question_key' => 'room'
                        ],
                        [
                            'answer_text' => $answer,
                            'created_at' => \Carbon\Carbon::now(),
                            'updated_at' => \Carbon\Carbon::now(),
                        ]
                    );
                }
                $customIndex++;
            }
        }
    }

    private function getCommonQuestionKey($page, $order)
    {
        // Соответствие страниц и порядка вопросов их ключам в новой системе
        $questionMapping = [
            // Страница 1
            1 => [
                1 => 'question_1_1', // Сколько человек будет проживать
                2 => 'question_1_2', // Домашние животные
                3 => 'question_1_3', // Хобби
                4 => 'question_1_4', // Перепланировка
                5 => 'question_1_5', // Гости
                6 => 'question_1_6', // Адрес
            ],
            // Страница 2
            2 => [
                1 => 'question_2_1', // Стиль интерьера
                2 => 'question_2_2', // Референсы
                3 => 'question_2_3', // Ценовой сегмент
                4 => 'question_2_4', // Что не должно быть
                5 => 'question_2_5', // Бюджет
            ],
            // Страница 3 - комнаты используют ключ 'room'
            3 => [
                1 => 'room', 2 => 'room', 3 => 'room', 4 => 'room', 5 => 'room',
                6 => 'room', 7 => 'room', 8 => 'room', 9 => 'room', 10 => 'room',
                11 => 'room', 12 => 'room', 13 => 'room', 14 => 'room'
            ],
            // Страница 4
            4 => [
                1 => 'question_4_1', // Напольные покрытия
                2 => 'question_4_2', // Двери
                3 => 'question_4_3', // Отделка стен
                4 => 'question_4_4', // Освещение
                5 => 'question_4_5', // Потолки
                6 => 'question_4_6', // Дополнительные пожелания
            ],
            // Страница 5
            5 => [
                1 => 'question_5_1', // Звукоизоляция
                2 => 'question_5_2', // Теплые полы
                3 => 'question_5_3', // Радиаторы
                4 => 'question_5_4', // Водоснабжение
                5 => 'question_5_5', // Кондиционирование
                6 => 'question_5_6', // Сети
            ]
        ];
        
        return $questionMapping[$page][$order] ?? null;
    }



    private function migrateCommercials()
    {
        $this->info('🏢 Переносим коммерческие брифы (Commercials)...');
        
        $commercials = $this->sourceConnection->table('commercials')->orderBy('id')->get();
        $totalCommercials = $commercials->count();
        
        if ($totalCommercials === 0) {
            $this->info('ℹ️ Коммерческие брифы не найдены в исходной БД');
            return;
        }
        
        $this->info("📊 Найдено коммерческих брифов: {$totalCommercials}");
        $progressBar = $this->output->createProgressBar($totalCommercials);
        $progressBar->start();
        
        $migratedCount = 0;
        $skippedCount = 0;
        
        foreach ($commercials as $commercial) {
            // Проверяем существование пользователя
            $userExists = $this->targetConnection->table('users')->where('id', $commercial->user_id)->exists();
            if (!$userExists) {
                $this->warn("⚠️ Пропускаем коммерческий бриф ID {$commercial->id} - пользователь {$commercial->user_id} не существует");
                $skippedCount++;
                $progressBar->advance();
                continue;
            }
            
            if (!$this->isDryRun) {
                try {
                    // Находим сделку, которая ссылается на этот Commercial бриф
                    $dealId = $this->findDealByCommercialId($commercial->id);
                    
                    // Создаем бриф в новой структуре
                    $briefData = [
                        'id' => $commercial->id,
                        'user_id' => $commercial->user_id,
                        'deal_id' => $dealId,
                        'type' => \App\Enums\Briefs\BriefType::COMMERCIAL->value,
                        'title' => $commercial->title,
                        'description' => $commercial->description,
                        'status' => \App\Enums\Briefs\BriefStatus::fromLabel($commercial->status),
                        'article' => $commercial->article,
                        'zones' => $commercial->zones,
                        'total_area' => $commercial->total_area,
                        'price' => $this->processPriceValue($commercial->price),
                        'preferences' => $commercial->preferences,
                        'created_at' => $commercial->created_at,
                        'updated_at' => $commercial->updated_at,
                    ];
                    
                    $this->targetConnection->table('briefs')->updateOrInsert(
                        ['id' => $commercial->id],
                        $briefData
                    );
                    
                    // Создаем комнаты из zones для коммерческого брифа
                    $this->createCommercialBriefRooms($commercial->id, $commercial);
                    
                    // Переносим общие поля projected_area и total_area
                    $this->migrateCommercialGeneralFields($commercial);
                    
                                        // Переносим ответы на вопросы 
                    $this->migrateCommercialAnswers($commercial);
                    
                    // Переносим документы в BriefDocument
                    $this->migrateCommercialDocuments($commercial);
                    
                } catch (\Exception $e) {
                    $this->error("❌ Ошибка при переносе коммерческого брифа ID {$commercial->id}: " . $e->getMessage());
                    $skippedCount++;
                    $progressBar->advance();
                    continue;
                }
            }
            
            $migratedCount++;
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine();
        $this->info("✅ Перенесено коммерческих брифов: {$migratedCount}");
        if ($skippedCount > 0) {
            $this->warn("⚠️ Пропущено коммерческих брифов: {$skippedCount}");
        }
    }

    private function createCommercialBriefRooms($briefId, $commercial)
    {
        if (empty($commercial->zones)) {
            return;
        }
        
        $zones = json_decode($commercial->zones, true);
        if (!is_array($zones)) {
            return;
        }
        
        $roomIdCounter = 1;
        foreach ($zones as $zone) {
            $zoneKey = 'zone_' . $roomIdCounter;
            $zoneName = $zone['name'] ?? 'Зона ' . $roomIdCounter;
            
            $this->targetConnection->table('brief_rooms')->updateOrInsert(
                ['brief_id' => $briefId, 'key' => $zoneKey],
                [
                    'brief_id' => $briefId,
                    'key' => $zoneKey,
                    'title' => $zoneName,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]
            );
            
            // Получаем ID созданной комнаты для связи с ответами
            $createdRoom = $this->targetConnection->table('brief_rooms')
                ->where('brief_id', $briefId)
                ->where('key', $zoneKey)
                ->first();
            
            if ($createdRoom) {
                // Создаем ответы для дополнительных полей зоны
                if (!empty($zone['description'])) {
                    $this->targetConnection->table('brief_answers')->updateOrInsert(
                        [
                            'brief_id' => $briefId,
                            'room_id' => $createdRoom->id,
                            'question_key' => 'zone_names'
                        ],
                        [
                            'answer_text' => $zone['description'],
                            'created_at' => \Carbon\Carbon::now(),
                            'updated_at' => \Carbon\Carbon::now(),
                        ]
                    );
                }
                
                if (!empty($zone['total_area'])) {
                    $this->targetConnection->table('brief_answers')->updateOrInsert(
                        [
                            'brief_id' => $briefId,
                            'room_id' => $createdRoom->id,
                            'question_key' => 'total_area'
                        ],
                        [
                            'answer_text' => $zone['total_area'],
                            'created_at' => \Carbon\Carbon::now(),
                            'updated_at' => \Carbon\Carbon::now(),
                        ]
                    );
                }
                
                if (!empty($zone['projected_area'])) {
                    $this->targetConnection->table('brief_answers')->updateOrInsert(
                        [
                            'brief_id' => $briefId,
                            'room_id' => $createdRoom->id,
                            'question_key' => 'project_area'
                        ],
                        [
                            'answer_text' => $zone['projected_area'],
                            'created_at' => \Carbon\Carbon::now(),
                            'updated_at' => \Carbon\Carbon::now(),
                        ]
                    );
                }
            }
            
            $roomIdCounter++;
        }
    }

    private function migrateCommercialGeneralFields($commercial)
    {
        // Переносим общие поля projected_area и total_area как ответы без привязки к зонам
        if (!empty($commercial->projected_area)) {
            $this->targetConnection->table('brief_answers')->updateOrInsert(
                [
                    'brief_id' => $commercial->id,
                    'room_id' => null,
                    'question_key' => 'project_area'
                ],
                [
                    'answer_text' => $commercial->projected_area,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]
            );
        }
        
        if (!empty($commercial->total_area)) {
            $this->targetConnection->table('brief_answers')->updateOrInsert(
                [
                    'brief_id' => $commercial->id,
                    'room_id' => null,
                    'question_key' => 'total_area'
                ],
                [
                    'answer_text' => $commercial->total_area,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]
            );
        }
    }

    private function migrateCommercialAnswers($commercial)
    {
        // Получаем все поля question_X_Y из Commercial
        $commercialData = (array) $commercial;
        
        // Получаем список зон для привязки ответов
        $zones = [];
        if (!empty($commercial->zones)) {
            $zonesData = json_decode($commercial->zones, true);
            if (is_array($zonesData)) {
                foreach ($zonesData as $index => $zone) {
                    $zoneKey = 'zone_' . ($index + 1);
                    
                    // Получаем реальный ID комнаты из БД
                    $room = $this->targetConnection->table('brief_rooms')
                        ->where('brief_id', $commercial->id)
                        ->where('key', $zoneKey)
                        ->first();
                    
                    if ($room) {
                        $zones[$zoneKey] = $room->id;
                    }
                }
            }
        }
        
        foreach ($commercialData as $field => $value) {
            if (preg_match('/^question_(\d+)_(\d+)$/', $field, $matches) && !empty($value)) {
                $page = (int) $matches[1];
                $order = (int) $matches[2];
                
                // Для коммерческих брифов находим вопрос по странице и порядку
                $questionKey = $this->getCommercialQuestionKey($page, $order);
                
                if ($questionKey) {
                    // Для коммерческих брифов каждый вопрос применяется ко всем зонам
                    if (!empty($zones)) {
                        foreach ($zones as $zoneKey => $roomId) {
                            $this->targetConnection->table('brief_answers')->updateOrInsert(
                                [
                                    'brief_id' => $commercial->id,
                                    'room_id' => $roomId,
                                    'question_key' => $questionKey
                                ],
                                [
                                    'answer_text' => $value,
                                    'created_at' => \Carbon\Carbon::now(),
                                    'updated_at' => \Carbon\Carbon::now(),
                                ]
                            );
                        }
                    } else {
                        // Если нет зон, сохраняем ответ без привязки к комнате
                        $this->targetConnection->table('brief_answers')->updateOrInsert(
                            [
                                'brief_id' => $commercial->id,
                                'room_id' => null,
                                'question_key' => $questionKey
                            ],
                            [
                                'answer_text' => $value,
                                'created_at' => \Carbon\Carbon::now(),
                                'updated_at' => \Carbon\Carbon::now(),
                            ]
                        );
                    }
                }
            }
        }
    }

    private function getCommercialQuestionKey($page, $order)
    {
        // Для коммерческих брифов ключ вопроса соответствует странице_порядку
        // Так как у нас только question_1_2 в миграции, но может быть больше
        // Возвращаем общий ключ на основе страницы
        $pageQuestionKeys = [
            1 => 'zone_1',     // Зоны и их функционал
            2 => 'zone_2',     // Метраж зон
            3 => 'zone_3',     // Зоны и их стиль оформления
            4 => 'zone_4',     // Мебилировка зон
            5 => 'zone_5',     // Предпочтения отделочных материалов
            6 => 'zone_6',     // Освещение зон
            7 => 'zone_7',     // Кондиционирование зон
            8 => 'zone_8',     // Напольное покрытие зон
        ];
        
        return $pageQuestionKeys[$page] ?? "question_{$page}_{$order}";
    }

    private function processPriceValue($price)
    {
        if (empty($price)) {
            return null;
        }
        
        // Конвертируем в число
        $numericPrice = is_numeric($price) ? (float) $price : null;
        
        if ($numericPrice === null) {
            return null;
        }
        
        // Максимальное значение для INTEGER в MySQL (2147483647)
        $maxIntValue = 2147483647;
        
        if ($numericPrice > $maxIntValue) {
            // Если значение слишком большое, ограничиваем его
            $this->warn("⚠️ Значение price {$numericPrice} превышает максимально допустимое, устанавливаем {$maxIntValue}");
            return $maxIntValue;
        }
        
        return (int) $numericPrice;
    }

    private function findDealByCommonId($commonId)
    {
        $deal = $this->targetConnection->table('deals')
            ->where('common_id', $commonId)
            ->first();
        
        return $deal ? $deal->id : null;
    }

    private function findDealByCommercialId($commercialId)
    {
        $deal = $this->targetConnection->table('deals')
            ->where('commercial_id', $commercialId)
            ->first();
        
        return $deal ? $deal->id : null;
    }

    private function migrateCommonDocuments($common)
    {
        if (empty($common->references)) {
            return;
        }

        $references = json_decode($common->references, true);
        if (!is_array($references)) {
            return;
        }

        foreach ($references as $referenceUrl) {
            if (empty($referenceUrl)) {
                continue;
            }

            // Извлекаем имя файла из URL
            $originalName = $this->extractFileNameFromUrl($referenceUrl);
            
            // Определяем MIME тип по расширению файла
            $mimeType = $this->getMimeTypeFromExtension($originalName);

            $this->targetConnection->table('brief_documents')->updateOrInsert(
                [
                    'brief_id' => $common->id,
                    'filepath' => $referenceUrl
                ],
                [
                    'brief_id' => $common->id,
                    'original_name' => $originalName,
                    'filepath' => $referenceUrl,
                    'mime_type' => $mimeType,
                    'file_size' => 0, // Размер файла неизвестен для внешних ссылок
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]
            );
        }
    }

    private function migrateCommercialDocuments($commercial)
    {
        if (empty($commercial->documents)) {
            return;
        }

        $documents = json_decode($commercial->documents, true);
        if (!is_array($documents)) {
            return;
        }

        foreach ($documents as $documentUrl) {
            if (empty($documentUrl)) {
                continue;
            }

            // Извлекаем имя файла из URL
            $originalName = $this->extractFileNameFromUrl($documentUrl);
            
            // Определяем MIME тип по расширению файла
            $mimeType = $this->getMimeTypeFromExtension($originalName);

            $this->targetConnection->table('brief_documents')->updateOrInsert(
                [
                    'brief_id' => $commercial->id,
                    'filepath' => $documentUrl
                ],
                [
                    'brief_id' => $commercial->id,
                    'original_name' => $originalName,
                    'filepath' => $documentUrl,
                    'mime_type' => $mimeType,
                    'file_size' => 0, // Размер файла неизвестен для внешних ссылок
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]
            );
        }
    }

    private function extractFileNameFromUrl($url)
    {
        // Если это URL с параметрами, попробуем извлечь имя файла
        if (str_contains($url, '?')) {
            $parsedUrl = parse_url($url);
            if (isset($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $queryParams);
                // Проверяем параметры на наличие имени файла
                if (isset($queryParams['filename'])) {
                    return $queryParams['filename'];
                }
                if (isset($queryParams['path'])) {
                    return basename($queryParams['path']);
                }
            }
            $url = strtok($url, '?'); // Убираем параметры запроса
        }

        $fileName = basename($url);
        
        // Если имя файла пустое или содержит только расширение, создаем базовое имя
        if (empty($fileName) || $fileName === '.' || str_starts_with($fileName, '.')) {
            return 'document.pdf';
        }

        return $fileName;
    }

    private function getMimeTypeFromExtension($fileName)
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        return match ($extension) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'txt' => 'text/plain',
            'zip' => 'application/zip',
            'rar' => 'application/vnd.rar',
            '7z' => 'application/x-7z-compressed',
            default => 'application/octet-stream'
        };
    }
}
