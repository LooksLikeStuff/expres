<ul>
    <li>
        <a href="#" onclick="event.preventDefault(); copyRegistrationLink('{{ $deal->registration_token ? route('register_by_deal', ['token' => $deal->registration_token]) : '#' }}')" title="Скопировать регистрационную ссылку для клиента">
            <img src="/storage/icon/link.svg" alt="Регистрационная ссылка">
        </a>
    </li>
    @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
        <li>
            <a href="{{ route('deal.change_logs.deal', ['deal' => $deal->id]) }}" title="Просмотр истории изменений сделки">
                <img src="/storage/icon/log.svg" alt="Логи">
            </a>
        </li>
    @endif
     @if (in_array(Auth::user()->status, ['admin', 'coordinator']))
        <li>
            <a href="#" class="find-brief-btn" data-deal-id="{{ $deal->id }}" data-client-phone="{{ $deal->client_phone }}" title="Найти и привязать бриф по номеру телефона">
                <img src="/storage/icon/F-Search.svg" alt="Поиск брифа">
            </a>
        </li>
    @endif
    @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']) && $deal->has_brief)
        <li>
            <a href="{{ $deal->brief_url }}" title="Открыть бриф клиента" target="_blank">
                <img src="/storage/icon/brif.svg" alt="Бриф клиента">
            </a>
        </li>
    @endif
</ul>
