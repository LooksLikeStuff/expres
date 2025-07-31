/**
 * Standalone Chat System - JavaScript Module
 * Обеспечивает функциональность корпоративного чата без перезагрузки страниц
 */

class StandaloneChat {
    constructor() {
        this.currentChatType = null; // 'contact' или 'group'
        this.currentChatId = null;
        this.currentPage = 1;
        this.loading = false;
        this.typingTimeout = null;
        this.lastMessageId = null;
        this.selectedFiles = [];
        this.pusher = null;
        this.channel = null;
        this.userId = document.querySelector('meta[name="user-id"]').getAttribute('content');
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        this.init();
    }

    /**
     * Инициализация чата
     */
    init() {
        this.setupEventListeners();
        this.loadContacts();
        this.initializePusher();
        this.setupAutoRefresh();
    }

    /**
     * Настройка слушателей событий
     */
    setupEventListeners() {
        // Переключение вкладок
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.switchTab(e.target.dataset.tab);
            });
        });

        // Поиск
        const searchInput = document.getElementById('chat-search-input');
        const searchBtn = document.getElementById('search-btn');
        
        searchInput.addEventListener('input', (e) => {
            this.debounce(() => this.searchUsers(e.target.value), 300);
        });
        
        searchBtn.addEventListener('click', () => {
            this.searchUsers(searchInput.value);
        });

        // Отправка сообщения
        const messageForm = document.getElementById('message-form');
        messageForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.sendMessage();
        });

        // Ввод сообщения (Enter для отправки)
        const messageInput = document.getElementById('message-input');
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        // Индикатор печатания
        messageInput.addEventListener('input', () => {
            this.handleTyping();
        });

        // Прикрепление файлов
        const attachBtn = document.getElementById('attach-file-btn');
        const fileInput = document.getElementById('file-input');
        
        attachBtn.addEventListener('click', () => {
            fileInput.click();
        });
        
        fileInput.addEventListener('change', (e) => {
            this.handleFileSelection(e.target.files);
        });

        // Создание группы
        const createGroupBtn = document.getElementById('create-group-btn');
        const createGroupModal = new bootstrap.Modal(document.getElementById('create-group-modal'));
        const createGroupSubmit = document.getElementById('create-group-submit');

        createGroupBtn.addEventListener('click', () => {
            createGroupModal.show();
        });

        createGroupSubmit.addEventListener('click', () => {
            this.createGroup();
        });

        // Поиск участников группы
        const groupMembersSearch = document.getElementById('group-members-search');
        groupMembersSearch.addEventListener('input', (e) => {
            this.debounce(() => this.searchGroupMembers(e.target.value), 300);
        });

        // Обновление чата
        const refreshBtn = document.getElementById('refresh-chat-btn');
        refreshBtn.addEventListener('click', () => {
            this.refreshCurrentChat();
        });
    }

    /**
     * Переключение вкладок (контакты/группы)
     */
    switchTab(tab) {
        // Обновляем активную вкладку
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tab}"]`).classList.add('active');

        // Показываем соответствующий контент
        document.getElementById('contacts-tab').style.display = tab === 'contacts' ? 'block' : 'none';
        document.getElementById('groups-tab').style.display = tab === 'groups' ? 'block' : 'none';

        // Загружаем данные если нужно
        if (tab === 'contacts' && !document.getElementById('contacts-list').children.length) {
            this.loadContacts();
        } else if (tab === 'groups' && !document.getElementById('groups-list').children.length) {
            this.loadGroups();
        }

        // Обновляем поиск placeholder
        const searchInput = document.getElementById('chat-search-input');
        searchInput.placeholder = tab === 'contacts' ? 'Поиск контактов...' : 'Поиск групп...';
    }

    /**
     * Загрузка списка контактов
     */
    async loadContacts() {
        try {
            this.showLoading('contacts');
            
            const response = await fetch('/standalone-chat/contacts', {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Ошибка загрузки контактов');
            }

            const data = await response.json();
            this.renderContacts(data.contacts);
            
        } catch (error) {
            console.error('Ошибка загрузки контактов:', error);
            this.showToast('Ошибка загрузки контактов', 'error');
        } finally {
            this.hideLoading('contacts');
        }
    }

    /**
     * Загрузка списка групп
     */
    async loadGroups() {
        try {
            this.showLoading('groups');
            
            const response = await fetch('/standalone-chat/groups', {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Ошибка загрузки групп');
            }

            const data = await response.json();
            this.renderGroups(data.groups);
            
        } catch (error) {
            console.error('Ошибка загрузки групп:', error);
            this.showToast('Ошибка загрузки групп', 'error');
        } finally {
            this.hideLoading('groups');
        }
    }

    /**
     * Отображение контактов
     */
    renderContacts(contacts) {
        const contactsList = document.getElementById('contacts-list');
        contactsList.innerHTML = '';

        if (!contacts.length) {
            contactsList.innerHTML = `
                <div class="chat-empty-state">
                    <i class="fas fa-users"></i>
                    <p>Нет доступных контактов</p>
                </div>
            `;
            return;
        }

        contacts.forEach(contact => {
            const contactElement = this.createContactElement(contact);
            contactsList.appendChild(contactElement);
        });
    }

    /**
     * Создание элемента контакта
     */
    createContactElement(contact) {
        const div = document.createElement('div');
        div.className = 'contact-item';
        div.dataset.contactId = contact.id;
        
        const avatar = contact.avatar || '/img/default-avatar.svg';
        const isOnline = contact.is_online;
        const unreadCount = contact.unread_count || 0;

        div.innerHTML = `
            <img src="${avatar}" alt="${contact.name}" class="contact-avatar">
            <div class="contact-info">
                <h6 class="contact-name">${this.escapeHtml(contact.name)}</h6>
                <p class="contact-status">${contact.role || 'Пользователь'}</p>
            </div>
            ${unreadCount > 0 ? `<span class="contact-badge">${unreadCount}</span>` : ''}
            ${isOnline ? '<div class="online-indicator"></div>' : ''}
        `;

        div.addEventListener('click', () => {
            this.selectContact(contact.id, contact);
        });

        return div;
    }

    /**
     * Отображение групп
     */
    renderGroups(groups) {
        const groupsList = document.getElementById('groups-list');
        groupsList.innerHTML = '';

        if (!groups.length) {
            groupsList.innerHTML = `
                <div class="chat-empty-state">
                    <i class="fas fa-users"></i>
                    <p>Нет доступных групп</p>
                </div>
            `;
            return;
        }

        groups.forEach(group => {
            const groupElement = this.createGroupElement(group);
            groupsList.appendChild(groupElement);
        });
    }

    /**
     * Создание элемента группы
     */
    createGroupElement(group) {
        const div = document.createElement('div');
        div.className = 'group-item';
        div.dataset.groupId = group.id;
        
        const avatar = group.avatar || '/img/default-group.svg';
        const membersCount = group.members_count || 0;
        const unreadCount = group.unread_count || 0;

        div.innerHTML = `
            <img src="${avatar}" alt="${group.name}" class="group-avatar">
            <div class="group-info">
                <h6 class="group-name">${this.escapeHtml(group.name)}</h6>
                <p class="group-status">${membersCount} участников</p>
            </div>
            ${unreadCount > 0 ? `<span class="contact-badge">${unreadCount}</span>` : ''}
        `;

        div.addEventListener('click', () => {
            this.selectGroup(group.id, group);
        });

        return div;
    }

    /**
     * Выбор контакта для чата
     */
    async selectContact(contactId, contactData) {
        // Убираем активный класс у всех элементов
        document.querySelectorAll('.contact-item, .group-item').forEach(item => {
            item.classList.remove('active');
        });

        // Добавляем активный класс выбранному контакту
        document.querySelector(`[data-contact-id="${contactId}"]`).classList.add('active');

        // Устанавливаем текущий чат
        this.currentChatType = 'contact';
        this.currentChatId = contactId;

        // Обновляем заголовок чата
        this.updateChatHeader(contactData);

        // Загружаем сообщения
        await this.loadMessages(contactId, 'contact');

        // Показываем интерфейс чата
        this.showChatInterface();

        // Подписываемся на канал
        this.subscribeToChannel(`private-chat.${contactId}`);
    }

    /**
     * Выбор группы для чата
     */
    async selectGroup(groupId, groupData) {
        // Убираем активный класс у всех элементов
        document.querySelectorAll('.contact-item, .group-item').forEach(item => {
            item.classList.remove('active');
        });

        // Добавляем активный класс выбранной группе
        document.querySelector(`[data-group-id="${groupId}"]`).classList.add('active');

        // Устанавливаем текущий чат
        this.currentChatType = 'group';
        this.currentChatId = groupId;

        // Обновляем заголовок чата
        this.updateChatHeader(groupData, true);

        // Загружаем сообщения
        await this.loadMessages(groupId, 'group');

        // Показываем интерфейс чата
        this.showChatInterface();

        // Подписываемся на канал группы
        this.subscribeToChannel(`private-group.${groupId}`);
    }

    /**
     * Обновление заголовка чата
     */
    updateChatHeader(data, isGroup = false) {
        const header = document.getElementById('current-chat-header');
        const avatar = document.getElementById('current-chat-avatar');
        const name = document.getElementById('current-chat-name');
        const status = document.getElementById('current-chat-status');

        avatar.src = data.avatar || (isGroup ? '/img/default-group.svg' : '/img/default-avatar.svg');
        name.textContent = data.name;
        
        if (isGroup) {
            status.textContent = `${data.members_count || 0} участников`;
        } else {
            status.textContent = data.is_online ? 'Онлайн' : 'Офлайн';
            status.className = data.is_online ? 'text-success' : 'text-muted';
        }

        header.style.display = 'flex';
    }

    /**
     * Загрузка сообщений
     */
    async loadMessages(chatId, chatType) {
        try {
            const endpoint = chatType === 'group' 
                ? `/standalone-chat/groups/${chatId}/messages`
                : `/standalone-chat/contacts/${chatId}/messages`;

            const response = await fetch(endpoint, {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Ошибка загрузки сообщений');
            }

            const data = await response.json();
            this.renderMessages(data.messages);

            // Прокрутка к последнему сообщению
            this.scrollToBottom();
            
        } catch (error) {
            console.error('Ошибка загрузки сообщений:', error);
            this.showToast('Ошибка загрузки сообщений', 'error');
        }
    }

    /**
     * Отображение сообщений
     */
    renderMessages(messages) {
        const container = document.getElementById('messages-container');
        container.innerHTML = '';

        if (!messages.length) {
            container.innerHTML = `
                <div class="chat-empty-state">
                    <i class="fas fa-comment"></i>
                    <p>Нет сообщений. Начните общение!</p>
                </div>
            `;
            return;
        }

        messages.forEach(message => {
            const messageElement = this.createMessageElement(message);
            container.appendChild(messageElement);
        });

        this.lastMessageId = messages[messages.length - 1]?.id;
    }

    /**
     * Создание элемента сообщения
     */
    createMessageElement(message) {
        const div = document.createElement('div');
        const isOwn = message.user_id == this.userId;
        
        div.className = `message-item ${isOwn ? 'own' : ''}`;
        div.dataset.messageId = message.id;

        const avatar = message.user?.avatar || '/img/default-avatar.svg';
        const userName = message.user?.name || 'Неизвестный пользователь';
        const messageTime = this.formatTime(message.created_at);
        
        // Обработка файлов
        let filesHtml = '';
        if (message.files && message.files.length) {
            filesHtml = '<div class="message-files">';
            message.files.forEach(file => {
                const fileIcon = this.getFileIcon(file.file_name);
                filesHtml += `
                    <a href="${file.file_path}" class="file-attachment" target="_blank">
                        <i class="${fileIcon}"></i>
                        ${this.escapeHtml(file.file_name)}
                    </a>
                `;
            });
            filesHtml += '</div>';
        }

        div.innerHTML = `
            <img src="${avatar}" alt="${userName}" class="message-avatar">
            <div class="message-content">
                <div class="message-bubble">
                    <p class="message-text">${this.escapeHtml(message.message)}</p>
                    ${filesHtml}
                </div>
                <div class="message-meta">
                    <span class="message-time">${messageTime}</span>
                    ${isOwn ? '<span class="message-status"><i class="fas fa-check"></i></span>' : ''}
                </div>
            </div>
        `;

        return div;
    }

    /**
     * Отправка сообщения
     */
    async sendMessage() {
        const messageInput = document.getElementById('message-input');
        const message = messageInput.value.trim();

        if (!message && !this.selectedFiles.length) {
            return;
        }

        if (!this.currentChatId || !this.currentChatType) {
            this.showToast('Выберите чат для отправки сообщения', 'warning');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('message', message);
            formData.append('chat_type', this.currentChatType);
            formData.append('chat_id', this.currentChatId);

            // Добавляем файлы
            this.selectedFiles.forEach((file, index) => {
                formData.append(`files[${index}]`, file);
            });

            const endpoint = this.currentChatType === 'group' 
                ? '/standalone-chat/send-group-message'
                : '/standalone-chat/send-message';

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: formData
            });

            if (!response.ok) {
                throw new Error('Ошибка отправки сообщения');
            }

            const data = await response.json();
            
            // Очищаем форму
            messageInput.value = '';
            this.clearSelectedFiles();

            // Добавляем сообщение в интерфейс
            if (data.message) {
                this.addMessageToChat(data.message);
            }

        } catch (error) {
            console.error('Ошибка отправки сообщения:', error);
            this.showToast('Ошибка отправки сообщения', 'error');
        }
    }

    /**
     * Добавление сообщения в чат
     */
    addMessageToChat(message) {
        const container = document.getElementById('messages-container');
        
        // Удаляем пустое состояние если есть
        const emptyState = container.querySelector('.chat-empty-state');
        if (emptyState) {
            emptyState.remove();
        }

        const messageElement = this.createMessageElement(message);
        container.appendChild(messageElement);

        this.scrollToBottom();
        this.lastMessageId = message.id;
    }

    /**
     * Обработка выбора файлов
     */
    handleFileSelection(files) {
        const maxFiles = 5;
        const maxFileSize = 10 * 1024 * 1024; // 10MB

        Array.from(files).forEach(file => {
            if (this.selectedFiles.length >= maxFiles) {
                this.showToast(`Максимум ${maxFiles} файлов за раз`, 'warning');
                return;
            }

            if (file.size > maxFileSize) {
                this.showToast(`Файл "${file.name}" слишком большой (макс. 10MB)`, 'warning');
                return;
            }

            this.selectedFiles.push(file);
        });

        this.updateFilesPreview();
    }

    /**
     * Обновление предпросмотра файлов
     */
    updateFilesPreview() {
        const preview = document.getElementById('files-preview');
        const list = document.getElementById('files-preview-list');

        if (!this.selectedFiles.length) {
            preview.style.display = 'none';
            return;
        }

        list.innerHTML = '';
        
        this.selectedFiles.forEach((file, index) => {
            const div = document.createElement('div');
            div.className = 'file-preview-item';
            
            const fileIcon = this.getFileIcon(file.name);
            const fileSize = this.formatFileSize(file.size);

            div.innerHTML = `
                <i class="${fileIcon} file-preview-icon"></i>
                <div class="file-preview-info">
                    <div class="file-preview-name">${this.escapeHtml(file.name)}</div>
                    <div class="file-preview-size">${fileSize}</div>
                </div>
                <button type="button" class="file-preview-remove" data-index="${index}">
                    <i class="fas fa-times"></i>
                </button>
            `;

            // Обработчик удаления файла
            div.querySelector('.file-preview-remove').addEventListener('click', () => {
                this.removeFile(index);
            });

            list.appendChild(div);
        });

        preview.style.display = 'block';
    }

    /**
     * Удаление файла из выбранных
     */
    removeFile(index) {
        this.selectedFiles.splice(index, 1);
        this.updateFilesPreview();
    }

    /**
     * Очистка выбранных файлов
     */
    clearSelectedFiles() {
        this.selectedFiles = [];
        document.getElementById('file-input').value = '';
        this.updateFilesPreview();
    }

    /**
     * Создание группы
     */
    async createGroup() {
        const nameInput = document.getElementById('group-name');
        const descriptionInput = document.getElementById('group-description');
        const name = nameInput.value.trim();
        const description = descriptionInput.value.trim();

        if (!name) {
            this.showToast('Введите название группы', 'warning');
            return;
        }

        const selectedMembers = Array.from(document.querySelectorAll('.selected-member'))
            .map(el => el.dataset.userId);

        if (selectedMembers.length === 0) {
            this.showToast('Выберите хотя бы одного участника', 'warning');
            return;
        }

        try {
            const response = await fetch('/standalone-chat/create-group', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    description: description,
                    members: selectedMembers
                })
            });

            if (!response.ok) {
                throw new Error('Ошибка создания группы');
            }

            const data = await response.json();

            // Закрываем модальное окно
            const modal = bootstrap.Modal.getInstance(document.getElementById('create-group-modal'));
            modal.hide();

            // Очищаем форму
            nameInput.value = '';
            descriptionInput.value = '';
            document.getElementById('selected-members').innerHTML = '';

            // Обновляем список групп
            this.loadGroups();

            this.showToast('Группа успешно создана', 'success');

        } catch (error) {
            console.error('Ошибка создания группы:', error);
            this.showToast('Ошибка создания группы', 'error');
        }
    }

    /**
     * Поиск пользователей
     */
    async searchUsers(query) {
        if (!query.trim()) {
            // Если запрос пустой, загружаем обычные списки
            if (document.querySelector('.tab-btn.active').dataset.tab === 'contacts') {
                this.loadContacts();
            } else {
                this.loadGroups();
            }
            return;
        }

        try {
            const response = await fetch(`/standalone-chat/search?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Ошибка поиска');
            }

            const data = await response.json();

            // Отображаем результаты в зависимости от активной вкладки
            if (document.querySelector('.tab-btn.active').dataset.tab === 'contacts') {
                this.renderContacts(data.users || []);
            } else {
                this.renderGroups(data.groups || []);
            }

        } catch (error) {
            console.error('Ошибка поиска:', error);
        }
    }

    /**
     * Поиск участников для группы
     */
    async searchGroupMembers(query) {
        if (!query.trim()) {
            document.getElementById('members-suggestions').style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`/standalone-chat/search?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Ошибка поиска участников');
            }

            const data = await response.json();
            this.renderMembersSuggestions(data.users || []);

        } catch (error) {
            console.error('Ошибка поиска участников:', error);
        }
    }

    /**
     * Отображение предложений участников
     */
    renderMembersSuggestions(users) {
        const suggestions = document.getElementById('members-suggestions');
        suggestions.innerHTML = '';

        if (!users.length) {
            suggestions.style.display = 'none';
            return;
        }

        users.forEach(user => {
            // Проверяем, не выбран ли уже пользователь
            const isSelected = document.querySelector(`[data-user-id="${user.id}"]`);
            if (isSelected) return;

            const div = document.createElement('div');
            div.className = 'member-suggestion';
            div.innerHTML = `${this.escapeHtml(user.name)} (${user.role || 'Пользователь'})`;
            
            div.addEventListener('click', () => {
                this.addGroupMember(user);
                suggestions.style.display = 'none';
                document.getElementById('group-members-search').value = '';
            });

            suggestions.appendChild(div);
        });

        suggestions.style.display = 'block';
    }

    /**
     * Добавление участника в группу
     */
    addGroupMember(user) {
        const selectedMembers = document.getElementById('selected-members');
        
        const span = document.createElement('span');
        span.className = 'selected-member';
        span.dataset.userId = user.id;
        span.innerHTML = `
            ${this.escapeHtml(user.name)}
            <button type="button" class="remove-member">×</button>
        `;

        span.querySelector('.remove-member').addEventListener('click', () => {
            span.remove();
        });

        selectedMembers.appendChild(span);
    }

    /**
     * Показать интерфейс чата
     */
    showChatInterface() {
        document.getElementById('welcome-message').style.display = 'none';
        document.getElementById('messages-container').style.display = 'block';
        document.getElementById('message-input-area').style.display = 'block';
    }

    /**
     * Инициализация Pusher
     */
    initializePusher() {
        // Если Pusher уже настроен в проекте
        if (typeof Pusher !== 'undefined' && window.Echo) {
            this.pusher = window.Echo;
        }
    }

    /**
     * Подписка на канал
     */
    subscribeToChannel(channelName) {
        if (!this.pusher) return;

        // Отписываемся от предыдущего канала
        if (this.channel) {
            this.pusher.leave(this.channel.name);
        }

        // Подписываемся на новый канал
        this.channel = this.pusher.private(channelName);
        
        this.channel.listen('MessageSent', (e) => {
            if (e.message.user_id !== this.userId) {
                this.addMessageToChat(e.message);
            }
        });

        this.channel.listen('UserTyping', (e) => {
            if (e.user_id !== this.userId) {
                this.showTypingIndicator(e.user_name);
            }
        });
    }

    /**
     * Обработка печатания
     */
    handleTyping() {
        if (!this.currentChatId || !this.pusher) return;

        // Отправляем событие печатания
        if (this.typingTimeout) {
            clearTimeout(this.typingTimeout);
        }

        // Здесь должна быть отправка события печатания через сервер
        
        this.typingTimeout = setTimeout(() => {
            // Здесь должна быть отправка события окончания печатания
        }, 1000);
    }

    /**
     * Показать индикатор печатания
     */
    showTypingIndicator(userName) {
        const indicator = document.getElementById('typing-indicator');
        const userSpan = document.getElementById('typing-user');
        
        userSpan.textContent = userName;
        indicator.style.display = 'block';

        setTimeout(() => {
            indicator.style.display = 'none';
        }, 3000);
    }

    /**
     * Автообновление
     */
    setupAutoRefresh() {
        // Обновляем статусы каждые 30 секунд
        setInterval(() => {
            if (this.currentChatType === 'contact') {
                // Можно добавить обновление статуса онлайн
            }
        }, 30000);
    }

    /**
     * Обновление текущего чата
     */
    refreshCurrentChat() {
        if (!this.currentChatId || !this.currentChatType) return;

        this.loadMessages(this.currentChatId, this.currentChatType);
        
        // Обновляем списки
        this.loadContacts();
        this.loadGroups();
    }

    /**
     * Показать индикатор загрузки
     */
    showLoading(type) {
        document.getElementById(`${type}-loading`).style.display = 'block';
    }

    /**
     * Скрыть индикатор загрузки
     */
    hideLoading(type) {
        document.getElementById(`${type}-loading`).style.display = 'none';
    }

    /**
     * Прокрутка к низу
     */
    scrollToBottom() {
        const messagesArea = document.getElementById('messages-area');
        setTimeout(() => {
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }, 100);
    }

    /**
     * Показать уведомление
     */
    showToast(message, type = 'info') {
        const toast = document.getElementById('chat-toast');
        const toastBody = document.getElementById('toast-body');
        
        toastBody.textContent = message;
        
        // Меняем цвет в зависимости от типа
        toast.className = `toast ${type === 'error' ? 'bg-danger text-white' : 
                                 type === 'success' ? 'bg-success text-white' : 
                                 type === 'warning' ? 'bg-warning' : 'bg-info text-white'}`;

        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    }

    /**
     * Debounce функция
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Форматирование времени
     */
    formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        
        // Если сегодня - показываем время
        if (diff < 24 * 60 * 60 * 1000) {
            return date.toLocaleTimeString('ru-RU', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        }
        
        // Если вчера - показываем "Вчера"
        if (diff < 48 * 60 * 60 * 1000) {
            return 'Вчера';
        }
        
        // Иначе показываем дату
        return date.toLocaleDateString('ru-RU');
    }

    /**
     * Форматирование размера файла
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * Получение иконки для файла
     */
    getFileIcon(fileName) {
        const extension = fileName.split('.').pop().toLowerCase();
        
        const icons = {
            // Изображения
            'jpg': 'fas fa-image',
            'jpeg': 'fas fa-image',
            'png': 'fas fa-image',
            'gif': 'fas fa-image',
            'bmp': 'fas fa-image',
            'svg': 'fas fa-image',
            
            // Документы
            'pdf': 'fas fa-file-pdf',
            'doc': 'fas fa-file-word',
            'docx': 'fas fa-file-word',
            'xls': 'fas fa-file-excel',
            'xlsx': 'fas fa-file-excel',
            'ppt': 'fas fa-file-powerpoint',
            'pptx': 'fas fa-file-powerpoint',
            'txt': 'fas fa-file-alt',
            
            // Архивы
            'zip': 'fas fa-file-archive',
            'rar': 'fas fa-file-archive',
            '7z': 'fas fa-file-archive',
            
            // Аудио
            'mp3': 'fas fa-file-audio',
            'wav': 'fas fa-file-audio',
            'ogg': 'fas fa-file-audio',
            
            // Видео
            'mp4': 'fas fa-file-video',
            'avi': 'fas fa-file-video',
            'mkv': 'fas fa-file-video',
            'mov': 'fas fa-file-video'
        };

        return icons[extension] || 'fas fa-file';
    }

    /**
     * Экранирование HTML
     */
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
}

// Инициализация чата при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    window.standaloneChat = new StandaloneChat();
});
