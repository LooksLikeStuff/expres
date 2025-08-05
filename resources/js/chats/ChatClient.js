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

    async sendMessage(chatId, content, attachments) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const formData = new FormData();

        formData.append('chat_id', chatId);
        formData.append('content', content);


        if (attachments && attachments.length) {
            Array.from(attachments).forEach((file) => {
                formData.append('attachments[]', file);
            });
        }

        return await fetch('/messages', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
            },
            body: formData,
        });
    }

    getUserId() {
        return parseInt(this.userId);
    }

    async removeUserFromCurrentChat(userId) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const response = await fetch(`/userChats/${this.currentChatId}/users/remove`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                user_id: userId,
                '_method': 'DELETE',
            })
        });

        if (response.ok) {
            const li = document.querySelector(`.chats__userlist-item[data-user-id="${userId}"]`);
            if (li) li.remove();
        } else {
            console.error('Ошибка при удалении пользователя');
        }
    }
}
