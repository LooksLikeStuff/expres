import 'select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css';
import debounce from 'lodash/debounce';

export default class ChatInterface {
    static UserRole = Object.freeze({
        ADMIN: 'admin',
        COORDINATOR: 'coordinator',
        PARTNER: 'partner',
        ARCHITECT: 'architect',
        DESIGNER: 'designer',
        VISUALIZER: 'visualizer',
        CLIENT: 'user',
    });

    // Маппинг ролей на лейблы
    static UserRoleLabel = {
        [ChatInterface.UserRole.ADMIN]: 'Администратор',
        [ChatInterface.UserRole.COORDINATOR]: 'Координатор',
        [ChatInterface.UserRole.PARTNER]: 'Партнёр',
        [ChatInterface.UserRole.ARCHITECT]: 'Архитектор',
        [ChatInterface.UserRole.DESIGNER]: 'Дизайнер',
        [ChatInterface.UserRole.VISUALIZER]: 'Визуализатор',
        [ChatInterface.UserRole.CLIENT]: 'Клиент',
    };

    constructor(chatClient) {
        this.chatClient = chatClient;

        this.chatList = $('#chatList');
        this.membersList = $('#membersList');
        this.newChatModalElem = $('#newChatModal');

        this.showSidebarButton = $('.mobile-menu-btn');

        this.createChatBtn = $('#createChatBtn');
        this.createChatForm = $('#newChatForm');

        this.messagesContainer = $('.messages-container');
        this.messagesList = $('#messagesList');
        this.messageInput = $('#messageInput');
        this.messageSending = false;

        //Файлы вложения
        this.attachPreviewContainer = document.getElementById('attach__container')
        this.attachInput = $('#fileInput');
        this.attachInputButton = $('#attachBtn');

        this.selectedFiles = [];

        this.MAX_FILES = 10;
        this.MAX_IMAGES = 5;

        this.currentUserId = $('#user_id').val();

        this.currentPage = 1;
        this.isLoadingMessages = false;
        this.hasMoreMessages = true;

        this.searchMessages = [];
        this.currentSearchIndex = -1;

        this.loadedPages = new Set();
        this.loadedMessageIds = new Set();
        this.maxPages = null;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }

    init() {
        this.bindEvents();
        this.initCreateChatForm();
        this.initChatInfoModal();
        this.initGlobalBroadcast();
    }

    bindEvents() {
        this.initFileAttachment();
        this.initDownloadHandler();
        this.initAttachmentsActions();
        this.initPageFocusHandler();
        this.initSearchMessages();

        this.createChatBtn.click(() => this.dispatchCreateChat());

        this.chatList.on('click', '.chat-item', (e) => {
            const chatItem = e.currentTarget;
            const chatId = chatItem.getAttribute('data-chat-id');
            this.activateChat(chatId);
        });

        this.messagesContainer.on('scroll', () => {
            const scrollTop = this.messagesContainer.scrollTop();
            const scrollBottom = this.messagesContainer[0].scrollHeight - this.messagesContainer.scrollTop() - this.messagesContainer.outerHeight();

            // старые сообщения (скролл вверх)
            if (scrollTop <= 100) {
                this.loadMoreMessages(this.chatClient.currentChatId);
            }

            // новые сообщения (скролл вниз)
            if (scrollBottom <= 100) {
                this.loadMoreMessages(this.chatClient.currentChatId);
            }

            // проверяем, есть ли недостающие сообщения поиска
            this.checkForMissingMessages();
        });


        $('#sendBtn').click(async () => {
            // Если уже идёт отправка — игнорируем клик
            if (this.messageSending) return;

            const content = this.getMessageContent();

            if (content || this.selectedFiles.length > 0) {
                this.messageSending = true;
                $('#sendBtn').prop('disabled', true);

                try {
                    await this.chatClient.sendMessage(
                        this.chatClient.currentChatId,
                        content,
                        this.selectedFiles
                    );

                    this.messageInput.val('');
                    this.clearAllFiles();
                } catch (err) {
                    console.error('Ошибка отправки:', err);
                } finally {
                    this.messageSending = false;
                    $('#sendBtn').prop('disabled', false);
                    this.messageInput.focus();
                }
            }
        });;

        this.messageInput.on('input', () => this.chatClient.sendTypingEvent());

        this.showSidebarButton.click(() => this.showSidebar());

        this.messageInput.on('keypress', async (e) => {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();

                // если уже идёт отправка — игнорируем Enter
                if (this.messageSending) return;

                const content = this.messageInput.val().trim();

                if (content || this.selectedFiles.length > 0) {
                    this.messageSending = true;
                    $('#sendBtn').prop('disabled', true);

                    try {
                        await this.chatClient.sendMessage(
                            this.chatClient.currentChatId,
                            content,
                            this.selectedFiles
                        );

                        this.messageInput.val('');
                        this.clearAllFiles();
                    } catch (err) {
                        console.error('Ошибка отправки:', err);
                    } finally {
                        this.messageSending = false;
                        $('#sendBtn').prop('disabled', false);
                        this.messageInput.focus();
                    }
                }
            }
        });


    }

    initSearchMessages() {
        $('.search-arrow-up').on('click', () => this.goToPrevSearchResult());
        $('.search-arrow-down').on('click', () => this.goToNextSearchResult());

        $('#searchBtn').click(() => this.showSearchElems());
        $('#searchClose').click(() => this.hideSearchElems());

        $('#searchInput').on('input', debounce(async (e) => {
            const value = $(e.currentTarget).val().trim();
            this.currentSearchQuery = value;
            this.isSearchingMessages = !!value;

            if (!value) {
                this.isSearchingMessages = false;
                return;
            }

            await this.chatClient.searchMessages(value, (data) => {
                this.renderSearchResults(data.messages);
            });
        }, 300));
    }

    renderSearchResults(messages) {
        this.searchMessages = messages;
        this.currentSearchIndex = messages.length ? 0 : -1;

        this.updateSearchCounter();
        if (messages.length > 0) {
            this.scrollToSearchResult(this.currentSearchIndex);
        }
    }

    updateSearchCounter() {
        if (this.searchMessages.length === 0) {
            $('.chat__amount').text('0 из 0');
        } else {
            $('.chat__amount').text(`${this.currentSearchIndex + 1} из ${this.searchMessages.length}`);
        }
    }

    scrollToSearchResult(index) {
        const messageId = this.searchMessages[index].id;
        const $messageElem = $(`.message[data-message-id="${messageId}"]`);

        $('.message').removeClass('highlight-search highlight-search-anim');

        if ($messageElem.length) {
            $messageElem[0].scrollIntoView({ block: 'center' });
            $messageElem.addClass('highlight-search highlight-search-anim');
            setTimeout(() => $messageElem.removeClass('highlight-search-anim'), 1500);
        } else {
            // если сообщения нет в DOM, подгружаем страницу
            this.loadPageForMessage(messageId, () => {
                this.scrollToSearchResult(index);
            });
        }
    }

    goToNextSearchResult() {
        if (this.searchMessages.length === 0) return;
        this.currentSearchIndex = (this.currentSearchIndex + 1) % this.searchMessages.length;
        this.scrollToSearchResult(this.currentSearchIndex);
        this.updateSearchCounter();
    }

    goToPrevSearchResult() {
        if (this.searchMessages.length === 0) return;
        this.currentSearchIndex = (this.currentSearchIndex - 1 + this.searchMessages.length) % this.searchMessages.length;
        this.scrollToSearchResult(this.currentSearchIndex);
        this.updateSearchCounter();
    }


    initAttachmentsActions() {
        $(document).on('click', '.download-file-btn', (e) => {
            const $item = $(e.currentTarget);
            const fileUrl = $item.data('download-url'); // ссылка на файл
            const fileName = $item.data('file-name') || 'file'; // имя для сохранения

            // уведомление
            this.showNotification(`Скачивание файла "${fileName}"`, 'success');

            // создаём временный <a> элемент
            const link = document.createElement('a');
            link.href = fileUrl;
            link.setAttribute('target', '_blank');
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        // Обработчик Enter в поле названия чата
        $('#chatNameInput').on('keypress', function (e) {
            if (e.which === 13) {
                $('#editChatNameBtn').click();
            }
        });
    }

    initCreateChatForm() {
        const $chatModalElem = this.newChatModalElem;

        const $chatType = $('input[name="type"]');
        const $chatNameField = $('#chatNameInput').closest('.mb-3');
        const $participants = $('#chatParticipants');
        const $chatAvatarField = $('#chatAvatarField'); // блок с инпутом для аватарки
        const $chatAvatarInput = $('#newChatAvatar'); // сам input

        // Инициализация select2 с базовыми настройками
        $participants.select2({
            theme: 'bootstrap-5',
            placeholder: 'Выберите участников',
            dropdownParent: $chatModalElem,
            width: '100%',
            multiple: true // по умолчанию групповая логика
        });

        function updateForm() {
            const type = $('input[name="type"]:checked').val();

            if (type === 'private') {
                // Скрыть поле названия чата через класс d-none
                $chatNameField.addClass('d-none');

                // Переключить select2 в режим одного выбора
                $participants.select2('destroy').prop('multiple', false).select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Выберите участника',
                    dropdownParent: $chatModalElem,
                    width: '100%',
                    multiple: false
                });

                // Скрыть поле аватарки и очистить выбор
                $chatAvatarField.addClass('d-none');
                $chatAvatarInput.val('');

            } else {
                // Показать поле названия чата через удаление класса d-none
                $chatNameField.removeClass('d-none');

                // Переключить select2 в multiple
                $participants.select2('destroy').prop('multiple', true).select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Выберите участников',
                    dropdownParent: $chatModalElem,
                    width: '100%',
                    multiple: true
                });

                // Показать поле аватарки
                $chatAvatarField.removeClass('d-none');
            }
        }

        // При загрузке модалки и при смене типа чата
        $chatType.on('change', updateForm);

        // Вызываем один раз при инициализации
        updateForm();
    }

    initGlobalBroadcast() {
        this.chatClient.joinGlobalChannel((type, data) => {
            switch (type) {
                case 'ChatCreated':
                    this.createChat(data.chat_id);
                    break;
            }
        });
    }

    initChatInfoModal() {
        $('#addMembersSelect').select2({
            dropdownParent: $('#chatInfoModal'),
            theme: 'bootstrap-5',
            placeholder: 'Выберите пользователя',
            width: '100%',
        });

        // Удаление участника
        this.setupDangerAction('.kick-member-btn', ($container, $button) => {
            const $memberItem = $button.closest('.member-item');
            const userId = $memberItem.attr('data-member-id');

            this.chatClient.removeUser(userId)
                .done(() => {
                    $memberItem.remove();
                    const memberCount = $(`#chat-${chatId} .member-item`).length;
                    $('#memberCountBadge').text(memberCount);
                })
                .fail(() => alert('Ошибка при удалении участника'));
        });

        // Выйти из чата
        this.setupDangerAction('#leaveChatBtn', ($container) => {
            const chatId = this.chatClient.currentChatId;
            this.chatClient.leaveChat(chatId)
                .done((data) => {
                    this.leaveFromCurrentChat();
                })
                .fail(() => alert('Ошибка при выходе из чата'));
        });

        // Удалить чат
        this.setupDangerAction('#deleteChatBtn', ($container) => {

            const chatId = this.chatClient.currentChatId;
            this.chatClient.deleteChat(chatId)
                .done(() => {
                    this.leaveFromCurrentChat();
                })
                .fail(() => alert('Ошибка при удалении чата'));
        });


        $('#showAddMemberBtn').on('click', (e) => {
            const $button = $(e.currentTarget);
            const $container = $button.closest('.members-add');
            const $actions = $container.find('.member-select-actions');
            const $selectContainer = $container.find('.members-select');
            const $select = $container.find('.members-select select');

            console.log($button, $container, $actions, $select);
            // Скрываем основную кнопку
            $button.addClass('d-none');
            $selectContainer.removeClass('d-none');


            // Вставляем кнопки подтверждения
            const $actionButtons = $(`
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-success confirm-btn add-member-btn" title="Добавить">
                        <i class="fas fa-user-plus"></i>
                    </button>
                    <button class="btn btn-sm btn-primary confirm-btn cancel-add-btn" title="Отмена">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                </div>
            `);

            $actions.html($actionButtons);

            // Кнопка "Добавить"
            $actionButtons.on('click', '.add-member-btn', () => {
                const selectedId = $select.val(); // одно значение
                if (!selectedId) {
                    alert('Выберите пользователя');
                    return;
                }

                // AJAX через ChatClient
                this.chatClient.addUser(selectedId).done((data) => {
                    this.addMember(data.user);

                    $actions.empty();
                    $button.removeClass('d-none');
                    $selectContainer.addClass('d-none');

                }).fail(() => alert('Ошибка при добавлении пользователя'));
            });

            // Кнопка "Отмена"
            $actionButtons.on('click', '.cancel-add-btn', () => {
                $actions.empty();
                $button.removeClass('d-none');
                $selectContainer.addClass('d-none');
            });
        });
    }

    // Универсальный обработчик для любых опасных кнопок
    setupDangerAction(selector, onConfirmCallback) {
        // HTML блока подтверждения — один и тот же для всех
        const CONFIRM_HTML = `
                <div class="d-flex gap-2 confirm-block">
                    <button class="btn btn-sm btn-danger kick-confirm-btn confirm-remove-btn" title="Подтвердить">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-primary kick-confirm-btn cancel-remove-btn" title="Отмена">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
        `;

        $('body').on('click', selector, function() {
            const $button = $(this);
            const $container = $button.closest('.leave-chat, .delete-chat, .member-actions');

            // Закрываем все уже открытые confirm-блоки
            $('.confirm-block').each(function() {
                const $existingConfirm = $(this);
                const $parentContainer = $existingConfirm.parent();
                $existingConfirm.remove();
                $parentContainer.find('> button').removeClass('d-none'); // показываем скрытые кнопки
            });

            $button.addClass('d-none'); // скрываем основную кнопку

            // Вставляем общий confirm блок
            const $confirmDiv = $(CONFIRM_HTML);
            $container.append($confirmDiv);

            // Подтверждение
            $confirmDiv.on('click', '.confirm-remove-btn', function() {
                onConfirmCallback($container, $button);
                $confirmDiv.remove();
            });

            // Отмена
            $confirmDiv.on('click', '.cancel-remove-btn', function() {
                $confirmDiv.remove();
                $button.removeClass('d-none'); // возвращаем основную кнопку
            });
        });
    }

    initPageFocusHandler() {
       document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                console.log('Вкладка снова активна — обновляем чат');
                this.refreshChatsState();
                this.refreshChatData();
            }
        });
    }

    async refreshChatsState() {
        $.ajax({
            url: '/chats',
            method: 'get',
            success: (data) => this.updateChats(data.chats),
        })
    }

    updateChats(chats) {
        Array.from(chats).forEach((chat) => {
            const data = {
                chat_id: chat.id,
                unread_count: chat.unread_count,
                body: chat.last_message?.content,
                formatted_time: chat.last_message?.formatted_time
            };

            this.updateChatInfo(data)
        });
    }



    async refreshChatData() {
        try {
            if (this.chatClient.currentChatId) {
                await this.activateChat(this.chatClient.currentChatId);
            }

            // 4. Уведомление
            // this.showNotification('Чат обновлен', 'success');
        } catch (error) {
            console.error('Ошибка при обновлении чата:', error);
            //  this.showNotification('Ошибка при обновлении чата', 'error');
        }
    }

    setChatInfoModal(chat, chatName, avatarSrc) {
        $('#chatInfoModalLabel').text(chatName);
        $('#chatMembersCount').text(`${chat.users.length} участников`);
        $('#chatInfoAvatar').attr('src', avatarSrc);

        $('#memberCountBadge').text(chat.users.length);

        if (chat.type === 'private') {
            $('.chat-add-user').addClass('d-none');
            $('#leaveChatBtn').addClass('d-none');
        } else {
            $('.chat-add-user').removeClass('d-none');
            $('#leaveChatBtn').removeClass('d-none');
        }

        if (
            (this.chatClient.currentUserStatus === ChatInterface.UserRole.ADMIN || this.chatClient.currentUserStatus === ChatInterface.UserRole.COORDINATOR)
            && chat.type !== 'private'
        ) {
            $('#showAddMemberBtn').removeClass('d-none');
        } else {
            $('#showAddMemberBtn').addClass('d-none');
        }


        this.loadMembers(chat.users);
        this.loadAttachments(chat.attachments);
    }

    loadMembers(members) {
        this.membersList.empty();

        members.forEach(member => this.addMember(member));
    }

    addMember(member) {
        const elem = this.createMemberElement(member, this.chatClient.currentChatType);
        this.membersList.append(elem);
    }
    createMemberElement(member) {
        const onlineUserIds = this.chatClient.onlineUsers || new Set();
        const currentUserStatus = this.chatClient.currentUserStatus; // статус текущего пользователя

        const isMemberOnline = onlineUserIds.has(member.id);
        const statusIndicator = isMemberOnline ? 'online' : 'offline';
        const statusText = isMemberOnline ? 'в сети' : `был(а) недавно`;
        // Используем маппинг для красивого вывода роли
        const memberLabel = ChatInterface.UserRoleLabel[member.status] || member.status;

        const showKickButton =
            currentUserStatus !== ChatInterface.UserRole.CLIENT
            && currentUserStatus !== ChatInterface.UserRole.VISUALIZER
            && this.chatClient.currentChatType !== 'private' //В личных чатах не показываем кнопки удаленя пользователей


        const memberHtml = `
            <div class="member-item" data-member-id="${member.id}" data-role="${member.status}">
                <img src="${member.profile_avatar}" alt="${member.name}" class="member-avatar">
                <div class="member-info">
                    <div class="member-name">${member.name}</div>
                    <div class="member-status">
                        <span class="online-indicator ${statusIndicator}"></span>
                        <span class="member-status-text">${statusText}</span>
                    </div>
                </div>
                <div class="member-role ${member.status}">${memberLabel}</div>
                 <div class="member-kick-confirm"></div>
                <div class="member-actions">
                    ${showKickButton && member.status !== ChatInterface.UserRole.ADMIN ? `
                        <button class="btn btn-sm btn-outline-danger kick-member-btn">
                            <i class="fas fa-user-times"></i>
                        </button>
                    ` : ''}
                </div>
            </div>
        `;

        return memberHtml;
    }

    loadAttachments(attachments) {
        this.updateAttachmentsStats(attachments);
        this.renderAttachments(attachments);
    }

    // Функция обновления статистики вложений
    updateAttachmentsStats(attachments) {
        const totalFiles = attachments.length;

        // считаем размер в мегабайтах
        const totalSizeMB = attachments.reduce((sum, file) => {
            const sizeInBytes = Number(file.filesize); // преобразуем в число на всякий случай
            const sizeInMB = sizeInBytes / (1024 * 1024); // переводим байты в MB
            return sum + sizeInMB;
        }, 0);

        $('#totalAttachments').text(totalFiles);
        $('#totalSize').text(`${totalSizeMB.toFixed(1)} MB`);
    }

    // Функция отображения вложений
    renderAttachments(attachments) {
        const timeline = $('#attachmentsTimeline');
        timeline.empty();

        if (attachments.length === 0) {
            $('#emptyAttachments').removeClass('d-none');
            $('.attachments-tab-body').addClass('d-none');
            return;
        }

        $('#emptyAttachments').addClass('d-none');
        $('.attachments-tab-body').removeClass('d-none');

        // Группируем файлы по датам
        const groupedByDate = {};
        attachments.forEach(file => {
            const date = file.created_day;
            if (!groupedByDate[date]) {
                groupedByDate[date] = [];
            }
            groupedByDate[date].push(file);
        });

        // Сортируем даты в убывающем порядке
        const sortedDates = Object.keys(groupedByDate).sort((a, b) => new Date(b) - new Date(a));

        sortedDates.forEach(date => {
            const files = groupedByDate[date];
            const formattedDate = this.formatDate(date);

            const dateGroupHtml = `
                <div class="date-group">
                    <div class="date-header">${formattedDate}</div>
                    <div class="files-list">
                        ${files.map(file => this.createFileItem(file)).join('')}
                    </div>
                </div>
            `;

            timeline.append(dateGroupHtml);
        });
    }

    createFileItem(file) {
        const fileIconClass = this.getFileTypeClassByMime(file.mime_type);
        const fileTypeElem = $(`<div class="file-icon ${fileIconClass}"><i></i></div>`);
        this.getFileTypeClass(fileTypeElem, file.original_name);

        const formattedFilesize = this.formatFileSize(file.filesize);
        const formattedDate = this.formatDate(file.created_at);

        return `<div class="attachment-item" data-file-id="${file.id}">
               ${fileTypeElem.prop('outerHTML')}
                <div class="file-info">
                    <div class="file-name">${file.original_name}</div>
                    <div class="file-meta">${formattedFilesize} • ${formattedDate}</div>
                </div>
                <div class="file-actions">
                    <button class="btn btn-sm btn-outline-success download-file-btn" data-download-url="${file.download_url}" data-file-name="${file.original_name}">
                        <i class="fas fa-download"></i>
                    </button>

                </div>
            </div>
        `;
    }

    getFileTypeClassByMime(mimeType) {
        if (!mimeType) return 'document';

        if (mimeType.startsWith('image/')) return 'image';
        if (mimeType.startsWith('video/')) return 'video';
        if (mimeType.startsWith('audio/')) return 'audio';

        // Для документов проверяем несколько известных MIME
        const documentMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain'
        ];

        if (documentMimes.includes(mimeType)) return 'document';

        // По умолчанию — документ
        return 'document';
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        if (date.toDateString() === today.toDateString()) {
            return 'Сегодня';
        } else if (date.toDateString() === yesterday.toDateString()) {
            return 'Вчера';
        } else {
            return date.toLocaleDateString('ru-RU', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        }
    }

    // Функция получения класса типа файла
    getFileTypeClass(iconElem, filename) {
        let extension = this.getFileExtension(filename);

        const icon = iconElem.find('i');
        this.setFileIcon(icon, extension);
    }


    // setChatInfoModal(chat, chatName, avatarSrc) {
    //     $('#chatInfoModalLabel').text(chat.title);
    //
    //     const $list = $('#participantsList');
    //     $list.empty();
    //
    //     // Скрыть/показать кнопку добавления участников
    //     if (chat.type === 'private') {
    //         $('.chat-add-user').addClass('d-none');
    //     } else {
    //         $('.chat-add-user').removeClass('d-none');
    //     }
    //
    //     chat.users.forEach(user => {
    //         const isPrivate = chat.type === 'private';
    //
    //         const $item = $(`
    //     <div class="participant-item list-group-item" data-user-id="${user.id}">
    //         <div class="participant-info">
    //             <img src="${user.profile_avatar}" loading="lazy" class="participant-avatar" alt="avatar">
    //             <span class="participant-name">${user.name}</span>
    //             <span class="participant-role">${user.status}</span>
    //         </div>
    //
    //         ${!isPrivate ? '<span class="remove-participant">&times;</span>' : ''}
    //     </div>
    // `);
    //
    //         $list.append($item);
    //     });
    // }

    async dispatchCreateChat() {
        this.newChatModalElem.hide();

        const formElement = this.createChatForm[0]; // нативный элемент формы

        // Создаем FormData из формы — включая файлы
        let formData = new FormData(formElement);


        let data = await this.chatClient.createNewChat(formData);
        if (data.status === 'exists') {
            this.activateChat(data.chat_id);
        }
    }

    createChat(chatId) {
        $.ajax({
            url: `/chats/${chatId}`,
            method: 'post',
            success: (data) => {
                this.chatList.prepend(this.createChatItem(data))
                this.activateChat(chatId);
            },
        });
    }

    showSearchElems() {
        //Прячем информацию о чате и контейнер с текстареа
        $('.chat__header').addClass('d-none');
        $('.message-input-container').addClass('d-none');

        //Показываем элементы для поиска
        $('#chat__search-actions').removeClass('d-none');
        $('.chat__search').removeClass('d-none');
        $('.chat__search-panel').removeClass('d-none');
        $('.chat__search-actions').removeClass('d-none');
    }


    hideSearchElems() {
//Прячем информацию о чате и контейнер с текстареа
        $('.chat__header').removeClass('d-none');
        $('.message-input-container').removeClass('d-none');

        //Показываем элементы для поиска
        $('#chat__search-actions').addClass('d-none');
        $('.chat__search').addClass('d-none');
        $('.chat__search-panel').addClass('d-none');
        $('.chat__search-actions').addClass('d-none');
    }

    createChatItem(chat) {
        const onlineUserIds = this.chatClient.onlineUsers || new Set();
        const template = $('#chatItemTemplate').html();
        const $item = $(template);

        $item.attr('data-chat-id', chat.id);
        $item.attr('data-chat-type', chat.type)
        $item.attr(
            'data-user-ids',
            chat.users
                .filter(user => user.id !== this.chatClient.getUserId()) // исключаем себя
                .map(user => user.id)
                .join(',')
        );

        $item.find('img').attr('src', this.getAvatarUrl(chat.avatar, chat.title));
        $item.find('.chat-item-name').text(chat.title);
        $item.find('.chat-item-message').text(chat.last_message);
        $item.find('.chat-item-time').text(chat.last_message_time);

        const userIdsStr = $item.attr('data-user-ids');
        const userIds = userIdsStr.toString().split(',').map(id => parseInt(id));

        const isAnyUserOnline = userIds.some(id => onlineUserIds.has(id));
        const indicator = $item.find('.online-indicator');

        if (isAnyUserOnline) {
            indicator.removeClass('offline');
        } else {
            indicator.addClass('offline');
        }

        // Unread count
        if (chat?.unread_count > 0) {
            $item.find('.unread-count').text(chat.unread_count).show();
        }

        return $item;
    }

    loadMoreMessages(chatId) {
        if (this.isLoadingMessages || !this.hasMoreMessages) return;

        // проверяем maxPages
        if (this.maxPages && (this.currentPage >= this.maxPages)) {
            this.hasMoreMessages = false;
            return;
        }

        this.isLoadingMessages = true;

        $.ajax({
            url: `/chats/${chatId}/messages?page=${this.currentPage + 1}`,
            method: 'POST',
            success: (data) => {
                const messages = data.messages.data.reverse();

                if (messages.length === 0) {
                    this.hasMoreMessages = false;
                    return;
                }

                // сохраняем максимум страниц
                this.maxPages = data.messages.last_page;

                const scrollPositionBefore = this.messagesList[0].scrollHeight;
                const scrollTarget = this.messagesList[0].scrollTop;

                this.renderMessages(messages, {prepend: true});
                this.chatClient.observeReadReceipts(this.messagesList);

                const scrollPositionAfter = this.messagesList[0].scrollHeight;
                this.messagesList[0].scrollTop = scrollPositionAfter - scrollPositionBefore + scrollTarget;

                this.currentPage++;
            },
            complete: () => {
                this.isLoadingMessages = false;
            },
            error: () => {
                console.error('Failed to load more messages');
            }
        });
    }

    showSidebar() {
        $('.sidebar').addClass('active');
        $('.mobile__ponel').removeClass('d-none');
    }

    async activateChat(chatId) {
        this.hideSearchElems(); //Прячем поиск
        this.loadedPages = new Set();
        this.loadedMessageIds = new Set();

        $('.sidebar').removeClass('active');
        $('.mobile__ponel').addClass('d-none');
        $('.chat-item').removeClass('active');
        $(`.chat-item[data-chat-id="${chatId}"]`).addClass('active');

        // Hide welcome screen and show chat window
        $('#welcomeScreen').hide();
        $('#chatWindow').show();
        this.messagesList.html('');

        // Load chat info
        await this.loadChatInfo(chatId);

        // Load messages
        this.loadMessages(chatId);

        // Clear unread count
        $(`.chat-item[data-chat-id="${chatId}"] .unread-count`).hide();

        // Focus message input
        this.messageInput.focus();

        // Hide sidebar on mobile
        if ($(window).width() <= 768) {
            this.closeMobileMenu();
        }

        this.chatClient.joinChat(chatId, (type, data) => {
            switch (type) {
                case 'message':
                    this.appendMessage(data);
                    break;
                case 'read':
                    this.markAsRead(data);
                    break;
                case 'typing':
                    this.handleTypingIndicator(data.username);
                    break;
            }
        });

        this.updateOnlineStatus();

    }

    leaveFromCurrentChat() {
        let chatId = this.chatClient.currentChatId;
        if (!chatId) return;

        this.hideSearchElems(); //Прячем поиск
        this.loadedPages = new Set();
        this.loadedMessageIds = new Set();

        $('.sidebar').addClass('active');
        $('.mobile__ponel').removeClass('d-none');
        $('.chat-item').removeClass('active');
        $(`.chat-item[data-chat-id="${chatId}"]`).remove();
        $('#chatInfoModal').hide();

        // Hide welcome screen and show chat window
        $('#welcomeScreen').show();
        $('#chatWindow').hide();
        this.messagesList.html('');

        this.chatClient.leaveCurrentChat();
        this.updateOnlineStatus();
    }

    async loadChatInfo(chatId) {
        // Find chat in current list
        const chatItem = $(`.chat-item[data-chat-id="${chatId}"]`);
        const chatName = chatItem.find('.chat-item-name').text();
        const avatarSrc = chatItem.find('img').attr('src');

        $('#chatName').text(chatName);
        $('#chatAvatar').attr('src', avatarSrc);

        $.ajax({
            url: `/chats/${chatId}`,
            method: 'post',
            success: (chat) => {
                this.setChatInfoModal(chat, chatName, avatarSrc);
                this.chatClient.currentChatType = chat.type;
            },
        });
    }

    loadMessages(chatId) {
        this.currentPage = 1;
        this.hasMoreMessages = true;
        this.isLoadingMessages = false;

        $.ajax({
            url: `/chats/${chatId}/messages`,
            method: 'POST',
            success: (data) => {
                this.renderMessages(data.messages.data.reverse());
                this.scrollToBottom();
                this.chatClient.observeReadReceipts(this.messagesList);
            },
            error: function () {
                console.error('Failed to load messages');
            }
        });
    }

    renderMessages(messages, options = {}) {

        messages.forEach(message => {
            const messageElement = this.createMessageElement(message);
            if (options.prepend) {
                this.messagesList.prepend(messageElement);
            } else {
                this.messagesList.append(messageElement);
            }
        });
    }

    markAsRead(data) {
        $(`.chat-item[data-chat-id="${data.chat_id}"]`)?.find('.chat-item-badges')?.html();
        const $message = $(`.message[data-message-id="${data.message_id}"]`);
        $message.attr('data-read-status', '1');
        $message.find('.message-status').addClass('read');


    }

    appendMessage(message) {
        // Add message to chat
        const messageElement = this.createMessageElement(message);
        $('#messagesList').append(messageElement);
        this.scrollToBottom();

        // Update last message in sidebar
        this.updateLastMessage(this.chatClient.currentChatId, message.content);
        // //Проверяем сообщение отправил сам юзер или собеседник из чата
        // const isOpportunity = this.chatClient.getUserId() !== message.sender_id;
        //
        // const div = document.createElement('div');
        // div.classList.add('message');
        //
        // const isRead = Boolean(message.read_at); // read_status может быть true/false
        // div.dataset.readStatus = isRead ? '1' : '0';
        // div.dataset.messageId = message.id;
        //
        //
        // if (isOpportunity) {
        //     div.classList.add('message__opportunity');
        // }
        //
        // const messageSenderDiv = document.createElement('div');
        // messageSenderDiv.classList.add('message__sender');
        //
        // //Указываем имя отправителя сообщения
        // if (isOpportunity) {
        //     messageSenderDiv.textContent = message.sender_name;
        // } else {
        //     messageSenderDiv.textContent = 'Вы';
        // }
        //
        // const messageBodyDiv = document.createElement('div');
        // messageBodyDiv.classList.add('message__body');
        //
        // const textSpan = document.createElement('span');
        // textSpan.textContent = message.content;
        //
        // const timeSpan = document.createElement('span');
        // timeSpan.classList.add('message__time');
        // timeSpan.textContent = message.time;
        //
        // messageBodyDiv.appendChild(textSpan);
        // messageBodyDiv.appendChild(timeSpan);
        //
        // div.appendChild(messageSenderDiv);
        // div.appendChild(messageBodyDiv);
        //
        // this.messageContainer.appendChild(div);
        //
        // if (message.type === 'file') {
        //     const messageDiv = document.createElement('div');
        //     messageDiv.classList.add('message');
        //     messageDiv.classList.add('message__files');
        //
        //     if (isOpportunity) messageDiv.classList.add('message__opportunity');
        //
        //     message.attachments.forEach((attach) => {
        //         const messageFileItemDiv = document.createElement('div');
        //         messageFileItemDiv.classList.add('message__files-item')
        //
        //         const img = document.createElement('img')
        //         img.setAttribute('src', attach.full_path);
        //         img.setAttribute('alt', 'image')
        //
        //         messageFileItemDiv.appendChild(img);
        //
        //         messageDiv.appendChild(messageFileItemDiv)
        //     })
        //
        //     this.messageContainer.appendChild(messageDiv);
        // }
    }

    createMessageElement(message) {
        const template = $('#messageTemplate').html();
        const $message = $(template);
        $message.attr('data-message-id', message.id);

        const isOwn = (message.hasOwnProperty('is_own') && message?.is_own) || this.isMessageOwn(message.sender_id);
        const currentUserId = this.chatClient.userId;

        let isMessageRead = false;

        const readReceipts = message.read_receipts;

        if (Array.isArray(readReceipts)) {
            if (isOwn) {
                // Ищем хотя бы одну запись с read_at, но не от самого себя
                isMessageRead = readReceipts.some(r => r.user_id != currentUserId && r.read_at);
            } else {
                // Ищем read_receipt от самого себя
                const selfReceipt = readReceipts.find(r => r.user_id == currentUserId);
                isMessageRead = Boolean(selfReceipt?.read_at);
            }
        }

        $message.attr('data-read-status', isMessageRead ? '1' : '0');

        if (isOwn) {
            $message.addClass('own');
            $message.find('.message-status').removeClass('d-none').addClass(isMessageRead ? 'read' : '');
        }

        const avatar = message.sender_avatar ?? message.sender?.profile_avatar;

        $message.find('img').attr('loading', 'lazy').attr('src', this.getAvatarUrl(avatar, message.sender_name));
        $message.find('.message-author').text(message.sender_name);
        $message.find('.message-time').text(message.formatted_time);
        $message.find('.message-text').text(message.content);

        // Add attachments if present
        if (message.attachments && message.attachments.length > 0) {
            const attachmentsContainer = $message.find('.message-attachments');
            attachmentsContainer.show();

            const images = this.getImagesFromMessageAttachments(message.attachments);
            const files = this.getFilesFromMessageAttachments(message.attachments);

            // Add images
            if (images.length > 0) {
                const imagesContainer = this.createImagesContainer(images);
                attachmentsContainer.append(imagesContainer);
            }

            // Add files
            if (files.length > 0) {
                const filesContainer = this.createFilesContainer(files);
                attachmentsContainer.append(filesContainer);
            }
        }


        return $message;
    }

    getImagesFromMessageAttachments(attachments) {
        return attachments.filter(att => att.mime_type?.startsWith('image/'));
    }

    getFilesFromMessageAttachments(attachments) {
        return attachments.filter(att => !att.mime_type?.startsWith('image/'));
    }


    // Update last message in sidebar
    updateLastMessage(chatId, message) {
        const chatItem = $(`.chat-item[data-chat-id="${chatId}"]`);
        chatItem.find('.chat-item-message').text(message);
        chatItem.find('.chat-item-time').text('сейчас');

        // Move chat to top
        chatItem.prependTo('#chatList');

        this.chatClient.observeReadReceipts(this.messagesList);
    }

    attachFile() {
        this.attachInput.click();
    }

    displayAttachmentFiles() {
        const files = this.attachInput.files;

        this.attachPreviewContainer.classList.remove('d-none');
        this.attachPreviewContainer.innerHTML = ''; // очищаем предыдущие превью

        Array.from(files).forEach((file) => {
            const div = document.createElement('div');
            div.classList.add('attach__item');
            div.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
            this.attachPreviewContainer.appendChild(div);
        });
    }

    hideAttachments() {
        // this.attachPreviewContainer.classList.add('d-none');
        // this.attachPreviewContainer.innerHTML = '';
        // this.attachInput.value = null;
    }

    // Scroll to bottom of messages
    scrollToBottom() {
        this.messagesContainer.scrollTop(this.messagesContainer[0].scrollHeight);
    }

    closeMobileMenu() {
        $('#sidebar').removeClass('active');
        $('#mobileOverlay').removeClass('active');
        $('body').removeClass('menu-open');
    }

    getAvatarUrl(avatar, name) {
        if (avatar && (avatar.startsWith('http') || avatar.startsWith('/storage'))) {
            return avatar;
        }

        // Generate placeholder avatar
        const colors = ['1059b7', '2196f3', '4caf50', 'ff9800', 'f44336', '9c27b0'];
        const colorIndex = name.length % colors.length;
        const color = colors[colorIndex];
        const initials = name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2);

        return '/img/chats/private/placeholder.png';
        // return `https://via.placeholder.com/40x40/${color}/ffffff?text=${initials}`;
    }

    getMessageContent() {
        return this.messageInput.val().trim();
    }

    isMessageOwn(sender_id) {
        return parseInt(sender_id) === parseInt(this.currentUserId);
    }

    updateOnlineStatus() {
        const onlineUserIds = this.chatClient.onlineUsers || new Set();
        const currentChatId = parseInt(this.chatClient.currentChatId);

        $('.chat-item').each(function () {
            const $chatItem = $(this);
            const chatId = parseInt($chatItem.data('chat-id'));
            const userIds = $chatItem.data('user-ids').toString().split(',').map(Number);
            const isAnyUserOnline = userIds.some(id => onlineUserIds.has(id));

            // Индикатор в списке чатов
            const listIndicator = $chatItem.find('.online-indicator');
            if (isAnyUserOnline) {
                listIndicator.removeClass('offline');
            } else {
                listIndicator.addClass('offline');
            }

            // Индикатор и статус внутри активного чата
            if (chatId === currentChatId) {
                const onlineIndicator = $('#onlineIndicator');
                const chatStatus = $('#chatStatus');

                if (isAnyUserOnline) {
                    onlineIndicator.removeClass('offline');
                    chatStatus.text('Онлайн');
                } else {
                    onlineIndicator.addClass('offline');
                    chatStatus.text('был(а) недавно');
                }
            }
        });
    }

    handleTypingIndicator(username) {
        const $typingIndicator = $('#typingIndicator');

        if (this.isGroupChat()) {
            $typingIndicator.find('.user-name').text(username);
        }

        // Показываем индикатор
        $typingIndicator.removeClass('d-none');
        $('#chatStatus').addClass('d-none');

        // Скрываем через 2 секунды после последнего "typing"
        clearTimeout(this._typingTimeout);
        this._typingTimeout = setTimeout(() => {
            $typingIndicator.addClass('d-none');
            $('#chatStatus').removeClass('d-none');
        }, 2000);
    }

    isGroupChat() {
        return $(`.chat-item[data-chat-id="${this.chatClient.currentChatId}"]`).attr('data-chat-type') === 'group';
    }


    updateChatInfo(chat) {
        const $chatItem = $(`.chat-item[data-chat-id="${chat.chat_id}"]`)
        $chatItem.find('.chat-item-message').text(chat.body);
        $chatItem.find('.chat-item-time').text(chat.formatted_time);

        if (chat.unread_count > 0) {
            $chatItem.find('.chat-item-badges').html(`<span class="unread-count">${chat.unread_count}</span>`);
        } else {
            $chatItem.find('.chat-item-badges').html();
        }

        // Перемещаем в начало списка
        const $chatList = $chatItem.parent();
        $chatItem.prependTo($chatList);
    }


    // Initialize file attachment functionality
    initFileAttachment() {
        // Attach button click
        this.attachInputButton.on('click', function () {
            $('#fileInput').click();
        });

        // File input change
        this.attachInput.on('change', (e) => {
            const files = Array.from(e.currentTarget.files);

            this.addFiles(files);
            // Clear input to allow selecting same file again
            $(e.currentTarget).val('');
        });

        // Clear all files button
        $('#clearFilesBtn').on('click', () => this.clearAllFiles())

        // Remove individual file
        $(document).on('click', '.remove-file-btn', (e) => {
            const fileIndex = parseInt($(e.currentTarget).closest('.file-preview-item').data('file-index'));
            this.removeFile(fileIndex);
        });

    }

// Add files to selection
    addFiles(files) {
        files.forEach(file => {
            if (this.selectedFiles.length >= this.MAX_FILES) {
                this.showNotification('Максимальное количество файлов: ' + this.MAX_FILES, 'warning');
                return;
            }

            // Check if file already selected
            const existingFile = this.selectedFiles.find(f =>
                f.name === file.name &&
                f.size === file.size &&
                f.lastModified === file.lastModified
            );

            if (existingFile) {
                this.showNotification('Файл "' + file.name + '" уже выбран', 'warning');
                return;
            }

            this.selectedFiles.push(file);
        });

        this.updateFilePreview();
    }

// Remove file from selection
    removeFile(index) {
        this.selectedFiles.splice(index, 1);
        this.updateFilePreview();
    }

    // Clear all files
    clearAllFiles() {
        this.selectedFiles = [];
        this.updateFilePreview();
    }

    // Update file preview display
    updateFilePreview() {
        const container = $('#filePreviewContainer');
        const list = $('#filePreviewList');

        if (this.selectedFiles.length === 0) {
            container.hide();
            return;
        }

        container.show();
        list.empty();

        this.selectedFiles.forEach((file, index) => {
            const previewItem = this.createFilePreviewItem(file, index);
            list.append(previewItem);
        });
    }

    // Create file preview item
    createFilePreviewItem(file, index) {
        const isImage = file.type.startsWith('image/');
        const template = isImage ? $('#imagePreviewTemplate').html() : $('#filePreviewTemplate').html();
        const $item = $(template);

        $item.attr('data-file-index', index);
        $item.find('.file-preview-name').text(file.name);
        $item.find('.file-preview-size').text(this.formatFileSize(file.size));

        if (isImage) {
            // Create image preview
            const reader = new FileReader();
            reader.onload = function (e) {
                $item.find('img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        } else {
            // Set file type icon
            const extension = this.getFileExtension(file.name);
            const icon = $item.find('.file-preview-icon i');
            this.setFileIcon(icon, extension);
        }

        return $item;
    }

// Format file size
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

// Get file extension
    getFileExtension(filename) {
        return filename.split('.').pop().toLowerCase();
    }

// Set file icon based on extension
    setFileIcon(iconElement, extension) {
        const iconMap = {
            // Documents
            'pdf': 'bi-file-earmark-pdf',
            'doc': 'bi-file-earmark-word',
            'docx': 'bi-file-earmark-word',
            'xls': 'bi-file-earmark-excel',
            'xlsx': 'bi-file-earmark-excel',
            'ppt': 'bi-file-earmark-ppt',
            'pptx': 'bi-file-earmark-ppt',
            'txt': 'bi-file-earmark-text',

            // Archives
            'zip': 'bi-file-earmark-zip',
            'rar': 'bi-file-earmark-zip',
            '7z': 'bi-file-earmark-zip',
            'tar': 'bi-file-earmark-zip',
            'gz': 'bi-file-earmark-zip',

            // Media
            'mp3': 'bi-file-earmark-music',
            'wav': 'bi-file-earmark-music',
            'flac': 'bi-file-earmark-music',
            'mp4': 'bi-file-earmark-play',
            'avi': 'bi-file-earmark-play',
            'mkv': 'bi-file-earmark-play',
            'mov': 'bi-file-earmark-play',

            // Images
            'jpg': 'bi-file-earmark-image',
            'jpeg': 'bi-file-earmark-image',
            'png': 'bi-file-earmark-image',
            'gif': 'bi-file-earmark-image',
            'svg': 'bi-file-earmark-image',
            'webp': 'bi-file-earmark-image',

            // Code
            'js': 'bi-file-earmark-code',
            'html': 'bi-file-earmark-code',
            'css': 'bi-file-earmark-code',
            'php': 'bi-file-earmark-code',
            'py': 'bi-file-earmark-code',
            'java': 'bi-file-earmark-code',
            'cpp': 'bi-file-earmark-code',
            'c': 'bi-file-earmark-code'
        };

        const iconClass = iconMap[extension] || 'bi-file-earmark';
        iconElement.removeClass().addClass('bi ' + iconClass);
    }


    createImagesContainer(images) {
        const container = $('<div class="message-images"></div>');

        // Set grid class based on image count
        const count = Math.min(images.length, this.MAX_IMAGES);
        const gridClasses = {
            1: 'single-image',
            2: 'two-images',
            3: 'three-images',
            4: 'four-images',
            5: 'five-images'
        };

        container.addClass(gridClasses[count] || 'single-image');

        images.slice(0, this.MAX_IMAGES).forEach(image => {
            const imageElement = this.createMessageImageElement(image);
            container.append(imageElement);
        });

        return container;
    }

    createMessageImageElement(image) {
        const template = $('#messageImageTemplate').html();
        const $element = $(template);

        $element.find('img').attr('src', image.full_path);
        $element.find('.download-btn').attr('data-download-url', image.download_url);

        return $element;
    }

    createFilesContainer(files) {
        const container = $('<div class="message-files"></div>');

        files.forEach(file => {
            const fileElement = this.createMessageFileElement(file);
            container.append(fileElement);
        });

        return container;
    }

// Create message file element
    createMessageFileElement(file) {
        const template = $('#messageFileTemplate').html();
        const $element = $(template);

        $element.find('.file-name').text(file.original_name);
        $element.find('.file-size').text(this.formatFileSize(file.filesize));
        $element.find('.download-btn').attr('data-download-url', file.download_url);

        // Set file icon
        const extension = this.getFileExtension(file.original_name);
        const icon = $element.find('.file-icon');
        icon.attr('data-file-type', extension);
        this.setFileIcon(icon.find('i'), extension);

        return $element;
    }


    initDownloadHandler() {
        this.messagesList.on('click', '.download-btn', (event) => {
            const $btn = $(event.currentTarget);
            const url = $btn.data('download-url');

            if (!url) {
                console.warn('Ссылка для скачивания не найдена в data-download-url');
                return;
            }

            this.downloadFile(url);
        });
    }
    downloadFile(url) {
        const a = document.createElement('a');
        a.href = url;
        a.download = '';
        document.body.appendChild(a);
        a.click();
        a.remove();
    }

    showNotification(message, type = 'info') {
        // Создаем элемент уведомления
        const alertClass = {
            'success': 'alert-success',
            'danger': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';

        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show notification-toast" role="alert" style="
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            ">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);

        // Добавляем в body
        $('body').append(notification);

        // Автоматически удаляем через 5 секунд
        setTimeout(() => {
            notification.alert('close');
        }, 5000);
    }

    loadMessagePage(messageId) {
        this.chatClient.getPageOfMessages(messageId, (data) => {
            this.renderMessages(data.messages.data, {prepend: true});
            this.scrollToSearchResult(this.currentSearchIndex);
        });
    }

    async loadPageForMessage(messageId, callback) {
        if (this.loadedMessageIds.has(messageId)) {
            callback && callback(); // сообщение уже загружено
            return;
        }

        this.chatClient.getPageOfMessages(messageId, (data) => {
            const messages = data.messages.data.reverse();
            this.renderMessages(data.messages.data, {prepend: true});
            data.messages.data.forEach(msg => this.loadedMessageIds.add(msg.id));
            this.loadedPages.add(data.page); // сервер должен вернуть номер страницы
            callback && callback();
        });
    }

    checkForMissingMessages() {
        if (!this.isSearchingMessages || !this.searchMessages.length) return;

        const firstMessageId = $('.message:first').data('message-id');
        const lastMessageId = $('.message:last').data('message-id');

        for (const msg of this.searchMessages) {
            if (!this.loadedMessageIds.has(msg.id) &&
                (msg.id < firstMessageId || msg.id > lastMessageId)) {
                this.loadPageForMessage(msg.id);
            }
        }
    }


}
