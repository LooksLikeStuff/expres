<!-- Модальное окно информации о чате -->
<div class="modal fade" id="chatInfoModal" tabindex="-1" aria-labelledby="chatInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content chat-info-modal">
            <div class="modal-header">
                <div class="chat-header-info">
                    <img src="{{asset('img/chats/private/placeholder.png')}}" alt="Chat Avatar" class="chat-avatar me-3" id="chatInfoAvatar">
                    <div>
                        <h5 class="modal-title mb-0" id="chatInfoModalLabel">Название чата</h5>
                        <small class="chat-members-count" id="chatMembersCount">0 участников</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>

            <div class="modal-body p-0">
                <!-- Навигация по вкладкам -->
                <nav class="chat-tabs">
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <button class="nav-link active" id="nav-main-tab" data-bs-toggle="tab" data-bs-target="#nav-main" type="button" role="tab" aria-controls="nav-main" aria-selected="true">
                            <i class="fas fa-info-circle me-2"></i>
                            <span class="tab-text">Основное</span>
                        </button>
                        <button class="nav-link" id="nav-attachments-tab" data-bs-toggle="tab" data-bs-target="#nav-attachments" type="button" role="tab" aria-controls="nav-attachments" aria-selected="false">
                            <i class="fas fa-paperclip me-2"></i>
                            <span class="tab-text">Вложения</span>
                        </button>
                    </div>
                </nav>

                <!-- Содержимое вкладок -->
                <div class="tab-content" id="nav-tabContent">
                    <!-- Вкладка "Основное" -->
                    <div class="tab-pane fade show active" id="nav-main" role="tabpanel" aria-labelledby="nav-main-tab">
                        <div class="main-tab-content">

                            <!-- Список участников -->
                            <div class="members-section">
                                <div class="section-header">
                                    <h6 class="section-title">
                                        <i class="fas fa-users me-2"></i>
                                        Участники
                                        <span class="member-count" id="memberCountBadge">0</span>
                                    </h6>

                                    @if($user->isStaff())
                                        <button class="btn btn-sm btn-outline-light" id="addMemberBtn">
                                            <i class="fas fa-user-plus me-1"></i>
                                            Добавить
                                        </button>
                                    @endif
                                </div>

                                <div class="members-list" id="membersList">
                                    <!-- Участники будут добавлены через JavaScript -->
                                </div>
                            </div>

                            @if($user->isStaff())
                                <!-- Опасная зона -->
                                <div class="danger-zone">
                                    <div class="danger-actions">
                                        <button class="btn btn-outline-danger" id="leaveChatBtn">
                                            <i class="fas fa-sign-out-alt me-2"></i>
                                            Покинуть чат
                                        </button>
                                        <button class="btn btn-danger" id="deleteChatBtn">
                                            <i class="fas fa-trash me-2"></i>
                                            Удалить чат
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Вкладка "Вложения" -->
                    <div class="tab-pane fade" id="nav-attachments" role="tabpanel" aria-labelledby="nav-attachments-tab">
                        <div class="attachments-tab-content">
                            <div class="attachments-tab-body">
                                <!-- Статистика вложений -->
                                <div class="attachments-stats">
                                    <div class="stat-item">
                                        <div class="stat-number" id="totalAttachments">0</div>
                                        <div class="stat-label">Всего файлов</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number" id="totalSize">0 MB</div>
                                        <div class="stat-label">Общий размер</div>
                                    </div>
                                </div>

                                <!-- Список вложений по датам -->
                                <div class="attachments-timeline" id="attachmentsTimeline">
                                    <!-- Вложения будут добавлены через JavaScript -->
                                </div>
                            </div>
                            <!-- Пустое состояние -->
                            <div class="empty-attachments d-none" id="emptyAttachments">
                                <i class="fas fa-paperclip fa-3x mb-3"></i>
                                <p>В этом чате пока нет файлов</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content confirm-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Подтверждение</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                    <h5 id="confirmDeleteTitle">Вы уверены?</h5>
                    <p id="confirmDeleteText">Это действие нельзя отменить.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Удалить</button>
            </div>
        </div>
    </div>
</div>
