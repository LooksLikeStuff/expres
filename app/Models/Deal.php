<?php

namespace App\Models;

use App\Models\Chats\Chat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Deal extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'start_date' => 'datetime',
        'payment_date' => 'datetime',
        'project_end_date' => 'datetime',
        'completion_date' => 'datetime',
        'registration_token_expiry' => 'datetime',
    ];

    protected $fillable = [
        'chat_id',
        'common_id',
        'commercial_id',
        'name',
        'total_sum',
        'measuring_cost',
        'project_budget',
        'status',
        'registration_link',
        'registration_link_expiry',
        'user_id',
        'coordinator_id',
        'registration_token',
        'registration_token_expiry',
        'avatar_path',
        'link',
        'created_date',
        'execution_comment',
        'comment',
        'project_number',
        'order_stage',
        'price_service_option',
        'rooms_count_pricing',
        'execution_order_comment',
        'execution_order_file',
        'office_partner_id',
        'measurement_comments',
        'measurements_file',
        'brief',
        'start_date',
        'project_duration',
        'project_end_date',
        'architect_id',
        'final_floorplan',
        'designer_id',
        'final_collage',
        'visualizer_id',
        'visualization_link',
        'final_project_file',
        'work_act',
        'archicad_file',
        'contract_number',
        'contract_attachment',
        'deal_note',
        'object_type',
        'package',
        'completion_responsible',
        'office_equipment',
        'stage',
        'coordinator_score',
        'has_animals',
        'has_plants',
        'object_style',
        'object_measurements',
        'rooms_count',
        'deal_end_date',
        'payment_date',
        // Добавляем новые поля для Яндекс Диска
        'yandex_url_execution_order_file',
        'yandex_url_measurements_file',
        'yandex_url_final_floorplan',
        'yandex_url_final_collage',
        'yandex_url_final_project_file',
        'yandex_url_work_act',
        'yandex_url_archicad_file',
        'yandex_url_contract_attachment',
        'yandex_disk_path_execution_order_file',
        'yandex_disk_path_measurements_file',
        'yandex_disk_path_final_floorplan',
        'yandex_disk_path_final_collage',
        'yandex_disk_path_final_project_file',
        'yandex_disk_path_work_act',
        'yandex_disk_path_archicad_file',
        'yandex_disk_path_contract_attachment',
        'original_name_execution_order_file',
        'original_name_measurements_file',
        'original_name_final_floorplan',
        'original_name_final_collage',
        'original_name_final_project_file',
        'original_name_work_act',
        'original_name_archicad_file',
        'original_name_contract_attachment',
        // Добавляем поля для новых типов файлов
        'yandex_url_plan_final',
        'yandex_disk_path_plan_final',
        'original_name_plan_final',
        'yandex_url_chat_screenshot',
        'yandex_disk_path_chat_screenshot',
        'original_name_chat_screenshot',
        // Добавляем поля для скриншотов работы
        'screenshot_work_1',
        'yandex_url_screenshot_work_1',
        'yandex_disk_path_screenshot_work_1',
        'original_name_screenshot_work_1',
        'screenshot_work_2',
        'yandex_url_screenshot_work_2',
        'yandex_disk_path_screenshot_work_2',
        'original_name_screenshot_work_2',
        'screenshot_work_3',
        'yandex_url_screenshot_work_3',
        'yandex_disk_path_screenshot_work_3',
        'original_name_screenshot_work_3',
        'screenshot_final',
        'yandex_url_screenshot_final',
        'yandex_disk_path_screenshot_final',
        'original_name_screenshot_final',
        // Добавляем новые поля для фотографий проекта
        'photos_folder_url',
        'photos_count',
        'yandex_disk_photos_path',
        // Добавляем новые поля для информации об удаленных пользователях
        'deleted_user_id',
        'deleted_user_name',
        'deleted_user_email',
        'deleted_user_phone',
        'deleted_coordinator_id',
        'deleted_architect_id',
        'deleted_designer_id',
        'deleted_visualizer_id',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    // Отношение к пользователю (клиенту)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Отношение к архитектору
    public function architect()
    {
        return $this->belongsTo(User::class, 'architect_id');
    }

    // Отношение к дизайнеру
    public function designer()
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    // Отношение к визуализатору
    public function visualizer()
    {
        return $this->belongsTo(User::class, 'visualizer_id');
    }

    // Отношение к координатору
    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    // Отношение к партнеру
    public function partner()
    {
        return $this->belongsTo(User::class, 'office_partner_id');
    }

    // Many-to-many отношение к пользователям (для команды проекта)
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // Отношение к рейтингам
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    // Отношение к клиенту сделки (новая таблица)
    public function dealClient()
    {
        return $this->hasOne(DealClient::class);
    }

    // Старое отношение к клиенту-пользователю (сохраняем для совместимости)
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function commercial()
    {
        return $this->belongsTo(Commercial::class, 'commercial_id');
    }

    public function brief()
    {
        return $this->belongsTo(Common::class, 'common_id');
    }

    public function briefs()
    {
        return $this->hasMany(Common::class, 'deal_id');
    }

    public function commercials()
    {
        return $this->hasMany(Commercial::class, 'deal_id');
    }

    public function coordinators()
    {
        return $this->users()->wherePivot('role', 'coordinator');
    }

    public function responsibles()
    {
        return $this->belongsToMany(User::class, 'deal_users');
    }

    public function allUsers()
    {
        return $this->users()->wherePivotIn('role', ['responsible', 'coordinator']);
    }

    public function dealFeeds()
    {
        return $this->hasMany(DealFeed::class);
    }

    public function changeLogs()
    {
        return $this->hasMany(DealChangeLog::class);
    }


//    /**
//     * Альтернативное отношение для прямого доступа к pivot-таблице.
//     */
//    public function usersPivot()
//    {
//        return $this->hasMany(DealUser::class);
//    }

    /**
     * Проверяет, поставил ли пользователь оценку другому пользователю в этой сделке
     *
     * @param int $raterUserId ID оценивающего пользователя
     * @param int $ratedUserId ID оцениваемого пользователя
     * @param string|null $role Роль, в которой оценивается пользователь
     * @return bool
     */
    public function hasRatingFrom($raterUserId, $ratedUserId, $role = null)
    {
        $query = Rating::where('deal_id', $this->id)
            ->where('rater_user_id', $raterUserId)
            ->where('rated_user_id', $ratedUserId);

        // Если указана роль, добавляем её в условие запроса
        if ($role) {
            $query->where('role', $role);
        }

        return $query->exists();
    }

    /**
     * Получить среднюю оценку сделки ТОЛЬКО от клиента по всем исполнителям
     *
     * @return float|null
     */
    public function getClientAverageRatingAttribute()
    {
        if ($this->status !== 'Проект завершен') {
            return null;
        }

        // Получаем все оценки по сделке, где оценивающий - клиент (user)
        $ratings = \App\Models\Rating::where('deal_id', $this->id)
                                    ->whereHas('raterUser', function($query) {
                                        $query->where('status', 'user');
                                    })
                                    ->get();

        if ($ratings->isEmpty()) {
            return null;
        }

        // Преобразуем строку, полученную от number_format, обратно в float
        return (float)number_format($ratings->avg('score'), 1);
    }

    /**
     * Получить количество оценок сделки от клиента
     *
     * @return int
     */
    public function getClientRatingsCountAttribute()
    {
        return \App\Models\Rating::where('deal_id', $this->id)
                               ->whereHas('raterUser', function($query) {
                                   $query->where('status', 'user');
                               })
                               ->count();
    }

    /**
     * Проверяет наличие загруженного файла на Яндекс Диске
     *
     * @param string $fieldName Имя поля файла
     * @return bool
     */
    public function hasYandexFile($fieldName)
    {
        $yandexPathField = "yandex_disk_path_{$fieldName}";
        return !empty($this->$yandexPathField);
    }

    /**
     * Получает URL файла с Яндекс Диска
     *
     * @param string $fieldName Имя поля файла
     * @return string|null
     */
    public function getYandexFileUrl($fieldName)
    {
        $yandexUrlField = "yandex_url_{$fieldName}";
        return $this->$yandexUrlField;
    }

    /**
     * Получает оригинальное имя файла
     *
     * @param string $fieldName Имя поля файла
     * @return string|null
     */
    public function getYandexFileOriginalName($fieldName)
    {
        $originalNameField = "original_name_{$fieldName}";
        return $this->$originalNameField;
    }

    /**
     * Получить имя владельца сделки, даже если он был удален
     *
     * @return string
     */
    public function getOwnerNameAttribute()
    {
        if ($this->user_id && User::withTrashed()->find($this->user_id)) {
            return User::withTrashed()->find($this->user_id)->name;
        } elseif ($this->deleted_user_name) {
            return $this->deleted_user_name . ' (удален)';
        } else {
            return 'Неизвестный пользователь';
        }
    }

    /**
     * Получить имя координатора сделки, даже если он был удален
     *
     * @return string
     */
    public function getCoordinatorNameAttribute()
    {
        if ($this->coordinator_id && User::withTrashed()->find($this->coordinator_id)) {
            return User::withTrashed()->find($this->coordinator_id)->name;
        } elseif ($this->deleted_coordinator_id) {
            $user = User::withTrashed()->find($this->deleted_coordinator_id);
            return $user ? $user->name . ' (удален)' : 'Бывший координатор (удален)';
        } else {
            return 'Нет координатора';
        }
    }

    /**
     * Получить URL брифа, привязанного к сделке
     *
     * @return string|null
     */
    public function getBriefUrlAttribute()
    {
        // Если есть прямая ссылка, используем её
        if (!empty($this->link)) {
            return $this->link;
        }

        // Если есть привязанный общий бриф
        if ($this->common_id) {
            $common = $this->brief;
            if ($common && !empty($common->link)) {
                return $common->link;
            }
            return route('common.show', $this->common_id);
        }

        // Если есть привязанный коммерческий бриф
        if ($this->commercial_id) {
            $commercial = $this->commercial;
            if ($commercial && !empty($commercial->link)) {
                return $commercial->link;
            }
            return route('commercial.show', $this->commercial_id);
        }

        return null;
    }

    /**
     * Проверяет, есть ли у сделки привязанный бриф
     *
     * @return bool
     */
    public function getHasBriefAttribute()
    {
        return !empty($this->link) || !empty($this->common_id) || !empty($this->commercial_id);
    }

    /**
     * Получить документы, связанные с этой сделкой
     *
     * @return array
     */
    public function getDocuments()
    {
        $documents = [];

        // Если documents хранится как JSON-строка
        if (!empty($this->documents)) {
            if (is_string($this->documents)) {
                $docs = json_decode($this->documents, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($docs)) {
                    foreach ($docs as $path) {
                        $filename = basename($path);
                        $extension = pathinfo($filename, PATHINFO_EXTENSION);

                        // Определяем иконку на основе расширения файла
                        $icon = $this->getFileIconClass($extension);

                        $documents[] = [
                            'path' => $path,
                            'name' => $filename,
                            'extension' => $extension,
                            'icon' => $icon,
                            'url' => asset('storage/' . $path)
                        ];
                    }
                }
            } elseif (is_array($this->documents)) {
                foreach ($this->documents as $path) {
                    $filename = basename($path);
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                    $icon = $this->getFileIconClass($extension);

                    $documents[] = [
                        'path' => $path,
                        'name' => $filename,
                        'extension' => $extension,
                        'icon' => $icon,
                        'url' => asset('storage/' . $path)
                    ];
                }
            }
        }

        // Также получаем документы Яндекс Диска
        $yandexDiskDocuments = $this->getYandexDiskDocuments();

        // Объединяем локальные и яндекс диск документы
        return array_merge($documents, $yandexDiskDocuments);
    }

    /**
     * Получить документы с Яндекс.Диска
     *
     * @return array
     */
    private function getYandexDiskDocuments()
    {
        $documents = [];
        $yandexFields = [
            'execution_order_file',
            'measurements_file',
            'final_floorplan',
            'final_collage',
            'final_project_file',
            'work_act',
            'archicad_file',
            'contract_attachment',
            'plan_final',
            'chat_screenshot',
            'screenshot_work_1',
            'screenshot_work_2',
            'screenshot_work_3',
            'screenshot_final'
        ];

        foreach ($yandexFields as $field) {
            $yandexUrlField = "yandex_url_{$field}";
            $originalNameField = "original_name_{$field}";

            if (!empty($this->$yandexUrlField)) {
                $filename = $this->$originalNameField ?? "{$field}.pdf";
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                $icon = $this->getFileIconClass($extension);

                $documents[] = [
                    'name' => $filename,
                    'extension' => $extension,
                    'icon' => $icon,
                    'url' => $this->$yandexUrlField
                ];
            }
        }

        return $documents;
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

    public function getMemberIds(): array
    {
        // Ролевые ID
        $ids[] = $this->user_id;
        $ids[] = $this->architect_id;
        $ids[] = $this->designer_id;
        $ids[] = $this->visualizer_id;
        $ids[] = $this->coordinator_id;
        $ids[] = $this->office_partner_id;

        // Из связи many-to-many
        $ids = array_merge($ids, $this->users->pluck('id')->toArray());

        // Убираем null и дубликаты
        return array_values(array_unique(array_filter($ids)));
    }

    // Методы для работы с клиентскими полями из таблицы deal_clients
    
    /**
     * Получить имя клиента только из таблицы deal_clients
     */
    public function getClientNameAttribute()
    {
        return $this->dealClient?->name;
    }

    /**
     * Получить телефон клиента только из таблицы deal_clients
     */
    public function getClientPhoneAttribute()
    {
        return $this->dealClient?->phone;
    }

    /**
     * Получить email клиента только из таблицы deal_clients
     */
    public function getClientEmailAttribute()
    {
        return $this->dealClient?->email;
    }

    /**
     * Получить город клиента только из таблицы deal_clients
     */
    public function getClientCityAttribute()
    {
        return $this->dealClient?->city;
    }

    /**
     * Получить часовой пояс клиента только из таблицы deal_clients
     */
    public function getClientTimezoneAttribute()
    {
        return $this->dealClient?->timezone;
    }

    /**
     * Получить информацию о клиенте только из таблицы deal_clients
     */
    public function getClientInfoAttribute()
    {
        return $this->dealClient?->info;
    }

    /**
     * Получить ссылку на аккаунт клиента только из таблицы deal_clients
     */
    public function getClientAccountLinkAttribute()
    {
        return $this->dealClient?->account_link;
    }

    /**
     * Проверить, есть ли данные клиента
     */
    public function hasClientData(): bool
    {
        return $this->dealClient !== null;
    }

    /**
     * Получить отформатированный телефон клиента
     */
    public function getFormattedClientPhoneAttribute(): ?string
    {
        return $this->dealClient?->getFormattedPhoneAttribute();
    }


}
