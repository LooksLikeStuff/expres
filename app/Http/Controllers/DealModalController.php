<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealFeed;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Http\Controllers\Traits\NotifyExecutorsTrait;

class DealModalController extends Controller
{
    use NotifyExecutorsTrait;
    /**
     * Отображение модального окна для сделки.
     */
    public function getDealModal($id)
    {
        try {
            // Проверяем существование обязательных шаблонов компонентов
            $requiredTemplates = [
                'deals.partials.components.header_actions',
                'deals.partials.components.tab_zakaz',
                'deals.partials.components.tab_rabota',
                'deals.partials.components.tab_documents',
                'deals.partials.components.styles',
                'deals.partials.components.scripts'
            ];

            foreach ($requiredTemplates as $template) {
                if (!View::exists($template)) {
                    Log::error("Отсутствует шаблон компонента: {$template}");
                    throw new \Exception("Отсутствует шаблон компонента: {$template}");
                }
            }

            // Сначала проверяем, существует ли сделка
            if (!Deal::where('id', $id)->exists()) {
                Log::warning("Попытка открыть несуществующую сделку", ['deal_id' => $id]);
                return response()->json([
                    'success' => false, 
                    'error' => 'Сделка не найдена'
                ], 404);
            }

            // Получаем сделку по ID с предзагрузкой связанных данных
            // Используем chainable with() для лучшей читаемости
            $deal = Deal::with('coordinator')
                ->with('responsibles')
                ->with('users')
                ->with('architect')
                ->with('designer')
                ->with('visualizer')
                ->with('partner')
                ->findOrFail($id);
                
            // Получаем фиды (комментарии) для сделки
            $feeds = DealFeed::where('deal_id', $id)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();
        
            // Формирование полей сделки
            $dealFields = $this->getDealFields($deal);

            // Добавляем переменную page для корректной работы пагинации
            $page = request()->get('page', 1);

            // Функция для получения имени пользователя по ID
            $getUserName = function($id) {
                if (!$id) return 'Не назначен';
                $user = User::find($id);
                return $user ? $user->name : 'Пользователь не найден';
            };
                
            // Добавляем информацию о специалистах
            $deal->visualizer_name = $getUserName($deal->visualizer_id);
            $deal->coordinator_name = $getUserName($deal->coordinator_id);
            $deal->partner_name = $getUserName($deal->office_partner_id);
            $deal->architect_name = $getUserName($deal->architect_id);
            $deal->designer_name = $getUserName($deal->designer_id);

            // Получаем документы сделки - обертываем в try-catch дополнительно
            try {
                $documents = $this->getDealDocuments($deal);
            } catch (\Exception $e) {
                Log::error("Ошибка при получении документов сделки: " . $e->getMessage(), [
                    'exception' => $e,
                    'deal_id' => $id
                ]);
                // В случае ошибки с документами используем пустой массив
                $documents = [];
            }

            // Проверяем наличие необходимых переменных перед рендерингом представления
            if (!isset($deal) || !isset($dealFields)) {
                Log::error("Отсутствуют необходимые данные для рендеринга представления", [
                    'deal_exists' => isset($deal),
                    'dealFields_exists' => isset($dealFields)
                ]);
                return response()->json([
                    'success' => false, 
                    'error' => 'Ошибка при подготовке данных сделки'
                ], 500);
            }

            $viewData = compact('deal', 'feeds', 'dealFields', 'page', 'documents');
            
            // Оборачиваем рендеринг представления в дополнительный try/catch
            try {
                $renderedView = view('deals.partials.dealModal', $viewData)->render();
                
            } catch (\Exception $e) {
                Log::error("Ошибка рендеринга представления: " . $e->getMessage(), [
                    'exception' => $e,
                    'deal_id' => $id
                ]);
                return response()->json([
                    'success' => false, 
                    'error' => 'Ошибка при формировании представления: ' . $e->getMessage()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'html' => $renderedView
            ]);
        } catch (\Exception $e) {
            Log::error("Ошибка отображения модального окна сделки: " . $e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'deal_id' => $id
            ]);
            
            return response()->json([
                'success' => false, 
                'error' => 'Ошибка при загрузке данных сделки: ' . $e->getMessage()
            ], 500);
        }
    }

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
                    'type' => 'text', // Явно указываем тип поля
                    'label' => 'Телефон клиента', // Добавляем понятную метку
                           'role' => ['coordinator', 'partner', 'admin'],
                  
                    'required' => true, // Делаем поле обязательным для заполнения
                    'class' => 'maskphone', // Добавляем стиль для маски телефона
                    'id' => 'client_phone', // Добавляем явный ID для надежности
                ],
                [
                    'name' => 'project_number',
                    'label' => '№ проекта',
                    'type' => 'text',
                    'role' => ['coordinator', 'admin'],
                    'maxlength' => 150,
                    'icon' => 'fas fa-hashtag',
                    'required' => true, // Добавляем required, так как это поле теперь основное
                    'description' => 'Основной идентификатор сделки', // Добавляем описание
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
                    'role' => ['coordinator', 'admin'],
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
                    'role' => ['coordinator', 'admin'],
                    'options' => $coordinators,
                    'selected' => $deal ? $deal->coordinator_id : null,
                    'icon' => 'fas fa-user-tie',
                ],[
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
                    'role' => ['coordinator', 'admin'],
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
                        'Ландшафт' => 'Ландшафт',
                        'экспресс экстерьер' => 'Экспресс экстерьер',
                        'экспресс эскизный экстерьер' => 'Экспресс эскизный экстерьер',
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
                    'role' => ['coordinator', 'admin'],
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
                    'role' => ['coordinator', 'admin'],
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
                    'role' => ['coordinator', 'admin', 'partner'],
                    'options' => $architects,
                    'selected' => $deal ? $deal->architect_id : null,
                    'icon' => 'fas fa-drafting-compass',
                ], [
                    'name' => 'designer_id',
                    'label' => 'Дизайнер',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'options' => $designers,
                    'selected' => $deal ? $deal->designer_id : null,
                    'icon' => 'fas fa-palette',
                ],
                [
                    'name' => 'visualizer_id',
                    'label' => 'Визуализатор',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'options' => $visualizers,
                    'selected' => $deal ? $deal->visualizer_id : null,
                    'icon' => 'fas fa-eye',
                ],
                [
                    'name' => 'plan_final',
                    'label' => 'Планировка финал (PDF, до 1.5ГБ)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'accept' => 'application/pdf',
                    'icon' => 'fas fa-map',
                    'description' => 'Финальная версия планировки в формате PDF'
                ],
                [
                    'name' => 'final_collage',
                    'label' => 'Коллаж финал (PDF, до 1.5ГБ)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'accept' => 'application/pdf',
                    'icon' => 'fas fa-object-group',
                    'description' => 'Финальная версия коллажа в формате PDF'
                ],
                
                [
                    'name' => 'visualization_link',
                    'label' => 'Ссылка на визуализацию',
                    'type' => 'url',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'icon' => 'fas fa-link',
                ],
                [
                    'name' => 'screenshot_work_1',
                    'label' => 'Скриншот работы №1',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'accept' => 'image/*',
                    'icon' => 'fas fa-camera',
                    'description' => 'Первый скриншот процесса работы над проектом'
                ],
                [
                    'name' => 'screenshot_work_2',
                    'label' => 'Скриншот работы №2',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'accept' => 'image/*',
                    'icon' => 'fas fa-camera',
                    'description' => 'Второй скриншот процесса работы над проектом'
                ],
                [
                    'name' => 'screenshot_work_3',
                    'label' => 'Скриншот работы №3',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'accept' => 'image/*',
                    'icon' => 'fas fa-camera',
                    'description' => 'Третий скриншот процесса работы над проектом'
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
                [
                    'name' => 'screenshot_final',
                    'label' => 'Скриншот финального этапа',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'accept' => 'image/*',
                    'icon' => 'fas fa-camera',
                    'description' => 'Скриншот завершенного проекта'
                ],
            ],
        ];
    }

    /**
     * Получить данные для модального окна сделки
     *
     * @param Deal $deal
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDealModalData(Deal $deal)
    {
        try {
            // Загружаем связанные данные
            $deal->load('coordinator', 'user', 'architect', 'designer', 'visualizer', 'partner');
            
            // Получаем историю изменений сделки
            $changeLogs = $deal->changeLogs()->with('user')->orderBy('created_at', 'desc')->take(10)->get();
            
            // Возвращаем данные в формате JSON совместимом с клиентским кодом
            return response()->json([
                'success' => true,
                'deal' => $deal,
                'change_logs' => $changeLogs,
            ]);
        } catch (\Exception $e) {
            Log::error("Ошибка получения данных модального окна сделки: " . $e->getMessage(), [
                'deal_id' => $deal->id,
                'exception' => $e
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Ошибка при получении данных сделки: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получает список документов сделки для отображения в модальном окне
     * 
     * @param Deal $deal
     * @return array Массив документов с информацией для отображения
     */
    private function getDealDocuments(Deal $deal)
    {
        try {
            $documents = [];
            
            // Проверяем существование свойства documents у сделки
            // Используем isset вместо property_exists так как мы работаем с моделью Eloquent
            if (isset($deal->documents)) {
                // Если documents хранится как JSON-строка
                if (is_string($deal->documents)) {
                    $decodedDocs = json_decode($deal->documents, true);
                    $documents = $decodedDocs !== null ? $decodedDocs : [];
                }
                // Если documents уже является массивом
                elseif (is_array($deal->documents)) {
                    $documents = $deal->documents;
                }
            }
            
            // Если documents пусто или не является массивом, используем пустой массив
            if (empty($documents) || !is_array($documents)) {
                $documents = [];
            }
            
            // Собираем документы из Яндекс Диска (файловые поля с префиксом yandex_url_)
            $fileFields = [
                'execution_order_file', 'measurements_file', 'final_floorplan', 'final_collage',
                'final_project_file', 'work_act', 'archicad_file', 'contract_attachment', 
                'plan_final', 'chat_screenshot', 'screenshot_work_1', 'screenshot_work_2', 
                'screenshot_work_3', 'screenshot_final'
            ];
            
            foreach ($fileFields as $field) {
                $yandexUrlField = "yandex_url_{$field}";
                $originalNameField = "original_name_{$field}";
                
                if (isset($deal->$yandexUrlField) && !empty($deal->$yandexUrlField)) {
                    // Получаем расширение файла из оригинального имени или присваиваем pdf по умолчанию
                    $extension = 'pdf';
                    $filename = $deal->$originalNameField ?? "{$field}.pdf";
                    
                    if (!empty($deal->$originalNameField)) {
                        $extension = pathinfo($deal->$originalNameField, PATHINFO_EXTENSION);
                    }

                    // Проверяем, является ли это ссылкой на Яндекс.Диск
                    $yandexUrl = $deal->$yandexUrlField;
                    $downloadUrl = $yandexUrl;
                    
                    // Если это ссылка на Яндекс.Диск, преобразуем в ссылку для скачивания
                    if (strpos($yandexUrl, 'yadi.sk') !== false || strpos($yandexUrl, 'disk.yandex') !== false) {
                        $downloadUrl = $this->convertYandexUrlToDownload($yandexUrl);
                    }
                    // Если это локальный файл, создаем маршрут для скачивания  
                    elseif (strpos($yandexUrl, 'storage/deals/') !== false || strpos($yandexUrl, '/storage/deals/') !== false) {
                        // Извлекаем имя файла из пути
                        $localFilename = basename($yandexUrl);
                        // Декодируем имя файла если оно было закодировано
                        $decodedFilename = urldecode($localFilename);
                        
                        $downloadUrl = route('deal.document.download', [
                            'deal' => $deal->id,
                            'filename' => $decodedFilename
                        ]);
                    }
                    // Если это другой тип локального файла
                    elseif (!filter_var($yandexUrl, FILTER_VALIDATE_URL)) {
                        // Считаем что это локальный файл
                        $localFilename = basename($yandexUrl);
                        $decodedFilename = urldecode($localFilename);
                        
                        $downloadUrl = route('deal.document.download', [
                            'deal' => $deal->id,
                            'filename' => $decodedFilename
                        ]);
                    }

                    $documents[] = [
                        'id' => $deal->id . '_' . $field, // Уникальный ID для удаления
                        'name' => $filename,
                        'path' => $yandexUrl, // Оригинальный путь
                        'extension' => $extension,
                        'icon' => $this->getFileIcon($extension),
                        'url' => $downloadUrl, // URL для скачивания
                        'size' => $this->getFileSize($yandexUrl), // Размер файла если доступен
                        'field' => $field // Поле для идентификации при удалении
                    ];
                }
            }
            
            // Подготовка документов для отображения в стандартном формате
            $documentFiles = [];
            foreach ($documents as $docItem) {
                // Если это уже готовый объект документа, просто добавляем его
                if (is_array($docItem) && isset($docItem['name']) && isset($docItem['extension'])) {
                    $documentFiles[] = $docItem;
                    continue;
                }
                
                // Иначе обрабатываем как путь к файлу
                if (is_string($docItem)) {
                    $path = $docItem;
                    $filename = basename($path);
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                    $icon = $this->getFileIcon($extension);
                    
                    $documentFiles[] = [
                        'name' => $filename,
                        'path' => $path,
                        'extension' => $extension,
                        'icon' => $icon,
                        'url' => $this->getFileUrl($path) // Метод для получения URL
                    ];
                }
            }
            
            return $documentFiles;
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем выполнение
            Log::error("Ошибка при обработке документов сделки: " . $e->getMessage(), [
                'exception' => $e,
                'deal_id' => $deal->id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            // Возвращаем пустой массив в случае ошибки
            return [];
        }
    }

    /**
     * Определяет иконку для файла на основе расширения
     * 
     * @param string $extension Расширение файла
     * @return string Класс иконки Font Awesome
     */
    private function getFileIcon($extension)
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
     * Получает URL для скачивания файла
     * 
     * @param string $path Путь к файлу
     * @return string URL файла
     */
    private function getFileUrl($path)
    {
        // Если путь начинается с http:// или https://, то это уже URL
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            return $path;
        }
        
        try {
            // Если не URL, пробуем использовать Storage::url
            return Storage::url($path);
        } catch (\Exception $e) {
            Log::warning("Не удалось получить URL файла: " . $e->getMessage(), [
                'path' => $path
            ]);
            // В случае ошибки возвращаем исходный путь
            return $path;
        }
    }

    /**
     * Преобразует ссылку на Яндекс.Диск в ссылку для прямого скачивания
     * 
     * @param string $url
     * @return string
     */
    private function convertYandexUrlToDownload($url)
    {
        // Если это уже прямая ссылка для скачивания, возвращаем как есть
        if (strpos($url, '/download') !== false) {
            return $url;
        }

        try {
            // Для ссылок вида https://yadi.sk/d/...
            if (strpos($url, 'yadi.sk/d/') !== false) {
                return str_replace('yadi.sk/d/', 'yadi.sk/d/', $url) . '/download';
            }

            // Для ссылок вида https://disk.yandex.ru/i/...
            if (strpos($url, 'disk.yandex.ru/i/') !== false) {
                return str_replace('/i/', '/d/', $url);
            }
            
            // Для других ссылок добавляем параметр download
            return $url . (strpos($url, '?') !== false ? '&' : '?') . 'download=1';
        } catch (\Exception $e) {
            Log::warning("Не удалось преобразовать URL Яндекс.Диска: " . $e->getMessage(), [
                'url' => $url
            ]);
            return $url;
        }
    }

    /**
     * Получает размер файла (если доступен)
     * 
     * @param string $path
     * @return string|null
     */
    private function getFileSize($path)
    {
        try {
            // Для локальных файлов пытаемся получить размер
            if (strpos($path, 'storage/') !== false) {
                $storagePath = str_replace('storage/', '', $path);
                if (Storage::exists($storagePath)) {
                    $bytes = Storage::size($storagePath);
                    return $this->formatFileSize($bytes);
                }
            }
            
            // Для внешних ссылок размер не можем определить
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Форматирует размер файла в человекочитаемый формат
     * 
     * @param int $bytes
     * @return string
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' ГБ';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' МБ';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' КБ';
        } else {
            return $bytes . ' байт';
        }
    }

    public function searchUsers(Request $request)
    {
        $query = $request->get('q');
        $status = $request->get('status');
        
        $users = User::where(function($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%");
        });
        
        if (!empty($status)) {
            $users->where('status', $status);
        }
        
        // Получаем пользователей с полем rating
        $users = $users->get(['id', 'name', 'email', 'rating']);
        
        // Для каждого пользователя, если rating не задан, получаем среднее значение из рейтингов
        foreach ($users as $user) {
            if (is_null($user->rating)) {
                $user->rating = $user->getAverageRatingAttribute();
            }
        }
        
        return response()->json($users);
    }
    
    /**
     * Поиск брифов по номеру телефона клиента
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function findBriefsByClientPhone(\Illuminate\Http\Request $request)
    {
        try {
            $clientPhone = $request->input('client_phone');
            $dealId = $request->input('deal_id');
            
            \Illuminate\Support\Facades\Log::info('Поиск брифов по номеру телефона', [
                'phone' => $clientPhone,
                'deal_id' => $dealId
            ]);
            
            if (empty($clientPhone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Номер телефона клиента не указан'
                ]);
            }
            
            // Нормализуем номер телефона для поиска
            $normalizedPhone = preg_replace('/[^0-9]/', '', $clientPhone);
            
            \Illuminate\Support\Facades\Log::info('Нормализованный телефон: ' . $normalizedPhone);
            
            // Варианты форматов телефона для более гибкого поиска
            $phoneVariations = [$clientPhone, $normalizedPhone];
            
            // Добавляем вариации с последними 10 цифрами и без кодов страны
            if (strlen($normalizedPhone) >= 10) {
                $lastTenDigits = substr($normalizedPhone, -10);
                $phoneVariations[] = $lastTenDigits; 
                $phoneVariations[] = '+7' . $lastTenDigits;
                $phoneVariations[] = '8' . $lastTenDigits;
                // Также добавляем телефон с разными форматами скобок, пробелов и дефисов
                $formattedPhone = '+7 (' . substr($lastTenDigits, 0, 3) . ') ' . substr($lastTenDigits, 3, 3) . '-' . substr($lastTenDigits, 6, 2) . '-' . substr($lastTenDigits, 8, 2);
                $phoneVariations[] = $formattedPhone;
            }
            
            \Illuminate\Support\Facades\Log::info('Варианты номера телефона для поиска:', $phoneVariations);
            
            // Ищем пользователей по номеру телефона с гибким поиском
            $users = \App\Models\User::where(function($query) use ($phoneVariations) {
                foreach ($phoneVariations as $phone) {
                    $query->orWhere('phone', 'like', '%' . $phone . '%');
                }
            })->get();
            
            \Illuminate\Support\Facades\Log::info('Найдено пользователей: ' . $users->count(), [
                'user_ids' => $users->pluck('id')->toArray(),
                'user_phones' => $users->pluck('phone')->toArray()
            ]);
            
            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь с указанным номером телефона не найден'
                ]);
            }
            
            // Получаем ID пользователей для поиска брифов
            $userIds = $users->pluck('id')->toArray();
            
            // Проверяем, привязан ли уже бриф к сделке
            $deal = \App\Models\Deal::find($dealId);
            $hasAttachedBrief = false;
            $attachedBriefType = null;
            
            if ($deal) {
                if ($deal->common_id) {
                    $hasAttachedBrief = true;
                    $attachedBriefType = 'common';
                } elseif ($deal->commercial_id) {
                    $hasAttachedBrief = true;
                    $attachedBriefType = 'commercial';
                }
            }
            
            // Логируем запрос на поиск общих брифов
            $commonQuery = \Illuminate\Support\Facades\DB::table('commons')->whereIn('user_id', $userIds)->toSql();
            \Illuminate\Support\Facades\Log::info('SQL запрос для поиска общих брифов: ' . $commonQuery, [
                'user_ids' => $userIds
            ]);
            
            // Получаем имя таблицы и класса модели для общих брифов
            $commonTable = (new \App\Models\Common())->getTable();
            $commonClass = get_class(new \App\Models\Common());
            
            \Illuminate\Support\Facades\Log::info('Информация о модели Common:', [
                'table' => $commonTable,
                'class' => $commonClass
            ]);
            
            try {
                // Проверяем наличие таблицы commons в базе данных
                $commonTableExists = \Illuminate\Support\Facades\Schema::hasTable($commonTable);
                \Illuminate\Support\Facades\Log::info("Таблица $commonTable " . ($commonTableExists ? 'существует' : 'не существует'));
                
                // Проверяем наличие поля user_id в таблице
                $commonUserIdExists = \Illuminate\Support\Facades\Schema::hasColumn($commonTable, 'user_id');
                \Illuminate\Support\Facades\Log::info("Поле user_id в таблице $commonTable " . ($commonUserIdExists ? 'существует' : 'не существует'));
                
                // Получаем список всех полей таблицы commons
                $commonColumns = \Illuminate\Support\Facades\Schema::getColumnListing($commonTable);
                \Illuminate\Support\Facades\Log::info("Колонки таблицы $commonTable:", $commonColumns);
                
                // Ищем все брифы, независимо от user_id для проверки
                $allCommonBriefsCount = \App\Models\Common::count();
                \Illuminate\Support\Facades\Log::info("Всего брифов в таблице $commonTable: $allCommonBriefsCount");
                
                // Ищем общие брифы по ID пользователей
                $commonBriefs = \App\Models\Common::whereIn('user_id', $userIds)
                    ->with('user')
                    ->get()
                    ->map(function($brief) use ($dealId) {
                        // Проверяем, привязан ли бриф к данной сделке
                        $alreadyLinked = \App\Models\Deal::where('id', $dealId)
                            ->where('common_id', $brief->id)
                            ->exists();
                        
                        return [
                            'id' => $brief->id,
                            'title' => $brief->name ?? ('Общий бриф #' . $brief->id),
                            'user_name' => $brief->user->name ?? 'Неизвестный пользователь',
                            'created_at' => \Carbon\Carbon::parse($brief->created_at)->format('d.m.Y H:i'),
                            'already_linked' => $alreadyLinked
                        ];
                    });
                
                \Illuminate\Support\Facades\Log::info('Найдено общих брифов: ' . $commonBriefs->count(), [
                    'brief_ids' => $commonBriefs->pluck('id')->toArray()
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Ошибка при поиске общих брифов: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
                $commonBriefs = collect([]);
            }
            
            // Логируем запрос на поиск коммерческих брифов
            $commercialQuery = \Illuminate\Support\Facades\DB::table('commercials')->whereIn('user_id', $userIds)->toSql();
            \Illuminate\Support\Facades\Log::info('SQL запрос для поиска коммерческих брифов: ' . $commercialQuery, [
                'user_ids' => $userIds
            ]);
            
            // Получаем имя таблицы и класса модели для коммерческих брифов
            $commercialTable = (new \App\Models\Commercial())->getTable();
            $commercialClass = get_class(new \App\Models\Commercial());
            
            \Illuminate\Support\Facades\Log::info('Информация о модели Commercial:', [
                'table' => $commercialTable,
                'class' => $commercialClass
            ]);
            
            try {
                // Проверяем наличие таблицы commercials в базе данных
                $commercialTableExists = \Illuminate\Support\Facades\Schema::hasTable($commercialTable);
                \Illuminate\Support\Facades\Log::info("Таблица $commercialTable " . ($commercialTableExists ? 'существует' : 'не существует'));
                
                // Проверяем наличие поля user_id в таблице
                $commercialUserIdExists = \Illuminate\Support\Facades\Schema::hasColumn($commercialTable, 'user_id');
                \Illuminate\Support\Facades\Log::info("Поле user_id в таблице $commercialTable " . ($commercialUserIdExists ? 'существует' : 'не существует'));
                
                // Получаем список всех полей таблицы commercials
                $commercialColumns = \Illuminate\Support\Facades\Schema::getColumnListing($commercialTable);
                \Illuminate\Support\Facades\Log::info("Колонки таблицы $commercialTable:", $commercialColumns);
                
                // Ищем все брифы, независимо от user_id для проверки
                $allCommercialBriefsCount = \App\Models\Commercial::count();
                \Illuminate\Support\Facades\Log::info("Всего брифов в таблице $commercialTable: $allCommercialBriefsCount");
                
                // Ищем коммерческие брифы по ID пользователей
                $commercialBriefs = \App\Models\Commercial::whereIn('user_id', $userIds)
                    ->with('user')
                    ->get()
                    ->map(function($brief) use ($dealId) {
                        // Проверяем, привязан ли бриф к данной сделке
                        $alreadyLinked = \App\Models\Deal::where('id', $dealId)
                            ->where('commercial_id', $brief->id)
                            ->exists();
                        
                        return [
                            'id' => $brief->id,
                            'title' => $brief->name ?? ('Коммерческий бриф #' . $brief->id),
                            'user_name' => $brief->user->name ?? 'Неизвестный пользователь',
                            'created_at' => \Carbon\Carbon::parse($brief->created_at)->format('d.m.Y H:i'),
                            'already_linked' => $alreadyLinked
                        ];
                    });
                
                \Illuminate\Support\Facades\Log::info('Найдено коммерческих брифов: ' . $commercialBriefs->count(), [
                    'brief_ids' => $commercialBriefs->pluck('id')->toArray()
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Ошибка при поиске коммерческих брифов: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
                $commercialBriefs = collect([]);
            }
            
            return response()->json([
                'success' => true,
                'users' => $users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone
                    ];
                }),
                'briefs' => $commonBriefs,
                'commercials' => $commercialBriefs,
                'has_attached_brief' => $hasAttachedBrief,
                'attached_brief_type' => $attachedBriefType,
                'message' => 'Найдено брифов: ' . ($commonBriefs->count() + $commercialBriefs->count())
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Ошибка при поиске брифов: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при поиске брифов: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Привязка брифа к сделке
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function linkBriefToDeal(\Illuminate\Http\Request $request)
    {
        try {
            $dealId = $request->input('deal_id');
            $briefId = $request->input('brief_id');
            $briefType = $request->input('brief_type');
            
            if (!$dealId || !$briefId || !$briefType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не указаны обязательные параметры'
                ]);
            }
            
            $deal = \App\Models\Deal::find($dealId);
            
            if (!$deal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Сделка не найдена'
                ]);
            }
            
            // Привязываем бриф к сделке в зависимости от типа
            if ($briefType === 'common') {
                $brief = \App\Models\Common::find($briefId);
                if (!$brief) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Общий бриф не найден'
                    ]);
                }
                $deal->common_id = $briefId;
                $deal->save();
            } elseif ($briefType === 'commercial') {
                $brief = \App\Models\Commercial::find($briefId);
                if (!$brief) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Коммерческий бриф не найден'
                    ]);
                }
                $deal->commercial_id = $briefId;
                $deal->save();
            }

            // Уведомляем исполнителей (архитектор, дизайнер, визуализатор), если они назначены
            $deal->loadMissing(['architect', 'designer', 'visualizer']);
            $this->notifyExecutorsAboutAttach($deal);
            
            return response()->json([
                'success' => true,
                'message' => 'Бриф успешно привязан к сделке',
                'reload_required' => true
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Ошибка при привязке брифа: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при привязке брифа: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Отвязка брифа от сделки
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlinkBriefFromDeal(\Illuminate\Http\Request $request)
    {
        try {
            $dealId = $request->input('deal_id');
            $briefType = $request->input('brief_type');
            
            if (!$dealId || !$briefType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не указаны обязательные параметры'
                ]);
            }
            
            $deal = \App\Models\Deal::find($dealId);
            
            if (!$deal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Сделка не найдена'
                ]);
            }
            
            // Отвязываем бриф от сделки в зависимости от типа
            if ($briefType === 'common') {
                $deal->common_id = null;
                $deal->save();
            } elseif ($briefType === 'commercial') {
                $deal->commercial_id = null;
                $deal->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Бриф успешно отвязан от сделки'
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Ошибка при отвязке брифа: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при отвязке брифа: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Санитизирует JavaScript код для безопасного вывода
     *
     * @param string $script
     * @return string
     */
    private function sanitizeJavaScript($script)
    {
        // Базовая очистка JavaScript кода
        $script = strip_tags($script);
        $script = htmlspecialchars($script, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Удаляем потенциально опасные паттерны
        $dangerousPatterns = [
            '/javascript:/i',
            '/vbscript:/i',
            '/onload=/i',
            '/onerror=/i',
            '/onclick=/i',
            '/onmouseover=/i',
            '/<script/i',
            '/<\/script>/i',
            '/eval\(/i',
            '/document\.write/i',
            '/document\.cookie/i',
            '/window\.location/i'
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            $script = preg_replace($pattern, '', $script);
        }
        
        return $script;
    }
}