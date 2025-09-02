<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Models\Chats\Chat;
use App\Models\Chats\Message;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Log;
use App\Models\ChatGroups\ChatGroup;
use App\Models\ChatGroups\GroupMessage;
use App\Models\ChatGroups\GroupMessageRead;
use App\Models\Common;
use App\Models\Commercial;
use App\Models\Deal;
use App\Models\DealUser;
use App\Models\Rating;
use App\Models\UserFCMToken;
use App\Models\VerificationCode;
use App\Models\UserSession;
use App\Models\UserToken;

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
 * @property string|null $avatar_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
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
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'string',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['profile_avatar'];

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

    public function isAdmin(): bool
    {
        return $this->status === UserStatus::ADMIN->value;
    }

    public function isCoordinator(): bool
    {
        return $this->status === UserStatus::COORDINATOR->value;
    }

    public function isPartner(): bool
    {
        return $this->status === UserStatus::PARTNER->value;
    }

    public function isArchitect(): bool
    {
        return $this->status === UserStatus::ARCHITECT->value;
    }

    public function isDesigner(): bool
    {
        return $this->status === UserStatus::DESIGNER->value;
    }

    public function isVisualizer(): bool
    {
        return $this->status === UserStatus::VISUALIZER->value;
    }

    public function isClient(): bool
    {
        return $this->status === UserStatus::CLIENT->value;
    }

    public function hasAnyRole(UserStatus ...$roles): bool
    {
        return in_array($this->status, array_map(fn($r) => $r->value, $roles), true);
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole(
            UserStatus::ADMIN,
            UserStatus::COORDINATOR,
            UserStatus::ARCHITECT,
            UserStatus::DESIGNER,
            UserStatus::VISUALIZER
        );
    }

    public function chats()
    {
        return $this->belongsToMany(Chat::class, 'user_chats')
            ->whereNull('chats.deleted_at')
            ->wherePivotNull('left_at');
    }

    /**
     * Отношение многие-ко-многим с моделью Deal.
     */
    public function deals()
    {
        return $this->belongsToMany(Deal::class, 'deal_users')
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

            if (!empty($this->attributes['avatar'])) {
                return asset('storage/' . $this->attributes['avatar']);
            }

            // Если ничего не найдено, возвращаем дефолтный аватар
        } catch (Exception $e) {
            Log::error('Ошибка при получении аватара: ' . $e->getMessage());
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

    public function getProfileAvatarAttribute(): string
    {
        $path = $this->getRawOriginal('avatar_url');
        if ($path) return url($path);

        return asset('img/chats/profile/placeholder.png');
    }




    public function coordinatorDeals()
    {
        return $this->belongsToMany(Deal::class, 'deal_users')
            ->withPivot('role')
            ->wherePivot('role', 'coordinator');
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
        } catch (Exception $e) {
            Log::error('Ошибка при подсчете непрочитанных сообщений: ' . $e->getMessage());
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
        return $this->hasMany(Common::class, 'user_id');
    }


    /**
     * Получить общие брифы пользователя
     *
     * @return HasMany
     */
    public function commons()
    {
        return $this->hasMany(Common::class, 'user_id');
    }

    /**
     * Получить коммерческие брифы пользователя
     *
     * @return HasMany
     */
    public function commercials()
    {
        return $this->hasMany(Commercial::class, 'user_id');
    }

    /**
     * Награды, полученные пользователем
     */
    public function awards()
    {
        return $this->belongsToMany(Award::class, 'user_awards')
            ->withPivot('awarded_by_id', 'comment', 'awarded_at')
            ->withTimestamps();
    }

    public function fcmTokens()
    {
        return $this->hasMany(UserFCMToken::class);
    }

    /**
     * Коды верификации пользователя
     */
    public function verificationCodes()
    {
        return $this->hasMany(VerificationCode::class);
    }

    /**
     * Сессии пользователя
     */
    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    /**
     * Токены пользователя (Firebase, FCM)
     */
    public function tokens()
    {
        return $this->hasMany(UserToken::class);
    }

    /**
     * Получить активную сессию пользователя
     */
    public function getActiveSession()
    {
        return $this->sessions()
            ->where('last_seen_at', '>', now()->subMinutes(30))
            ->first();
    }

    /**
     * Обновить время последней активности
     */
    public function updateLastSeen()
    {
        $session = $this->getActiveSession();

        if ($session) {
            $session->updateLastSeen();
        } else {
            $this->sessions()->create([
                'last_seen_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}
