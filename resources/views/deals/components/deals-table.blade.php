<!-- Компонент: Табличное представление сделок -->

@if ($viewType === 'table')
    <div class="table-container">
        <table id="dealTable" class="deal-table display">
            <thead>
                <tr>
                    <th>Номер сделки</th>
                    <th>Клиент</th>
                    <th>Номер клиента</th>
                    <th>Координатор</th>
                    <th>Сумма сделки</th>
                    <th>Статус</th>
                    <th>Партнер</th>
                    <th>Средняя оценка</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody class="flex_table__format_table">
                @foreach ($deals as $dealItem)
                    <tr>
                        <td class="deal-name">{{ $dealItem->project_number ?? 'Не указан' }}</td>

                        <td class="deal-client">
                            @if ($dealItem->user_id)
                                <a href="{{ route('profile.view', $dealItem->user_id) }}">
                                    {{ $dealItem->client_name ?? 'Не указан' }}
                                </a>
                            @else
                                {{ $dealItem->client_name ?? 'Не указан' }}
                            @endif
                        </td>
                        
                        <td class="deal-phone">
                            <a href="tel:{{ $dealItem->client_phone }}">
                                {{ $dealItem->client_phone }}
                            </a>
                        </td>

                        <td class="deal-coordinator">
                            @if ($dealItem->coordinator_id)
                                <a href="{{ route('profile.view', $dealItem->coordinator_id) }}">
                                    {{ \App\Models\User::find($dealItem->coordinator_id)->name ?? 'Не указан' }}
                                </a>
                            @else
                                Не указан
                            @endif
                        </td>
                        
                        <td class="deal-sum">
                            {{ number_format($dealItem->total_sum, 0, '.', ' ') ?? 'Отсутствует' }} ₽</td>
                        <td
                            class="deal-status status-{{ strtolower(str_replace(' ', '-', $dealItem->status)) }}">
                            {{ $dealItem->status }}</td>
                        <td class="deal-partner">
                            @if ($dealItem->office_partner_id)
                                <a href="{{ route('profile.view', $dealItem->office_partner_id) }}">
                                    {{ \App\Models\User::find($dealItem->office_partner_id)->name ?? 'Не указан' }}
                                </a>
                            @else
                                Не указан
                            @endif
                        </td>
                        <td class="deal-rating">
                            @if ($dealItem->status === 'Проект завершен')
                                <div class="rating-block">
                                    @if ($dealItem->client_average_rating)
                                        <div class="deal-rating-stars client-rating"
                                            title="Оценка от клиента: {{ $dealItem->client_average_rating }} ({{ $dealItem->client_ratings_count }} оценок)">
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
                                            <span
                                                class="rating-value">{{ $dealItem->client_average_rating }}</span>
                                        </div>
                                    @endif

                                    @if ($dealItem->average_rating && !$dealItem->client_average_rating)
                                        <div class="deal-rating-stars overall-rating"
                                            title="Общая средняя оценка: {{ $dealItem->average_rating }} ({{ $dealItem->ratings_count }} оценок)">
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
                                            <span
                                                class="rating-value">{{ $dealItem->average_rating }}</span>
                                        </div>
                                    @endif

                                    @if (!$dealItem->average_rating && !$dealItem->client_average_rating)
                                        <span class="no-rating">Нет оценок</span>
                                    @endif
                                </div>
                            @else
                                <span class="no-rating">—</span>
                            @endif
                        </td>
                        <td class="link__deistv">
                            @if ($dealItem->registration_token)
                                <a href="{{ $dealItem->registration_token ? route('register_by_deal', ['token' => $dealItem->registration_token]) : '' }}"
                                    onclick="event.preventDefault(); copyRegistrationLink(this.href)"
                                    title="Скопировать регистрационную ссылку">
                                    <img src="/storage/icon/link.svg" alt="Регистрационная ссылка">
                                </a>
                            @else
                                <a href="#" title="Регистрационная ссылка отсутствует">
                                    <img src="/storage/icon/link.svg" alt="Регистрационная ссылка">
                                </a>
                            @endif

                            <a href="{{ $dealItem->link ? url($dealItem->link) : '#' }}" title="Бриф">
                                <img src="/storage/icon/brif.svg" alt="Бриф">
                            </a>

                            @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                                <a href="{{ route('deal.change_logs.deal', ['deal' => $dealItem->id]) }}"
                                    title="Логи сделки">
                                    <img src="/storage/icon/log.svg" alt="Логи">
                                </a>
                            @endif

                            @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                                <a href="{{ route('deal.edit-page', $dealItem->id) }}" class="edit-deal-btn"
                                    data-id="{{ $dealItem->id }}" title="Редактировать сделку">
                                    <img src="/storage/icon/add.svg" alt="Редактировать">
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
