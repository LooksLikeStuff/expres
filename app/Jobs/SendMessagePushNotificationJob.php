<?php

namespace App\Jobs;

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
    }

    public function handle(): void
    {
        $chat = $this->message->chat()->with('users.fcmTokens')->first();

        foreach ($chat->users as $user) {
            if ($user->id === $this->message->sender_id) {
                continue; // не шлём себе
            }

            foreach ($user->fcmTokens as $token) {
                $projectId = config('firebase.project_id');
                $response = Http::withToken($this->getAccessToken())
                ->post('https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send', [
                    'message' => [
                        'token' => $token->token,
                            'data' => [
                                'chat_id' => (string)($chat->id),
                                'unread_count' => (string)$chat->unreadCountForUser($user->id),
                                'last_message' => $chat->lastMessage?->content,
                                'time' => $chat->lastMessage?->formatted_time
                            ],
                        ],
                ]);

                Log::info('FCM RESPONSE', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        }
    }

    private function getAccessToken()
    {
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

        return $oauth->fetchAuthToken()['access_token'];
    }
}
