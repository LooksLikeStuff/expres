@extends('layouts.app')

@vite(['resources/js/app.js', 'resources/js/chats/index.js', 'resources/sass/chats/index.scss'])

@section('stylesheets')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
@endsection

@section('scripts')
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
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
                <div class="sidebar">
                    <!-- Header -->
                    <div class="sidebar-header">
                        <div class="user-info">
                            <div class="user-avatar">
                                <img src="{{$user->getProfileAvatar()}}" alt="User Avatar">
                            </div>
                            <div class="user-details">
                                <h6 class="user-name">{{$user->name}}</h6>
                                <span class="user-status online">онлайн</span>
                            </div>
                        </div>
                        <div class="header-actions">
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
                                    <div class="online-indicator {{ in_array($chat->id, $onlineUsers ?? []) ? '' : 'offline' }}"></div>
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
                                <button class="btn-icon" title="Поиск">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button class="btn-icon" title="Меню">
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
                            <div class="message-input-wrapper">
                                <button class="btn-icon attach-btn" title="Прикрепить файл">
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
                        <div class="message-text"></div>
                    </div>
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


