<div class="mb mobile__ponel" id="step-mobile-2">
    <ul>
        <li>
            <button onclick="location.href='{{ url('/brifs') }}'" id="step-mobile-4" title="Просмотр и управление вашими брифами">
                <img src="/storage/icon/brif.svg" alt=""><span>Брифы</span>
            </button>
        </li>

        @if (Auth::check() && (Auth::user()->status == 'coordinator' ||
                Auth::user()->status == 'admin' ||
                Auth::user()->status == 'partner' ||
                Auth::user()->status == 'support' ||
                Auth::user()->status == 'architect' ||
                Auth::user()->status == 'designer' ||
                Auth::user()->status == 'visualizer'))
        <li>
            <button onclick="location.href='{{ route('deal.cardinator') }}'" title="Просмотр и управление вашими сделками">
                <img src="/storage/icon/deal.svg" alt=""> <span>Сделка </span>
            </button>
        </li>
        @elseif (Auth::check())
        <li>
            <button onclick="location.href='{{ route('user_deal') }}'" id="step-mobile-5" title="Просмотр информации о вашей сделке">
                <img src="/storage/icon/deal.svg" alt=""> <span>Сделка</span>
            </button>
        </li>
        @endif

        @if (Auth::check() && (Auth::user()->status == 'partner' || Auth::user()->status == 'admin' || Auth::user()->status == 'coordinator'))
        <li>
            <button onclick="window.location.href='{{ route('ratings.specialists') }}'" title="Просмотр рейтингов специалистов">
                <img src="{{ asset('storage/icon/F-Chart.svg') }}" alt="Рейтинги специалистов">
                <span>Рейтинги</span>
            </button>
        </li>
        @endif

        @if (Auth::check())
        <li>
            <button onclick="location.href='{{ url('/profile') }}'" id="step-mobile-6" title="Просмотр и редактирование вашего профиля">
                <img src="/storage/icon/F-User.svg" alt=""><span>Профиль</span>
            </button>
        </li>

            <li>
                <button onclick="location.href='{{ route('chats.index') }}'" title="Чаты">
                    <img src="/storage/icon/chat.svg" alt=""> <span>Чаты </span>
                </button>
            </li>
        @endif
        @if (Auth::check() && Auth::user()->status == 'admin' )
        <!-- Админские ссылки для мобильного меню -->
        <li>
            <button onclick="location.href='{{ url('/admin') }}'" title="Дашборд администратора">
                <img src="/storage/icon/admin.svg" alt=""> <span>Дашборд</span>
            </button>
        </li>
        <li>
            <button onclick="location.href='{{ route('admin.users') }}'" title="Управление пользователями">
                <img src="/storage/icon/people.svg" alt=""> <span>Пользователи</span>
            </button>
        </li>
        <li>
            <button onclick="location.href='{{ route('admin.awards.index') }}'" title="Управление наградами пользователей">
                <img src="/storage/icon/award.svg" alt=""> <span>Награды</span>
            </button>
        </li>
    @endif
    </ul>
</div>

@if(isset($deal) && $deal !== null)

@else

@endif
