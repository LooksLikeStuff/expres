<div class="ponel__body " id="step-2">
    <div class="ponel__ul">
        <li class="logo_flex flex">
            <a class="flex" href="{{ url('/home') }}" title="Вернуться на главную страницу"> <img src="/storage/icon/Ex.svg" alt=""> <span><img
                        src="/storage/icon/logo.svg" alt=""></span></a>
        </li>
        <li>
            <button id="toggle-panel" class="toggle-btn" title="Свернуть/развернуть боковую панель"> <img src="/storage/icon/burger.svg" alt="">
                <span>Свернуть меню</span></button>
        </li>
        <li>
            <button onclick="location.href='{{ url('/chats') }}'" id="step-3" title="Чаты">
                <img src="/storage/icon/chat.svg" alt=""><span>Чаты </span>
            </button>
        </li>
        @if(!in_array(Auth::user()->status, ['architect', 'designer', 'visualizer']))
            <li>
                <button onclick="location.href='{{ url('/brifs') }}'" id="step-4" title="Просмотр и управление вашими брифами">
                    <img src="/storage/icon/brif.svg" alt=""><span>Ваши брифы </span>
                </button>
            </li>
        @endif
        @if (Auth::user()->status == 'coordinator' ||
                Auth::user()->status == 'admin' ||
                Auth::user()->status == 'partner' ||
                Auth::user()->status == 'support' ||
                Auth::user()->status == 'architect' ||
                Auth::user()->status == 'designer' || Auth::user()->status == 'visualizer')
            <li>
                <button onclick="location.href='{{ route('deal.cardinator') }}'" id="step-5" title="Просмотр и управление вашими сделками">
                    <img src="/storage/icon/deal.svg" alt=""> <span>Ваши сделки</span>
                </button>
            </li>
            {{-- <li>
                <button onclick="location.href='{{ url('/chats') }}'">
                    <img src="/storage/icon/chat.svg" alt="">   @if(Auth::user()->unreadMessagesCount() > 0)
                    <span class="badge bg-danger rounded-pill">{{ Auth::user()->unreadMessagesCount() }}</span>
                @endif <span>Ваши чаты</span>
                </button>
            </li> --}}
        @else
            <li>
                <button onclick="location.href='{{ route('user_deal') }}'" title="Просмотр информации о вашей сделке">
                    <img src="/storage/icon/deal.svg" alt=""> <span>Сделка </span>
                </button>
            </li>
        @endif
        {{-- @if (Auth::user()->status == 'partner' || Auth::user()->status == 'admin')
            <li>
                <button onclick="location.href='{{ url('/estimate') }}'" title="Просмотр и управление сметами">
                    <img src="/storage/icon/estimates.svg" alt=""> <span>Ваши сметы</span>
                </button>
            </li>
        @endif --}}
        @if (Auth::user()->status == 'partner' || Auth::user()->status == 'admin' || Auth::user()->status == 'coordinator')
        <li>
            <button onclick="window.location.href='{{ route('ratings.specialists') }}'" title="Просмотр рейтингов специалистов">
                <img src="{{ asset('storage/icon/F-Chart.svg') }}" alt="Рейтинги специалистов">
                <span>Рейтинги</span>
            </button>
        </li>
    @endif
        <li>
            <button onclick="location.href='{{ url('/profile') }}'" id="step-6" title="Просмотр и редактирование вашего профиля">
                <img src="/storage/icon/F-User.svg" alt=""><span>Ваш профиль</span>
            </button>
        </li>

        <!-- Блок админских ссылок -->
        @if (Auth::user()->status == 'admin')
            <li>
                <button onclick="location.href='{{ url('/admin') }}'" title="Панель администрирования системы">
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

        {{-- <li>
            <button onclick="location.href='{{ url('/support') }}'" id="step-7">
                <img src="/storage/icon/support.svg" alt=""><span>Помощь</span>
            </button>
        </li> --}}
    </div>
</div>

<div id="chat-notification-container">
    <!-- Здесь будут появляться уведомления чата -->
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Получаем текущий URL
        const currentUrl = window.location.pathname;

        // Находим все кнопки с атрибутами onclick
        const buttons = document.querySelectorAll('button[onclick]');

        // Перебираем кнопки
        buttons.forEach(button => {
            // Извлекаем URL из атрибута onclick
            const onclickValue = button.getAttribute('onclick');
            const urlMatch = onclickValue.match(/'(.*?)'/);

            // Если URL найден в атрибуте onclick
            if (urlMatch && urlMatch[1]) {
                const buttonUrl = new URL(urlMatch[1], window.location.origin).pathname;

                // Сравниваем URL кнопки с текущим URL
                if (buttonUrl === currentUrl) {
                    button.classList.add('active_Btn');
                }
            }
        });


    });
</script>
