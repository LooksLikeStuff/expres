<?php

namespace App\Jobs;

use App\Models\Chats\Message;
use App\Models\User;
use Google\Auth\OAuth2;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendMessagePushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
        Log::info('[PushJob] Создана задача для сообщения', [
            'message_id' => $this->message->id,
            'chat_id' => $this->message->chat_id,
            'sender_id' => $this->message->sender_id,
        ]);
    }

    public function handle(): void
    {
        Log::info('[PushJob] Начало обработки');

        $chat = $this->message->chat()->with('users.fcmTokens')->first();

        if (!$chat) {
            Log::warning('[PushJob] Чат не найден', [
                'chat_id' => $this->message->chat_id,
            ]);
            return;
        }

        Log::info('[PushJob] Найден чат', [
            'chat_id' => $chat->id,
            'users_count' => $chat->users->count(),
        ]);

        $sender = $chat->users->firstWhere('id', $this->message->sender_id);

        foreach ($chat->users as $user) {
            if ($user->id === $sender->id) {
                Log::info('[PushJob] Пропущен отправитель', ['user_id' => $user->id]);
                continue;
            }

            if ($user->fcmTokens->isEmpty()) {
                Log::warning('[PushJob] У пользователя нет FCM токенов', ['user_id' => $user->id]);
                continue;
            }

            foreach ($user->fcmTokens as $token) {
                Log::info('[PushJob] Отправка пуша', [
                    'to_user_id' => $user->id,
                    'token' => $token->token,
                ]);

                try {
                    $projectId = config('firebase.project_id');

                    $response = Http::withToken($this->getAccessToken())
                        ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                            'message' => [
                                'token' => $token->token,
                                'notification' => [
                                    'title' => 'Новое сообщение от - ' . $sender->name,
                                    'body' => $this->message->content,
                                    'image' => asset('img/chats/notification.png'),
                                ],
                                'data' => [
                                    'chat_id' => (string)$chat->id,
                                    'unread_count' => (string)$chat->unreadCountForUser($user->id),
                                    'formatted_time' => (string)$this->message->formatted_time,
                                    'body' => $this->message->content,
                                ],
                                'android' => [
                                    'priority' => 'high',
                                ],
                                'apns' => [
                                    'headers' => [
                                        'apns-priority' => '10',
                                    ],
                                    'payload' => [
                                        'aps' => [
                                            'sound' => 'default',
                                        ],
                                    ],
                                ],
                            ],
                        ]);

                    Log::info('[PushJob] FCM RESPONSE', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    if ($response->status() !== 200) {
                        Log::error('[PushJob] Ошибка FCM', [
                            'user_id' => $user->id,
                            'token' => $token->token,
                            'response' => $response->body(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('[PushJob] Исключение при отправке', [
                        'user_id' => $user->id,
                        'token' => $token->token,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        Log::info('[PushJob] Завершено');
    }

    private function getAccessToken()
    {
        try {
            $credentialsPath = base_path(config('firebase.credentials'));
            $credentials = json_decode(file_get_contents($credentialsPath));

            $oauth = new OAuth2([
                'audience' => 'https://oauth2.googleapis.com/token',
                'issuer' => $credentials->client_email,
                'signingAlgorithm' => 'RS256',
                'signingKey' => $credentials->private_key,
                'tokenCredentialUri' => 'https://oauth2.googleapis.com/token',
                'scope' => ['https://www.googleapis.com/auth/firebase.messaging'],
            ]);

            $token = $oauth->fetchAuthToken()['access_token'];

            Log::info('[PushJob] Получен новый access_token');

            return $token;
        } catch (\Throwable $e) {
            Log::error('[PushJob] Ошибка получения access_token', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
