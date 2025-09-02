<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Добавляем импорт фасада DB
use App\Models\User;
use App\Models\Deal;
use App\Models\DealFeed;
use App\Services\YandexDiskService;
use App\Services\DealClientService;
use App\DTO\DealClientDTO;
use App\Models\DealChangeLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Common;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\NotifyExecutorsTrait;

class DealsController extends Controller
{
    use NotifyExecutorsTrait;

    protected DealClientService $dealClientService;

    public function __construct(YandexDiskService $yandexDiskService, DealClientService $dealClientService)
    {
        $this->yandexDiskService = $yandexDiskService;
        $this->dealClientService = $dealClientService;

        // ВРЕМЕННО ОТКЛЮЧЕНО: Проверяем валидность токена при инициализации
        // if (!$this->yandexDiskService->checkAuth()) {
        //     Log::error("Ошибка авторизации в Яндекс.Диск при инициализации DealsController");
        // }

        // Убираем ограничения для загрузки больших файлов
        ini_set('upload_max_filesize', '0'); // Без ограничений
        ini_set('post_max_size', '0'); // Без ограничений
        ini_set('max_execution_time', '0'); // Без ограничений времени
        ini_set('max_input_time', '0'); // Без ограничений времени ввода
        ini_set('memory_limit', '2048M'); // 2 ГБ для больших файлов
    }
    /**
     * Загрузка файла на Яндекс.Диск
     */
    public function uploadFileToYandex(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file', // Убираем ограничение размера
                'field_name' => 'required|string',
                'deal_id' => 'sometimes|integer'
            ]);

            $file = $request->file('file');
            $fieldName = $request->input('field_name');
            $dealId = $request->input('deal_id');

            Log::info('🚀 Начинаем загрузку файла на Яндекс.Диск', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'field_name' => $fieldName,
                'deal_id' => $dealId
            ]);

            // Инициализируем сервис Яндекс.Диска
            $yandexService = new YandexDiskService();

            // Определяем папку для загрузки
            $folderPath = 'lk_deals';
            if ($dealId) {
                $folderPath .= '/deal_' . $dealId;
            }

            // Загружаем файл
            $result = $yandexService->uploadFile($file, $folderPath);

            if ($result['success']) {
                Log::info('✅ Файл успешно загружен на Яндекс.Диск', [
                    'yandex_url' => $result['yandex_url'],
                    'file_name' => $file->getClientOriginalName()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Файл успешно загружен на Яндекс.Диск',
                    'yandex_url' => $result['yandex_url'],
                    'file_name' => $file->getClientOriginalName(),
                    'field_name' => $fieldName
                ]);
            } else {
                Log::error('❌ Ошибка загрузки файла на Яндекс.Диск', [
                    'error' => $result['error'] ?? 'Неизвестная ошибка'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Не удалось загрузить файл на Яндекс.Диск'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('❌ Исключение при загрузке файла на Яндекс.Диск', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при загрузке файла: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Быстрая загрузка файлов на Яндекс.Диск с автоматическим обновлением сделки
     */
    public function fastYandexUpload(Request $request)
    {
        try {
            $request->validate([
                'documents' => 'required|array',
                'documents.*' => 'file', // Убираем ограничение размера
                'deal_id' => 'sometimes|integer'
            ]);

            $files = $request->file('documents');
            $dealId = $request->input('deal_id');

            Log::info('🚀 Начинаем быструю загрузку файлов на Яндекс.Диск', [
                'files_count' => count($files),
                'deal_id' => $dealId
            ]);

            $yandexService = new YandexDiskService();
            $uploadResults = [];
            $deal = null;

            // Если есть ID сделки, получаем её для обновления
            if ($dealId) {
                $deal = Deal::find($dealId);
                if (!$deal) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Сделка не найдена'
                    ], 404);
                }
            }

            // Определяем папку для загрузки
            $folderPath = 'lk_deals';
            if ($dealId) {
                $folderPath .= '/deal_' . $dealId;
            }

            // Загружаем файлы
            foreach ($files as $file) {
                $result = $yandexService->uploadFile($file, $folderPath);

                if ($result['success']) {
                    $uploadResults[] = [
                        'success' => true,
                        'url' => $result['url'],
                        'original_name' => $file->getClientOriginalName(),
                        'file_name' => $file->getClientOriginalName()
                    ];

                    // Если есть сделка, обновляем соответствующие поля
                    if ($deal) {
                        $this->updateDealFileFields($deal, $file->getClientOriginalName(), $result['url']);
                    }

                    Log::info('✅ Файл успешно загружен', [
                        'file_name' => $file->getClientOriginalName(),
                        'url' => $result['url']
                    ]);
                } else {
                    $uploadResults[] = [
                        'success' => false,
                        'error' => $result['message'] ?? 'Ошибка загрузки',
                        'file_name' => $file->getClientOriginalName()
                    ];

                    Log::error('❌ Ошибка загрузки файла', [
                        'file_name' => $file->getClientOriginalName(),
                        'error' => $result['message'] ?? 'Неизвестная ошибка'
                    ]);
                }
            }

            // Сохраняем изменения в сделке
            if ($deal) {
                $deal->save();
                Log::info('📝 Сделка обновлена с новыми файлами', ['deal_id' => $deal->id]);
            }

            $successCount = count(array_filter($uploadResults, function($result) {
                return $result['success'];
            }));

            return response()->json([
                'success' => $successCount > 0,
                'message' => "Успешно загружено {$successCount} из " . count($files) . " файлов",
                'results' => $uploadResults,
                'deal' => $deal ? $deal->fresh() : null // Возвращаем обновленную сделку
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Исключение при быстрой загрузке файлов', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при загрузке файлов: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Автоматическое обновление полей сделки с файлами
     */
    private function updateDealFileFields($deal, $originalName, $yandexUrl)
    {
        // Мапинг расширений файлов к полям сделки
        $extensionToFieldMap = [
            // Изображения и скриншоты
            'jpg' => ['screenshot_work_1', 'screenshot_work_2', 'screenshot_work_3', 'chat_screenshot', 'screenshot_final'],
            'jpeg' => ['screenshot_work_1', 'screenshot_work_2', 'screenshot_work_3', 'chat_screenshot', 'screenshot_final'],
            'png' => ['screenshot_work_1', 'screenshot_work_2', 'screenshot_work_3', 'chat_screenshot', 'screenshot_final'],
            'gif' => ['screenshot_work_1', 'screenshot_work_2', 'screenshot_work_3', 'chat_screenshot', 'screenshot_final'],
            'webp' => ['screenshot_work_1', 'screenshot_work_2', 'screenshot_work_3', 'chat_screenshot', 'screenshot_final'],

            // Документы и проекты
            'pdf' => ['final_project_file', 'work_act', 'execution_order_file'],
            'doc' => ['final_project_file', 'work_act', 'execution_order_file'],
            'docx' => ['final_project_file', 'work_act', 'execution_order_file'],

            // Архитектурные файлы
            'dwg' => ['archicad_file'],
            'pln' => ['archicad_file'],

            // Измерения
            'xlsx' => ['measurements_file'],
            'xls' => ['measurements_file'],
        ];

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $fileName = strtolower($originalName);

        // Определяем поле на основе имени файла или расширения
        $targetField = null;

        // Сначала пытаемся определить по имени файла
        if (strpos($fileName, 'замер') !== false || strpos($fileName, 'measurement') !== false) {
            $targetField = 'measurements_file';
        } elseif (strpos($fileName, 'финал') !== false || strpos($fileName, 'final') !== false) {
            $targetField = 'final_project_file';
        } elseif (strpos($fileName, 'акт') !== false || strpos($fileName, 'work_act') !== false) {
            $targetField = 'work_act';
        } elseif (strpos($fileName, 'чат') !== false || strpos($fileName, 'chat') !== false) {
            $targetField = 'chat_screenshot';
        } elseif (strpos($fileName, 'архикад') !== false || strpos($fileName, 'archicad') !== false) {
            $targetField = 'archicad_file';
        } else {
            // Если по имени не определили, используем расширение
            if (isset($extensionToFieldMap[$extension])) {
                $possibleFields = $extensionToFieldMap[$extension];

                // Находим первое пустое поле
                foreach ($possibleFields as $field) {
                    $yandexField = 'yandex_url_' . $field;
                    if (empty($deal->$yandexField)) {
                        $targetField = $field;
                        break;
                    }
                }

                // Если все поля заняты, используем первое
                if (!$targetField) {
                    $targetField = $possibleFields[0];
                }
            }
        }

        // Обновляем поля сделки
        if ($targetField) {
            $yandexUrlField = 'yandex_url_' . $targetField;
            $originalNameField = 'original_name_' . $targetField;

            $deal->$yandexUrlField = $yandexUrl;
            $deal->$originalNameField = $originalName;

            Log::info('📎 Обновлено поле сделки', [
                'field' => $targetField,
                'original_name' => $originalName,
                'yandex_url' => $yandexUrl
            ]);
        } else {
            Log::warning('⚠️ Не удалось определить поле для файла', [
                'file_name' => $originalName,
                'extension' => $extension
            ]);
        }
    }

    protected $yandexDiskService;


    /**
     * Отображение списка сделок.
     */
    public function dealCardinator(Request $request)
    {
        $title_site = "Сделки | Личный кабинет Экспресс-дизайн";
        $user = Auth::user();

        $search = $request->input('search');
        $status = $request->input('status');
        $view_type = $request->input('view_type', 'blocks');
        $viewType = $view_type;
          // Параметры фильтрации
        $package = $request->input('package');
        $priceServiceOption = $request->input('price_service_option');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $partnerId = $request->input('partner_id');
        $coordinatorId = $request->input('coordinator_id');
        $sortBy = $request->input('sort_by');

        $query = Deal::query();        // Фильтр по роли пользователя
        if ($user->status === 'admin') {
            // Админ видит все сделки, применяется фильтр по coordinator_id и partner_id, если они заданы
            if ($coordinatorId) {
                $query->where('coordinator_id', $coordinatorId);
            }
            if ($partnerId) {
                $query->where('office_partner_id', $partnerId);
            }
        } elseif ($user->status === 'partner') {
            $query->where('office_partner_id', $user->id);
        } elseif ($user->status === 'coordinator') {
            // Координатор может фильтровать только в рамках своих сделок
            // Если фильтр по координатору не задан или равен ID текущего пользователя
            if (!$coordinatorId || $coordinatorId == $user->id) {
                $query->where('coordinator_id', $user->id);
            } else {
                // Если координатор пытается посмотреть сделки другого координатора, возвращаем пустой результат
                $query->where('id', -1); // Это гарантирует пустой результат
            }
        } elseif (in_array($user->status, ['architect', 'designer', 'visualizer'])) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('role', $user->status);
            });
        } else {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Применяем поиск
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('client_phone', 'LIKE', "%{$search}%")
                  ->orWhere('client_email', 'LIKE', "%{$search}%")
                  ->orWhere('project_number', 'LIKE', "%{$search}%")
                  ->orWhere('package', 'LIKE', "%{$search}%")
                  ->orWhere('deal_note', 'LIKE', "%{$search}%")
                  ->orWhere('client_city', 'LIKE', "%{$search}%")
                  ->orWhere('total_sum', 'LIKE', "%{$search}%");
            });
        }

        // Фильтр по статусу
        if ($request->has('statuses')) {
            $statuses = $request->input('statuses');
            if (!empty($statuses)) {
                $query->whereIn('status', $statuses);
            }
        } elseif ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }        // Применяем дополнительные фильтры (только те, которые не обрабатываются в логике ролей)
        if ($package) $query->where('package', $package);
        if ($priceServiceOption) $query->where('price_service_option', $priceServiceOption);
        if ($dateFrom) $query->whereDate('created_date', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_date', '<=', $dateTo);
        // partnerId и coordinatorId обрабатываются в логике ролей выше

        // Применяем сортировку
        if ($sortBy) {
            switch ($sortBy) {
                case 'name_asc': $query->orderBy('name', 'asc'); break;
                case 'name_desc': $query->orderBy('name', 'desc'); break;
                case 'created_date_asc': $query->orderBy('created_date', 'desc'); break;
                case 'total_sum_asc': $query->orderBy('total_sum', 'asc'); break;
                case 'total_sum_desc': $query->orderBy('total_sum', 'desc'); break;
                default: $query->orderBy('created_at', 'desc');
            }
        } else {
            // Сортировка по умолчанию
            $query->orderBy('created_at', 'desc');
        }

        // Добавляем подсчет клиентских оценок
        $query->withCount(['ratings as client_ratings_count' => function($query) {
            $query->whereHas('raterUser', function($q) {
                $q->where('status', 'client');
            });
        }]);

        // Добавляем среднее значение клиентских оценок
        $query->withAvg(['ratings as client_rating_avg' => function($query) {
            $query->whereHas('raterUser', function($q) {
                $q->where('status', 'client');
            });
        }], 'score');

        $deals = $query->get();

        $statuses = [
            'Ждем ТЗ', 'Планировка', 'Коллажи', 'Визуализация', 'Рабочка/сбор ИП',
            'Проект готов', 'Проект завершен', 'Проект на паузе', 'Возврат',
            'В работе', 'Завершенный', 'На потом', 'Регистрация',
            'Бриф прикриплен', 'Поддержка', 'Активный'
        ];

        $feeds = DealFeed::whereIn('deal_id', $deals->pluck('id'))->get();        return view('cardinators', compact(
            'deals',
            'title_site',
            'search',
            'status',
            'viewType',
            'statuses',
            'feeds',
            'package',
            'priceServiceOption',
            'dateFrom',
            'dateTo',
            'partnerId',
            'coordinatorId',
            'sortBy'
        ));
    }

    /**
     * Обновление сделки
     */
    public function updateDeal(Request $request, $id)
    {
        $deal = Deal::findOrFail($id);
        $user = Auth::user();

        // Сохраняем оригинальные значения для логирования
        $original = $deal->getAttributes();

        // Получаем валидированные данные - убираем ограничения размера файлов
        $validatedData = $request->validate([
            'client_name' => 'required|string|max:255', // Добавляем валидацию для имени клиента
            'client_phone' => 'nullable|string|max:20',
            'client_email' => 'nullable|email|max:255',
            'client_city' => 'nullable|string|max:255',
            'client_timezone' => 'nullable|string|max:255', // Добавляем валидацию для часового пояса
            'status' => 'nullable|string|max:255',
            'coordinator_id' => 'nullable|numeric',
            'office_partner_id' => 'nullable|numeric',
            'architect_id' => 'nullable|numeric',
            'designer_id' => 'nullable|numeric',
            'visualizer_id' => 'nullable|numeric',
            'comment' => 'nullable|string',
            'total_sum' => 'nullable|numeric',
            'package' => 'nullable|string',
            'price_service_option' => 'nullable|string',
            'created_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'payment_date' => 'nullable|date',
            'project_end_date' => 'nullable|date',
            'visualization_link' => 'nullable|url',
            'project_duration' => 'nullable|integer',
            'client_city_id' => 'nullable|string',
            'completion_responsible' => 'nullable|string',
            'rooms_count_pricing' => 'nullable|string',
            'project_number' => 'nullable|max:150',  // Добавляем валидацию для номера проекта
            // Файловые поля - убираем ограничения размера
            'execution_order_file' => 'nullable|file',
            'measurements_file' => 'nullable|file',
            'final_floorplan' => 'nullable|file',
            'final_collage' => 'nullable|file',
            'final_project_file' => 'nullable|file',
            'work_act' => 'nullable|file',
            'archicad_file' => 'nullable|file',
            'contract_attachment' => 'nullable|file',
            'plan_final' => 'nullable|file',
            'chat_screenshot' => 'nullable|file',
            'screenshot_work_1' => 'nullable|file',
            'screenshot_work_2' => 'nullable|file',
            'screenshot_work_3' => 'nullable|file',
            'screenshot_final' => 'nullable|file',
            'avatar_path' => 'nullable|file|image', // Убрали ограничение max:5000
            // Правильная валидация для multiple file uploads - убираем ограничения размера
            'project_photos' => 'nullable|array',
            'project_photos.*' => 'file', // Убрали все ограничения размера
        ]);

        // Убираем поля файлов из массива для обновления
        $fileFields = [
            'execution_order_file', 'measurements_file', 'final_floorplan',
            'final_collage', 'final_project_file', 'work_act',
            'archicad_file', 'contract_attachment', 'plan_final', 'chat_screenshot', 'avatar_path',
            'screenshot_work_1', 'screenshot_work_2', 'screenshot_work_3', 'screenshot_final',
            'project_photos'  // Добавляем наше поле с фотографиями
        ];

        $dataToUpdate = array_diff_key($validatedData, array_flip($fileFields));

        // Извлекаем клиентские данные из запроса
        $clientData = array_intersect_key($dataToUpdate, array_flip([
            'client_name', 'client_phone', 'client_email', 'client_city', 
            'client_timezone', 'client_info', 'client_account_link'
        ]));

        // Удаляем клиентские поля из данных для обновления Deal
        $dealData = array_diff_key($dataToUpdate, array_flip([
            'client_name', 'client_phone', 'client_email', 'client_city', 
            'client_timezone', 'client_info', 'client_account_link'
        ]));

        // Обновляем данные сделки без клиентских полей
        $deal->update($dealData);

        // Обновляем клиентские данные через новый сервис
        if (!empty($clientData)) {
            // Переименовываем поля для DTO
            $clientDTOData = [
                'deal_id' => $deal->id,
                'name' => $clientData['client_name'] ?? $deal->dealClient?->name ?? '',
                'phone' => $clientData['client_phone'] ?? $deal->dealClient?->phone ?? '',
                'email' => $clientData['client_email'] ?? $deal->dealClient?->email ?? null,
                'city' => $clientData['client_city'] ?? $deal->dealClient?->city ?? null,
                'timezone' => $clientData['client_timezone'] ?? $deal->dealClient?->timezone ?? null,
                'info' => $clientData['client_info'] ?? $deal->dealClient?->info ?? null,
                'account_link' => $clientData['client_account_link'] ?? $deal->dealClient?->account_link ?? null,
            ];

            try {
                $clientDTO = DealClientDTO::fromArray($clientDTOData);
                $this->dealClientService->createOrUpdate($clientDTO);
            } catch (\InvalidArgumentException $e) {
                Log::warning("Ошибка обновления клиентских данных для сделки {$deal->id}: " . $e->getMessage());
                // Продолжаем без критической ошибки
            }
        }

        // СТАРАЯ система для файлов документов ОТКЛЮЧЕНА - используется новая система v3.0 через API
        // Файлы документов теперь загружаются через YandexDiskController API

        // Загрузка фотографий проекта (оставляем старую систему для массовой загрузки фотографий)
        $this->handleProjectPhotosUpload($request, $deal);

        // Обработка загрузки аватара
        if ($request->hasFile('avatar_path')) {
            $avatarFile = $request->file('avatar_path');
            $avatarPath = $avatarFile->store('deal_avatars', 'public');
            $deal->avatar_path = $avatarPath;
            $deal->save();
        }

        // Проверяем, изменился ли статус сделки
        $statusChanged = $original['status'] !== $deal->status;
        $changedToCompleted = $statusChanged && $deal->status === 'Проект завершен';

        // Логирование изменений
        $this->logDealChanges($deal, $original, $deal->getAttributes());

        // Проверяем, изменились ли исполнители в сделке
        $executorsChanged =
            ($original['architect_id'] != $deal->architect_id && $deal->architect_id) ||
            ($original['designer_id'] != $deal->designer_id && $deal->designer_id) ||
            ($original['visualizer_id'] != $deal->visualizer_id && $deal->visualizer_id);

        // Уведомляем исполнителей, если они были изменены
        if ($executorsChanged) {
            // Загружаем связанные модели исполнителей для получения номеров телефонов
            $deal->loadMissing(['architect', 'designer', 'visualizer']);
            $this->notifyExecutorsAboutAttach($deal);
        }

        // Проверяем тип запроса - AJAX или обычная форма
        if ($request->expectsJson() || $request->wantsJson()) {
            // Возвращаем JSON для AJAX-запросов
            return response()->json([
                'success' => true,
                'message' => 'Сделка успешно обновлена',
                'status_changed_to_completed' => $changedToCompleted,
                'deal' => $deal,
                'deal_id' => $deal->id
            ]);
        } else {
            // Возвращаем редирект для обычной отправки формы
            $message = 'Сделка успешно обновлена';
            if ($changedToCompleted) {
                $message .= '. Статус изменен на "Проект завершен"';
            }

            return redirect()->route('deal.edit-page', $deal->id)
                ->with('success', $message);
        }
    }

    /**
     * УСТАРЕВШИЙ метод загрузки файлов документов на Яндекс Диск
     * ЗАМЕНЕН на новую систему v3.0 через YandexDiskController API
     * Сохранен для совместимости, но НЕ ИСПОЛЬЗУЕТСЯ
     */
    private function handleYandexDiskFileUploads_DEPRECATED(Request $request, Deal $deal)
    {
        // Проверяем авторизацию перед загрузкой
        if (!$this->yandexDiskService->checkAuth()) {
            Log::error("Ошибка авторизации в Яндекс.Диск при загрузке файлов", [
                'deal_id' => $deal->id
            ]);
            return; // Прерываем загрузку, если нет авторизации
        }

        // Массив соответствия полей файлов и их префиксов
        $fileFieldsMapping = [
            'execution_order_file' => 'Распоряжение на исполнение',
            'measurements_file' => 'Замеры',
            'final_floorplan' => 'Финальная планировка',
            'final_collage' => 'Финальный коллаж',
            'final_project_file' => 'Финальный проект',
            'work_act' => 'Акт выполненных работ',
            'archicad_file' => 'Файл Archicad',
            'contract_attachment' => 'Приложение к договору',
            'plan_final' => 'Планировка финал', // Добавляем поле plan_final
            'chat_screenshot' => 'Скриншот чата', // Добавляем поле chat_screenshot
        ];

        // Базовый путь для хранения файлов
        $basePath = config('services.yandex_disk.base_folder', 'lk_deals');
        // Всегда используем формат "deal_IDDEAL" для имени папки сделки
        $projectFolder = "deal_{$deal->id}";
        $dealFolder = "{$basePath}/{$projectFolder}";

        // Обрабатываем каждый файл
        foreach ($fileFieldsMapping as $fieldName => $filePrefix) {
            if ($request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                $originalName = $file->getClientOriginalName();
                $fileName = Str::slug($filePrefix) . '_' . time() . '_' . $originalName;
                $diskPath = "{$dealFolder}/{$fieldName}/{$fileName}";

                try {
                    // Убираем ограничения времени для загрузки больших файлов
                    set_time_limit(0);
                    ini_set('memory_limit', '-1');

                    // Устанавливаем неограниченное время ожидания для Яндекс.Диск
                    $this->yandexDiskService->setTimeout(0); // Без ограничений

                    $uploadResult = $this->yandexDiskService->uploadFile($file, $diskPath);

                    if ($uploadResult['success']) {
                        $deal->update([
                            "yandex_url_{$fieldName}" => $uploadResult['url'],
                            "yandex_disk_path_{$fieldName}" => $uploadResult['path'],
                            "original_name_{$fieldName}" => $originalName,
                        ]);

                        Log::info("Файл {$fieldName} успешно загружен на Яндекс.Диск", [
                            'deal_id' => $deal->id,
                            'path' => $diskPath
                        ]);
                    } else {
                        Log::error("Ошибка при загрузке файла {$fieldName} на Яндекс.Диск", [
                            'deal_id' => $deal->id,
                            'error' => $uploadResult['message'] ?? 'Неизвестная ошибка'
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Исключение при загрузке файла {$fieldName} на Яндекс.Диск", [
                        'deal_id' => $deal->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Обработка загрузки нескольких фотографий проекта на Яндекс Диск
     */
    private function handleProjectPhotosUpload(Request $request, Deal $deal)
    {
        // Проверяем были ли загружены файлы и авторизацию
        if (!$request->hasFile('project_photos') || !$this->yandexDiskService->checkAuth()) {
            if (!$request->hasFile('project_photos')) {
                Log::info("Нет файлов project_photos для загрузки", ['deal_id' => $deal->id]);
            } else {
                Log::error("Ошибка авторизации в Яндекс.Диск при загрузке фотографий", [
                    'deal_id' => $deal->id
                ]);
            }
            return;
        }

        $files = $request->file('project_photos');

        // Проверка типа переменной $files
        if (!is_array($files)) {
            Log::error("project_photos не является массивом", [
                'deal_id' => $deal->id,
                'type' => gettype($files)
            ]);
            return;
        }

        // Базовый путь для хранения файлов
        $basePath = config('services.yandex_disk.base_folder', 'lk_deals');
        // Всегда используем формат "deal_IDDEAL" для имени папки сделки
        $projectFolder = "deal_{$deal->id}";
        $photosFolder = "{$basePath}/{$projectFolder}/project_photos";

        try {
            // Создаем директорию для файлов на Яндекс Диске, если ещё не существует
            $dirCreated = $this->yandexDiskService->createDirectory($photosFolder);

            if (!$dirCreated) {
                Log::error("Не удалось создать директорию на Яндекс Диске", [
                    'deal_id' => $deal->id,
                    'folder' => $photosFolder
                ]);
                return;
            }

            Log::info("Директория создана успешно", [
                'deal_id' => $deal->id,
                'folder' => $photosFolder
            ]);

            // Убираем ограничения времени для загрузки больших файлов
            set_time_limit(0);
            ini_set('memory_limit', '-1');

            // Устанавливаем неограниченное время ожидания для Яндекс.Диск
            $this->yandexDiskService->setTimeout(0); // Без ограничений

            $uploadedCount = 0;
            $maxFiles = 100; // Увеличиваем максимальное количество файлов до 100

            // Ограничиваем количество загружаемых файлов до maxFiles
            $filesToUpload = array_slice($files, 0, $maxFiles);

            // Загружаем каждый файл
            foreach ($filesToUpload as $index => $file) {
                if (!$file->isValid()) {
                    Log::error("Невалидный файл project_photos[{$index}]", [
                        'deal_id' => $deal->id,
                        'error' => $file->getError()
                    ]);
                    continue;
                }

                $originalName = $file->getClientOriginalName();
                $safeFileName = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $originalName);
                $fileName = 'photo_' . time() . '_' . $index . '_' . $safeFileName;
                $diskPath = "{$photosFolder}/{$fileName}";

                Log::info("Загружаем файл на Яндекс Диск", [
                    'deal_id' => $deal->id,
                    'file' => $originalName,
                    'path' => $diskPath
                ]);

                // Загружаем файл на Яндекс Диск
                $uploadResult = $this->yandexDiskService->uploadFile($file, $diskPath);

                if ($uploadResult['success']) {
                    $uploadedCount++;
                    Log::info("Файл успешно загружен на Яндекс Диск", [
                        'deal_id' => $deal->id,
                        'file' => $originalName,
                        'path' => $diskPath
                    ]);
                } else {
                    Log::error("Ошибка при загрузке файла на Яндекс Диск", [
                        'deal_id' => $deal->id,
                        'file' => $originalName,
                        'error' => $uploadResult['message'] ?? 'Неизвестная ошибка'
                    ]);
                }
            }

            // Если загружены файлы, публикуем папку для получения ссылки
            if ($uploadedCount > 0) {
                $folderPublicUrl = $this->yandexDiskService->publishFile($photosFolder);

                if ($folderPublicUrl) {
                    // Обновляем данные сделки с информацией о загруженных фото
                    $deal->update([
                        'photos_folder_url' => $folderPublicUrl,
                        'photos_count' => $uploadedCount,
                        'yandex_disk_photos_path' => $photosFolder,
                    ]);

                    Log::info("Папка с фотографиями проекта опубликована", [
                        'deal_id' => $deal->id,
                        'url' => $folderPublicUrl,
                        'count' => $uploadedCount
                    ]);
                } else {
                    Log::error("Не удалось опубликовать папку с фотографиями", [
                        'deal_id' => $deal->id,
                        'folder' => $photosFolder
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Исключение при загрузке файлов на Яндекс Диск", [
                'deal_id' => $deal->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function logDealChanges($deal, $original, $new)
    {
        foreach (['updated_at', 'created_at'] as $key) {
            unset($original[$key], $new[$key]);
        }

        $changes = [];
        foreach ($new as $key => $newValue) {
            if (array_key_exists($key, $original) && $original[$key] != $newValue) {
                $changes[$key] = [
                    'old' => $original[$key],
                    'new' => $newValue,
                ];
            }
        }

        if (!empty($changes)) {
            DealChangeLog::create([
                'deal_id'   => $deal->id,
                'user_id'   => Auth::id(),
                'user_name' => Auth::user()->name,
                'changes'   => $changes,
            ]);
        }
    }

    public function storeDealFeed(Request $request, $dealId)
    {
        $request->validate([
            'content' => 'required|string|max:1990',
        ]);

        $deal = Deal::findOrFail($dealId);
        $user = Auth::user();

        $feed = new DealFeed();
        $feed->deal_id = $deal->id;
        $feed->user_id = $user->id;
        $feed->content = $request->input('content');
        $feed->save();

        return response()->json([
            'user_name'  => $user->name,
            'content'    => $feed->content,
            'date'       => $feed->created_at->format('d.m.Y H:i'),
            'avatar_url' => $user->avatar_url,
        ]);
    }

    /**
     * Форма создания сделки – доступна для координатора, администратора и партнёра.
     */
    public function createDeal()
    {
        $user = Auth::user();
        if (!in_array($user->status, ['coordinator', 'admin', 'partner'])) {
            return redirect()->route('deal.cardinator')
                ->with('error', 'Только координатор, администратор или партнер могут создавать сделку.');
        }
        $title_site = "Создание сделки";

        $citiesFile = public_path('cities.json');
        if (file_exists($citiesFile)) {
            $citiesJson = file_get_contents($citiesFile);
            $russianCities = json_decode($citiesJson, true);
        } else {
            $russianCities = [];
        }

        $coordinators = User::where('status', 'coordinator')->get();
        $partners = User::where('status', 'partner')->get();

        return view('create_deal', compact(
            'title_site',
            'user',
            'coordinators',
            'partners',
            'russianCities'
        ));
    }

    /**
     * Сохранение сделки с автоматическим созданием группового чата для ответственных.
     */
    public function storeDeal(Request $request)
    {
        $validated = $request->validate([
            'client_phone'            => 'required|string|max:50',
            'client_name'             => 'required|string|max:255', // Добавляем валидацию для имени клиента
            'package'                 => 'required|string|max:255',
            'price_service_option'    => 'required|string|max:255',
            'rooms_count_pricing'     => 'nullable|string|max:255',
            'execution_order_comment' => 'nullable|string|max:1000',
            'execution_order_file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png', // Убрали ограничение max:5120
            'office_partner_id'       => 'nullable|exists:users,id',
            'coordinator_id'          => 'nullable|exists:users,id',
            'total_sum'               => 'nullable|numeric',
            'client_info'             => 'nullable|string',
            'payment_date'            => 'nullable|date',
            'execution_comment'       => 'nullable|string',
            'comment'                 => 'nullable|string',
            'client_timezone'         => 'nullable|string',
            'completion_responsible'  => 'required|string', // Изменено с nullable на required
            'start_date'              => 'nullable|date',
            'project_duration'        => 'nullable|integer',
            'project_end_date'        => 'nullable|date',
            'documents'               => 'nullable|array', // Добавляем валидацию для массива документов
            'documents.*'             => 'nullable|file', // Убрали ограничение max:20480
        ]);

        $user = Auth::user();
        if (!in_array($user->status, ['coordinator', 'admin', 'partner'])) {
            return redirect()->route('deal.cardinator')
                ->with('error', 'Только координатор, администратор или партнер могут создавать сделку.');
        }

        try {
            $coordinatorId = $validated['coordinator_id'] ?? auth()->id();

            // Нормализация номера телефона клиента для поиска (удаление нецифровых символов)
            $normalizedPhone = preg_replace('/\D/', '', $validated['client_phone']);

            // Поиск существующего пользователя по номеру телефона
            $existingUser = User::where('phone', 'LIKE', '%' . $normalizedPhone . '%')->first();

            // Используем ID существующего пользователя или текущего авторизованного пользователя
            // Это гарантирует, что user_id никогда не будет NULL
            $userId = $existingUser ? $existingUser->id : auth()->id();

            // Создаем сделку без клиентских полей
            $deal = Deal::create([
                'status'                 => 'Ждем ТЗ', // устанавливаем значение по умолчанию
                'package'                => $validated['package'],
                'price_service_option'   => $validated['price_service_option'],
                'rooms_count_pricing'    => $validated['rooms_count_pricing'] ?? null,
                'execution_order_comment'=> $validated['execution_order_comment'] ?? null,
                'office_partner_id'      => $validated['office_partner_id'] ?? null,
                'coordinator_id'         => $coordinatorId,
                'total_sum'              => $validated['total_sum'] ?? null,
                'payment_date'           => $validated['payment_date'] ?? null,
                'execution_comment'      => $validated['execution_comment'] ?? null,
                'comment'                => $validated['comment'] ?? null,
                'completion_responsible' => $validated['completion_responsible'] ?? null,
                'user_id'                => $userId, // Устанавливаем ID найденного пользователя или текущего
                'registration_token'     => Str::random(32),
                'registration_token_expiry' => now()->addDays(7),
                'start_date'             => $validated['start_date'] ?? null,
                'project_duration'       => $validated['project_duration'] ?? null,
                'project_end_date'       => $validated['project_end_date'] ?? null,
            ]);

            // Создаем клиентские данные через новый сервис
            $clientDTO = DealClientDTO::fromArray([
                'deal_id' => $deal->id,
                'name' => $validated['client_name'],
                'phone' => $validated['client_phone'],
                'email' => $validated['client_email'] ?? null,
                'city' => $validated['client_city'] ?? null,
                'timezone' => $validated['client_timezone'] ?? null,
                'info' => $validated['client_info'] ?? null,
                'account_link' => $validated['client_account_link'] ?? null,
            ]);

            try {
                $this->dealClientService->createOrUpdate($clientDTO);
            } catch (\InvalidArgumentException $e) {
                // Если не удалось создать клиента, удаляем сделку и возвращаем ошибку
                $deal->delete();
                throw new \Exception('Ошибка создания данных клиента: ' . $e->getMessage());
            }

            // Сохраняем документы и получаем пути к файлам
            if ($request->hasFile('documents')) {
                $documentsPaths = $this->saveDocuments($request, $deal->id);

                // Сохраняем пути в JSON-поле documents
                if (!empty($documentsPaths)) {
                    $deal->documents = json_encode($documentsPaths);
                    $deal->save();

                    // Логируем успешную загрузку
                    Log::info('Документы успешно загружены для сделки ID: ' . $deal->id, [
                        'count' => count($documentsPaths),
                        'paths' => $documentsPaths
                    ]);
                }
            }

            // Загрузка файлов
            $fileFields = [
                'avatar',
                'execution_order_file',
            ];

            foreach ($fileFields as $field) {
                $uploadData = $this->handleFileUpload($request, $deal, $field, $field === 'avatar' ? 'avatar_path' : $field);
                if (!empty($uploadData)) {
                    $deal->update($uploadData);
                }
            }

            // Привязываем текущего пользователя как координатора
            $deal->users()->attach([auth()->id() => ['role' => 'coordinator']]);

            // Формируем массив связей для таблицы deal_user
            $dealUsers = [auth()->id() => ['role' => 'coordinator']];
            if ($request->filled('architect_id') && User::where('id', $request->input('architect_id'))->exists()) {
                $dealUsers[$request->input('architect_id')] = ['role' => 'architect'];
                $deal->architect_id = $request->input('architect_id');
            }
            if ($request->filled('designer_id') && User::where('id', $request->input('designer_id'))->exists()) {
                $dealUsers[$request->input('designer_id')] = ['role' => 'designer'];
                $deal->designer_id = $request->input('designer_id');
            }
            if ($request->filled('visualizer_id') && User::where('id', $request->input('visualizer_id'))->exists()) {
                $dealUsers[$request->input('visualizer_id')] = ['role' => 'visualizer'];
                $deal->visualizer_id = $request->input('visualizer_id');
            }

            // Привязываем существующего клиента, если найден
            if ($existingUser) {
                $dealUsers[$existingUser->id] = ['role' => 'client'];
                // Записываем в лог привязку клиента по номеру телефона
                \Illuminate\Support\Facades\Log::info('Клиент привязан к сделке по номеру телефона', [
                    'deal_id' => $deal->id,
                    'client_id' => $existingUser->id,
                    'client_phone' => $validated['client_phone'],
                    'normalized_phone' => $normalizedPhone
                ]);
            }

            $deal->save();
            $deal->users()->attach($dealUsers);

            // Отправляем смс с регистрационной ссылкой ТОЛЬКО если клиент ещё не зарегистрирован
            if (!$existingUser) {
                $this->sendSmsNotification($deal, $deal->registration_token);
            } else {
                // Для существующего клиента сразу обновляем статус сделки
                $deal->status = 'Регистрация';
                $deal->save();
            }

            // Добавляем клиента в пользователей сделки, если такого клиента нет по email
            if(!empty($deal->client_email)) {
                $clientByEmail = User::where('email', $deal->client_email)->first();
                if($clientByEmail && !$deal->users()->where('user_id', $clientByEmail->id)->exists()) {
                    $deal->users()->attach($clientByEmail->id, ['role' => 'client']);
                }
            }

            return redirect()->route('deal.cardinator')->with('success', 'Сделка успешно создана.');
        } catch (\Exception $e) {
            Log::error("Ошибка при создании сделки: " . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при создании сделки: ' . $e->getMessage());
        }
    }

    /**
     * Сохраняет загруженные документы и возвращает массив путей
     *
     * @param Request $request
     * @param int $dealId ID сделки для создания папки
     * @return array Массив путей к сохраненным документам
     */
    private function saveDocuments(Request $request, $dealId)
    {
        $documentsPaths = [];

        if ($request->hasFile('documents')) {
            // Создаем директорию, если она не существует
            $directory = "dels/{$dealId}";
            $fullPath = storage_path("app/public/{$directory}");

            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            foreach ($request->file('documents') as $file) {
                if ($file->isValid()) {
                    // Сохраняем оригинальное имя файла, но делаем его безопасным
                    $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFileName = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $fileName);
                    $extension = $file->getClientOriginalExtension();
                    $uniqueFileName = $safeFileName . '_' . time() . '_' . uniqid() . '.' . $extension;

                    // Сохраняем файл в storage/app/public/dels/{dealId}
                    $path = $file->storeAs($directory, $uniqueFileName, 'public');

                    if ($path) {
                        $documentsPaths[] = $path;
                    }
                }
            }
        }

        return $documentsPaths;
    }

    /**
     * Отправляет SMS-уведомление координатору о смене статуса сделки
     *
     * @param \App\Models\Deal $deal Сделка с обновленным статусом
     * @param string $oldStatus Предыдущий статус сделки
     * @return void
     */
    protected function notifyCoordinatorAboutStatusChange($deal, $oldStatus)
    {
        try {
            // Проверяем наличие координатора
            if (!$deal->coordinator_id) {
                Log::warning("Не удалось отправить SMS: у сделки #{$deal->id} не указан координатор");
                return;
            }

            // Получаем данные координатора
            $coordinator = \App\Models\User::find($deal->coordinator_id);
            if (!$coordinator || !$coordinator->phone) {
                Log::warning("Не удалось отправить SMS: у координатора сделки #{$deal->id} нет номера телефона");
                return;
            }

            // Формируем сообщение
            $message = "Статус сделки #{$deal->id} изменен c \"{$oldStatus}\" на \"{$deal->status}\". Клиент: {$deal->name}";

            // Ограничиваем длину сообщения
            if (strlen($message) > 160) {
                $message = substr($message, 0, 157) . '...';
            }

            // Отправляем SMS через сервис
            $smsService = new \App\Services\SmsService();
            $result = $smsService->sendSms($coordinator->phone, $message);

            if (!$result) {
                Log::error("Ошибка при отправке SMS координатору {$coordinator->name} ({$coordinator->phone})");
            }
        } catch (\Exception $e) {
            Log::error("Исключение при отправке SMS о смене статуса: " . $e->getMessage());
        }
    }

    /**
     * Отправляет SMS-уведомление клиенту о смене статуса сделки
     *
     * @param \App\Models\Deal $deal Сделка с обновленным статусом
     * @param string $oldStatus Предыдущий статус сделки
     * @return void
     */
    protected function notifyClientAboutStatusChange($deal, $oldStatus)
    {
        try {
            // Проверяем наличие номера телефона клиента
            if (!$deal->client_phone) {
                Log::warning("Не удалось отправить SMS клиенту: у сделки #{$deal->id} не указан телефон клиента");
                return;
            }

            // Нормализуем номер телефона клиента для отправки
            $rawPhone = preg_replace('/\D/', '', $deal->client_phone);
            if (strlen($rawPhone) < 10) {
                Log::warning("Не удалось отправить SMS: некорректный номер телефона клиента в сделке #{$deal->id}");
                return;
            }

            // Получаем домен сайта из конфигурации
            $domain = config('app.url', 'https://express-design.ru');

            // Формируем сообщение
            $message = "Статус вашего проекта изменен с \"{$oldStatus}\" на \"{$deal->status}\". Подробности: {$domain}";

            // Ограничиваем длину сообщения
            if (strlen($message) > 160) {
                $message = substr($message, 0, 157) . '...';
            }

            // Отправляем SMS через сервис
            $apiKey = config('services.smsru.api_id', '6CDCE0B0-6091-278C-5145-360657FF0F9B');
            $response = Http::get("https://sms.ru/sms/send", [
                'api_id'    => $apiKey,
                'to'        => $rawPhone,
                'msg'       => $message,
                'partner_id'=> 1,
            ]);

            if ($response->failed()) {
                Log::error("Ошибка при отправке SMS клиенту для сделки #{$deal->id}. Ответ: " . $response->body());
            } else {
                Log::info("SMS-уведомление о смене статуса отправлено клиенту", [
                    'deal_id' => $deal->id,
                    'phone' => $rawPhone,
                    'new_status' => $deal->status,
                    'old_status' => $oldStatus
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Исключение при отправке SMS клиенту о смене статуса: " . $e->getMessage());
        }
    }

    /**
     * Отправка SMS-уведомления с регистрационной ссылкой.
     */
    private function sendSmsNotification($deal, $registrationToken)
    {
        if (!$registrationToken) {
            Log::error("Отсутствует регистрационный токен для сделки ID: {$deal->id}");
            throw new \Exception('Отсутствует регистрационный токен для сделки.');
        }

        $rawPhone = preg_replace('/\D/', '', $deal->client_phone);

        $apiKey = config('services.smsru.api_id', '6CDCE0B0-6091-278C-5145-360657FF0F9B');

        $response = Http::get("https://sms.ru/sms/send", [
            'api_id'    => $apiKey,
            'to'        => $rawPhone,
            'msg'       => "Здравствуйте! Для регистрации пройдите по ссылке: https://lk.express-diz.ru/register ",
            'partner_id'=> 1,
        ]);

        if ($response->failed()) {
            Log::error("Ошибка при отправке SMS для сделки ID: {$deal->id}. Ответ сервера: " . $response->body());
            throw new \Exception('Ошибка при отправке SMS.');
        }
    }

    /**
     * Отображение логов изменений для конкретной сделки.
     */
    public function changeLogsForDeal($dealId)
    {
        $deal = Deal::findOrFail($dealId);
        $logs = DealChangeLog::where('deal_id', $deal->id)
            ->orderBy('created_at', 'desc')
            ->get();
        $title_site = "Логи изменений сделки";
        return view('deal_change_logs', compact('deal', 'logs', 'title_site'));
    }

    /**
     * Метод для загрузки ленты комментариев по сделке.
     * Вызывается AJAX‑запросом и возвращает JSON с записями ленты.
     */
    public function getDealFeeds($dealId)
    {
        try {
            $deal = Deal::findOrFail($dealId);
            $feeds = $deal->dealFeeds()->with('user')->orderBy('created_at', 'desc')->get();
            $result = $feeds->map(function ($feed) {
                return [
                    'user_name'  => $feed->user->name,
                    'content'    => $feed->content,
                    'date'       => $feed->created_at->format('d.m.Y H:i'),
                    'avatar_url' => $feed->user->avatar_url ? $feed->user->avatar_url : asset('storage/default-avatar.png'),
                ];
            });
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error("Ошибка загрузки ленты: " . $e->getMessage());
            return response()->json(['error' => 'Ошибка загрузки ленты'], 500);
        }
    }

    /**
     * Отображение общих логов изменений для всех сделок.
     */
    public function changeLogs()
    {
        $logs = DealChangeLog::with('deal')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        $title_site = "Логи изменений сделок";
        return view('deals.deal_change_logs', compact('logs', 'title_site'));
    }

    /**
     * Создает сделку на основе брифа
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createDealFromBrief(Request $request)
    {
        try {
            // Валидация запроса с учетом типа брифа
            $validator = Validator::make($request->all(), [
                'brief_id' => 'required|integer',
                'brief_type' => 'required|in:common,commercial',
                'client_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации: ' . implode(', ', $validator->errors()->all())
                ], 422);
            }

            $briefId = $request->input('brief_id');
            $briefType = $request->input('brief_type');
            $clientId = $request->input('client_id');

            // Получаем бриф в зависимости от типа
            if ($briefType === 'common') {
                $brief = Common::findOrFail($briefId);
                $briefTitle = $brief->title ?? 'Сделка по общему брифу #' . $briefId;
            } else {
                $brief = \App\Models\Commercial::findOrFail($briefId);
                $briefTitle = $brief->title ?? 'Сделка по коммерческому брифу #' . $briefId;
            }

            // Проверяем, что сделка по этому брифу ещё не создана
            if ($brief->deal_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Сделка по данному брифу уже создана'
                ], 400);
            }

            // Получаем клиента
            $client = User::findOrFail($clientId);

            // Создаем новую сделку

            $deal = new Deal();

            // Устанавливаем связь с соответствующим типом брифа
            if ($briefType === 'common') {
                $deal->common_id = $briefId;
            } else {
                $deal->commercial_id = $briefId;
            }

            $deal->user_id = $clientId;

            // Заполняем данные из брифа
            $deal->name = $briefTitle;
            $deal->status = 'В работе';
            $deal->coordinator_id = Auth::id(); // Текущий пользователь становится координатором

            // Другие необходимые поля
            // ...

            $deal->save();

            // Создаем клиентские данные через новый сервис
            $clientDTO = DealClientDTO::fromArray([
                'deal_id' => $deal->id,
                'name' => $client->name,
                'phone' => $client->phone,
                'email' => $client->email,
                'city' => $client->city ?? null,
                'timezone' => null,
                'info' => null,
                'account_link' => null,
            ]);

            try {
                $this->dealClientService->createOrUpdate($clientDTO);
            } catch (\InvalidArgumentException $e) {
                Log::warning("Не удалось создать клиента для сделки {$deal->id}: " . $e->getMessage());
                // Продолжаем без ошибки, так как основные данные клиента есть в User
            }

            // Обновляем бриф, указывая ссылку на созданную сделку
            $brief->deal_id = $deal->id;
            $brief->save();

            Log::info('Создана В работе из брифа', [
                'deal_id' => $deal->id,
                'brief_id' => $briefId,
                'brief_type' => $briefType,
                'user_id' => $clientId,
                'creator_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Сделка успешно создана',
                'deal_id' => $deal->id,
                'redirect_url' => route('deal.cardinator') // меняем маршрут редиректа
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка при создании сделки из брифа: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Внутренняя ошибка сервера: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Удаление сделки без потери связей (только для администраторов)
     *
     * @param int $dealId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteDeal($dealId)
    {
        // Проверка прав доступа (должно быть обработано middleware, но добавляем дополнительную проверку)
        if (Auth::user()->status !== 'admin') {
            return redirect()->back()->with('error', 'У вас нет прав на удаление сделок');
        }

        try {
            $deal = Deal::findOrFail($dealId);

            // Логируем действие перед удалением
            Log::info('Удаление сделки администратором', [
                'deal_id' => $deal->id,
                'deal_name' => $deal->name,
                'admin_id' => Auth::id(),
                'admin_name' => Auth::user()->name
            ]);

            // Сохраняем ID брифа перед удалением для информационных целей
            $briefId = $deal->brief_id;
            $briefType = $deal->brief_type;

            // Удаляем сделку
            $deal->delete();

            return redirect()->route('deal.cardinator')->with('success', 'Сделка успешно удалена. Связанные данные сохранены.');

        } catch (\Exception $e) {
            Log::error('Ошибка при удалении сделки: ' . $e->getMessage(), [
                'exception' => $e,
                'deal_id' => $dealId,
                'admin_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Произошла ошибка при удалении сделки: ' . $e->getMessage());
        }
    }

    /**
     * Поиск брифов по номеру телефона клиента
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function findBriefsByDealPhone(Request $request)
    {
        try {
            $dealId = $request->input('deal_id');
            $clientPhone = $request->input('client_phone');

            if (empty($clientPhone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не указан телефон клиента'
                ], 400);
            }

            // Нормализуем телефон для поиска (убираем все нецифровые символы)
            $normalizedPhone = preg_replace('/[^0-9]/', '', $clientPhone);

            // Логируем входные данные
            \Log::info('Поиск пользователя по телефону для брифов', [
                'dealId' => $dealId,
                'original_phone' => $clientPhone,
                'normalized_phone' => $normalizedPhone
            ]);

            // Ищем пользователя по номеру телефона с различными вариантами форматирования
            $query = \App\Models\User::where(function($q) use ($normalizedPhone) {
                // Ищем по полному номеру
                $q->where('phone', 'LIKE', '%' . $normalizedPhone . '%');

                // Для российских номеров пробуем разные варианты
                if (strlen($normalizedPhone) >= 10) {
                    // Последние 10 цифр (без кода страны)
                    $lastTenDigits = substr($normalizedPhone, -10);
                    $q->orWhere('phone', 'LIKE', '%' . $lastTenDigits . '%');

                    // Вариации с 7 и 8 в начале для российских номеров
                    if (strlen($normalizedPhone) == 11) {
                        if (substr($normalizedPhone, 0, 1) == '7') {
                            $altPhone = '8' . substr($normalizedPhone, 1);
                            $q->orWhere('phone', 'LIKE', '%' . $altPhone . '%');
                        } else if (substr($normalizedPhone, 0, 1) == '8') {
                            $altPhone = '7' . substr($normalizedPhone, 1);
                            $q->orWhere('phone', 'LIKE', '%' . $altPhone . '%');
                        }
                    }
                }

                // Ищем по последним цифрам номера для более широкого поиска
                if (strlen($normalizedPhone) >= 6) {
                    $lastDigits = substr($normalizedPhone, -6);
                    $q->orWhere('phone', 'LIKE', '%' . $lastDigits);
                }
            });

            // Получаем найденных пользователей
            $users = $query->get();
            $userIds = $users->pluck('id')->toArray();

            \Log::info('Найдены пользователи по телефону', [
                'count' => count($users),
                'user_ids' => $userIds
            ]);

            // Получаем информацию о сделке
            $deal = \App\Models\Deal::find($dealId);

            if (!$deal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Сделка не найдена'
                ], 404);
            }

            // Проверяем наличие привязанных брифов
            $hasAttachedBrief = !empty($deal->common_id) || !empty($deal->commercial_id);
            $attachedBriefType = !empty($deal->common_id) ? 'common' : (!empty($deal->commercial_id) ? 'commercial' : null);

            // Получаем брифы для найденных пользователей
            $commonBriefs = [];
            $commercialBriefs = [];

            if (!empty($userIds)) {
                // Общие брифы со статусом "Завершенный" или "Завершен"
                $commonBriefs = \App\Models\Common::whereIn('user_id', $userIds)
                    ->whereIn('status', ['Завершенный', 'Завершен'])
                    ->where(function($query) use ($dealId) {
                        $query->whereNull('deal_id')  // Только не привязанные брифы
                              ->orWhere('deal_id', $dealId); // Или привязанные к текущей сделке
                    })
                    ->get()
                    ->map(function($brief) use ($dealId, $users) {
                        $userName = '';
                        foreach ($users as $user) {
                            if ($user->id == $brief->user_id) {
                                $userName = $user->name;
                                break;
                            }
                        }

                        return [
                            'id' => $brief->id,
                            'title' => $brief->title ?? ('Бриф #' . $brief->id),
                            'user_id' => $brief->user_id,
                            'user_name' => $userName,
                            'created_at' => $brief->created_at->format('d.m.Y H:i'),
                            'already_linked' => $brief->deal_id == $dealId
                        ];
                    })
                    ->toArray();

                // Коммерческие брифы со статусом "Завершенный" или "Завершен"
                $commercialBriefs = \App\Models\Commercial::whereIn('user_id', $userIds)
                    ->whereIn('status', ['Завершенный', 'Завершен'])
                    ->where(function($query) use ($dealId) {
                        $query->whereNull('deal_id')  // Только не привязанные брифы
                              ->orWhere('deal_id', $dealId); // Или привязанные к текущей сделке
                    })
                    ->get()
                    ->map(function($brief) use ($dealId, $users) {
                        $userName = '';
                        foreach ($users as $user) {
                            if ($user->id == $brief->user_id) {
                                $userName = $user->name;
                                break;
                            }
                        }

                        return [
                            'id' => $brief->id,
                            'title' => $brief->title ?? ('Коммерческий бриф #' . $brief->id),
                            'user_id' => $brief->user_id,
                            'user_name' => $userName,
                            'created_at' => $brief->created_at->format('d.m.Y H:i'),
                            'already_linked' => $brief->deal_id == $dealId
                        ];
                    })
                    ->toArray();
            }

            // Формируем информацию о пользователях для отображения в результатах
            $usersInfo = $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'users' => $usersInfo,
                'briefs' => $commonBriefs,
                'commercials' => $commercialBriefs,
                'has_attached_brief' => $hasAttachedBrief,
                'attached_brief_type' => $attachedBriefType,
                'searched_phone' => $clientPhone,
                'message' => count($commonBriefs) + count($commercialBriefs) > 0
                    ? 'Найдены брифы для указанного телефона'
                    : 'Брифы не найдены для указанного телефона'
            ]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при поиске брифов по телефону: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при поиске брифов: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Привязка брифа к сделке
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function linkBriefToDeal(Request $request)
    {
        try {
            $dealId = $request->input('deal_id');
            $briefId = $request->input('brief_id');
            $briefType = $request->input('brief_type', 'common');

            if (!$dealId || !$briefId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не указан ID сделки или брифа'
                ], 400);
            }

            // Получаем информацию о сделке
            $deal = Deal::find($dealId);

            if (!$deal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Сделка не найдена'
                ], 404);
            }

            // В зависимости от типа брифа привязываем его к сделке
            if ($briefType === 'common') {
                $brief = Common::find($briefId);

                if (!$brief) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Бриф не найден'
                    ], 404);
                }

                // Проверяем, если к сделке уже привязан бриф
                if (!empty($deal->common_id) && $deal->common_id != $briefId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'К сделке уже привязан другой общий бриф'
                    ], 400);
                }

                // Привязываем бриф к сделке
                $deal->common_id = $briefId;
                $deal->save();

                // Также обновляем поле deal_id в брифе
                $brief->deal_id = $dealId;
                $brief->save();
            } elseif ($briefType === 'commercial') {
                $brief = Commercial::find($briefId);

                if (!$brief) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Коммерческий бриф не найден'
                    ], 404);
                }

                // Проверяем, если к сделке уже привязан бриф
                if (!empty($deal->commercial_id) && $deal->commercial_id != $briefId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'К сделке уже привязан другой коммерческий бриф'
                    ], 400);
                }

                // Привязываем бриф к сделке
                $deal->commercial_id = $briefId;
                $deal->save();

                // Также обновляем поле deal_id в брифе
                $brief->deal_id = $dealId;
                $brief->save();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Неизвестный тип брифа'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Бриф успешно привязан к сделке',
                'reload_required' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при привязке брифа: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Отвязка брифа от сделки
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlinkBriefFromDeal(Request $request)
    {
        try {
            $dealId = $request->input('deal_id');
            $briefType = $request->input('brief_type', 'common');

            if (!$dealId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не указан ID сделки'
                ], 400);
            }

            // Получаем информацию о сделке
            $deal = Deal::find($dealId);

            if (!$deal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Сделка не найдена'
                ], 404);
            }

            // В зависимости от типа отвязываем бриф от сделки
            if ($briefType === 'common') {
                if (empty($deal->common_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'К сделке не привязан общий бриф'
                    ], 400);
                }

                // Находим бриф и отвязываем его от сделки
                $brief = Common::find($deal->common_id);
                if ($brief) {
                    $brief->deal_id = null;
                    $brief->save();
                }

                // Отвязываем бриф от сделки
                $deal->common_id = null;
                $deal->save();
            } elseif ($briefType === 'commercial') {
                if (empty($deal->commercial_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'К сделке не привязан коммерческий бриф'
                    ], 400);
                }

                // Находим бриф и отвязываем его от сделки
                $brief = Commercial::find($deal->commercial_id);
                if ($brief) {
                    $brief->deal_id = null;
                    $brief->save();
                }

                // Отвязываем бриф от сделки
                $deal->commercial_id = null;
                $deal->save();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Неизвестный тип брифа'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Бриф успешно отвязан от сделки'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при отвязке брифа: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обработка загрузки файлов.
     *
     * @param Request $request
     * @param Deal $deal
     * @param string $field Имя поля с файлом
     * @param string|null $targetField Имя поля для сохранения пути
     * @return array Массив с путями к сохраненным файлам
     */
    private function handleFileUpload(Request $request, $deal, $field, $targetField = null)
    {
        if ($request->hasFile($field) && $request->file($field)->isValid()) {
            // Обрабатываем и "avatar", и "avatar_path" как аватар сделки
            if ($field === 'avatar' || $field === 'avatar_path') {
                $dir = "dels/{$deal->id}"; // Файл сохраняется в папку dels/{id сделки}
                $fileName = "avatar." . $request->file($field)->getClientOriginalExtension(); // Имя файла всегда "avatar"
            } else {
                $dir = "dels/{$deal->id}";
                $fileName = $field . '.' . $request->file($field)->getClientOriginalExtension();
            }

            // Проверяем, существует ли директория, и создаем её при необходимости
            $fullPath = storage_path("app/public/{$dir}");
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            $filePath = $request->file($field)->storeAs($dir, $fileName, 'public');

            // Логируем успешную загрузку файла
            Log::info('Файл успешно загружен', [
                'deal_id' => $deal->id,
                'field' => $field,
                'path' => $filePath
            ]);

            return [$targetField ?? $field => $filePath]; // Для аватара "avatar_path" будет установлен путь сохраненного файла
        }
        return [];
    }

    /**
     * Поиск брифов по ID пользователя (клиента)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function findBriefsByUserId(Request $request)
    {
        try {
            $dealId = $request->input('deal_id');
            $userId = $request->input('user_id');

            if (!$dealId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не указан ID сделки'
                ], 400);
            }

            // Получаем информацию о сделке
            $deal = Deal::find($dealId);

            if (!$deal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Сделка не найдена'
                ], 404);
            }

            // Если user_id не передан, пытаемся взять его из сделки
            if (!$userId && !empty($deal->user_id)) {
                $userId = $deal->user_id;
                \Log::info('Использование user_id из сделки', ['deal_id' => $dealId, 'user_id' => $userId]);
            }

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не найден ID пользователя для поиска брифов'
                ], 400);
            }

            // Получаем информацию о пользователе
            $user = \App\Models\User::find($userId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не найден'
                ], 404);
            }

            \Log::info('Поиск брифов для пользователя', [
                'deal_id' => $dealId,
                'user_id' => $userId,
                'user_name' => $user->name
            ]);

            // Проверяем наличие привязанных брифов в сделке
            $hasAttachedBrief = !empty($deal->common_id) || !empty($deal->commercial_id);
            $attachedBriefType = !empty($deal->common_id) ? 'common' : (!empty($deal->commercial_id) ? 'commercial' : null);

            // Получаем брифы пользователя

            // Общие брифы со статусом "Завершенный" или "Завершен"
            $commonBriefs = \App\Models\Common::where('user_id', $userId)
                ->whereIn('status', ['Завершенный', 'Завершен'])
                ->where(function($query) use ($dealId) {
                    $query->whereNull('deal_id')  // Только не привязанные брифы
                          ->orWhere('deal_id', $dealId); // Или привязанные к текущей сделке
                })
                ->get()
                ->map(function($brief) use ($dealId, $user) {
                    return [
                        'id' => $brief->id,
                        'title' => $brief->title ?? ('Бриф #' . $brief->id),
                        'user_id' => $brief->user_id,
                        'user_name' => $user->name,
                        'created_at' => $brief->created_at->format('d.m.Y H:i'),
                        'already_linked' => $brief->deal_id == $dealId
                    ];
                })
                ->toArray();

            \Log::info('Найдены общие брифы', ['count' => count($commonBriefs)]);

            // Коммерческие брифы со статусом "Завершенный" или "Завершен"
            $commercialBriefs = \App\Models\Commercial::where('user_id', $userId)
                ->whereIn('status', ['Завершенный', 'Завершен'])
                ->where(function($query) use ($dealId) {
                    $query->whereNull('deal_id')  // Только не привязанные брифы
                          ->orWhere('deal_id', $dealId); // Или привязанные к текущей сделке
                })
                ->get()
                ->map(function($brief) use ($dealId, $user) {
                    return [
                        'id' => $brief->id,
                        'title' => $brief->title ?? ('Коммерческий бриф #' . $brief->id),
                        'user_id' => $brief->user_id,
                        'user_name' => $user->name,
                        'created_at' => $brief->created_at->format('d.m.Y H:i'),
                        'already_linked' => $brief->deal_id == $dealId
                    ];
                })
                ->toArray();

            \Log::info('Найдены коммерческие брифы', ['count' => count($commercialBriefs)]);

            // Формируем информацию о пользователе для отображения в результатах
            $usersInfo = [[
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone
            ]];

            return response()->json([
                'success' => true,
                'users' => $usersInfo,
                'briefs' => $commonBriefs,
                'commercials' => $commercialBriefs,
                'has_attached_brief' => $hasAttachedBrief,
                'attached_brief_type' => $attachedBriefType,
                'user_id' => $userId,
                'message' => count($commonBriefs) + count($commercialBriefs) > 0
                    ? 'Найдены брифы для клиента'
                    : 'Брифы не найдены для клиента'
            ]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при поиске брифов по user_id: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при поиске брифов: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обработка загрузки документов сделки
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Загрузка документов для сделки (улучшенная версия с поддержкой Яндекс.Диска)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadDocuments(Request $request)
    {
        try {
            $dealId = $request->input('deal_id');

            Log::info('🚀 Начало загрузки документов', [
                'deal_id' => $dealId,
                'files_count' => $request->hasFile('documents') ? count($request->file('documents')) : 0,
                'user_id' => Auth::id()
            ]);

            // Валидация входящих данных - убираем ограничения размера файлов для поддержки больших файлов
            $validator = Validator::make($request->all(), [
                'deal_id' => 'required|exists:deals,id',
                'documents' => 'required|array',
                'documents.*' => 'file', // Убираем ограничение размера
            ]);

            if ($validator->fails()) {
                Log::warning('⚠️ Ошибка валидации при загрузке документов', [
                    'errors' => $validator->errors()->all(),
                    'deal_id' => $dealId
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации: ' . implode(', ', $validator->errors()->all())
                ], 422);
            }

            // Получаем сделку
            $deal = Deal::findOrFail($dealId);

            // Проверяем права доступа
            if (!in_array(Auth::user()->status, ['coordinator', 'partner', 'admin'])) {
                Log::warning('🚫 Отказ в доступе для загрузки документов', [
                    'user_id' => Auth::id(),
                    'user_status' => Auth::user()->status,
                    'deal_id' => $dealId
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'У вас нет прав на загрузку документов'
                ], 403);
            }

            $uploadedDocuments = [];
            $uploadErrors = [];
            // Принудительно используем локальное хранилище вместо Яндекс.Диска
            $useYandexDisk = false;

            Log::info('📂 Способ загрузки определен', [
                'use_yandex_disk' => $useYandexDisk,
                'storage_type' => 'local',
                'files_count' => count($request->file('documents'))
            ]);

            if ($useYandexDisk) {
                // Загрузка через Яндекс.Диск для больших файлов
                $yandexDiskService = app(YandexDiskService::class);
                $uploadPath = "deals/{$dealId}/documents";

                Log::info('☁️ Используем Яндекс.Диск для загрузки', ['upload_path' => $uploadPath]);

                // Обрабатываем каждый файл
                foreach ($request->file('documents') as $index => $file) {
                    if ($file->isValid()) {
                        try {
                            Log::info("📄 Загружаем файл #{$index}", [
                                'original_name' => $file->getClientOriginalName(),
                                'size' => $file->getSize(),
                                'mime_type' => $file->getMimeType()
                            ]);

                            // Генерируем уникальное имя файла
                            $originalName = $file->getClientOriginalName();
                            $safeFileName = $this->generateSafeFileName($originalName);
                            $filePath = $uploadPath . '/' . $safeFileName;

                            // Загружаем на Яндекс.Диск
                            $uploadResult = $yandexDiskService->uploadFile($file, $filePath);

                            if ($uploadResult['success']) {
                                $uploadedDocuments[] = [
                                    'name' => $originalName,
                                    'path' => $filePath,
                                    'url' => $uploadResult['url'],
                                    'original_name' => $originalName,
                                    'size' => $file->getSize(),
                                    'extension' => $file->getClientOriginalExtension(),
                                    'icon' => 'fas ' . $this->getFileIconClass($file->getClientOriginalExtension()),
                                    'storage_type' => 'yandex_disk',
                                    'uploaded_at' => now()->toISOString()
                                ];

                                Log::info("✅ Файл успешно загружен на Яндекс.Диск", [
                                    'file' => $originalName,
                                    'url' => $uploadResult['url']
                                ]);
                            } else {
                                $uploadErrors[] = [
                                    'file' => $originalName,
                                    'error' => $uploadResult['message'] ?? 'Неизвестная ошибка загрузки на Яндекс.Диск'
                                ];

                                Log::error("❌ Ошибка загрузки на Яндекс.Диск", [
                                    'file' => $originalName,
                                    'error' => $uploadResult['message'] ?? 'Неизвестная ошибка'
                                ]);
                            }

                        } catch (\Exception $e) {
                            $uploadErrors[] = [
                                'file' => $file->getClientOriginalName(),
                                'error' => 'Исключение при загрузке: ' . $e->getMessage()
                            ];

                            Log::error("💥 Исключение при загрузке файла", [
                                'file' => $file->getClientOriginalName(),
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    } else {
                        $uploadErrors[] = [
                            'file' => $file->getClientOriginalName(),
                            'error' => 'Файл поврежден или невалиден'
                        ];
                    }
                }

            } else {
                // Локальная загрузка (fallback)
                Log::info('💾 Используем локальное хранилище');

                $directory = "deals/{$dealId}/documents";
                $fullPath = storage_path("app/public/{$directory}");

                if (!file_exists($fullPath)) {
                    mkdir($fullPath, 0755, true);
                }

                foreach ($request->file('documents') as $file) {
                    if ($file->isValid()) {
                        try {
                            $originalName = $file->getClientOriginalName();
                            $safeFileName = $this->generateSafeFileName($originalName);

                            $path = $file->storeAs($directory, $safeFileName, 'public');

                            if ($path) {
                                $uploadedDocuments[] = [
                                    'name' => $originalName,
                                    'path' => $path,
                                    'url' => url('storage/' . $path),
                                    'original_name' => $originalName,
                                    'size' => $file->getSize(),
                                    'extension' => $file->getClientOriginalExtension(),
                                    'icon' => 'fas ' . $this->getFileIconClass($file->getClientOriginalExtension()),
                                    'storage_type' => 'local',
                                    'uploaded_at' => now()->toISOString()
                                ];
                            }
                        } catch (\Exception $e) {
                            $uploadErrors[] = [
                                'file' => $file->getClientOriginalName(),
                                'error' => 'Ошибка локального сохранения: ' . $e->getMessage()
                            ];
                        }
                    }
                }
            }

            // Обновляем список документов в сделке
            $this->updateDealDocuments($deal, $uploadedDocuments);

            $successCount = count($uploadedDocuments);
            $errorCount = count($uploadErrors);
            $totalCount = $successCount + $errorCount;

            Log::info('📊 Результаты загрузки документов', [
                'deal_id' => $dealId,
                'total_files' => $totalCount,
                'successful' => $successCount,
                'errors' => $errorCount,
                'storage_type' => $useYandexDisk ? 'yandex_disk' : 'local'
            ]);

            // Формируем ответ
            $response = [
                'success' => $successCount > 0,
                'message' => $this->formatUploadMessage($successCount, $errorCount, $totalCount),
                'documents' => $uploadedDocuments,
                'errors' => $uploadErrors,
                'stats' => [
                    'total' => $totalCount,
                    'successful' => $successCount,
                    'failed' => $errorCount,
                    'storage_type' => $useYandexDisk ? 'yandex_disk' : 'local'
                ]
            ];

            if ($errorCount > 0) {
                $response['warnings'] = "Не удалось загрузить {$errorCount} файлов";
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('💥 Критическая ошибка при загрузке документов', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'deal_id' => $request->input('deal_id'),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Произошла критическая ошибка при загрузке документов: ' . $e->getMessage(),
                'error_type' => 'system_error'
            ], 500);
        }
    }

    /**
     * Генерация безопасного имени файла
     */
    private function generateSafeFileName($originalName)
    {
        $pathInfo = pathinfo($originalName);
        $fileName = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? '';

        // Очищаем имя файла от опасных символов
        $safeFileName = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $fileName);
        $safeFileName = preg_replace('/_+/', '_', $safeFileName); // Убираем множественные подчеркивания
        $safeFileName = trim($safeFileName, '_');

        // Ограничиваем длину
        if (strlen($safeFileName) > 50) {
            $safeFileName = substr($safeFileName, 0, 50);
        }

        // Добавляем временную метку и уникальный ID
        $timestamp = time();
        $uniqueId = substr(uniqid(), -6);

        return $safeFileName . '_' . $timestamp . '_' . $uniqueId . ($extension ? '.' . $extension : '');
    }

    /**
     * Обновление списка документов в сделке
     */
    private function updateDealDocuments($deal, $newDocuments)
    {
        try {
            // Получаем существующие документы
            $currentDocuments = [];
            if ($deal->documents) {
                if (is_string($deal->documents)) {
                    $currentDocuments = json_decode($deal->documents, true) ?: [];
                } elseif (is_array($deal->documents)) {
                    $currentDocuments = $deal->documents;
                }
            }

            // Добавляем новые документы с полной информацией
            foreach ($newDocuments as $doc) {
                $currentDocuments[] = [
                    'name' => $doc['name'],
                    'original_name' => $doc['original_name'],
                    'path' => $doc['path'],
                    'url' => $doc['url'],
                    'size' => $doc['size'],
                    'extension' => $doc['extension'],
                    'storage_type' => $doc['storage_type'],
                    'uploaded_at' => $doc['uploaded_at'],
                    'uploaded_by' => Auth::id()
                ];
            }

            // Сохраняем обновленный список
            $deal->documents = json_encode($currentDocuments);
            $deal->save();

            Log::info('📝 Список документов сделки обновлен', [
                'deal_id' => $deal->id,
                'total_documents' => count($currentDocuments),
                'new_documents' => count($newDocuments)
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Ошибка обновления списка документов', [
                'deal_id' => $deal->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Форматирование сообщения о результатах загрузки
     */
    private function formatUploadMessage($successCount, $errorCount, $totalCount)
    {
        if ($errorCount === 0) {
            if ($successCount === 1) {
                return "Документ успешно загружен";
            } else {
                return "Все {$successCount} документов успешно загружены";
            }
        } elseif ($successCount === 0) {
            if ($totalCount === 1) {
                return "Не удалось загрузить документ";
            } else {
                return "Не удалось загрузить ни одного документа";
            }
        } else {
            return "Загружено {$successCount} из {$totalCount} документов";
        }
    }

    /**
     * Определить класс иконки файла по расширению
     *
     * @param string $extension
     * @return string
     */
    private function getFileIconClass($extension)
    {
        $extension = strtolower($extension);

        switch ($extension) {
            case 'pdf':
                return 'fa-file-pdf';
            case 'doc':
            case 'docx':
                return 'fa-file-word';
            case 'xls':
            case 'xlsx':
                return 'fa-file-excel';
            case 'ppt':
            case 'pptx':
                return 'fa-file-powerpoint';
            case 'zip':
            case 'rar':
            case '7z':
                return 'fa-file-archive';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'webp':
            case 'svg':
            case 'bmp':
                return 'fa-file-image';
            case 'txt':
                return 'fa-file-alt';
            case 'mp4':
            case 'avi':
            case 'mov':
            case 'wmv':
                return 'fa-file-video';
            case 'mp3':
            case 'wav':
            case 'ogg':
                return 'fa-file-audio';
            case 'html':
            case 'css':
            case 'js':
            case 'php':
            case 'json':
            case 'xml':
                return 'fa-file-code';
            default:
                return 'fa-file';
        }
    }

    /**
     * Отображение отдельной страницы редактирования сделки
     * Заменяет функциональность модального окна на полную страницу
     */
    public function editDealPage($dealId)
    {
        try {
            // Получаем сделку с необходимыми связями
            $deal = Deal::with([
                'coordinator',
                'partner',
                'architect',
                'designer',
                'visualizer',
                'dealFeeds.user',
                'dealFeeds' => function($q) {
                    $q->orderBy('created_at', 'desc');
                },
                'users'
            ])->findOrFail($dealId);

            // Проверяем права доступа к сделке - только coordinator, admin, partner
            $user = Auth::user();
            $hasAccess = false;

            // Администратор и координатор имеют доступ ко всем сделкам
            if (in_array($user->status, ['admin', 'coordinator'])) {
                $hasAccess = true;
            }
            // Партнер имеет доступ к своим сделкам
            elseif ($user->status === 'partner' && $deal->office_partner_id === $user->id) {
                $hasAccess = true;
            }

            if (!$hasAccess) {
                return redirect()->route('deal.cardinator')
                    ->with('error', 'У вас нет доступа к этой сделке');
            }

            // Получаем структуру полей сделки
            $dealFields = $this->getDealFields($deal);

            // Получаем дополнительные данные для формы
            $coordinators = \App\Models\User::where('status', 'coordinator')->get();
            $partners = \App\Models\User::where('status', 'partner')->get();
            $architects = \App\Models\User::where('status', 'architect')->get();
            $designers = \App\Models\User::where('status', 'designer')->get();
            $visualizers = \App\Models\User::where('status', 'visualizer')->get();

            // Получаем города из JSON файла
            $citiesFile = public_path('cities.json');
            $russianCities = [];
            if (file_exists($citiesFile)) {
                $citiesJson = file_get_contents($citiesFile);
                $citiesData = json_decode($citiesJson, true) ?: [];

                // Добавляем базовые часовые пояса для основных городов
                $timezones = [
                    'Москва' => 'UTC+3',
                    'Санкт-Петербург' => 'UTC+3',
                    'Новосибирск' => 'UTC+7',
                    'Екатеринбург' => 'UTC+5',
                    'Казань' => 'UTC+3',
                    'Нижний Новгород' => 'UTC+3',
                    'Челябинск' => 'UTC+5',
                    'Самара' => 'UTC+4',
                    'Омск' => 'UTC+6',
                    'Ростов-на-Дону' => 'UTC+3',
                    'Уфа' => 'UTC+5',
                    'Красноярск' => 'UTC+7',
                    'Воронеж' => 'UTC+3',
                    'Пермь' => 'UTC+5',
                    'Волгоград' => 'UTC+3'
                ];

                // Преобразуем данные городов, добавляя часовые пояса где возможно
                foreach ($citiesData as $cityData) {
                    if (isset($cityData['city'])) {
                        $city = $cityData['city'];
                        $russianCities[] = [
                            'city' => $city,
                            'region' => $cityData['region'] ?? '',
                            'timezone' => $timezones[$city] ?? 'UTC+3' // По умолчанию московское время
                        ];
                    }
                }
            }

            // Статусы сделок
            $statuses = [
                'Ждем ТЗ', 'Планировка', 'Коллажи', 'Визуализация', 'Рабочка/сбор ИП',
                'Проект готов', 'Проект завершен', 'Проект на паузе', 'Возврат',
                'В работе', 'Завершенный', 'На потом', 'Регистрация',
                'Бриф прикриплен', 'Поддержка', 'Активный'
            ];

            // Пакеты услуг
            $packages = [
                'Стандарт',
                'Премиум',
                'Люкс'
            ];

            // Опции ценообразования
            $priceServiceOptions = [
                'За м²',
                'За объект',
                'Почасовая оплата'
            ];

            // Заголовок страницы
            $title_site = "Редактирование сделки #{$deal->id} - {$deal->client_name}";

            Log::info('Открыта страница редактирования сделки', [
                'deal_id' => $deal->id,
                'user_id' => $user->id,
                'user_status' => $user->status
            ]);

            return view('deals.edit', compact(
                'deal',
                'title_site',
                'coordinators',
                'partners',
                'architects',
                'designers',
                'visualizers',
                'russianCities',
                'statuses',
                'packages',
                'priceServiceOptions',
                'dealFields'
            ))->with('userRole', $user->status);

        } catch (\Exception $e) {
            Log::error('Ошибка при открытии страницы редактирования сделки', [
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('deal.cardinator')
                ->with('error', 'Ошибка при открытии сделки: ' . $e->getMessage());
        }
    }

    /**
     * Получение структуры полей сделки для формы редактирования
     * Скопировано из DealModalController для полной совместимости
     */
    private function getDealFields($deal = null) {
        // Получаем только необходимые списки пользователей для полей
        $coordinators = User::where('status', 'coordinator')->pluck('name', 'id')->toArray();
        $partners = User::where('status', 'partner')->pluck('name', 'id')->toArray();
        $architects = User::where('status', 'architect')->pluck('name', 'id')->toArray();
        $designers = User::where('status', 'designer')->pluck('name', 'id')->toArray();
        $visualizers = User::where('status', 'visualizer')->pluck('name', 'id')->toArray();

        // Добавляем пустые опции в начало списков для возможности сброса выбора
        $coordinators = ['' => '-- Выберите координатора --'] + $coordinators;
        $partners = ['' => '-- Выберите партнера --'] + $partners;
        $architects = ['' => '-- Выберите архитектора --'] + $architects;
        $designers = ['' => '-- Выберите дизайнера --'] + $designers;
        $visualizers = ['' => '-- Выберите визуализатора --'] + $visualizers;

        return [
            'zakaz' => [
                [
                    'name' => 'client_phone',
                    'icon' => 'fas fa-phone',
                    'type' => 'text',
                    'label' => 'Телефон клиента',
                    'role' => ['coordinator', 'partner', 'admin'],
                    'required' => true,
                    'class' => 'maskphone',
                    'id' => 'client_phone',
                ],
                [
                    'name' => 'project_number',
                    'label' => '№ проекта',
                    'type' => 'text',
                    'role' => ['coordinator', 'admin'], // PARTNER только читает
                    'readonly_roles' => ['partner'], // Добавляем поле для ролей только для чтения
                    'maxlength' => 150,
                    'icon' => 'fas fa-hashtag',
                    'required' => true,
                    'description' => 'Основной идентификатор сделки',
                ],
                [
                    'name' => 'client_name',
                    'label' => 'Имя клиента',
                    'type' => 'text',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'maxlength' => 255,
                    'icon' => 'fas fa-user',
                    'required' => true,
                    'description' => 'Имя клиента по сделке',
                ],
                [
                    'name' => 'avatar_path',
                    'label' => 'Аватар сделки',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'image/*',
                    'icon' => 'fas fa-image',
                ],
                [
                    'name' => 'status',
                    'label' => 'Статус',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'], // PARTNER только читает
                    'readonly_roles' => ['partner'], // Добавляем поле для ролей только для чтения
                    'options' => [
                        'Ждем ТЗ' => 'Ждем ТЗ',
                        'Планировка' => 'Планировка',
                        'Коллажи' => 'Коллажи',
                        'Визуализация' => 'Визуализация',
                        'Рабочка/сбор ИП' => 'Рабочка/сбор ИП',
                        'Проект готов' => 'Проект готов',
                        'Проект завершен' => 'Проект завершен',
                        'Проект на паузе' => 'Проект на паузе',
                        'Возврат' => 'Возврат',
                        'Регистрация' => 'Регистрация',
                        'Бриф прикриплен' => 'Бриф прикриплен',
                    ],
                    'selected' => $deal ? $deal->status : null,
                    'icon' => 'fas fa-tag',
                ],
                [
                    'name' => 'coordinator_id',
                    'label' => 'Координатор',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'], // PARTNER только читает
                    'readonly_roles' => ['partner'], // Добавляем поле для ролей только для чтения
                    'options' => $coordinators,
                    'selected' => $deal ? $deal->coordinator_id : null,
                    'icon' => 'fas fa-user-tie',
                ],
                [
                    'name' => 'client_timezone',
                    'label' => 'Город/часовой пояс',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'options' => [],
                    'selected' => $deal ? $deal->client_timezone : null,
                    'icon' => 'fas fa-city',
                ],
                [
                    'name' => 'office_partner_id',
                    'label' => 'Партнер',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'], // PARTNER только читает
                    'readonly_roles' => ['partner'], // Добавляем поле для ролей только для чтения
                    'options' => $partners,
                    'selected' => $deal ? $deal->office_partner_id : null,
                    'icon' => 'fas fa-handshake',
                ],
                [
                    'name' => 'package',
                    'label' => 'Пакет',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'options' => [
                        'Первый пакет 1400 м2' => 'Первый пакет 1400 м2',
                        'Второй пакет 85% комиссия' => 'Второй пакет 85% комиссия',
                        'Третий пакет 55% комиссия' => 'Третий пакет 55% комиссия',
                        'Партнер 75% комиссия' => 'Партнер 75% комиссия',
                    ],
                    'selected' => $deal ? $deal->package : null,
                    'icon' => 'fas fa-box',
                ],
                [
                    'name' => 'price_service_option',
                    'label' => 'Услуга по прайсу',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'options' => [
                        'экспресс планировка' => 'Экспресс планировка',
                        'экспресс планировка с коллажами' => 'Экспресс планировка с коллажами',
                        'экспресс проект с электрикой' => 'Экспресс проект с электрикой',
                        'экспресс планировка с электрикой и коллажами' => 'Экспресс планировка с электрикой и коллажами',
                        'экспресс рабочий проект' => 'Экспресс рабочий проект',
                        'экспресс эскизный проект с рабочей документацией' => 'Экспресс эскизный проект с рабочей документацией',
                        'экспресс 3Dвизуализация с коллажами' => 'экспресс 3Dвизуализация с коллажами ',
                        'экспресс полный дизайн-проект' => 'Экспресс полный дизайн-проект',
                        'Визуализация на одну комнату' => 'Визуализация на одну комнату',
                    ],
                    'selected' => $deal ? $deal->price_service_option : null,
                    'required' => true,
                    'icon' => 'fas fa-list-check',
                ],
                [
                    'name' => 'rooms_count_pricing',
                    'label' => 'Кол-во комнат по прайсу',
                    'type' => 'text',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'icon' => 'fas fa-door-open',
                ],
                [
                    'name' => 'completion_responsible',
                    'label' => 'Кто делает комплектацию',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'options' => [
                        'клиент' => 'Клиент',
                        'партнер' => 'Партнер',
                        'шопинг-лист' => 'Шопинг-лист',
                        'закупки и снабжение от УК' => 'Нужны закупки и снабжение от УК',
                    ],
                    'selected' => $deal ? $deal->completion_responsible : null,
                    'icon' => 'fas fa-clipboard-check',
                ],
                [
                    'name' => 'created_date',
                    'label' => 'Дата создания сделки',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin'], // PARTNER только читает
                    'readonly_roles' => ['partner'], // Добавляем поле для ролей только для чтения
                    'icon' => 'fas fa-calendar-plus',
                ],
                [
                    'name' => 'payment_date',
                    'label' => 'Дата оплаты',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'icon' => 'fas fa-money-check',
                ],
                [
                    'name' => 'total_sum',
                    'label' => 'Сумма заказа',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin'], // PARTNER только читает
                    'readonly_roles' => ['partner'], // Добавляем поле для ролей только для чтения
                    'step' => '0.01',
                    'icon' => 'fas fa-ruble-sign',
                ],
                [
                    'name' => 'comment',
                    'label' => 'Общий комментарий',
                    'description' => 'Подробные заметки о сделке',
                    'type' => 'textarea',
                    'icon' => 'fas fa-sticky-note',
                    'role' => ['admin', 'coordinator', 'partner'],
                    'maxlength' => 1000,
                ],
                [
                    'name' => 'measurements_file',
                    'label' => 'Замеры',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'accept' => '.pdf,.dwg,image/*',
                    'icon' => 'fas fa-ruler-combined',
                ],
            ],
            'rabota' => [
                [
                    'name' => 'start_date',
                    'label' => 'Дата старта работы по проекту',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'icon' => 'fas fa-play',
                ],
                [
                    'name' => 'project_duration',
                    'label' => 'Общий срок проекта (в рабочих днях)',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'icon' => 'fas fa-hourglass-half',
                ],
                [
                    'name' => 'project_end_date',
                    'label' => 'Дата завершения',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'icon' => 'fas fa-flag-checkered',
                ],
                [
                    'name' => 'architect_id',
                    'label' => 'Архитектор',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'], // PARTNER только читает
                    'readonly_roles' => ['partner'], // Добавляем поле для ролей только для чтения
                    'options' => $architects,
                    'selected' => $deal ? $deal->architect_id : null,
                    'icon' => 'fas fa-drafting-compass',
                ],
                [
                    'name' => 'designer_id',
                    'label' => 'Дизайнер',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'], // PARTNER только читает
                    'readonly_roles' => ['partner'], // Добавляем поле для ролей только для чтения
                    'options' => $designers,
                    'selected' => $deal ? $deal->designer_id : null,
                    'icon' => 'fas fa-palette',
                ],
                [
                    'name' => 'visualizer_id',
                    'label' => 'Визуализатор',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'], // PARTNER только читает
                    'readonly_roles' => ['partner'], // Добавляем поле для ролей только для чтения
                    'options' => $visualizers,
                    'selected' => $deal ? $deal->visualizer_id : null,
                    'icon' => 'fas fa-eye',
                ],
                [
                    'name' => 'plan_final',
                    'label' => 'Ссылка на планировку',
                    'type' => 'url',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'icon' => 'fas fa-link',
                ],
            ],
            'final' => [
                [
                    'name' => 'measurements_file',
                    'label' => 'Замеры',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png',
                    'icon' => 'fas fa-ruler',
                    'description' => 'Файл с замерами помещений'
                ],
                [
                    'name' => 'final_project_file',
                    'label' => 'Финал проекта (PDF, до 1.5ГБ)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'accept' => 'application/pdf',
                    'icon' => 'fas fa-file-pdf',
                    'description' => 'Финальная версия проекта в формате PDF'
                ],
                [
                    'name' => 'work_act',
                    'label' => 'Акт выполненных работ (PDF)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'accept' => 'application/pdf',
                    'icon' => 'fas fa-file-signature',
                    'description' => 'Акт выполненных работ в формате PDF'
                ],
                [
                    'name' => 'chat_screenshot',
                    'label' => 'Скрин чата с оценкой и актом (JPEG)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'accept' => 'image/jpeg,image/jpg,image/png',
                    'icon' => 'fas fa-camera',
                    'description' => 'Скриншот чата с оценкой и актом'
                ],
                [
                    'name' => 'archicad_file',
                    'label' => 'Исходный файл архикад (pln, dwg)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'accept' => '.pln,.dwg',
                    'icon' => 'fas fa-file-code',
                    'description' => 'Исходный файл проекта в формате ArchiCAD или AutoCAD'
                ],
            ],
        ];
    }

    /**
     * Получить информацию о документах сделки
     */
    private function getDealDocuments($deal)
    {
        $documents = [];

        $fileFields = [
            'execution_order_file', 'measurements_file', 'final_floorplan', 'final_collage',
            'final_project_file', 'work_act', 'archicad_file', 'contract_attachment',
            'plan_final', 'chat_screenshot'
        ];

        foreach ($fileFields as $field) {
            $yandexUrlField = "yandex_url_{$field}";
            $originalNameField = "original_name_{$field}";

            if (isset($deal->$yandexUrlField) && !empty($deal->$yandexUrlField)) {
                $extension = 'pdf';
                $filename = $deal->$originalNameField ?? "{$field}.pdf";

                if (!empty($deal->$originalNameField)) {
                    $extension = pathinfo($deal->$originalNameField, PATHINFO_EXTENSION);
                }

                $documents[] = [
                    'id' => $deal->id . '_' . $field,
                    'name' => $filename,
                    'path' => $deal->$yandexUrlField,
                    'extension' => $extension,
                    'icon' => $this->getFileIcon($extension),
                    'url' => $deal->$yandexUrlField,
                    'field' => $field
                ];
            }
        }

        return $documents;
    }

    /**
     * Получить иконку для файла по расширению
     */
    private function getFileIcon($extension)
    {
        $icons = [
            'pdf' => 'fas fa-file-pdf',
            'doc' => 'fas fa-file-word',
            'docx' => 'fas fa-file-word',
            'jpg' => 'fas fa-file-image',
            'jpeg' => 'fas fa-file-image',
            'png' => 'fas fa-file-image',
            'dwg' => 'fas fa-file-code',
            'pln' => 'fas fa-file-code',
        ];

        return $icons[strtolower($extension)] ?? 'fas fa-file';
    }

    /**
     * Получение актуальных данных сделки для обновления интерфейса
     */
    public function getDealData($id)
    {
        try {
            $deal = Deal::findOrFail($id);

            // Проверяем права доступа к сделке
            $user = Auth::user();
            if (!$this->canUserAccessDeal($user, $deal)) {
                return response()->json([
                    'success' => false,
                    'message' => 'У вас нет прав для просмотра этой сделки'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'deal' => $deal->toArray()
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка при получении данных сделки', [
                'deal_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении данных сделки: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Проверка прав доступа пользователя к сделке
     */
    private function canUserAccessDeal($user, $deal)
    {
        // Администраторы и координаторы имеют доступ ко всем сделкам
        if (in_array($user->status, ['admin', 'coordinator'])) {
            return true;
        }

        // Клиенты имеют доступ только к своим сделкам
        if ($user->status === 'user' && $deal->user_id === $user->id) {
            return true;
        }

        // Исполнители имеют доступ к назначенным им сделкам
        if (in_array($user->status, ['architect', 'designer', 'visualizer']) &&
            ($deal->architect_id === $user->id ||
             $deal->designer_id === $user->id ||
             $deal->visualizer_id === $user->id)) {
            return true;
        }

        // Партнеры имеют доступ к сделкам где они указаны как партнер
        if ($user->status === 'partner' && $deal->partner_id === $user->id) {
            return true;
        }

        return false;
    }
}
