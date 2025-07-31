<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $status
 * @property string|null $city
 * @property string|null $phone
 * @property string|null $contract_number
 * @property string|null $comment
 * @property string|null $portfolio_link
 * @property int|null $experience
 * @property float|null $rating
 * @property int|null $active_projects_count
 * @property string|null $firebase_token
 * @property string|null $verification_code
 * @property \DateTime|null $verification_code_expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'city',
        'phone',
        'contract_number',
        'comment',
        'portfolio_link',
        'experience',
        'rating',
        'active_projects_count',
        'firebase_token',
        'verification_code',
        'verification_code_expires_at',
        'fcm_token',
        'last_seen_at', // Добавляем поле последней активности
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'string',
        'verification_code_expires_at' => 'datetime',
        'last_seen_at' => 'datetime', // Добавляем приведение типа
        'deleted_at' => 'datetime', // Добавляем приведение типа для мягкого удаления
    ];

    /**
     * Метод, вызываемый при загрузке модели
     */
    protected static function boot()
    {
        parent::boot();

        // Обрабатываем событие перед удалением пользователя
        static::deleting(function ($user) {
            // Для брифов и коммерческих брифов сохраняем связь, но anonymize
            foreach ($user->briefs as $brief) {
                $brief->user_id_before_deletion = $user->id;
                $brief->save();
            }

            foreach ($user->commercials as $commercial) {
                $commercial->user_id_before_deletion = $user->id;
                $commercial->save();
            }

            // Для сделок сохраняем информацию о клиенте
            foreach ($user->deals as $deal) {
                // Если удаляется основной владелец сделки, сохраняем его данные
                if ($deal->user_id == $user->id) {
                    $deal->deleted_user_id = $user->id;
                    $deal->deleted_user_name = $user->name;
                    $deal->deleted_user_email = $user->email;
                    $deal->deleted_user_phone = $user->phone;
                    $deal->save();
                }
            }

            // Очищаем связи пользователя с сделками через pivot-таблицу
            // но НЕ удаляем записи в самой pivot-таблице
            // $user->dealsPivot()->update(['deleted_user_id' => $user->id]);
            
            // Аналогично для других связей, если необходимо
        });
    }

    /**
     * Отношение многие-ко-многим с моделью Deal.
     */
    public function deals()
    {
        return $this->belongsToMany(Deal::class, 'deal_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }
    
    /**
     * Альтернативное отношение для прямого доступа к pivot-таблице.
     */
    public function dealsPivot()
    {
        return $this->hasMany(DealUser::class);
    }

    public function responsibleDeals()
    {
        return $this->belongsToMany(Deal::class, 'deal_responsible', 'user_id', 'deal_id');
    }

    /**
     * Получить URL аватара пользователя
     * 
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        try {
            // Если есть avatar_url как внешняя ссылка (с Яндекс.Диска), используем её напрямую
            if (!empty($this->attributes['avatar_url']) && filter_var($this->attributes['avatar_url'], FILTER_VALIDATE_URL)) {
                return $this->attributes['avatar_url'];
            }
            
            // Если есть profile_image и файл существует, используем его
            if ($this->profile_image && Storage::disk('public')->exists($this->profile_image)) {
                return asset('storage/' . $this->profile_image);
            }
            
            // Если avatar_url существует как локальный путь, используем его
            if (!empty($this->attributes['avatar_url'])) {
                return asset('' . ltrim($this->attributes['avatar_url'], ''));
            }
            
            // Проверяем, существует ли столбец avatar и есть ли в нем значение
            if (Schema::hasColumn('users', 'avatar') && !empty($this->attributes['avatar'])) {
                return asset('storage/' . $this->attributes['avatar']);
            }
            
            // Если ничего не найдено, возвращаем дефолтный аватар
        } catch (\Exception $e) {
            \Log::error('Ошибка при получении аватара: ' . $e->getMessage());
        }
        
        // Проверяем, существует ли файл дефолтного аватара
        $defaultPaths = [
            'storage/icon/profile.svg',
            'storage/icon/profile.svg',
            'img/avatar-default.png'
        ];
        
        foreach ($defaultPaths as $path) {
            if (file_exists(public_path($path))) {
                return asset($path);
            }
        }
        
        // Запасной вариант - вернуть URL заглушки
        return asset('storage/icon/profile.svg');
    }

    /**
     * Аксессор для получения URL аватара с дефолтным значением
     *
     * @return string
     */
    
    public function isCoordinator()
    {
        return $this->status === 'coordinator';
    }

    public function isClient()
    {
        return $this->status === 'user';
    }

    public function coordinatorDeals()
    {
        return $this->belongsToMany(Deal::class, 'deal_user')
                    ->withPivot('role')
                    ->wherePivot('role', 'coordinator');
    }

    public function tokens()
    {
        return $this->hasMany(UserToken::class);
    }

    /**
     * Сообщения, отправленные пользователем
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Сообщения, полученные пользователем
     */
    public function receivedMessages()
    {
        // Проверяем, какое имя колонки существует
        if (Schema::hasColumn('messages', 'receiver_id')) {
            return $this->hasMany(Message::class, 'receiver_id');
        } else if (Schema::hasColumn('messages', 'recipient_id')) {
            return $this->hasMany(Message::class, 'recipient_id');
        }
        
        // По умолчанию используем receiver_id
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Получение всех сообщений пользователя (отправленных и полученных)
     */
    public function messages()
    {
        // Проверяем, какое имя колонки существует
        $receiverColumn = Schema::hasColumn('messages', 'receiver_id') 
            ? 'receiver_id' 
            : (Schema::hasColumn('messages', 'recipient_id') ? 'recipient_id' : 'receiver_id');
        
        return Message::where(function ($query) use ($receiverColumn) {
            $query->where('sender_id', $this->id)
                  ->orWhere($receiverColumn, $this->id);
        })->orderBy('created_at', 'desc');
    }

    /**
     * Получить количество непрочитанных сообщений для пользователя
     */
    public function unreadMessagesCount()
    {
        try {
            // Проверяем, существует ли таблица сообщений
            if (!Schema::hasTable('messages')) {
                return 0;
            }
            
            return $this->receivedMessages()
                ->whereNull('read_at')
                ->count();
        } catch (\Exception $e) {
            \Log::error('Ошибка при подсчете непрочитанных сообщений: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Группы пользователя
     */

    /**
     * Группы, в которых пользователь является администратором
     */
    public function adminChatGroups()
    {
        return $this->belongsToMany(ChatGroup::class, 'chat_group_users')
                    ->wherePivot('role', 'admin')
                    ->withTimestamps();
    }

    /**
     * Созданные пользователем группы
     */
    public function createdChatGroups()
    {
        return $this->hasMany(ChatGroup::class, 'created_by');
    }

    /**
     * Получить групповые чаты, в которых участвует пользователь.
     */
    public function chatGroups(): BelongsToMany
    {
        return $this->belongsToMany(ChatGroup::class, 'chat_group_user')
            ->withTimestamps()
            ->withPivot('role', 'is_admin');
    }

    /**
     * Получить сообщения в групповых чатах, отправленные пользователем.
     */
    public function groupMessages(): HasMany
    {
        return $this->hasMany(GroupMessage::class);
    }

    /**
     * Подсчитать количество непрочитанных сообщений в групповых чатах.
     */
    public function unreadGroupMessagesCount(): int
    {
        $readMessageIds = GroupMessageRead::where('user_id', $this->id)
            ->pluck('group_message_id');
        
        return GroupMessage::whereIn(
            'chat_group_id', 
            $this->chatGroups()->pluck('chat_groups.id')
        )
            ->whereNotIn('id', $readMessageIds)
            ->where('user_id', '!=', $this->id)
            ->count();
    }

    /**
     * Проверяет, находится ли пользователь в сети
     * Пользователь считается онлайн, если был активен за последние 5 минут
     * 
     * @return bool
     */
    public function isOnline()
    {
        if (!$this->last_seen_at) {
            return false;
        }
        
        // Считаем пользователя онлайн, если он был активен в последние 5 минут
        return $this->last_seen_at->gt(now()->subMinutes(5));
    }
    
    /**
     * Обновляет время последней активности пользователя
     */
    public function updateLastSeen()
    {
        $this->last_seen_at = now();
        $this->save();
    }

    /**
     * Оценки, полученные пользователем
     */
    public function receivedRatings()
    {
        return $this->hasMany(Rating::class, 'rated_user_id');
    }

    /**
     * Оценки, выставленные пользователем
     */
    public function givenRatings()
    {
        return $this->hasMany(Rating::class, 'rater_user_id');
    }

    /**
     * Получить средний рейтинг пользователя
     */
    public function getAverageRatingAttribute()
    {
        return $this->receivedRatings()->avg('score') ?: 0;
    }

    /**
     * Получить средний рейтинг пользователя в определенной роли
     *
     * @param string $role Роль пользователя (coordinator, architect, designer, visualizer)
     * @return float|null
     */
    public function getAverageRatingByRole($role)
    {
        return $this->receivedRatings()
            ->where('role', $role)
            ->avg('score');
    }
    
    /**
     * Получить общий средний рейтинг пользователя
     *
     * @return float|null
     */
    public function getAverageRating()
    {
        return $this->receivedRatings()
            ->avg('score');
    }
    
    /**
     * Получить количество полученных оценок
     *
     * @return int
     */
    public function getRatingsCount()
    {
        return $this->receivedRatings()
            ->count();
    }

    public function briefs()
    {
        return $this->hasMany(\App\Models\Common::class, 'user_id');
    }


    /**
     * Получить общие брифы пользователя
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function commons()
    {
        return $this->hasMany(\App\Models\Common::class, 'user_id');
    }

    /**
     * Получить коммерческие брифы пользователя
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function commercials()
    {
        return $this->hasMany(\App\Models\Commercial::class, 'user_id');
    }

    /**
     * Награды, полученные пользователем
     */
    public function awards()
    {
        return $this->belongsToMany(Award::class, 'user_awards')
            ->withPivot('awarded_by', 'comment', 'awarded_at')
            ->withTimestamps();
    }
}
