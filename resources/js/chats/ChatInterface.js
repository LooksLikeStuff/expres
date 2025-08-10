import 'select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css';

export default class ChatInterface {
    constructor(chatClient) {
        this.chatClient = chatClient;
        this.chatElements = $('.chat-item');
        this.showSidebarButton = $('.mobile-menu-btn');

        this.messagesContainer = $('.messages-container');
        this.messagesList = $('#messagesList');
        this.messageInput = $('#messageInput');

        //Файлы вложения
        this.attachPreviewContainer = document.getElementById('attach__container')
        this.attachInput = $('#fileInput');
        this.attachInputButton = $('#attachBtn');

        this.selectedFiles = [];

        this.MAX_FILES = 10;
        this.MAX_IMAGES = 5;

        this.userListContainer = document.getElementById('chats_userlist');
        this.currentUserId = $('#user_id').val();

        this.currentPage = 1;
        this.isLoadingMessages = false;
        this.hasMoreMessages = true;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }

    init() {
        this.bindEvents();
        this.initCreateChatForm();
    }

    bindEvents() {
        this.initFileAttachment();
        // this.attachInputButton.click(() => this.attachFile());
        // this.attachInput.change(() => this.displayAttachmentFiles());
        //
        this.chatElements.on('click', (e) => {
            const chatItem = e.currentTarget;
            const chatId = chatItem.getAttribute('data-chat-id');
            this.activateChat(chatId);
        });

        this.messagesContainer.on('scroll', () => {
            if (this.messagesContainer.scrollTop() <= 100) {
                this.loadMoreMessages(this.chatClient.currentChatId);
            }
        });
        $('#sendBtn').click(async () => {
            const content = this.getMessageContent();
            if (content) {
                await this.chatClient.sendMessage(this.chatClient.currentChatId, content, this.selectedFiles);
                this.messageInput.val('');
                this.clearAllFiles();
            }
        });

        this.messageInput.on('input', () => this.chatClient.sendTypingEvent());

        this.showSidebarButton.click(() => this.showSidebar());

        this.messageInput.on('keypress', async (e) => {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();

                const content = this.messageInput.val().trim();
                if (content) {
                    await this.chatClient.sendMessage(this.chatClient.currentChatId, content, this.selectedFiles);
                    this.messageInput.val('');
                    this.clearAllFiles();
                }
            }
        });

        //Обработчики кнопки удалить
        document.querySelector('body').addEventListener('click', async (event) => {
            // Проверяем, нажата ли кнопка удаления
            if (event.target.matches('.chats__userlist-remove button')) {
                event.preventDefault();

                const userId = event.target.getAttribute('data-user-id');

                await this.chatClient.removeUserFromCurrentChat(userId);
            }
        });
    }

    initCreateChatForm() {
        const $chatModalElem = $('#newChatModal');
        const $chatType = $('input[name="chatType"]');
        const $chatNameField = $('#chatNameInput').closest('.mb-3');
        const $participants = $('#chatParticipants');
        const $chatAvatarField = $('#chatAvatarField'); // блок с инпутом для аватарки
        const $chatAvatarInput = $('#chatAvatar'); // сам input

        // Инициализация select2 с базовыми настройками
        $participants.select2({
            theme: 'bootstrap-5',
            placeholder: 'Выберите участников',
            dropdownParent: $chatModalElem,
            width: '100%',
            multiple: true // по умолчанию групповая логика
        });

        function updateForm() {
            const type = $('input[name="chatType"]:checked').val();

            if (type === 'personal') {
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

    loadMoreMessages(chatId) {
        if (this.isLoadingMessages || !this.hasMoreMessages) return;

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

                const scrollPositionBefore = this.messagesList[0].scrollHeight;
                const scrollTarget = this.messagesList[0].scrollTop;

                this.renderMessages(messages, { prepend: true });
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
        $('.sidebar').removeClass('active');
        $('.mobile__ponel').addClass('d-none');
        $('.chat-item').removeClass('active');
        $(`.chat-item[data-chat-id="${chatId}"]`).addClass('active');

        // Hide welcome screen and show chat window
        $('#welcomeScreen').hide();
        $('#chatWindow').show();

        // Load chat info
        this.loadChatInfo(chatId);

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

        // document.querySelectorAll('.message').forEach(el => el.remove());
        // document.querySelector('.empty-chat').classList.add('d-none');
        // this.messagesLoader.classList.remove('d-none');
        //
        // //Прячем форму ввода сообщений
        // this.messageForm.classList.add('d-none');
        //
        // this.highlightActiveChat(chatId);
        //
        // //Получаем сообщения чата
        // let data = await this.getChatData(chatId);
        //
        // let messages = data.messages;
        // let users = data.users;
        //
        //
        // this.displayChatMessages(messages.data.reverse());
        // this.chatClient.observeReadReceipts(this.messageContainer);
        // this.displayChatUsers(users);

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

// Удаляем предыдущий обработчик и добавляем новый
//         this.messageInput.removeEventListener('_typing', this._typingListener);
//         this._typingListener = () => {
//             this.chatClient.sendTypingEvent();
//         };
//         this.messageInput.addEventListener('input', this._typingListener);
//         this.messageInput.addEventListener('_typing', this._typingListener); // для возможного удаления

    }

    loadChatInfo(chatId) {
        // Find chat in current list
        const chatItem = $(`.chat-item[data-chat-id="${chatId}"]`);
        const chatName = chatItem.find('.chat-item-name').text();
        const avatarSrc = chatItem.find('img').attr('src');

        $('#chatName').text(chatName);
        $('#chatAvatar').attr('src', avatarSrc);
    }

    loadMessages(chatId) {
        this.currentPage = 1;
        this.hasMoreMessages = true;
        this.isLoadingMessages = false;

        $.ajax({
            url: `/chats/${chatId}/messages`,
            method: 'POST',
            success: (data)=> {
                this.renderMessages(data.messages.data.reverse());
                this.scrollToBottom();
                this.chatClient.observeReadReceipts(this.messagesList);
            },
            error: function() {
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
                const selfReceipt =readReceipts.find(r => r.user_id == currentUserId);
                isMessageRead = Boolean(selfReceipt?.read_at);
            }
        }

        $message.attr('data-read-status', isMessageRead ? '1' : '0');

        if (isOwn) {
            $message.addClass('own');
            $message.find('.message-status').removeClass('d-none').addClass(isMessageRead ? 'read' : '');
        }
        console.log(message);

        const avatar = message.sender_avatar ?? message.sender?.profile_avatar;

        $message.find('img').attr('src', this.getAvatarUrl(avatar, message.sender_name));
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
        if (avatar && avatar.startsWith('http')) {
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
        const chatStatus = $('#chatStatus');
        const onlineIndicator = $('#onlineIndicator');

        $('.chat-item').each(function () {
            const $chatItem = $(this);
            const userIdsStr = $chatItem.data('user-ids');
            const userIds = userIdsStr.toString().split(',').map(id => parseInt(id));

            const isAnyUserOnline = userIds.some(id => onlineUserIds.has(id));
            const indicator = $chatItem.find('.online-indicator');

            if (isAnyUserOnline) {
                indicator.removeClass('offline');
                onlineIndicator.removeClass('offline');
                chatStatus.text('Онлайн');
            } else {
                indicator.addClass('offline');
                onlineIndicator.addClass('offline');
                chatStatus.text('был(а) недавно');
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

    updateChatInfo(data) {
        const $chatItem = $(`.chat-item[data-chat-id="${data.chat_id}"]`)

        $chatItem.find('.chat-item-message').text(data.body);
        $chatItem.find('.chat-item-badges').html(`<span class="unread-count">${data.unread_count}</span>`);
        $chatItem.find('.chat-item-time').text(data.time);
    }


    // Initialize file attachment functionality
    initFileAttachment() {
        // Attach button click
        this.attachInputButton.on('click', function() {
            $('#fileInput').click();
        });

        // File input change
        this.attachInput.on('change', (e)=> {
            const files = Array.from(e.currentTarget.files);
            console.log(files);
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

        // Drag and drop functionality
        const messageInputContainer = $('.message-input-container');

        messageInputContainer.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('drag-over');
        });

        messageInputContainer.on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
        });

        messageInputContainer.on('drop', (e)=>  {
            e.preventDefault();
            $(e.currentTarget).removeClass('drag-over');

            const files = Array.from(e.originalEvent.dataTransfer.files);
            this.addFiles(files);
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
            reader.onload = function(e) {
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
    showNotification(message, type = 'info') {
        const notification = $(`
            <div class="notification ${type}">
                <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `);

        $('body').append(notification);

        setTimeout(() => {
            notification.addClass('show');
        }, 100);

        setTimeout(() => {
            notification.removeClass('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
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


}
