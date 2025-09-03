<table>
    <tr>
        <td><strong>Название:</strong></td>
        <td>{{ $brief->title ?? 'Не указано' }}</td>
    </tr>
    <tr>
        <td><strong>Артикль:</strong></td>
        <td>{{ $brief->article ?? 'Не указан' }}</td>
    </tr>
    <tr>
        <td><strong>Описание:</strong></td>
        <td>{{ $brief->description ?? 'Не указано' }}</td>
    </tr>
    <tr>
        <td><strong>Общая сумма:</strong></td>
        <td>{{ number_format($brief->price ?? 0, 0, ',', ' ') }} руб</td>
    </tr>
    <tr>
        <td><strong>Статус:</strong></td>
        <td>{{ $brief->status->value ?? 'Не указан' }}</td>
    </tr>
    <tr>
        <td><strong>Создатель брифа:</strong></td>
        <td>{{ $user->name ?? 'Не указан' }}</td>
    </tr>
    <tr>
        <td><strong>Номер клиента:</strong></td>
        <td>{{ $user->phone ?? 'Не указан' }}</td>
    </tr>
    <tr>
        <td><strong>Дата создания:</strong></td>
        <td>{{ $brief->created_at->format('d.m.Y H:i') }}</td>
    </tr>
    <tr>
        <td><strong>Дата обновления:</strong></td>
        <td>{{ $brief->updated_at->format('d.m.Y H:i') }}</td>
    </tr>
</table>
