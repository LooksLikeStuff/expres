import {read} from "@popperjs/core";
import $ from "jquery";

export default class ChatInterface {
    constructor(chatClient) {
        this.chatClient = chatClient;
        this.chatElements = $('.chat-item');
        this.messageInput = $('#messageInput');
        this.messageForm = document.getElementById('message-form');
        this.messagesLoader = document.getElementById('messages-loading');

        //Файлы вложения
        this.attachPreviewContainer = document.getElementById('attach__container')
        this.attachInput = document.getElementById('file-input');
        this.attachInputButton = document.getElementById('attachment-button');

        this.userListContainer = document.getElementById('chats_userlist');
        this.currentUserId = $('#user_id').val();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // this.attachInputButton.addEventListener('click', () => this.attachFile());
        // this.attachInput.addEventListener('change', () => this.displayAttachmentFiles());
        //
        this.chatElements.on('click', (e) => {
            const chatItem = e.currentTarget;
            const chatId = chatItem.getAttribute('data-chat-id');
            this.activateChat(chatId);
        });

        $('#sendBtn').click(async () => {
            const content = this.getMessageContent();
            if (content) {
                await this.chatClient.sendMessage(this.chatClient.currentChatId, content, this.attachInput?.files);
                this.messageInput.val('');
                this.hideAttachments();
            }
        });

        this.messageInput.on('input', () => this.chatClient.sendTypingEvent());

        this.messageInput.on('keypress', async (e) => {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();

                const content = this.messageInput.val().trim();
                if (content) {
                    await this.chatClient.sendMessage(this.chatClient.currentChatId, content, this.attachInput?.files);
                    this.messageInput.val('');
                    this.hideAttachments();
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

    async activateChat(chatId) {
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
        $.ajax({
            url: `/chats/${chatId}/messages`,
            method: 'POST',
            success: (data)=> {
                this.renderMessages(data.messages.data.reverse());
                this.scrollToBottom();
            },
            error: function() {
                console.error('Failed to load messages');
            }
        });
    }

    renderMessages(messages) {
        const messagesList = $('#messagesList');
        messagesList.empty();

        messages.forEach(message => {
            const messageElement = this.createMessageElement(message);
            messagesList.append(messageElement);
        });
    }

    markAsRead(data) {
        console.log(data);
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

        if ((message.hasOwnProperty('is_own') && message?.is_own) || this.isMessageOwn(message.sender_id)) {
            $message.addClass('own');
        }


        $message.find('img').attr('src', this.getAvatarUrl(message.user_avatar, message.user_name));
        $message.find('.message-author').text(message.sender_name);
        $message.find('.message-time').text(message.formatted_time);
        $message.find('.message-text').text(message.content);

        return $message;
    }

    // Update last message in sidebar
    updateLastMessage(chatId, message) {
        const chatItem = $(`.chat-item[data-chat-id="${chatId}"]`);
        chatItem.find('.chat-item-message').text(message);
        chatItem.find('.chat-item-time').text('сейчас');

        // Move chat to top
        chatItem.prependTo('#chatList');
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
        const container = $('#messagesContainer');
        container.scrollTop(container[0].scrollHeight);
    }

    closeMobileMenu() {
        $('#sidebar').removeClass('active');
        $('#mobileOverlay').removeClass('active');
        $('body').removeClass('menu-open');
    }

    getAvatarUrl(avatar, name) {
        return '/img/chats/private/placeholder.png';
        if (avatar && avatar.startsWith('http')) {
            return avatar;
        }

        // Generate placeholder avatar
        const colors = ['1059b7', '2196f3', '4caf50', 'ff9800', 'f44336', '9c27b0'];
        const colorIndex = name.length % colors.length;
        const color = colors[colorIndex];
        const initials = name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2);

        return `https://via.placeholder.com/40x40/${color}/ffffff?text=${initials}`;
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
}
