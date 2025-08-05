import Echo from 'laravel-echo';

export default class ChatClient {
    constructor(userId) {
        this.userId = userId;
        this.currentChatId = null;
        this.echo = null;
        this.currentPresenceChannel = null;
        this.lastTyping = null;
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
        if (this.currentChatId === chatId) return;

        this.leaveCurrentChat();
        this.currentChatId = chatId;

        this.echo.private(`chat.${chatId}`)
            .listen('MessageSent', (e) => {
                callback('message', e);
            })
            .listen('MessageRead', (e) => {
                if (parseInt(e.user_id) === this.getUserId()) return;
                callback('read', e);
            })
            .listenForWhisper('typing', (e) => {
                console.log(e);
                if (parseInt(e.user_id) === this.getUserId()) return;
                callback('typing', e);
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

    observeReadReceipts(container) {
        if (!this.observer) {
            this.observer = new IntersectionObserver(this.handleIntersection.bind(this), {
                threshold: 1.0,
            });
        }

        container.querySelectorAll('[data-read-status="0"]').forEach(el => {
            this.observer.observe(el);
        });
    }

    async handleIntersection(entries) {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                const el = entry.target;
                const messageId = el.dataset.messageId;
                const userId = this.getUserId();

                if (!messageId || el.dataset.readStatus !== '0') continue;

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const response = await fetch('/readReceipts/read', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({
                        message_id: messageId,
                        user_id: userId,
                        read_at: new Date().toISOString(),
                    }),
                });

                if (response.ok) {
                    el.dataset.readStatus = '1';
                    this.observer.unobserve(el);
                }
            }
        }
    }

    joinPresenceChannel(chatId, onUsersChange) {
        const channelName = `presence-chat.${chatId}`;

        this.currentPresenceChannel = this.echo.join(channelName)
            .here((users) => {
                // начальный список пользователей
                onUsersChange(users);
            })
            .joining((user) => {
                // пользователь присоединился
                onUsersChange(null, {type: 'joined', user});
            })
            .leaving((user) => {
                // пользователь вышел
                onUsersChange(null, {type: 'left', user});
            });
    }

    sendTypingEvent() {
        if (!this.currentChatId || !this.echo) return;

        if (this.lastTyping && Date.now() - this.lastTyping < 2000) return;

        this.lastTyping = Date.now();

        this.echo.private(`chat.${this.currentChatId}`)
            .whisper('typing', {
                user_id: this.userId,
                timestamp: new Date().toISOString(),
            });
    }
}
