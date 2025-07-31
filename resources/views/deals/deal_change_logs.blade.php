<div class="container">
    <h1>{{ $title_site }}</h1>

    @if(isset($deal))
      <p>Логи изменений для сделки: <strong>{{ $deal->name }}</strong> (ID: {{ $deal->id }})</p>
      <a href="{{ route('deal.cardinator') }}" class="btn btn-secondary mb-3" title="Вернуться к списку всех сделок">Назад к сделкам</a>
    @endif

    <table id="logsTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th title="Уникальный идентификатор записи лога">ID</th>
                <th title="Идентификатор сделки, к которой относится изменение">ID сделки</th>
                <th title="Пользователь, внесший изменения">Пользователь</th>
                <th title="Дата и время внесения изменений">Дата изменения</th>
                <th title="Список полей, которые были изменены">Изменённые поля</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->id }}</td>
                <td>{{ $log->deal_id }}</td>
                <td title="ID пользователя: {{ $log->user_id }}">[{{ $log->user_id }}] {{ $log->user_name }}</td>
                <td title="Полная дата: {{ $log->created_at }}">{{ $log->created_at->format('d.m.Y H:i:s') }}</td>
                <td>
                    <ul>
                        @foreach($log->changes as $field => $change)
                            <li>
                                <strong title="Название измененного поля">{{ $field }}:</strong>
                                <br>
                                <em title="Предыдущее значение">Было:</em> {{ is_array($change['old']) ? json_encode($change['old']) : $change['old'] }}<br>
                                <em title="Новое значение">Стало:</em> {{ is_array($change['new']) ? json_encode($change['new']) : $change['new'] }}
                            </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $('#logsTable').DataTable({
        "order": [[ 3, "desc" ]],
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"
        }
    });
    
    // Инициализация всплывающих подсказок
    if (typeof $().tooltip === 'function') {
        $('[title]').tooltip({
            placement: 'auto',
            trigger: 'hover',
            delay: {show: 1000, hide: 100}, // Задержка в 1 секунду
            template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
        });
    }
});
</script>