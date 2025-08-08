import Echo from 'laravel-echo';
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
import { getMessaging, getToken, onMessage } from 'firebase/messaging';
import $ from "jquery";

export default class ChatClient {
    constructor(userId, userName) {
        this.userId = userId;
        this.userName = userName;
        this.currentChatId = null;
        this.onlineUsers = null;
        this.echo = null;
        this.globalPresenceChannel = null;
        this.lastTyping = null;
        this.observer = null;
    }

    init() {
        this.initEcho();
        this.initFirebase();
        this.joinGlobalPresenceChannel();
    }

    initFirebase() {
        const firebaseConfig = {
            apiKey: "AIzaSyBFrkGJgs8g3OzVCv-g1J8pCkZo-QLTZqY",
            authDomain: "mypersonal-38208.firebaseapp.com",
            projectId: "mypersonal-38208",
            storageBucket: "mypersonal-38208.firebasestorage.app",
            messagingSenderId: "444177232931",
            appId: "1:444177232931:web:503d0aa632374e236f2d96",
            measurementId: "G-T5V0Z8E2B8"
        };

        const app = initializeApp(firebaseConfig);
        getAnalytics(app);
        const messaging = getMessaging(app);

        Notification.requestPermission().then((permission) => {
            if (permission === 'granted') {
                getToken(messaging, {
                    vapidKey: 'BEgwA4ZZgZw5TABmL4ndYRgcXHpdvs0eAfQviId0ZzTVFVgE57T5UNCNPOxy-v60YlOjakP3XEUl6PwlWt9ovTI'
                }).then((currentToken) => {
                    if (currentToken) {
                        this.registerFirebaseToken(currentToken);
                    } else {
                        console.warn('Нет токена. Запроси разрешение на уведомления.');
                    }
                }).catch((err) => {
                    console.error('Ошибка при получении токена:', err);
                });
            } else {
                console.warn('Пользователь запретил уведомления:', permission);
            }
        });


        onMessage(messaging, (payload) => {
            this.onFirebaseMessage(payload);
        });

    }


    async registerFirebaseToken(fcmToken) {
        await fetch('/fcm/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ token: fcmToken }),
        });
    }

    initEcho() {
        this.echo = new Echo({
            broadcaster: 'pusher',
            key: '371b92c8af1e4bce7e5f',
            cluster: 'ap1',
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
        if (!this.echo) return;
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
                if (parseInt(e.user_id) === this.getUserId()) return;
                callback('typing', e);
            });
    }

    joinGlobalPresenceChannel() {
        if (!this.echo) return;

        const channelName = 'presence.global';

        this.onlineUsers = new Set();

        this.globalPresenceChannel = this.echo.join(channelName)
            .here((users) => {
                this.onlineUsers = new Set(users.map(user => user.id));
                this.onOnlineUsersChange?.(this.onlineUsers);
            })
            .joining((user) => {
                this.onlineUsers.add(user.id);
                this.onOnlineUsersChange?.(this.onlineUsers);
            })
            .leaving((user) => {
                this.onlineUsers.delete(user.id);
                this.onOnlineUsersChange?.(this.onlineUsers);
            });
    }

    setOnlineUsersChangeHandler(callback) {
        this.onOnlineUsersChange = callback;
    }

    setFirebaseMessageHandler(callback) {
        this.onFirebaseMessage = callback;
    }


    leaveCurrentChat() {
        if (!this.echo || !this.currentChatId) return;

        this.echo.leave(`chat.${this.currentChatId}`);
        this.currentChatId = null;
    }


    async sendMessage(chatId, content, attachments) {
        const formData = new FormData();

        formData.append('chat_id', chatId);
        formData.append('content', content);

        if (attachments) {
            for (let i = 0; i < attachments.length; i++) {
                formData.append('attachments[]', attachments[i]);
            }
        }


        for (const [key, value] of formData.entries()) {
            console.log(`${key}:`, value);
        }

        try {
            const response = await $.ajax({
                url: '/messages',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
            });

            return response;
        } catch (error) {
            console.error('Ошибка при отправке сообщения:', error);
            throw error;
        }
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

        $(container).find('.message:not(.own)').each((_, el) => {
            this.observer.observe(el);
        });
    }

    async handleIntersection(entries) {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                const $el = $(entry.target);
                const messageId = $el.data('message-id');
                const userId = this.getUserId();

                if (!messageId || $el.data('read-status') !== 0) continue;

                try {
                    const response = await $.ajax({
                        url: '/readReceipts/read',
                        method: 'PATCH',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            message_id: messageId,
                            user_id: userId,
                            read_at: new Date().toISOString(),
                        }),
                    });

                    $el.data('read-status', 1);
                    this.observer.unobserve(entry.target);

                } catch (error) {
                    console.error('Ошибка при отправке readReceipt:', error);
                }
            }
        }
    }

    sendTypingEvent() {
        if (!this.currentChatId || !this.echo) return;

        // Ограничим отправку whisper'ов раз в 2 секунды
        if (this.lastTyping && Date.now() - this.lastTyping < 2000) return;

        this.lastTyping = Date.now();


        this.echo.private(`chat.${this.currentChatId}`)
            .whisper('typing', {
                user_id: this.userId,
                username: this.userName,
                timestamp: new Date().toISOString(),
            });
    }


}
