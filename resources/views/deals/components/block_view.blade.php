<!-- Компонент с блочным представлением сделок -->
<div class="faq__body__deal" id="all-deals-container">
    <h4 class="flex">
        @if(request()->has('statuses') && in_array('Проект завершен', request()->statuses) || request()->status === 'Проект завершен')
            Завершенные проекты
        @else
            Все активные сделки
        @endif
    </h4>
    @if(!(request()->has('statuses') && in_array('Проект завершен', request()->statuses)) && request()->status !== 'Проект завершен')

    @endif
    @if ($deals->isEmpty())
        <div class="faq_block__deal faq_block-blur brifs__button__create-faq_block__deal"
            onclick="window.location.href='{{ route('deals.create') }}'">

            @if (Auth::check() &&
                    (Auth::user()->status == 'architect' ||
                        Auth::user()->status == 'designer' ||
                        Auth::user()->status == 'visualizer'))
                <h3 style="text-align: center;width: 100%;display: flex;align-items: center;justify-content: center;align-content: center;">
                    Тут будут отображаться ваши сделки </br> к которым вы относитесь!</h3>
            @elseif (Auth::check() && in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                <button>
                    <img src="/storage/icon/add.svg" alt="Создать сделку">
                </button>
            @endif
        </div>
    @else
       <div class="faq_block__deal faq_block-blur brifs__button__create-faq_block__deal"
            onclick="window.location.href='{{ route('deals.create') }}'">
            @if (Auth::check() &&
                    (Auth::user()->status == 'architect' ||
                        Auth::user()->status == 'designer' ||
                        Auth::user()->status == 'visualizer'))
                <h3 style="text-align: center;width: 100%;display: flex;align-items: center;justify-content: center;align-content: center;">
                    Тут будут отображаться ваши сделки </br> к которым вы относитесь!</h3>
            @elseif (Auth::check() && in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                <button>
                    <img src="/storage/icon/add.svg" alt="Создать сделку">
                </button>
            @endif        </div>        @foreach ($deals as $dealItem)
            @if($dealItem->status !== 'Проект завершен' || (request()->has('statuses') && in_array('Проект завершен', request()->statuses)) || request()->status === 'Проект завершен')
            <div class="faq_block__deal" data-id="{{ $dealItem->id }}"
                data-status="{{ $dealItem->status }}">
                <div class="faq_item__deal">
                    <div class="faq_question__deal flex between">
                        <div class="faq_question__deal__info">

                            <div class="deal__avatar deal__avatar__cardinator">
                                <img src="{{ $dealItem->avatar_path ? asset('storage/' . $dealItem->avatar_path) : asset('storage/icon/deal_default.jpg') }}" 
                                     alt="Avatar" title="{{ $dealItem->avatar_path ? 'Логотип сделки' : 'Дефолтный логотип' }}">
                            </div>
                            
                            <div class="deal__cardinator__info">
                                <div class="ctatus__deal___info">
                                    <div class="div__status_info">{{ $dealItem->status }}</div>
                                </div>
                                <h4>{{ $dealItem->project_number  ?? 'Не указан'}}</h4>
                                
                                <p>Клиент:
                                    {{ $dealItem->client_name ?? 'Не указан' }}
                                </p>
                               
                                <p>Телефон:
                                    <a href="tel:{{ $dealItem->client_phone }}">
                                        {{ $dealItem->client_phone }}
                                    </a>
                                </p>
                                <p>Координатор:
                                    @if ($dealItem->coordinator_id)
                                        <a href="{{ route('profile.view', $dealItem->coordinator_id) }}">
                                            {{ \App\Models\User::find($dealItem->coordinator_id)->name ?? 'Не указан' }}
                                        </a>
                                    @else
                                        Не указан
                                    @endif
                                </p>
                                <p>Партнер:
                                    @if ($dealItem->office_partner_id)
                                        <a href="{{ route('profile.view', $dealItem->office_partner_id) }}">
                                            {{ \App\Models\User::find($dealItem->office_partner_id)->name ?? 'Не указан' }}
                                        </a>
                                    @else
                                        Не указан
                                    @endif
                                </p>
                                <!-- Добавляем отображение средней оценки -->
                                @if ($dealItem->status === 'Проект завершен')
                                    <div class="deal-rating-block">
                                        @if ($dealItem->client_average_rating)
                                            <p>Оценка клиента:
                                                <span class="deal-rating-stars client-rating"
                                                    title="Оценка от клиента ({{ $dealItem->client_ratings_count }} оценок)">
                                                    <i class="fas fa-user-tie"></i>
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        @if ($i <= floor($dealItem->client_average_rating))
                                                            <i class="fas fa-star"></i>
                                                        @elseif($i - 0.5 <= $dealItem->client_average_rating)
                                                            <i class="fas fa-star-half-alt"></i>
                                                        @else
                                                            <i class="far fa-star"></i>
                                                        @endif
                                                    @endfor
                                                    <span class="rating-value">{{ $dealItem->client_average_rating }}</span>
                                                </span>
                                            </p>
                                        @endif

                                        @if ($dealItem->average_rating && !$dealItem->client_average_rating)
                                            <p>Общая оценка:
                                                <span class="deal-rating-stars overall-rating"
                                                    title="Общая средняя оценка ({{ $dealItem->ratings_count }} оценок)">
                                                    <i class="fas fa-users"></i>
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        @if ($i <= floor($dealItem->average_rating))
                                                            <i class="fas fa-star"></i>
                                                        @elseif($i - 0.5 <= $dealItem->average_rating)
                                                            <i class="fas fa-star-half-alt"></i>
                                                        @else
                                                            <i class="far fa-star"></i>
                                                        @endif
                                                    @endfor
                                                    <span class="rating-value">{{ $dealItem->average_rating }}</span>
                                                </span>
                                            </p>
                                        @endif

                                        @if (!$dealItem->average_rating && !$dealItem->client_average_rating)
                                            <p>Нет оценок</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                        <ul>
                            <li>
                                @php
                                    // Убираем переменную $groupChat
                                @endphp
                            </li>
                            <li>
                                @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                                    <a href="{{ route('deal.edit-page', $dealItem->id) }}" class="edit-deal-btn"
                                        data-id="{{ $dealItem->id }}" title="Редактировать сделку">
                                        <img src="/storage/icon/create__blue.svg" alt="">
                                        <span>Изменить</span>
                                    </a>
                                @endif
                            </li>
                        </ul>                    </div>
                </div>
            </div>
            @endif
        @endforeach
    @endif
    <div class="pagination" id="all-deals-pagination"></div>
</div>
