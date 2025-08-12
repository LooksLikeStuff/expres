@extends('chats.layouts.layout')

@vite(['resources/js/chats/index.js', 'resources/sass/chats/index.scss'])

@section('stylesheets')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
@endsection


@section('content')
    <input type="hidden" id="user_id" value="{{$user->id}}">
    <input type="hidden" id="user_name" value="{{$user->name}}">

    <div class="main__flex gap-0">
        <div class="main__ponel">
            @include('layouts/ponel')
        </div>
        <div class="main__module p-0">
            <div class="telegram-container">
                <!-- Sidebar with chats -->
                <div class="sidebar active">
                    <!-- Header -->
                    <div class="sidebar-header">
                        <div class="user-info">
                            <div class="user-avatar">
                                <img src="{{$user->profile_avatar}}" alt="User Avatar">
                            </div>
                            <div class="user-details">
                                <h6 class="user-name">{{$user->name}}</h6>
                                <span class="user-status online">онлайн</span>
                            </div>
                        </div>
                        <div class="header-actions">
                            <div class="header-actions">
                                <!-- Кнопка создать чат -->

                                <i class="bi bi-plus-lg" data-bs-toggle="modal" data-bs-target="#newChatModal"></i>

                                <!-- New Chat Modal -->
                                <div class="modal fade" id="newChatModal" tabindex="-1" aria-labelledby="newChatModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="newChatModalLabel">Создать новый чат</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form id="newChatForm" enctype="multipart/form-data">
                                                    <div class="mb-3">
                                                        <label for="chatType" class="form-label">Тип чата</label>
                                                        <div class="d-flex align-items-center justify-content-center gap-3">
                                                            <div class="form-check d-flex align-items-center">
                                                                <input class="form-check-input" type="radio" name="type" id="personalChat" value="private" checked>
                                                                <label class="form-check-label" for="personalChat">Личный</label>
                                                            </div>
                                                            <div class="form-check d-flex align-items-center">
                                                                <input class="form-check-input" type="radio" name="type" id="groupChat" value="group">
                                                                <label class="form-check-label" for="groupChat">Групповой</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3" id="chatNameField">
                                                        <label for="chatName" class="form-label">Название чата</label>
                                                        <input type="text" class="form-control" id="chatNameInput" name="title" placeholder="Введите название чата" autocomplete="1">
                                                    </div>
                                                    <div class="mb-3" id="participantsField">
                                                        <label for="chatParticipants" class="form-label">Выберите участника</label>
                                                        <select class="form-select" id="chatParticipants" name="participants[]" multiple aria-label="Выберите участников">
                                                            @foreach($users as $participant)
                                                                <option value="{{$participant->id}}">{{$participant->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <!-- Блок аватарки с классом d-none -->
                                                    <div class="mb-3 d-none" id="chatAvatarField">
                                                        <label for="newChatAvatar" class="form-label">Аватарка чата</label>
                                                        <input class="form-control" type="file" id="newChatAvatar" name="avatar" accept="image/*" />
                                                        <div class="form-text">Необязательное поле. Можно загрузить один файл изображения.</div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                                <button type="button" class="btn btn-primary" id="createChatBtn">Создать</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
{{--                            <button class="btn-icon" title="Настройки">--}}
{{--                                <i class="bi bi-gear"></i>--}}
{{--                            </button>--}}
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="search-container">
                        <div class="search-input-wrapper">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" class="search-input" placeholder="Поиск">
                        </div>
                    </div>

                    <!-- Chat filters -->
                    <div class="chat-filters">
                        <button class="filter-btn active" data-filter="all">Все</button>
                        <button class="filter-btn" data-filter="private">Личные</button>
                        <button class="filter-btn" data-filter="group">Групповые</button>
                    </div>

                    <!-- Chat list -->
                    <div class="chat-list" id="chatList">
                        @foreach ($user->chats as $chat)
                            @php
                                $participantIds = $chat->users->except(['id' => $user->id])->pluck('id')->join(',');
                            @endphp

                            <div class="chat-item"
                                 data-chat-id="{{ $chat->id }}"
                                 data-chat-type="{{ $chat->type->value }}"
                                 data-user-ids="{{ $participantIds }}">

                                <div class="chat-item-avatar">
                                    <img src="{{ $chat->getAvatar() }}" alt="Avatar">
                                    <div class="online-indicator"></div>
                                </div>
                                <div class="chat-item-content">
                                    <div class="chat-item-header">
                                        <h6 class="chat-item-name">{{ $chat->getTitleForUser($user->id) }}</h6>
                                        <span class="chat-item-time">{{ $chat->lastMessage?->formatted_time }}</span>
                                    </div>
                                    <div class="chat-item-footer">
                                        <p class="chat-item-message">{{ $chat->lastMessage?->content }}</p>
                                        <div class="chat-item-badges">
                                            @if ($chat->unread_count > 0)
                                                <span class="unread-count">{{ $chat->unread_count }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Main chat area -->
                <div class="main-chat">
                    <!-- Welcome screen -->
                    <div class="welcome-screen" id="welcomeScreen">
                        <div class="welcome-content">
                            <div class="telegram-logo">
                                <i class="bi bi-chat-dots"></i>
                            </div>
                            <h3>Выберите чат, чтобы начать общение</h3>
                        </div>
                    </div>


                    <!-- New Chat Modal -->
                    <div class="modal fade" id="chatInfoModal" tabindex="-1" aria-labelledby="chatInfoModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="chatInfoModalLabel">Название чата</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">

                                    <!-- Список участников -->
                                    <div id="participantsList" class="list-group mb-3">
                                        <!-- динамически сюда вставляем участников -->
                                    </div>

                                    <!-- Кнопка добавить участника -->
                                    <div class="chat-add-user">
                                        <select id="addParticipantSelect" class="form-select form-select-sm">
                                            @foreach($users as $participant)
                                                <option value="{{$participant->id}}">{{$participant->name}}</option>
                                            @endforeach
                                        </select>
                                        <button id="addParticipantBtn" class="btn btn-outline-primary btn-sm ms-2">+</button>
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat window -->
                    <div class="chat-window" id="chatWindow" style="display: none;">

                        <!-- Chat header -->
                        <div class="chat__header">
                            <div class="chat-info">
                                <div class="chat-avatar">
                                    <img id="chatAvatar" src="" alt="Chat Avatar">
                                    <div class="online-indicator" id="onlineIndicator"></div>
                                </div>
                                <div class="chat-details">
                                    <h6 class="chat-name" id="chatName"></h6>
                                    <span class="chat-status" id="chatStatus">был(а) недавно</span>
                                    <div class="typing-indicator d-none" id="typingIndicator">
                                        <span class="user-name"></span>
                                        <span>печатает</span>
                                        <div class="typing-dots">
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="chat-actions">
                                <button class="btn-icon mobile-menu-btn">
                                    <i class="bi bi-list"></i>
                                </button>
                                <button class="btn-icon">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button class="btn-icon"  data-bs-toggle="modal" data-bs-target="#chatInfoModal">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                            </div>
                        </div>


                        <!-- Messages area -->
                        <div class="messages-container" id="messagesContainer">
                            <div class="messages-list" id="messagesList">
                                <!-- Messages will be loaded here -->
                            </div>
                        </div>

                        <!-- Message input -->
                        <div class="message-input-container">
                            <div class="file-preview-container" id="filePreviewContainer" style="display: none;">
                                <div class="file-preview-header">
                                    <span class="file-preview-title">Выбранные файлы:</span>
                                    <button class="btn-icon clear-files-btn" id="clearFilesBtn" title="Очистить все файлы">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                                <div class="file-preview-list" id="filePreviewList">
                                    <!-- File previews will be added here -->
                                </div>
                            </div>

                            <div class="message-input-wrapper">
                                <input type="file" id="fileInput" multiple accept="*/*" style="display: none;">
                                <button class="btn-icon attach-btn" id="attachBtn" title="Прикрепить файл">
                                    <i class="bi bi-paperclip"></i>
                                </button>
                                <div class="input-wrapper">
                        <textarea
                            id="messageInput"
                            class="message-input"
                            placeholder="Написать сообщение..."
                            rows="1"
                        ></textarea>
                                </div>
                                <button class="btn-send" id="sendBtn" title="Отправить">
                                    <i class="bi bi-send"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat item template -->
            <template id="chatItemTemplate">
                <div class="chat-item" data-chat-id="" data-chat-type>
                    <div class="chat-item-avatar">
                        <img src="" alt="Avatar">
                        <div class="online-indicator"></div>
                    </div>
                    <div class="chat-item-content">
                        <div class="chat-item-header">
                            <h6 class="chat-item-name"></h6>
                            <span class="chat-item-time"></span>
                        </div>
                        <div class="chat-item-footer">
                            <p class="chat-item-message"></p>
                            <div class="chat-item-badges">
                                <span class="unread-count" style="display: none;"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Message template -->
            <template id="messageTemplate">
                <div class="message">
                    <div class="message-avatar">
                        <img src="" alt="Avatar">
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-author"></span>
                            <span class="message-time"></span>
                        </div>
                        <div class="message-attachments" style="display: none;">
                            <!-- Attachments will be added here -->
                        </div>

                        <div class="message-body">
                            <div class="message-text"></div>
                            <span class="message-status d-none">
                                <i class="bi bi-check"></i>
                                <i class="bi bi-check2-all"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </template>

            <!-- File preview template -->
            <template id="filePreviewTemplate">
                <div class="file-preview-item" data-file-index="">
                    <div class="file-preview-icon">
                        <i class="bi bi-file-earmark"></i>
                    </div>
                    <div class="file-preview-info">
                        <div class="file-preview-name"></div>
                        <div class="file-preview-size"></div>
                    </div>
                    <button class="btn-icon remove-file-btn" title="Удалить файл">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </template>

            <!-- Image preview template -->
            <template id="imagePreviewTemplate">
                <div class="file-preview-item image-preview" data-file-index="">
                    <div class="image-preview-thumbnail">
                        <img src="" alt="Preview">
                    </div>
                    <div class="file-preview-info">
                        <div class="file-preview-name"></div>
                        <div class="file-preview-size"></div>
                    </div>
                    <button class="btn-icon remove-file-btn" title="Удалить файл">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </template>

            <!-- Message attachment templates -->
            <template id="messageImageTemplate">
                <div class="message-image">
                    <img src="" alt="Image" loading="lazy">
                    <div class="image-overlay">
                        <button class="btn-icon download-btn" title="Скачать">
                            <i class="bi bi-download"></i>
                        </button>
                    </div>
                </div>
            </template>

            <template id="messageFileTemplate">
                <div class="message-file">
                    <div class="file-icon">
                        <i class="bi bi-file-earmark"></i>
                    </div>
                    <div class="file-info">
                        <div class="file-name"></div>
                        <div class="file-size"></div>
                    </div>
                    <button class="btn-icon download-btn" title="Скачать">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
            </template>
            <!-- Левая панель: список контактов -->
            {{--                    @include('chats.components.contacts-panel')--}}

            {{--                    <!-- Правая панель: область чата -->--}}
            {{--                    @include('chats.components.chat-panel')--}}
            {{--                <div class="row chat-container">--}}
            {{--                </div>--}}
{{--            <!-- Уведомление о новом сообщении -->--}}
{{--            <div class="chat-notification" id="chat-notification" style="display: none;">--}}
{{--                <div class="chat-notification-header mb-1 fw-bold">--}}
{{--                    <i class="bi bi-envelope-fill me-2"></i>Новое сообщение--}}
{{--                </div>--}}
{{--                <div class="chat-notification-body" id="notification-text"></div>--}}
{{--            </div>--}}

        </div>
    </div>
@endsection


