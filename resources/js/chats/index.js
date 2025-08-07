import ChatClient from './ChatClient';
import ChatInterface from './ChatInterface';
import $ from 'jquery';
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


$(document).ready(function() {
    // Global variables
    let currentChatId = null;
    let currentFilter = 'all';
    let typingTimeout = null;
    let isTyping = false;
    let onlineUsers = [1, 2]; // Simulate online users

    // Initialize app
    init();

    function init() {
        loadChats();
        bindEvents();
        simulateOnlineStatus();
        autoResizeTextarea();
    }

    // Event bindings
    function bindEvents() {
        // Filter buttons
        $('.filter-btn').on('click', function() {
            const filter = $(this).data('filter');
            setActiveFilter(filter);
            loadChats(filter);
        });

        // Chat item click
        $(document).on('click', '.chat-item', function() {
            const chatId = $(this).data('chat-id');
            selectChat(chatId);
        });

        // Send message
        $('#sendBtn').on('click', sendMessage);
        $('#messageInput').on('keypress', function(e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Typing indicator
        $('#messageInput').on('input', function() {
            handleTyping();
        });

        // Search functionality
        $('.search-input').on('input', function() {
            const query = $(this).val().toLowerCase();
            filterChats(query);
        });

        // Mobile menu functionality
        $('#mobileMenuBtn').on('click', function(e) {
            e.stopPropagation();
            toggleMobileMenu();
        });

        // Mobile overlay click
        $('#mobileOverlay').on('click', function() {
            closeMobileMenu();
        });

        // Close mobile menu on escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
            }
        });

        // Window resize handler
        $(window).on('resize', handleResize);

        // Prevent body scroll when mobile menu is open
        $(document).on('touchmove', function(e) {
            if ($('#sidebar').hasClass('active')) {
                e.preventDefault();
            }
        });
    }

    // Load chats from API
    function loadChats(filter = 'all') {
        $.ajax({
            url: '/api/chats',
            method: 'GET',
            data: { filter: filter },
            success: function(chats) {
                renderChats(chats);
            },
            error: function() {
                console.error('Failed to load chats');
            }
        });
    }

    // Render chats in sidebar
    function renderChats(chats) {
        const chatList = $('#chatList');
        chatList.empty();

        chats.forEach(chat => {
            const chatItem = createChatItem(chat);
            chatList.append(chatItem);
        });
    }

    // Create chat item element
    function createChatItem(chat) {
        const template = $('#chatItemTemplate').html();
        const $item = $(template);

        $item.attr('data-chat-id', chat.id);
        $item.find('img').attr('src', getAvatarUrl(chat.avatar, chat.name));
        $item.find('.chat-item-name').text(chat.name);
        $item.find('.chat-item-message').text(chat.last_message);
        $item.find('.chat-item-time').text(chat.last_message_time);

        // Online indicator
        const onlineIndicator = $item.find('.online-indicator');
        if (onlineUsers.includes(chat.id)) {
            onlineIndicator.removeClass('offline');
        } else {
            onlineIndicator.addClass('offline');
        }

        // Unread count
        if (chat.unread_count > 0) {
            $item.find('.unread-count').text(chat.unread_count).show();
        }

        return $item;
    }

    // Select chat and load messages
    function selectChat(chatId) {
        currentChatId = chatId;

        // Update active chat
        $('.chat-item').removeClass('active');
        $(`.chat-item[data-chat-id="${chatId}"]`).addClass('active');

        // Hide welcome screen and show chat window
        $('#welcomeScreen').hide();
        $('#chatWindow').show();

        // Load chat info
        loadChatInfo(chatId);

        // Load messages
        loadMessages(chatId);

        // Clear unread count
        $(`.chat-item[data-chat-id="${chatId}"] .unread-count`).hide();

        // Focus message input
        $('#messageInput').focus();

        // Hide sidebar on mobile
        if ($(window).width() <= 768) {
            closeMobileMenu();
        }
    }

    // Mobile menu functions
    function toggleMobileMenu() {
        const sidebar = $('#sidebar');
        const overlay = $('#mobileOverlay');

        if (sidebar.hasClass('active')) {
            closeMobileMenu();
        } else {
            openMobileMenu();
        }
    }

    function openMobileMenu() {
        $('#sidebar').addClass('active');
        $('#mobileOverlay').addClass('active');
        $('body').addClass('menu-open');
    }

    function closeMobileMenu() {
        $('#sidebar').removeClass('active');
        $('#mobileOverlay').removeClass('active');
        $('body').removeClass('menu-open');
    }

    // Load chat information
    function loadChatInfo(chatId) {
        // Find chat in current list
        const chatItem = $(`.chat-item[data-chat-id="${chatId}"]`);
        const chatName = chatItem.find('.chat-item-name').text();
        const avatarSrc = chatItem.find('img').attr('src');
        const isOnline = onlineUsers.includes(parseInt(chatId));

        $('#chatName').text(chatName);
        $('#chatAvatar').attr('src', avatarSrc);

        const onlineIndicator = $('#onlineIndicator');
        const chatStatus = $('#chatStatus');

        if (isOnline) {
            onlineIndicator.removeClass('offline');
            chatStatus.text('онлайн');
        } else {
            onlineIndicator.addClass('offline');
            chatStatus.text('был(а) недавно');
        }
    }

    // Load messages for chat
    function loadMessages(chatId) {
        $.ajax({
            url: `/api/messages/${chatId}`,
            method: 'GET',
            success: function(messages) {
                renderMessages(messages);
                scrollToBottom();
            },
            error: function() {
                console.error('Failed to load messages');
            }
        });
    }

    // Render messages
    function renderMessages(messages) {
        const messagesList = $('#messagesList');
        messagesList.empty();

        messages.forEach(message => {
            const messageElement = createMessageElement(message);
            messagesList.append(messageElement);
        });
    }

    // Create message element
    function createMessageElement(message) {
        const template = $('#messageTemplate').html();
        const $message = $(template);

        if (message.is_own) {
            $message.addClass('own');
        }

        $message.find('img').attr('src', getAvatarUrl(message.user_avatar, message.user_name));
        $message.find('.message-author').text(message.user_name);
        $message.find('.message-time').text(message.time);
        $message.find('.message-text').text(message.message);

        return $message;
    }

    // Send message
    function sendMessage() {
        const messageText = $('#messageInput').val().trim();

        if (!messageText || !currentChatId) {
            return;
        }

        // Clear input
        $('#messageInput').val('').trigger('input');

        // Send to API
        $.ajax({
            url: '/api/messages',
            method: 'POST',
            data: {
                chat_id: currentChatId,
                message: messageText,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(message) {
                // Add message to chat
                const messageElement = createMessageElement(message);
                $('#messagesList').append(messageElement);
                scrollToBottom();

                // Update last message in sidebar
                updateLastMessage(currentChatId, messageText);

                // Simulate response after delay
                setTimeout(() => {
                    simulateResponse();
                }, 1000 + Math.random() * 2000);
            },
            error: function() {
                console.error('Failed to send message');
                // Re-add message to input on error
                $('#messageInput').val(messageText);
            }
        });
    }

    // Handle typing indicator
    function handleTyping() {
        if (!isTyping) {
            isTyping = true;
            // In real app, send typing status to server
        }

        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => {
            isTyping = false;
            // In real app, send stop typing status to server
        }, 1000);
    }

    // Simulate typing indicator from other user
    function simulateTyping(duration = 2000) {
        $('#typingIndicator').show();
        $('#chatStatus').hide();

        setTimeout(() => {
            $('#typingIndicator').hide();
            $('#chatStatus').show();
        }, duration);
    }

    // Simulate response message
    function simulateResponse() {
        if (!currentChatId) return;

        simulateTyping();

        setTimeout(() => {
            const responses = [
                'Понятно!',
                'Хорошо, спасибо!',
                'Согласен',
                'Отлично!',
                'Да, конечно',
                'Интересно...',
                'Спасибо за информацию',
                'Буду иметь в виду'
            ];

            const randomResponse = responses[Math.floor(Math.random() * responses.length)];

            const responseMessage = {
                id: Date.now(),
                user_id: currentChatId,
                user_name: $('#chatName').text(),
                user_avatar: $('#chatAvatar').attr('src'),
                message: randomResponse,
                time: new Date().toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' }),
                is_own: false
            };

            const messageElement = createMessageElement(responseMessage);
            $('#messagesList').append(messageElement);
            scrollToBottom();

            // Update last message in sidebar
            updateLastMessage(currentChatId, randomResponse);

        }, 2000);
    }

    // Update last message in sidebar
    function updateLastMessage(chatId, message) {
        const chatItem = $(`.chat-item[data-chat-id="${chatId}"]`);
        chatItem.find('.chat-item-message').text(message);
        chatItem.find('.chat-item-time').text('сейчас');

        // Move chat to top
        chatItem.prependTo('#chatList');
    }

    // Scroll to bottom of messages
    function scrollToBottom() {
        const container = $('#messagesContainer');
        container.scrollTop(container[0].scrollHeight);
    }

    // Auto-resize textarea
    function autoResizeTextarea() {
        $('#messageInput').on('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
    }

    // Set active filter
    function setActiveFilter(filter) {
        currentFilter = filter;
        $('.filter-btn').removeClass('active');
        $(`.filter-btn[data-filter="${filter}"]`).addClass('active');


        const $chatItems = $('.chat-item');
        if (filter === 'all') {
            $chatItems.removeClass('d-none');
        } else {
            const $filteredChats = $(`.chat-item[data-chat-type="${filter}"]`);
            $filteredChats.removeClass('d-none');
            $chatItems.not($filteredChats).addClass('d-none');
        }


    }

    // Filter chats by search query
    function filterChats(query) {
        $('.chat-item').each(function() {
            const chatName = $(this).find('.chat-item-name').text().toLowerCase();
            const lastMessage = $(this).find('.chat-item-message').text().toLowerCase();

            if (currentFilter !== 'all') {
                if ($(this).attr('data-chat-type') !== currentFilter) return;
            }

            if ((chatName.includes(query) || lastMessage.includes(query))) {
                $(this).removeClass('d-none');
            } else {
                $(this).addClass('d-none');
            }
        });
    }

    // Simulate online status changes
    function simulateOnlineStatus() {
        setInterval(() => {
            // Randomly change online status
            const allChatIds = $('.chat-item').map(function() {
                return parseInt($(this).data('chat-id'));
            }).get();

            allChatIds.forEach(chatId => {
                const isCurrentlyOnline = onlineUsers.includes(chatId);
                const shouldBeOnline = Math.random() > 0.7; // 30% chance to be online

                if (isCurrentlyOnline !== shouldBeOnline) {
                    if (shouldBeOnline) {
                        onlineUsers.push(chatId);
                    } else {
                        onlineUsers = onlineUsers.filter(id => id !== chatId);
                    }

                    // Update UI
                    updateOnlineStatus(chatId, shouldBeOnline);
                }
            });
        }, 10000); // Check every 10 seconds
    }

    // Update online status in UI
    function updateOnlineStatus(chatId, isOnline) {
        const chatItem = $(`.chat-item[data-chat-id="${chatId}"]`);
        const onlineIndicator = chatItem.find('.online-indicator');

        if (isOnline) {
            onlineIndicator.removeClass('offline');
        } else {
            onlineIndicator.addClass('offline');
        }

        // Update current chat header if it's the active chat
        if (currentChatId === chatId) {
            const headerIndicator = $('#onlineIndicator');
            const chatStatus = $('#chatStatus');

            if (isOnline) {
                headerIndicator.removeClass('offline');
                chatStatus.text('онлайн');
            } else {
                headerIndicator.addClass('offline');
                chatStatus.text('был(а) недавно');
            }
        }
    }

    // Handle window resize
    function handleResize() {
        if ($(window).width() > 768) {
            closeMobileMenu();
        }
    }

    // Get avatar URL (fallback to placeholder)
    function getAvatarUrl(avatar, name) {
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

    // Utility function to format time
    function formatTime(date) {
        return date.toLocaleTimeString('ru-RU', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Add mobile menu button to chat header
    if ($(window).width() <= 768) {
        $('.chat-actions').prepend(`
            <button class="btn-icon mobile-menu-btn" title="Меню">
                <i class="bi bi-list"></i>
            </button>
        `);
    }
});


// Modal functionality
let currentChatData = null;
let selectedMembers = [];

// Initialize modal functionality
function initModalFunctionality() {
    // Chat header click to open modal
    $(document).on('click', '#chatHeader .chat-info', function() {
        openChatInfoModal();
    });

    // Modal close buttons
    $('#closeChatInfo, #closeAddMember').on('click', function() {
        closeModals();
    });

    // Modal overlay click to close
    $('.modal-overlay').on('click', function(e) {
        if (e.target === this) {
            closeModals();
        }
    });

    // Edit chat name
    $('#editNameBtn').on('click', function() {
        toggleChatNameEdit();
    });

    // Add member button
    $('#addMemberBtn').on('click', function() {
        openAddMemberModal();
    });

    // Done adding members
    $('#doneAddMember').on('click', function() {
        addSelectedMembers();
    });

    // Member search
    $('#memberSearchInput').on('input', function() {
        const query = $(this).val().toLowerCase();
        filterContacts(query);
    });

    // Contact selection
    $(document).on('click', '.contact-item', function() {
        toggleContactSelection($(this));
    });

    // Remove member
    $(document).on('click', '.remove-member-btn', function(e) {
        e.stopPropagation();
        const memberId = $(this).closest('.member-item').data('member-id');
        removeMember(memberId);
    });

    // Escape key to close modals
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModals();
        }
    });
}

// Open chat info modal
function openChatInfoModal() {
    if (!currentChatData) return;

    // Populate modal with current chat data
    $('#modalChatAvatar').attr('src', currentChatData.avatar);
    $('#chatNameInput').val(currentChatData.name);
    $('#chatDescription').text(currentChatData.type === 'group' ? 'Групповой чат' : 'Личный чат');

    // Load members
    loadChatMembers();

    // Show modal
    $('#chatInfoModal').addClass('active');
    $('body').addClass('modal-open');
}

// Close all modals
function closeModals() {
    $('.modal-overlay').removeClass('active');
    $('body').removeClass('modal-open');

    // Reset edit mode
    $('#chatNameInput').attr('readonly', true);
    selectedMembers = [];
    $('.contact-item').removeClass('selected');
    $('.contact-checkbox').removeClass('checked');
}

// Toggle chat name edit
function toggleChatNameEdit() {
    const input = $('#chatNameInput');
    const isReadonly = input.attr('readonly');

    if (isReadonly) {
        input.removeAttr('readonly').focus().select();
        $('#editNameBtn i').removeClass('bi-pencil').addClass('bi-check');
    } else {
        const newName = input.val().trim();
        if (newName && newName !== currentChatData.name) {
            updateChatName(newName);
        }
        input.attr('readonly', true);
        $('#editNameBtn i').removeClass('bi-check').addClass('bi-pencil');
    }
}

// Update chat name
function updateChatName(newName) {
    currentChatData.name = newName;
    $('#chatName').text(newName);

    // Update in chat list
    $(`.chat-item[data-chat-id="${currentChatData.id}"] .chat-item-name`).text(newName);

    // Show success message
    showNotification('Название чата обновлено', 'success');
}

// Open add member modal
function openAddMemberModal() {
    loadAvailableContacts();
    $('#addMemberModal').addClass('active');
}

// Load chat members
function loadChatMembers() {
    const members = [
        {
            id: 1,
            name: 'Администратор',
            avatar: 'https://via.placeholder.com/48',
            status: 'онлайн',
            role: 'admin'
        },
        {
            id: 2,
            name: 'Иван Петров',
            avatar: 'https://via.placeholder.com/48',
            status: 'был недавно',
            role: 'member'
        },
        {
            id: 3,
            name: 'Мария Сидорова',
            avatar: 'https://via.placeholder.com/48',
            status: 'онлайн',
            role: 'member'
        },
        {
            id: 4,
            name: 'Команда разработки',
            avatar: 'https://via.placeholder.com/48',
            status: 'была вчера',
            role: 'member'
        }
    ];

    const membersList = $('#membersList');
    membersList.empty();

    members.forEach(member => {
        const memberHtml = `
                <div class="member-item" data-member-id="${member.id}">
                    <img src="${member.avatar}" alt="${member.name}" class="member-avatar">
                    <div class="member-info">
                        <h6 class="member-name">${member.name}</h6>
                        <p class="member-status">${member.status}</p>
                    </div>
                    <div class="member-actions">
                        <span class="member-role">${member.role === 'admin' ? 'Админ' : 'Участник'}</span>
                        ${member.role !== 'admin' ? '<button class="remove-member-btn"><i class="bi bi-x"></i></button>' : ''}
                    </div>
                </div>
            `;
        membersList.append(memberHtml);
    });

    $('#membersCount').text(members.length);
}

// Load available contacts
function loadAvailableContacts() {
    const contacts = [
        {
            id: 5,
            name: 'Анна Козлова',
            avatar: 'https://via.placeholder.com/48',
            status: 'онлайн'
        },
        {
            id: 6,
            name: 'Дмитрий Волков',
            avatar: 'https://via.placeholder.com/48',
            status: 'был недавно'
        },
        {
            id: 7,
            name: 'Елена Морозова',
            avatar: 'https://via.placeholder.com/48',
            status: 'онлайн'
        },
        {
            id: 8,
            name: 'Сергей Новиков',
            avatar: 'https://via.placeholder.com/48',
            status: 'был вчера'
        }
    ];

    const contactsList = $('#contactsList');
    contactsList.empty();

    contacts.forEach(contact => {
        const contactHtml = `
                <div class="contact-item" data-contact-id="${contact.id}">
                    <img src="${contact.avatar}" alt="${contact.name}" class="contact-avatar">
                    <div class="contact-info">
                        <h6 class="contact-name">${contact.name}</h6>
                        <p class="contact-status">${contact.status}</p>
                    </div>
                    <div class="contact-checkbox"></div>
                </div>
            `;
        contactsList.append(contactHtml);
    });
}

// Toggle contact selection
function toggleContactSelection(contactItem) {
    const contactId = contactItem.data('contact-id');
    const checkbox = contactItem.find('.contact-checkbox');

    if (contactItem.hasClass('selected')) {
        contactItem.removeClass('selected');
        checkbox.removeClass('checked');
        selectedMembers = selectedMembers.filter(id => id !== contactId);
    } else {
        contactItem.addClass('selected');
        checkbox.addClass('checked');
        selectedMembers.push(contactId);
    }
}

// Add selected members
function addSelectedMembers() {
    if (selectedMembers.length === 0) {
        closeModals();
        return;
    }

    // Simulate adding members
    selectedMembers.forEach(memberId => {
        const contactItem = $(`.contact-item[data-contact-id="${memberId}"]`);
        const name = contactItem.find('.contact-name').text();
        const avatar = contactItem.find('.contact-avatar').attr('src');

        // Add to members list
        const memberHtml = `
                <div class="member-item" data-member-id="${memberId}">
                    <img src="${avatar}" alt="${name}" class="member-avatar">
                    <div class="member-info">
                        <h6 class="member-name">${name}</h6>
                        <p class="member-status">только что добавлен</p>
                    </div>
                    <div class="member-actions">
                        <span class="member-role">Участник</span>
                        <button class="remove-member-btn"><i class="bi bi-x"></i></button>
                    </div>
                </div>
            `;
        $('#membersList').append(memberHtml);
    });

    // Update members count
    const currentCount = parseInt($('#membersCount').text());
    $('#membersCount').text(currentCount + selectedMembers.length);

    showNotification(`Добавлено участников: ${selectedMembers.length}`, 'success');
    closeModals();
}

// Remove member
function removeMember(memberId) {
    if (confirm('Удалить участника из чата?')) {
        $(`.member-item[data-member-id="${memberId}"]`).fadeOut(300, function() {
            $(this).remove();

            // Update members count
            const currentCount = parseInt($('#membersCount').text());
            $('#membersCount').text(currentCount - 1);
        });

        showNotification('Участник удален из чата', 'info');
    }
}

// Filter contacts
function filterContacts(query) {
    $('.contact-item').each(function() {
        const name = $(this).find('.contact-name').text().toLowerCase();
        if (name.includes(query)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

// Show notification
function showNotification(message, type = 'info') {
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


// Swipe functionality for mobile
let touchStartX = 0;
let touchStartY = 0;
let touchEndX = 0;
let touchEndY = 0;
let isSwipeActive = false;
let swipeThreshold = 50;
let swipeContainer = null;

// Initialize swipe functionality
function initSwipeFunctionality() {
    if ($(window).width() <= 768) {
        setupMobileLayout();
    }

    // Touch events for swipe
    $(document).on('touchstart', handleTouchStart);
    $(document).on('touchmove', handleTouchMove);
    $(document).on('touchend', handleTouchEnd);

    // Close contacts panel
    $('#closeContacts').on('click', function() {
        closeContactsPanel();
    });

    // Window resize handler
    $(window).on('resize', function() {
        if ($(window).width() <= 768) {
            setupMobileLayout();
        } else {
            restoreDesktopLayout();
        }
    });
}

// Setup mobile layout
function setupMobileLayout() {
    if ($('#swipeContainer').length === 0) return;

    const telegramContainer = $('.telegram-container');
    const swipeContainer = $('#swipeContainer');
    const swipeContent = $('#swipeContent');

    // Move telegram container into swipe content
    if (swipeContent.children().length === 0) {
        telegramContainer.appendTo(swipeContent);
    }

    // Show swipe container
    swipeContainer.show();

    // Load contacts for panel
    loadContactsPanel();
}

// Restore desktop layout
function restoreDesktopLayout() {
    const telegramContainer = $('.telegram-container');
    const swipeContainer = $('#swipeContainer');
    const appContainer = $('#app');

    // Move telegram container back to app
    if (telegramContainer.parent().is('#swipeContent')) {
        telegramContainer.appendTo(appContainer);
    }

    // Hide swipe container
    swipeContainer.hide();
    closeContactsPanel();
}

// Handle touch start
function handleTouchStart(e) {
    if ($(window).width() > 768) return;

    const touch = e.originalEvent.touches[0];
    touchStartX = touch.clientX;
    touchStartY = touch.clientY;

    // Only allow swipe from left edge
    if (touchStartX < 50) {
        isSwipeActive = true;
        $('#swipeContainer').addClass('swiping');
    }
}

// Handle touch move
function handleTouchMove(e) {
    if (!isSwipeActive || $(window).width() > 768) return;

    const touch = e.originalEvent.touches[0];
    touchEndX = touch.clientX;
    touchEndY = touch.clientY;

    const deltaX = touchEndX - touchStartX;
    const deltaY = Math.abs(touchEndY - touchStartY);

    // Prevent vertical scrolling during horizontal swipe
    if (Math.abs(deltaX) > deltaY && deltaX > 0) {
        e.preventDefault();

        // Show swipe indicator
        showSwipeIndicator(deltaX);

        // Move content based on swipe distance
        const swipeContent = $('#swipeContent');
        const maxSwipe = Math.min(deltaX, $(window).width() * 0.8);
        swipeContent.css('transform', `translateX(${maxSwipe}px)`);
    }
}

// Handle touch end
function handleTouchEnd(e) {
    if (!isSwipeActive || $(window).width() > 768) return;

    const deltaX = touchEndX - touchStartX;
    const deltaY = Math.abs(touchEndY - touchStartY);

    $('#swipeContainer').removeClass('swiping');
    hideSwipeIndicator();

    // Check if swipe is valid (horizontal and sufficient distance)
    if (Math.abs(deltaX) > deltaY && deltaX > swipeThreshold) {
        openContactsPanel();
    } else {
        // Reset position
        $('#swipeContent').css('transform', '');
    }

    // Reset swipe state
    isSwipeActive = false;
    touchStartX = 0;
    touchStartY = 0;
    touchEndX = 0;
    touchEndY = 0;
}

// Open contacts panel
function openContactsPanel() {
    $('#swipeContainer').addClass('contacts-open');
    $('#contactsPanel').addClass('active');
    $('body').addClass('contacts-open');

    // Reset swipe content position
    $('#swipeContent').css('transform', '');
}

// Close contacts panel
function closeContactsPanel() {
    $('#swipeContainer').removeClass('contacts-open');
    $('#contactsPanel').removeClass('active');
    $('body').removeClass('contacts-open');
}

// Show swipe indicator
function showSwipeIndicator(deltaX) {
    let indicator = $('.swipe-indicator');

    if (indicator.length === 0) {
        indicator = $('<div class="swipe-indicator">Контакты →</div>');
        $('body').append(indicator);
    }

    const progress = Math.min(deltaX / swipeThreshold, 1);
    indicator.addClass('visible').css('opacity', progress);

    if (progress >= 1) {
        indicator.text('Отпустите для открытия');
    } else {
        indicator.text('Контакты →');
    }
}

// Hide swipe indicator
function hideSwipeIndicator() {
    $('.swipe-indicator').removeClass('visible');
}

// Load contacts for panel
function loadContactsPanel() {
    const contacts = [
        {
            id: 1,
            name: 'Администратор',
            avatar: 'https://via.placeholder.com/48',
            status: 'онлайн'
        },
        {
            id: 2,
            name: 'Иван Петров',
            avatar: 'https://via.placeholder.com/48',
            status: 'был недавно'
        },
        {
            id: 3,
            name: 'Мария Сидорова',
            avatar: 'https://via.placeholder.com/48',
            status: 'онлайн'
        },
        {
            id: 4,
            name: 'Команда разработки',
            avatar: 'https://via.placeholder.com/48',
            status: 'была вчера'
        },
        {
            id: 5,
            name: 'Анна Козлова',
            avatar: 'https://via.placeholder.com/48',
            status: 'онлайн'
        },
        {
            id: 6,
            name: 'Дмитрий Волков',
            avatar: 'https://via.placeholder.com/48',
            status: 'был недавно'
        }
    ];

    const contactsList = $('#contactsListPanel');
    contactsList.empty();

    contacts.forEach(contact => {
        const contactHtml = `
                <div class="contact-item" data-contact-id="${contact.id}">
                    <img src="${contact.avatar}" alt="${contact.name}" class="contact-avatar">
                    <div class="contact-info">
                        <h6 class="contact-name">${contact.name}</h6>
                        <p class="contact-status">${contact.status}</p>
                    </div>
                </div>
            `;
        contactsList.append(contactHtml);
    });

    // Handle contact click in panel
    $(document).on('click', '#contactsListPanel .contact-item', function() {
        const contactId = $(this).data('contact-id');
        const contactName = $(this).find('.contact-name').text();

        // Start new chat or open existing
        startChatWithContact(contactId, contactName);
        closeContactsPanel();
    });
}

// Start chat with contact
function startChatWithContact(contactId, contactName) {
    // Simulate starting a new chat
    showNotification(`Начат чат с ${contactName}`, 'success');

    // You can add logic here to actually create/open the chat
    // For now, we'll just show a notification
}

// Initialize all new functionality
$(document).ready(function() {
    initModalFunctionality();
    initSwipeFunctionality();
});


// Push Notifications System
let notificationPermission = 'default';
let notificationQueue = [];
let activeNotifications = [];
let notificationId = 0;

// Initialize push notifications
function initPushNotifications() {
    // Check if browser supports notifications
    if (!('Notification' in window)) {
        console.log('This browser does not support notifications');
        return;
    }

    // Check current permission status
    notificationPermission = Notification.permission;

    // If permission is default, show permission modal
    if (notificationPermission === 'default') {
        setTimeout(() => {
            showNotificationPermissionModal();
        }, 3000); // Show after 3 seconds
    }

    // Initialize test buttons
    $('#testInviteBtn').on('click', function() {
        showChatInvitationNotification({
            inviterName: 'Анна Козлова',
            inviterAvatar: 'https://via.placeholder.com/48',
            chatName: 'Команда дизайнеров',
            chatType: 'group'
        });
    });

    $('#testMessageBtn').on('click', function() {
        showNewMessageNotification({
            senderName: 'Иван Петров',
            senderAvatar: 'https://via.placeholder.com/48',
            chatName: 'Общий чат',
            messagePreview: 'Привет! Как дела с проектом?',
            timestamp: new Date()
        });
    });

    // Permission modal handlers
    $('#allowNotifications').on('click', function() {
        requestNotificationPermission();
    });

    $('#denyNotifications').on('click', function() {
        closeNotificationPermissionModal();
    });

    // Auto-generate demo notifications
    setTimeout(() => {
        if (notificationPermission === 'granted') {
            generateDemoNotifications();
        }
    }, 10000); // Start demo after 10 seconds
}

// Show notification permission modal
function showNotificationPermissionModal() {
    $('#notificationPermissionModal').addClass('active');
    $('body').addClass('modal-open');
}

// Close notification permission modal
function closeNotificationPermissionModal() {
    $('#notificationPermissionModal').removeClass('active');
    $('body').removeClass('modal-open');
}

// Request notification permission
function requestNotificationPermission() {
    Notification.requestPermission().then(function(permission) {
        notificationPermission = permission;
        closeNotificationPermissionModal();

        if (permission === 'granted') {
            showNotification('Уведомления включены!', 'success');
            // Process any queued notifications
            processNotificationQueue();
        } else {
            showNotification('Уведомления отключены', 'info');
        }
    });
}

// Show chat invitation notification
function showChatInvitationNotification(data) {
    const notification = createInAppNotification({
        type: 'chat-invitation',
        title: 'Приглашение в чат',
        icon: 'bi-person-plus',
        avatar: data.inviterAvatar,
        content: `<strong class="sender-name">${data.inviterName}</strong> приглашает вас в чат <strong class="chat-name">${data.chatName}</strong>`,
        actions: [
            {
                text: 'Принять',
                class: 'accept-btn',
                action: () => acceptChatInvitation(data)
            },
            {
                text: 'Отклонить',
                class: 'decline-btn',
                action: () => declineChatInvitation(data)
            }
        ]
    });

    // Also show browser notification if permission granted
    if (notificationPermission === 'granted') {
        showBrowserNotification({
            title: 'Приглашение в чат',
            body: `${data.inviterName} приглашает вас в чат "${data.chatName}"`,
            icon: data.inviterAvatar,
            tag: `invitation-${data.chatName}`,
            actions: [
                { action: 'accept', title: 'Принять' },
                { action: 'decline', title: 'Отклонить' }
            ]
        });
    }

    return notification;
}

// Show new message notification
function showNewMessageNotification(data) {
    const notification = createInAppNotification({
        type: 'new-message',
        title: 'Новое сообщение',
        icon: 'bi-chat-dots',
        avatar: data.senderAvatar,
        content: `<strong class="sender-name">${data.senderName}</strong> в чате <strong class="chat-name">${data.chatName}</strong><br><span class="message-preview">${data.messagePreview}</span>`,
        actions: [
            {
                text: 'Открыть',
                class: 'view-btn',
                action: () => openChatFromNotification(data)
            },
            {
                text: 'Закрыть',
                class: 'dismiss-btn',
                action: () => dismissNotification(notification.id)
            }
        ]
    });

    // Also show browser notification if permission granted
    if (notificationPermission === 'granted') {
        showBrowserNotification({
            title: `${data.senderName} - ${data.chatName}`,
            body: data.messagePreview,
            icon: data.senderAvatar,
            tag: `message-${data.chatName}`,
            actions: [
                { action: 'view', title: 'Открыть' },
                { action: 'dismiss', title: 'Закрыть' }
            ]
        });
    }

    return notification;
}

// Create in-app notification
function createInAppNotification(config) {
    const id = ++notificationId;
    const notification = {
        id: id,
        type: config.type,
        element: null,
        timeout: null
    };

    const notificationHtml = `
            <div class="push-notification ${config.type}" data-notification-id="${id}">
                <div class="notification-header">
                    <div class="notification-title">
                        <div class="notification-icon ${config.type === 'chat-invitation' ? 'invitation-icon' : 'message-icon'}">
                            <i class="${config.icon}"></i>
                        </div>
                        <span>${config.title}</span>
                    </div>
                    <button class="notification-close" onclick="dismissNotification(${id})">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="notification-content">
                    <img src="${config.avatar}" alt="Avatar" class="notification-avatar">
                    <div class="notification-text">${config.content}</div>
                    <div class="notification-actions">
                        ${config.actions.map(action =>
        `<button class="notification-btn ${action.class}" onclick="handleNotificationAction(${id}, '${action.action || 'custom'}', ${config.actions.indexOf(action)})">${action.text}</button>`
    ).join('')}
                    </div>
                </div>
            </div>
        `;

    const $notification = $(notificationHtml);
    notification.element = $notification;
    notification.actions = config.actions;

    $('#notificationsContainer').append($notification);
    activeNotifications.push(notification);

    // Show notification with animation
    setTimeout(() => {
        $notification.addClass('show');
        playNotificationSound();
    }, 100);

    // Auto-dismiss after 10 seconds for message notifications
    if (config.type === 'new-message') {
        notification.timeout = setTimeout(() => {
            dismissNotification(id);
        }, 10000);
    }

    return notification;
}

// Show browser notification
function showBrowserNotification(config) {
    if (notificationPermission !== 'granted') return;

    const notification = new Notification(config.title, {
        body: config.body,
        icon: config.icon,
        tag: config.tag,
        badge: '/favicon.ico',
        requireInteraction: config.actions && config.actions.length > 0,
        actions: config.actions || []
    });

    notification.onclick = function() {
        window.focus();
        notification.close();
    };

    // Auto-close after 5 seconds if no actions
    if (!config.actions || config.actions.length === 0) {
        setTimeout(() => {
            notification.close();
        }, 5000);
    }

    return notification;
}

// Handle notification action
function handleNotificationAction(notificationId, actionType, actionIndex) {
    const notification = activeNotifications.find(n => n.id === notificationId);
    if (!notification) return;

    if (actionIndex !== undefined && notification.actions[actionIndex]) {
        notification.actions[actionIndex].action();
    }

    // Remove notification after action
    dismissNotification(notificationId);
}

// Dismiss notification
function dismissNotification(notificationId) {
    const notificationIndex = activeNotifications.findIndex(n => n.id === notificationId);
    if (notificationIndex === -1) return;

    const notification = activeNotifications[notificationIndex];

    // Clear timeout if exists
    if (notification.timeout) {
        clearTimeout(notification.timeout);
    }

    // Hide with animation
    notification.element.removeClass('show').addClass('hide');

    // Remove from DOM after animation
    setTimeout(() => {
        notification.element.remove();
        activeNotifications.splice(notificationIndex, 1);
    }, 400);
}

// Accept chat invitation
function acceptChatInvitation(data) {
    showNotification(`Вы присоединились к чату "${data.chatName}"`, 'success');

    // Add chat to chat list (simulate)
    const chatHtml = `
            <div class="chat-item group-chat" data-chat-id="new-${Date.now()}">
                <div class="chat-item-avatar">
                    <img src="${data.inviterAvatar}" alt="${data.chatName}">
                    <div class="online-indicator"></div>
                </div>
                <div class="chat-item-content">
                    <div class="chat-item-header">
                        <h6 class="chat-item-name">${data.chatName}</h6>
                        <span class="chat-item-time">сейчас</span>
                    </div>
                    <p class="chat-item-message">Вы присоединились к чату</p>
                </div>
                <div class="chat-item-badge">1</div>
            </div>
        `;

    $('.chat-list').prepend(chatHtml);
}

// Decline chat invitation
function declineChatInvitation(data) {
    showNotification(`Приглашение в чат "${data.chatName}" отклонено`, 'info');
}

// Open chat from notification
function openChatFromNotification(data) {
    showNotification(`Открываем чат "${data.chatName}"`, 'info');

    // Find and select the chat
    const chatItem = $(`.chat-item:contains("${data.chatName}")`).first();
    if (chatItem.length > 0) {
        chatItem.click();
    }
}

// Play notification sound
function playNotificationSound() {
    // Add visual feedback
    $('body').addClass('notification-sound-effect');
    setTimeout(() => {
        $('body').removeClass('notification-sound-effect');
    }, 300);

    // Play sound if available
    try {
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT');
        audio.volume = 0.3;
        audio.play().catch(() => {
            // Ignore audio play errors
        });
    } catch (e) {
        // Ignore audio errors
    }
}

// Process notification queue
function processNotificationQueue() {
    while (notificationQueue.length > 0) {
        const notification = notificationQueue.shift();
        if (notification.type === 'invitation') {
            showChatInvitationNotification(notification.data);
        } else if (notification.type === 'message') {
            showNewMessageNotification(notification.data);
        }
    }
}

// Generate demo notifications
function generateDemoNotifications() {
    const demoInvitations = [
        {
            inviterName: 'Мария Сидорова',
            inviterAvatar: 'https://via.placeholder.com/48',
            chatName: 'Проект Alpha',
            chatType: 'group'
        },
        {
            inviterName: 'Дмитрий Волков',
            inviterAvatar: 'https://via.placeholder.com/48',
            chatName: 'Обсуждение задач',
            chatType: 'group'
        }
    ];

    const demoMessages = [
        {
            senderName: 'Команда разработки',
            senderAvatar: 'https://via.placeholder.com/48',
            chatName: 'Dev Team',
            messagePreview: 'Новая версия готова к тестированию!',
            timestamp: new Date()
        },
        {
            senderName: 'Анна Козлова',
            senderAvatar: 'https://via.placeholder.com/48',
            chatName: 'Дизайн',
            messagePreview: 'Посмотрите новые макеты',
            timestamp: new Date()
        }
    ];

    // Show demo invitation after 15 seconds
    setTimeout(() => {
        if (Math.random() > 0.5) {
            const invitation = demoInvitations[Math.floor(Math.random() * demoInvitations.length)];
            showChatInvitationNotification(invitation);
        }
    }, 15000);

    // Show demo message after 25 seconds
    setTimeout(() => {
        const message = demoMessages[Math.floor(Math.random() * demoMessages.length)];
        showNewMessageNotification(message);
    }, 25000);

    // Continue generating random notifications
    setInterval(() => {
        if (Math.random() > 0.7) { // 30% chance every 30 seconds
            if (Math.random() > 0.6) {
                const invitation = demoInvitations[Math.floor(Math.random() * demoInvitations.length)];
                showChatInvitationNotification(invitation);
            } else {
                const message = demoMessages[Math.floor(Math.random() * demoMessages.length)];
                showNewMessageNotification(message);
            }
        }
    }, 30000);
}

// Make functions globally available
window.dismissNotification = dismissNotification;
window.handleNotificationAction = handleNotificationAction;

// Initialize notifications when document is ready
$(document).ready(function() {
    initPushNotifications();
});

