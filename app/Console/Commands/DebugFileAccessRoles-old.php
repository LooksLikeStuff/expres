<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\DealModalController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Deal;

class DebugFileAccessRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:file-access-roles {deal_id} {user_status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отладка доступа к файлам для различных ролей пользователей';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dealId = $this->argument('deal_id');
        $userStatus = $this->argument('user_status');

        $deal = Deal::find($dealId);
        
        if (!$deal) {
            $this->error("Сделка с ID {$dealId} не найдена");
            return 1;
        }
        
        $user = User::where('status', $userStatus)->first();
        
        if (!$user) {
            $this->error("Пользователь со статусом {$userStatus} не найден");
            return 1;
        }
        
        // Временно подменяем авторизованного пользователя для проверки
        $this->info("Проверяем доступ для пользователя {$user->name} со статусом {$user->status}");
        Auth::setUser($user);
        
        $controller = new DealModalController();
        $response = $controller->getDealModal($dealId);
        $responseData = json_decode($response->getContent(), true);
        
        if (!isset($responseData['success']) || !$responseData['success']) {
            $this->error("Ошибка получения данных модального окна: " . print_r($responseData, true));
            return 1;
        }
        
        // Анализируем результат
        $this->info("Анализ доступа к файловым полям:");
        
        // Парсим HTML для проверки доступа к файловым полям
        $html = $responseData['html'];
        $deal = Deal::find($dealId);
        
        // Проверяем поля файлов финального раздела
        $fileFields = [
            'plan_final' => 'Планировка финал',
            'final_collage' => 'Коллаж финал',
            'final_project_file' => 'Финал проекта',
            'work_act' => 'Акт выполненных работ',
            'chat_screenshot' => 'Скрин чата',
            'archicad_file' => 'Исходный файл архикад',
        ];
        
        $this->info(str_repeat('-', 80));
        $this->info(sprintf("%-25s | %-10s | %-30s", "Поле", "Доступ", "Комментарий"));
        $this->info(str_repeat('-', 80));
        
        foreach ($fileFields as $fieldName => $label) {
            // Проверка наличия инпутов для загрузки файлов
            $hasUploadAccess = strpos($html, 'name="' . $fieldName . '"') !== false && 
                              strpos($html, 'type="file"') !== false;
            
            $yandexUrlField = 'yandex_url_' . $fieldName;
            $hasExistingFile = !empty($deal->$yandexUrlField);
            
            $accessStatus = $hasUploadAccess ? "ДА" : "НЕТ";
            $comment = $hasExistingFile ? "Файл уже загружен" : "Файл не загружен";
            
            $this->info(sprintf("%-25s | %-10s | %-30s", $label, $accessStatus, $comment));
        }
        
        $this->info(str_repeat('-', 80));
        
        return 0;
    }
}
