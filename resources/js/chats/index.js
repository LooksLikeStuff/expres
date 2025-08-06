import ChatClient from './ChatClient';
import ChatInterface from './ChatInterface';

document.addEventListener('DOMContentLoaded', function () {
    const userId = document.getElementById('user_id')?.value;

    if (!userId) {
        console.warn('User ID not found.');
        return;
    }
    const chatClient = new ChatClient(userId);
    chatClient.init();

    const chatInterface = new ChatInterface(chatClient);
    chatInterface.init();


    document.querySelectorAll('.chats__option').forEach((elem) => {
        elem.addEventListener('click', async function () {
            const chatId = this.getAttribute('data-chat-id');


            if (!openedChannels.has(chatId)) {
                echo.private(`chat.${chatId}`)
                    .listen('MessageSent', (e) => {
                        console.log('Новое сообщение:', e.message);
                    });

                openedChannels.add(chatId);
            }


            const content = 'ya tvou mamky ebal';
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const response = await fetch('/messages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({ chat_id: chatId, content })
            });

            if (response.ok) {
                console.log(response.body);
                document.getElementById('message_input').value = '';
            } else {
                console.error('Ошибка при отправке сообщения');
            }
        })

    })

});
