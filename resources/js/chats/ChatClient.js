import Echo from 'laravel-echo';

export default class ChatClient {
    constructor(userId) {
        this.userId = userId;
        this.currentChatId = null;
        this.echo = null;
    }

    initEcho(pusherKey, cluster) {
        this.echo = new Echo({
            broadcaster: 'pusher',
            key: pusherKey,
            cluster,
            forceTLS: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            }
        });
    }

    joinChat(chatId, callback) {
        //Проверяем чтобы не создавать одинаковые соединения
        if (this.currentChatId === chatId) return;

        //Выходим с текущего чата (закрываем соединение)
        this.leaveCurrentChat();

        this.currentChatId = chatId;

        this.echo.private(`chat.${chatId}`)
            .listen('MessageSent', (e) => {
                console.log(e);
                callback(e);
            });
    }

    leaveCurrentChat() {
        this.echo.leave(`chat.${this.currentChatId}`);
    }

    async sendMessage(chatId, content) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        return await fetch('/messages', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify({ chat_id: chatId, content }),
        });
    }

    getUserId() {
        return parseInt(this.userId);
    }
}
