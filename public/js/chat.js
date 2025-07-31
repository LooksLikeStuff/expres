/**
 * Чат-система для корпоративного общения
 * Использует Laravel + Pusher для real-time сообщений
 */

class ChatSystem {
    constructor() {
        this.currentChatId = null;
        this.currentChatType = null; // 'private' или 'group'
        this.pusher = null;
        this.channels = new Map(); // Хранение активных каналов
        this.users = new Map(); // Кэш пользователей
        
        this.init();
    }

    /**
     * Инициализация системы чата
     */
    async init() {
        try {
            console.log('Инициализация чат-системы...');
            
            // Настройка Pusher
            await this.initPusher();
            
            // Привязка событий
            this.bindEvents();
            
            // Загрузка чатов пользователя
            await this.loadUserChats();
            
            // Загрузка пользователей для модальных окон
            await this.loadUsers();
            
            console.log('Чат-система успешно инициализирована');
        } catch (error) {
            console.error('Ошибка инициализации чата:', error);
            this.showError('Ошибка инициализации чата');
        }
    }

    /**
     * Настройка Pusher для real-time сообщений
     */
    async initPusher() {
        if (!window.Laravel.pusher.key) {
            console.warn('Pusher не настроен');
            return;
        }

        try {
            this.pusher = new Pusher(window.Laravel.pusher.key, {
                cluster: window.Laravel.pusher.cluster,
                encrypted: true,
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': window.Laravel.csrfToken
                    }
                }
            });

            console.log('Pusher подключен');
        } catch (error) {
            console.error('Ошибка подключения Pusher:', error);
        }
    }

    /**
     * Привязка событий к элементам интерфейса
     */
    bindEvents() {
        // Поиск чатов
        const chatSearch = document.getElementById('chatSearch');
        if (chatSearch) {
            chatSearch.addEventListener('input', (e) => {
                this.searchChats(e.target.value);
            });
        }

        // Кнопки создания чатов
        const newPrivateChatBtn = document.getElementById('newPrivateChatBtn');
        if (newPrivateChatBtn) {
            newPrivateChatBtn.addEventListener('click', () => {
                this.showNewPrivateChatModal();
            });
        }

        const newGroupChatBtn = document.getElementById('newGroupChatBtn');
        if (newGroupChatBtn) {
            newGroupChatBtn.addEventListener('click', () => {
                this.showNewGroupChatModal();
            });
        }

        const startNewChatBtn = document.getElementById('startNewChatBtn');
        if (startNewChatBtn) {
            startNewChatBtn.addEventListener('click', () => {
                this.showNewPrivateChatModal();
            });
        }

        // Форма отправки сообщения
        const messageForm = document.getElementById('messageForm');
        if (messageForm) {
            messageForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.sendMessage();
            });
        }

        // Автоподстройка высоты текстовой области
        const messageInput = document.getElementById('messageInput');
        if (messageInput) {
            messageInput.addEventListener('input', () => {
                this.autoResizeTextarea(messageInput);
                this.toggleSendButton();
            });
        }

        // Прикрепление файлов
        const attachFileBtn = document.getElementById('attachFileBtn');
        const fileInput = document.getElementById('fileInput');
        if (attachFileBtn && fileInput) {
            attachFileBtn.addEventListener('click', () => {
                fileInput.click();
            });

            fileInput.addEventListener('change', (e) => {
                this.handleFileSelect(e.target.files);
            });
        }

        // Модальные окна
        this.bindModalEvents();
    }

    /**
     * Привязка событий модальных окон
     */
    bindModalEvents() {
        // Создание приватного чата
        const createPrivateChatBtn = document.getElementById('createPrivateChatBtn');
        if (createPrivateChatBtn) {
            createPrivateChatBtn.addEventListener('click', () => {
                this.createPrivateChat();
            });
        }

        // Создание группового чата
        const createGroupChatBtn = document.getElementById('createGroupChatBtn');
        if (createGroupChatBtn) {
            createGroupChatBtn.addEventListener('click', () => {
                this.createGroupChat();
            });
        }
    }

    /**
     * Загрузка чатов пользователя
     */
    async loadUserChats() {
        try {
            this.showChatsLoading(true);

            const response = await fetch('/chat/user-chats', {
                headers: {
                    'X-CSRF-TOKEN': window.Laravel.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            this.renderChatsList(data.chats || []);
        } catch (error) {
            console.error('Ошибка загрузки чатов:', error);
            this.showError('Не удалось загрузить чаты');
        } finally {
            this.showChatsLoading(false);
        }
    }

    /**
     * Отображение/скрытие загрузки чатов
     */
    showChatsLoading(show) {
        const loading = document.getElementById('chatsLoading');
        if (loading) {
            loading.style.display = show ? 'block' : 'none';
        }
    }

    /**
     * Отрисовка списка чатов
     */
    renderChatsList(chats) {
        const chatsList = document.getElementById('chatsList');
        if (!chatsList) return;

        const loading = document.getElementById('chatsLoading');
        if (loading) {
            loading.style.display = 'none';
        }

        if (chats.length === 0) {
            chatsList.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-comments fa-2x mb-2"></i>
                    <p>Нет активных чатов</p>
                    <button class="btn btn-primary btn-sm" onclick="chatSystem.showNewPrivateChatModal()">
                        Создать чат
                    </button>
                </div>
            `;
            return;
        }

        chatsList.innerHTML = chats.map(chat => `
            <div class="chat-list-item" data-chat-id="${chat.id}" data-chat-type="${chat.type}">
                <div class="chat-avatar">
                    <img src="${chat.avatar || '/img/default-avatar.svg'}" alt="${chat.name}">
                    ${chat.is_online ? '<div class="online-indicator"></div>' : ''}
                </div>
                <div class="chat-info">
                    <div class="chat-name">${this.escapeHtml(chat.name)}</div>
                    <div class="chat-last-message">
                        ${chat.last_message ? this.escapeHtml(chat.last_message.message || 'Файл') : 'Нет сообщений'}
                    </div>
                </div>
                <div class="chat-meta">
                    <div class="chat-time">
                        ${chat.last_message ? this.formatTime(chat.last_message.created_at) : ''}
                    </div>
                    ${chat.unread_count > 0 ? `<div class="unread-badge">${chat.unread_count}</div>` : ''}
                </div>
            </div>
        `).join('');

        // Привязка кликов на чаты
        chatsList.querySelectorAll('.chat-list-item').forEach(item => {
            item.addEventListener('click', () => {
                const chatId = item.dataset.chatId;
                const chatType = item.dataset.chatType;
                this.openChat(chatId, chatType);
            });
        });
    }

    /**
     * Открытие чата
     */
    async openChat(chatId, chatType = 'private') {
        try {
            this.currentChatId = chatId;
            this.currentChatType = chatType;

            // Обновление активного элемента в списке
            document.querySelectorAll('.chat-list-item').forEach(item => {
                item.classList.remove('active');
            });
            const activeItem = document.querySelector(`[data-chat-id="${chatId}"]`);
            if (activeItem) {
                activeItem.classList.add('active');
            }

            // Показ контейнера активного чата
            const chatEmpty = document.getElementById('chatEmpty');
            const activeChatContainer = document.getElementById('activeChatContainer');
            
            if (chatEmpty) chatEmpty.style.display = 'none';
            if (activeChatContainer) {
                activeChatContainer.classList.remove('d-none');
            }

            // Загрузка сообщений
            await this.loadMessages(chatId);

            // Подписка на канал чата
            this.subscribeToChat(chatId);

            // Обновление заголовка чата
            this.updateChatHeader(chatId);

        } catch (error) {
            console.error('Ошибка открытия чата:', error);
            this.showError('Не удалось открыть чат');
        }
    }

    /**
     * Загрузка сообщений чата
     */
    async loadMessages(chatId) {
        try {
            const response = await fetch(`/chat/messages/${chatId}`, {
                headers: {
                    'X-CSRF-TOKEN': window.Laravel.csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            this.renderMessages(data.messages || []);
        } catch (error) {
            console.error('Ошибка загрузки сообщений:', error);
            this.showError('Не удалось загрузить сообщения');
        }
    }

    /**
     * Отрисовка сообщений
     */
    renderMessages(messages) {
        const container = document.getElementById('messagesContainer');
        if (!container) return;

        if (messages.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-comments fa-2x mb-2"></i>
                    <p>Начните беседу, отправив первое сообщение</p>
                </div>
            `;
            return;
        }

        container.innerHTML = messages.map(message => {
            const isOwn = message.user_id == window.Laravel.user.id;
            return `
                <div class="message ${isOwn ? 'own' : ''} fade-in">
                    <div class="message-avatar">
                        <img src="${message.user.avatar || '/img/default-avatar.svg'}" alt="${message.user.name}">
                    </div>
                    <div class="message-content">
                        ${!isOwn ? `<div class="message-author">${this.escapeHtml(message.user.name)}</div>` : ''}
                        <div class="message-text">${this.escapeHtml(message.message || '')}</div>
                        ${message.attachments ? this.renderAttachments(message.attachments) : ''}
                        <div class="message-time">${this.formatTime(message.created_at)}</div>
                    </div>
                </div>
            `;
        }).join('');

        // Прокрутка к последнему сообщению
        this.scrollToBottom();
    }

    /**
     * Отправка сообщения
     */
    async sendMessage() {
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        
        if (!messageInput || !this.currentChatId) return;

        const message = messageInput.value.trim();
        if (!message) return;

        try {
            sendBtn.disabled = true;
            
            const formData = new FormData();
            formData.append('room_id', this.currentChatId);
            formData.append('message', message);
            formData.append('message_type', 'text');

            const response = await fetch('/chat/send', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.Laravel.csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            
            // Очистка поля ввода
            messageInput.value = '';
            this.autoResizeTextarea(messageInput);
            this.toggleSendButton();

            // Обновление сообщений через Pusher произойдет автоматически
            
        } catch (error) {
            console.error('Ошибка отправки сообщения:', error);
            this.showError('Не удалось отправить сообщение');
        } finally {
            sendBtn.disabled = false;
        }
    }

    /**
     * Загрузка пользователей для модальных окон
     */
    async loadUsers() {
        try {
            const response = await fetch('/chat/search/users', {
                headers: {
                    'X-CSRF-TOKEN': window.Laravel.csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            this.populateUserSelects(data.users || []);
        } catch (error) {
            console.error('Ошибка загрузки пользователей:', error);
        }
    }

    /**
     * Заполнение селектов пользователями
     */
    populateUserSelects(users) {
        const selectUser = document.getElementById('selectUser');
        if (selectUser) {
            selectUser.innerHTML = '<option value="">Выберите пользователя</option>' +
                users.map(user => `<option value="${user.id}">${this.escapeHtml(user.name)}</option>`).join('');
        }

        const groupUsersList = document.getElementById('groupUsersList');
        if (groupUsersList) {
            groupUsersList.innerHTML = users.map(user => `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="${user.id}" id="user_${user.id}">
                    <label class="form-check-label" for="user_${user.id}">
                        ${this.escapeHtml(user.name)}
                    </label>
                </div>
            `).join('');
        }
    }

    /**
     * Показ модального окна создания приватного чата
     */
    showNewPrivateChatModal() {
        const modal = new bootstrap.Modal(document.getElementById('newPrivateChatModal'));
        modal.show();
    }

    /**
     * Показ модального окна создания группового чата
     */
    showNewGroupChatModal() {
        const modal = new bootstrap.Modal(document.getElementById('newGroupChatModal'));
        modal.show();
    }

    /**
     * Создание приватного чата
     */
    async createPrivateChat() {
        const selectUser = document.getElementById('selectUser');
        if (!selectUser || !selectUser.value) {
            this.showError('Выберите пользователя');
            return;
        }

        try {
            const response = await fetch('/chat/private', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.Laravel.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    participant_id: selectUser.value
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            
            // Закрытие модального окна
            const modal = bootstrap.Modal.getInstance(document.getElementById('newPrivateChatModal'));
            modal.hide();

            // Обновление списка чатов
            await this.loadUserChats();

            // Открытие нового чата
            this.openChat(data.chat.id, 'private');

        } catch (error) {
            console.error('Ошибка создания чата:', error);
            this.showError('Не удалось создать чат');
        }
    }

    /**
     * Подписка на канал чата для real-time обновлений
     */
    subscribeToChat(chatId) {
        if (!this.pusher) return;

        // Отписка от предыдущих каналов
        this.channels.forEach(channel => {
            this.pusher.unsubscribe(channel.name);
        });
        this.channels.clear();

        // Подписка на новый канал
        const channelName = `chat.${chatId}`;
        const channel = this.pusher.subscribe(channelName);
        
        channel.bind('new-message', (data) => {
            this.handleNewMessage(data);
        });

        this.channels.set(chatId, channel);
    }

    /**
     * Обработка нового сообщения через Pusher
     */
    handleNewMessage(data) {
        if (data.message.chat_room_id == this.currentChatId) {
            // Добавление сообщения в текущий чат
            this.addMessageToChat(data.message);
        }
        
        // Обновление списка чатов
        this.updateChatInList(data.message);
    }

    /**
     * Добавление сообщения в чат
     */
    addMessageToChat(message) {
        const container = document.getElementById('messagesContainer');
        if (!container) return;

        const isOwn = message.user_id == window.Laravel.user.id;
        const messageHtml = `
            <div class="message ${isOwn ? 'own' : ''} fade-in">
                <div class="message-avatar">
                    <img src="${message.user.avatar || '/img/default-avatar.svg'}" alt="${message.user.name}">
                </div>
                <div class="message-content">
                    ${!isOwn ? `<div class="message-author">${this.escapeHtml(message.user.name)}</div>` : ''}
                    <div class="message-text">${this.escapeHtml(message.message || '')}</div>
                    <div class="message-time">${this.formatTime(message.created_at)}</div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', messageHtml);
        this.scrollToBottom();
    }

    /**
     * Вспомогательные методы
     */
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;

        if (diff < 24 * 60 * 60 * 1000) { // Меньше суток
            return date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
        } else {
            return date.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit' });
        }
    }

    autoResizeTextarea(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }

    toggleSendButton() {
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        
        if (messageInput && sendBtn) {
            sendBtn.disabled = !messageInput.value.trim();
        }
    }

    scrollToBottom() {
        const container = document.getElementById('messagesContainer');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    showError(message) {
        // Простое уведомление об ошибке
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger position-fixed';
        alert.style.cssText = 'top: 80px; right: 20px; z-index: 9999; max-width: 300px;';
        alert.innerHTML = `
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            ${message}
        `;
        document.body.appendChild(alert);

        // Автоудаление через 5 секунд
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 5000);
    }
}

// Инициализация при загрузке страницы
let chatSystem;
document.addEventListener('DOMContentLoaded', function() {
    if (window.Laravel && window.Laravel.user) {
        chatSystem = new ChatSystem();
        
        // Глобальная доступность для debug
        window.chatSystem = chatSystem;
    }
});
