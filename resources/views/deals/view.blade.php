<div class="deals-list deals-list-user">
    <h1>
        Ваша сделка 
        <p class="status__user__deal" title="Текущий статус сделки">{{ $deal->status }}</p>
        <a href="{{ route('user_deal') }}" class="btn btn-sm btn-secondary" style="float: right; margin-top: 5px;">
            <i class="fas fa-arrow-left"></i> Назад к списку
        </a>
    </h1>
    
    <div class="deal" id="deal-{{ $deal->id }}" data-id="{{ $deal->id }}" data-status="{{ $deal->status }}">
        <div class="deal__body">
            <!-- Информация о сделке -->
            <div class="deal__info">
                <div class="deal__info__profile">
                    <div class="deal__avatar" title="Аватар сделки">
                        <img src="{{ asset('storage/' . ($deal->avatar_path ?? 'avatars/group_default.svg')) }}" alt="Avatar">
                    </div>
                    <div class="deal__info__title">
                        <h2>{{ $deal->project_number  ?? 'Не указан'}}</h2>
                          <h3 class="deal-title">{{ $deal->client_name  ?? 'Не указан'}}</h3>
                    </div>
                </div>
            </div>
            
         
            
            <!-- Улучшенная информационная сетка о сделке -->
            <div class="deal__details">
                <div class="deal__progress">
                    <h4 class="details-title">Статус выполнения</h4>
                    @php
                        $stages = [
                            'Ждем ТЗ' => 0,
                            'Планировка' => 20,
                            'Коллажи' => 40,
                            'Визуализация' => 60,
                            'Рабочка/сбор ИП' => 80,
                            'Проект готов' => 90,
                            'Проект завершен' => 100,
                            'Проект на паузе' => -1 // Особый случай
                        ];
                        
                        $currentStage = $deal->status;
                        $progress = isset($stages[$currentStage]) ? $stages[$currentStage] : 0;
                        $isPaused = $currentStage === 'Проект на паузе';
                    @endphp
                    
                    <div class="progress-container">
                        @if($isPaused)
                            <div class="progress-paused">
                                <i class="fas fa-pause-circle"></i> Проект временно на паузе
                            </div>
                        @else
                            <div class="progress-bar-wrapper">
                                <div class="progress-bar" style="width: {{ $progress }}%"></div>
                            </div>
                            <div class="progress-text">{{ $progress }}% выполнено</div>
                        @endif
                    </div>
                </div>
                
                <!-- Обновленная информационная сетка -->
                <div class="deal__info-grid">
                    @if($deal->price_service_option)
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-clipboard-list"></i> Услуга</span>
                        <span class="info-value">{{ $deal->price_service_option }}</span>
                    </div>
                    @endif
                    
                    @if($deal->rooms_count_pricing)
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-door-open"></i> Количество комнат</span>
                        <span class="info-value">{{ $deal->rooms_count_pricing }}</span>
                    </div>
                    @endif
                    
                    @if($deal->client_city)
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-city"></i> Город</span>
                        <span class="info-value">{{ $deal->client_city }}</span>
                    </div>
                    @endif
                </div>
                
                <!-- Блок с файлами проекта, если они есть -->
                <div class="deal__files">
                    @php
                        $hasFiles = false;
                        $files = [];
                        
                        if($deal->final_floorplan) {
                            $hasFiles = true;
                            $files[] = [
                                'name' => 'Планировка',
                                'path' => $deal->final_floorplan,
                                'icon' => 'fas fa-map'
                            ];
                        }
                        
                        if($deal->final_collage) {
                            $hasFiles = true;
                            $files[] = [
                                'name' => 'Коллаж',
                                'path' => $deal->final_collage,
                                'icon' => 'fas fa-object-group'
                            ];
                        }
                        
                        if($deal->final_project_file) {
                            $hasFiles = true;
                            $files[] = [
                                'name' => 'Итоговый проект',
                                'path' => $deal->final_project_file,
                                'icon' => 'fas fa-file-pdf'
                            ];
                        }
                        
                        if($deal->work_act && $deal->status == 'Проект завершен') {
                            $hasFiles = true;
                            $files[] = [
                                'name' => 'Акт выполненных работ',
                                'path' => $deal->work_act,
                                'icon' => 'fas fa-file-signature'
                            ];
                        }
                        
                        if($deal->visualization_link) {
                            $hasFiles = true;
                            $files[] = [
                                'name' => 'Визуализация',
                                'path' => $deal->visualization_link,
                                'icon' => 'fas fa-eye',
                                'is_link' => true
                            ];
                        }
                    @endphp
                    
                    @if($hasFiles)
                        <h4 class="details-title">Файлы проекта</h4>
                        <div class="files-grid">
                            @foreach($files as $file)
                                <div class="file-item">
                                    <div class="file-icon">
                                        <i class="{{ $file['icon'] }}"></i>
                                    </div>
                                    <div class="file-info">
                                        <span class="file-name">{{ $file['name'] }}</span>
                                        @if(isset($file['is_link']) && $file['is_link'])
                                            <a href="{{ $file['path'] }}" target="_blank" class="file-link">
                                                <i class="fas fa-external-link-alt"></i> Открыть
                                            </a>
                                        @else
                                            <a href="{{ asset('storage/'.$file['path']) }}" target="_blank" class="file-link">
                                                <i class="fas fa-download"></i> Скачать
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="deal__container deal__container__modul">
                <!-- Исправленная секция отображения команды проекта -->
                <div class="deal__responsible">
                    <h4 class="responsible-title"><i class="fas fa-users"></i> Команда проекта</h4>
                    <ul>
                        @php
                            // Получим всех участников сделки с разными ролями
                            $teamMembers = collect();
                            
                            // Координатор сделки
                            if($deal->coordinator_id) {
                                $coordinator = App\Models\User::find($deal->coordinator_id);
                                if($coordinator) {
                                    $teamMembers->push([
                                        'id' => $coordinator->id,
                                        'name' => $coordinator->name,
                                        'role' => 'Координатор',
                                        'avatar' => $coordinator->avatar_url ?? 'storage/avatars/default-avatar.png',
                                        'order' => 1 // Для сортировки
                                    ]);
                                }
                            }
                            
                            // Архитектор
                            if($deal->architect_id) {
                                $architect = App\Models\User::find($deal->architect_id);
                                if($architect) {
                                    $teamMembers->push([
                                        'id' => $architect->id,
                                        'name' => $architect->name,
                                        'role' => 'Архитектор',
                                        'avatar' => $architect->avatar_url ?? 'storage/avatars/default-avatar.png',
                                        'order' => 2
                                    ]);
                                }
                            }
                            
                            // Дизайнер
                            if($deal->designer_id) {
                                $designer = App\Models\User::find($deal->designer_id);
                                if($designer) {
                                    $teamMembers->push([
                                        'id' => $designer->id,
                                        'name' => $designer->name,
                                        'role' => 'Дизайнер',
                                        'avatar' => $designer->avatar_url ?? 'storage/avatars/default-avatar.png',
                                        'order' => 3
                                    ]);
                                }
                            }
                            
                            // Визуализатор
                            if($deal->visualizer_id) {
                                $visualizer = App\Models\User::find($deal->visualizer_id);
                                if($visualizer) {
                                    $teamMembers->push([
                                        'id' => $visualizer->id,
                                        'name' => $visualizer->name,
                                        'role' => 'Визуализатор',
                                        'avatar' => $visualizer->avatar_url ?? 'storage/avatars/default-avatar.png',
                                        'order' => 4
                                    ]);
                                }
                            }
                            
                            // Партнер
                            if($deal->office_partner_id) {
                                $partner = App\Models\User::find($deal->office_partner_id);
                                if($partner) {
                                    $teamMembers->push([
                                        'id' => $partner->id,
                                        'name' => $partner->name,
                                        'role' => 'Партнер',
                                        'avatar' => $partner->avatar_url ?? 'storage/avatars/default-avatar.png',
                                        'order' => 5
                                    ]);
                                }
                            }
                            
                            // Сортировка по порядку ролей
                            $teamMembers = $teamMembers->sortBy('order');
                        @endphp
                        
                        @if($teamMembers->count() > 0)
                            @foreach($teamMembers as $member)
                            <li onclick="window.location.href='/profile/view/{{ $member['id'] }}'" class="deal__responsible__user" title="Нажмите, чтобы просмотреть профиль {{ $member['name'] }}">
                                <div class="deal__responsible__avatar">
                                    <img src="{{ asset($member['avatar']) }}" alt="Аватар {{ $member['name'] }}">
                                </div>
                                <div class="deal__responsible__info">
                                    <h5>{{ $member['name'] }}</h5>
                                    <p title="Роль в проекте">{{ $member['role'] }}</p>
                                </div>
                            </li>
                            @endforeach
                        @else
                            <li class="deal__responsible__user">
                                <p title="За сделку пока никто не назначен ответственным">Ответственные не назначены</p>
                            </li>
                        @endif
                    </ul>
                    
                    <!-- Новый блок FAQ (вопрос-ответ) -->
                    <div class="deal__faq">
                        <h4 class="faq-title"><i class="fas fa-question-circle"></i> Часто задаваемые вопросы</h4>
                        
                        <div class="faq-item">
                            <div class="faq-question">
                                Какие этапы проходит мой дизайн-проект?
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <div class="faq-answer-content">
                                    Дизайн-проект обычно проходит следующие этапы: планировка, подбор материалов и коллажи, визуализация, 
                                    разработка рабочей документации, финальная доработка и выдача готового проекта. Текущий статус вашего
                                    проекта всегда отображается в этом интерфейсе.
                                </div>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <div class="faq-question">
                                Как я могу внести изменения в проект?
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <div class="faq-answer-content">
                                    Все пожелания по изменениям необходимо направлять координатору проекта через чат сделки. 
                                    После обсуждения команда внесет необходимые корректировки согласно условиям договора.
                                    Обращаем внимание, что количество правок может быть ограничено выбранным тарифом.
                                </div>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <div class="faq-question">
                                Что делать, если я хочу изменить объем работ?
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <div class="faq-answer-content">
                                    Если вы хотите изменить объем работ, добавить дополнительные услуги или изменить текущий пакет,
                                    свяжитесь с вашим координатором через чат сделки. Он проконсультирует вас по всем вопросам 
                                    и поможет скорректировать проект с учетом новых пожеланий.
                                </div>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <div class="faq-question">
                                Как получить готовые файлы проекта?
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <div class="faq-answer-content">
                                    По мере завершения этапов все готовые файлы будут появляться в разделе "Файлы проекта" на этой странице.
                                    После полного завершения проекта вы сможете скачать все материалы одним архивом. Также координатор
                                    проекта может выслать вам дополнительные материалы в чате.
                                </div>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <div class="faq-question">
                                Как связаться с командой проекта?
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <div class="faq-answer-content">
                                    Для связи с командой проекта используйте чат сделки. Нажмите на кнопку "Перейти в чат проекта" 
                                    внизу страницы. Там вы сможете общаться со всеми участниками процесса и задавать интересующие 
                                    вас вопросы.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Кнопки действий -->
            <div class="deal__actions">
                <a href="{{ route('user_deal') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> К списку сделок
                </a>
            
              
            </div>
        </div>
    </div>
</div>

<!-- Добавляем модальное окно для оценки если нужно -->
@if($deal->status == 'Проект завершен' && !$deal->client_rating_avg)
<div class="modal fade" id="ratingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Оценка проекта</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Форма оценки -->
                <form id="ratingForm">
                    <input type="hidden" id="dealIdForRating" name="deal_id" value="{{ $deal->id }}">
                    <div class="form-group">
                        <label>Оцените качество выполненной работы</label>
                        <div class="rating-input">
                            <div class="stars">
                                <i class="far fa-star" data-value="1"></i>
                                <i class="far fa-star" data-value="2"></i>
                                <i class="far fa-star" data-value="3"></i>
                                <i class="far fa-star" data-value="4"></i>
                                <i class="far fa-star" data-value="5"></i>
                            </div>
                            <input type="hidden" name="score" id="ratingScore" value="0">
                            <div class="rating-text">Выберите оценку</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ratingComment">Комментарий (необязательно)</label>
                        <textarea id="ratingComment" name="comment" class="form-control" rows="3" placeholder="Напишите ваши впечатления о проекте..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="submitRating" disabled>Отправить оценку</button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация FAQ
    const faqQuestions = document.querySelectorAll('.faq-question');
    faqQuestions.forEach(question => {
        question.addEventListener('click', function() {
            // Закрываем все ответы
            const allFaqItems = document.querySelectorAll('.faq-item');
            allFaqItems.forEach(item => {
                if(item !== this.parentElement) {
                    item.classList.remove('active');
                    item.querySelector('.faq-question').classList.remove('active');
                }
            });
            
            // Переключаем активное состояние
            const faqItem = this.parentElement;
            faqItem.classList.toggle('active');
            this.classList.toggle('active');
        });
    });

    // Инициализация обработчиков для оценки проекта
    const ratingButtons = document.querySelectorAll('.btn-rate-deal');
    ratingButtons.forEach(button => {
        button.addEventListener('click', function() {
            $('#ratingModal').modal('show');
        });
    });

    // Обработка выбора звезд для оценки
    document.querySelectorAll('.stars i').forEach(star => {
        star.addEventListener('click', function() {
            const value = parseInt(this.dataset.value);
            document.getElementById('ratingScore').value = value;
            
            document.querySelectorAll('.stars i').forEach(s => {
                if (parseInt(s.dataset.value) <= value) {
                    s.className = 'fas fa-star selected';
                } else {
                    s.className = 'far fa-star';
                }
            });
            
            const ratingTexts = ['', 'Очень плохо', 'Плохо', 'Нормально', 'Хорошо', 'Отлично'];
            document.querySelector('.rating-text').textContent = ratingTexts[value];
            
            document.getElementById('submitRating').disabled = false;
        });
    });

    // Отправка оценки
    document.getElementById('submitRating')?.addEventListener('click', function() {
        const dealId = document.getElementById('dealIdForRating').value;
        const score = document.getElementById('ratingScore').value;
        const comment = document.getElementById('ratingComment').value;
        
        if (score < 1) {
            alert('Пожалуйста, выберите оценку');
            return;
        }
        
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отправка...';
        
        // Отправляем запрос на сервер
        fetch('{{ route("ratings.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                deal_id: dealId,
                score: score,
                comment: comment
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Закрываем модальное окно
                $('#ratingModal').modal('hide');
                
                // Показываем сообщение об успехе
                alert('Спасибо за вашу оценку!');
                
                // Перезагружаем страницу для обновления интерфейса
                window.location.reload();
            } else {
                alert(data.message || 'Произошла ошибка при отправке оценки');
                this.disabled = false;
                this.innerHTML = 'Отправить оценку';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при отправке оценки');
            this.disabled = false;
            this.innerHTML = 'Отправить оценку';
        });
    });
    
    // Анимируем прогресс-бары
    const progressBars = document.querySelectorAll('.progress-bar');
    setTimeout(() => {
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            
            setTimeout(() => {
                bar.style.width = width;
            }, 300);
        });
    }, 400);
    
    // Проверка, нужно ли показать окно оценок
    const dealStatus = '{{ $deal->status }}';
    
    if (dealStatus === 'Проект завершен') {
        console.log('Открыта завершенная сделка, проверка необходимости оценок');
        setTimeout(() => {
            if (typeof window.RatingSystem !== 'undefined') {
                window.RatingSystem.checkPendingRatings('{{ $deal->id }}');
            } else if (typeof window.checkPendingRatings === 'function') {
                window.checkPendingRatings('{{ $deal->id }}');
            } else {
                console.warn('Система рейтингов не инициализирована');
            }
        }, 1000);
    }
    
    // Проверка localStorage на наличие сохраненного ID сделки
    const completedDealId = localStorage.getItem('completed_deal_id');
    if (completedDealId) {
        console.log('Найден ID завершенной сделки в localStorage:', completedDealId);
        setTimeout(() => {
            if (typeof window.RatingSystem !== 'undefined') {
                window.RatingSystem.checkPendingRatings(completedDealId);
            } else if (typeof window.checkPendingRatings === 'function') {
                window.checkPendingRatings(completedDealId);
            } else {
                console.warn('Система рейтингов не инициализирована');
            }
        }, 1000);
    }
});
</script>

<style>
.deal-participants {
    margin-top: 20px;
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 8px;
}

.deal-participants h4 {
    margin-bottom: 15px;
    font-size: 18px;
    color: #333;
}

.participants-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.participant-card {
    background-color: white;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: transform 0.2s, box-shadow 0.2s;
}

.participant-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.participant-link {
    display: flex;
    padding: 10px;
    color: inherit;
    text-decoration: none;
}

.participant-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 12px;
    flex-shrink: 0;
}

.participant-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.participant-info {
    flex-grow: 1;
}

.participant-name {
    font-weight: 600;
    margin-bottom: 3px;
}

.participant-role {
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
}

.participant-awards {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 5px;
}

.award-icon.mini {
    width: 20px;
    height: 20px;
}

.award-icon.mini svg {
    width: 100%;
    height: 100%;
}
</style>
