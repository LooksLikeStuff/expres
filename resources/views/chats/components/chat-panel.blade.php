<!-- Область чата -->
<div class="col-md-9 col-lg-8 p-0 chat-area">
    <!-- Заголовок чата -->
    <div class="chat-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <h5 id="current-chat-name">Выберите чат</h5>
            <div id="chat-status" class="small text-muted ms-2"></div>

            <button
                id="show_user_list"
                class="btn-success p-3"
                data-bs-toggle="modal"
                data-bs-target="#userListModal"
            >Список пользователей</button>

            <div class="modal chats__modal fade" id="userListModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Пользователи чата</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <ul id="chats_userlist" class="chats__userlist">
                                <li class="chats__userlist-item">
                                    Данек
                                    <span class="chats__userlist-remove">
                                        <button class="remove__btn btn btn-danger" data-user-id="123">x</button>
                                    </span>
                                </li>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userAddModal">
                                Добавить
                            </button>

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="userAddModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Добавить пользователя</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <ul id="all_users_list" class="list-group">
                                @foreach($users as $user)
                                    <li class="list-group-item d-flex justify-content-between align-items-center" data-user-id="{{ $user->id }}">
                                        {{ $user->name }}
                                        <button class="btn btn-sm btn-success add-user-btn" data-user-id="{{ $user->id }}">Добавить</button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Сообщения -->
    <div class="messages-box messages position-relative">
        <div class="empty-chat align-self-center">
            <i class="bi bi-chat-dots"></i>
            <p>Выберите контакт, чтобы начать общение</p>
        </div>
        <div class="loading-overlay d-none" id="messages-loading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Загрузка сообщений...</span>
            </div>
        </div>
        <div id="messages_container" class="messages__container">

        </div>
{{--        <div class="message">--}}
{{--            <div class="message__sender">--}}
{{--                Данчик Скворцов--}}
{{--            </div>--}}
{{--            <div class="message__body">--}}
{{--                Личное сообщение--}}
{{--                <span class="message__time">16:15</span>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="message message__opportunity">--}}
{{--            <div class="message__sender">--}}
{{--                НЕ Данчик Скворцов--}}
{{--            </div>--}}
{{--            <div class="message__body">--}}
{{--                Собеседник написал сообщение--}}
{{--                <span class="message__time">16:15</span>--}}
{{--            </div>--}}
{{--        </div>--}}
    </div>

    <!-- Индикатор набора текста -->
    <div class="typing-indicator" id="typing-indicator">
        <i class="bi bi-pencil-fill me-2"></i> Собеседник печатает...
    </div>

    <!-- Форма отправки сообщений -->
    <div class="chat-input-area">
        <!-- Область предпросмотра файлов -->
        <div id="file-preview" class="file-preview d-none">
            <div id="file-preview-list">
                <!-- Элементы предпросмотра будут добавляться здесь -->
            </div>
        </div>
        <!-- Прогресс загрузки файлов -->
        <div class="file-upload-progress">
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated"
                     role="progressbar" aria-valuenow="0" aria-valuemin="0"
                     aria-valuemax="100" style="width: 0%"></div>
            </div>
        </div>

        <!-- Сообщение об ошибке -->
        <div id="error-message" class="error-message" style="display: none;"></div>

        <!-- Форма сообщения -->
        <div id="message-form" class="messages__form d-none">
            <div id="attach__container" class="attach__container d-none">
{{--                <div class="attach__item">--}}
{{--                    item--}}
{{--                </div>--}}
            </div>
            <div class="input-group">
                <input type="text" class="form-control" id="message-input" placeholder="Введите сообщение...">
                <button type="button" class="btn btn-outline-secondary" id="emoji-button" title="Эмодзи">
                    <i class="bi bi-emoji-smile"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary" id="attachment-button" title="Прикрепить файл">
                    <i class="bi bi-paperclip"></i>
                </button>
                <input type="file" id="file-input" class="d-none" accept="image/*" multiple max="4">

                <button type="submit" class="btn btn-primary" id="send_btn" title="Отправить">
                    <i class="bi bi-send"></i>
                </button>
                <div class="loading-spinner" id="loading-spinner">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Отправка...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Панель эмодзи -->
    @include('chats.components.emoji-picker')
</div>

<style>
.new-messages-notifier {
    position: absolute;
    bottom: 70px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #0084ff;
    color: white;
    padding: 8px 15px;
    border-radius: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    cursor: pointer;
    display: none;
    align-items: center;
    gap: 8px;
    z-index: 5;
    animation: bounce 1s infinite alternate;
}

.new-messages-notifier i {
    font-size: 1.2em;
}

@keyframes bounce {
    0% { transform: translateX(-50%) translateY(0); }
    100% { transform: translateX(-50%) translateY(-5px); }
}
</style>
