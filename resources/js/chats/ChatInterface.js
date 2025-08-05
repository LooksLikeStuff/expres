import {read} from "@popperjs/core";

export default class ChatInterface {
    constructor(chatClient) {
        this.chatClient = chatClient;
        this.chatElements = document.querySelectorAll('.chats__list-option');
        this.messageContainer = document.getElementById('messages_container');
        this.messageInput = document.getElementById('message-input');
        this.messageForm = document.getElementById('message-form');
        this.messagesLoader = document.getElementById('messages-loading');

        //Файлы вложения
        this.attachPreviewContainer = document.getElementById('attach__container')
        this.attachInput = document.getElementById('file-input');
        this.attachInputButton = document.getElementById('attachment-button');

        this.attachInputButton.addEventListener('click', () => this.attachFile());
        this.attachInput.addEventListener('change', () => this.displayAttachmentFiles());

        this.userListContainer = document.getElementById('chats_userlist');
    }

    init() {
        this.chatElements.forEach((elem) => {
            elem.addEventListener('click', () => {
                const chatId = elem.getAttribute('data-chat-id');

                this.activateChat(chatId);
            });
        });

        document.getElementById('send_btn').addEventListener('click', async () => {
            const content = this.messageInput.value.trim();
            if (content) {
                await this.chatClient.sendMessage(this.chatClient.currentChatId, content, this.attachInput.files);
                this.messageInput.value = '';
                this.hideAttachments();
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
        document.querySelectorAll('.message').forEach(el => el.remove());
        document.querySelector('.empty-chat').classList.add('d-none');
        this.messagesLoader.classList.remove('d-none');

        //Прячем форму ввода сообщений
        this.messageForm.classList.add('d-none');

        this.highlightActiveChat(chatId);

        //Получаем сообщения чата
        let data = await this.getChatData(chatId);

        let messages = data.messages;
        let users = data.users;


        this.displayChatMessages(messages.data.reverse());
        this.chatClient.observeReadReceipts(this.messageContainer);
        this.displayChatUsers(users);


        this.chatClient.joinChat(chatId, (type, data) => {
            if (type === 'message') {
                this.appendMessage(data);
            } else if (type === 'read') {
                this.markAsRead(data);
            }
        });
    }

    markAsRead(data) {
        console.log(data);
    }

    async getChatData(chatId) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const response = await fetch(`/chats/${chatId}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
            },
        });

        if (response.ok) {
            return await response.json(); // ← тут получаем JSON-ответ
        } else {
            console.log('Ошибка: ' + response.status);
            return []; // безопасно вернуть пустой массив, чтобы не ломать фронт
        }
    }
    displayChatMessages(messages) {
        messages.forEach((message) => this.appendMessage(message, false));

        this.messagesLoader.classList.add('d-none');

        //Прячем форму ввода сообщений
        this.messageForm.classList.remove('d-none');
    }

    highlightActiveChat(chatId) {
        this.chatElements.forEach((el) => el.classList.remove('active'));
        document.querySelector(`[data-chat-id="${chatId}"]`)?.classList.add('active');
    }


    appendMessage(message) {
        //Проверяем сообщение отправил сам юзер или собеседник из чата
        const isOpportunity = this.chatClient.getUserId() !== message.sender_id;

        const div = document.createElement('div');
        div.classList.add('message');

        const isRead = Boolean(message.read_at); // read_status может быть true/false
        div.dataset.readStatus = isRead ? '1' : '0';
        div.dataset.messageId = message.id;


        if (isOpportunity) {
            div.classList.add('message__opportunity');
        }

        const messageSenderDiv = document.createElement('div');
        messageSenderDiv.classList.add('message__sender');

        //Указываем имя отправителя сообщения
        if (isOpportunity) {
            messageSenderDiv.textContent = message.sender_name;
        } else {
            messageSenderDiv.textContent = 'Вы';
        }

        const messageBodyDiv = document.createElement('div');
        messageBodyDiv.classList.add('message__body');

        const textSpan = document.createElement('span');
        textSpan.textContent = message.content;

        const timeSpan = document.createElement('span');
        timeSpan.classList.add('message__time');
        timeSpan.textContent = message.time;

        messageBodyDiv.appendChild(textSpan);
        messageBodyDiv.appendChild(timeSpan);

        div.appendChild(messageSenderDiv);
        div.appendChild(messageBodyDiv);

        this.messageContainer.appendChild(div);

        if (message.type === 'file') {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message');
            messageDiv.classList.add('message__files');

            if (isOpportunity) messageDiv.classList.add('message__opportunity');

            message.attachments.forEach((attach) => {
                const messageFileItemDiv = document.createElement('div');
                messageFileItemDiv.classList.add('message__files-item')

                const img = document.createElement('img')
                img.setAttribute('src', attach.full_path);
                img.setAttribute('alt', 'image')

                messageFileItemDiv.appendChild(img);

                messageDiv.appendChild(messageFileItemDiv)
            })

            this.messageContainer.appendChild(messageDiv);
        }
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
        this.attachPreviewContainer.classList.add('d-none');
        this.attachPreviewContainer.innerHTML = '';
        this.attachInput.value = null;
    }

    addUserItem(user) {
        const li = document.createElement('li');
        li.classList.add('chats__userlist-item');
        li.setAttribute('data-user-id', user.id); // можно будет потом легко удалить

        li.innerHTML = `
            ${user.name}
            <span class="chats__userlist-remove">
                <button class="btn btn-danger remove__btn" data-user-id="${user.id}">x</button>
            </span>
        `;

        this.userListContainer.appendChild(li);
    }

    displayChatUsers(users) {
        this.userListContainer.innerHTML = '';

        Array.from(users).forEach((user) => this.addUserItem(user));
    }
}
