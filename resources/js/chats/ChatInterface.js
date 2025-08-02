export default class ChatInterface {
    constructor(chatClient) {
        this.chatClient = chatClient;
        this.chatElements = document.querySelectorAll('.chats__list-option');
        this.messageContainer = document.getElementById('messages_container');
        this.messageInput = document.getElementById('message-input');
        this.messageForm = document.getElementById('message-form');
        this.messagesLoader = document.getElementById('messages-loading');
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
                await this.chatClient.sendMessage(this.chatClient.currentChatId, content);
                this.messageInput.value = '';
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
        let messages = await this.getChatMessages(chatId);
        console.log(messages)
        this.displayChatMessages(messages.messages.data.reverse());


        this.chatClient.joinChat(chatId, (message) => {
            this.appendMessage(message);
        });
    }

    async getChatMessages(chatId) {
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
        messages.forEach((message) => this.appendMessage(message));

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
    }

}
