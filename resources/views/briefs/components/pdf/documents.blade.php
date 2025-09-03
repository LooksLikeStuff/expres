{{-- Прикрепленные документы --}}
@if($brief->documents && $brief->documents->count() > 0)
    <h3>Прикрепленные документы</h3>
    <table>
        <thead>
            <tr>
                <th>Название файла</th>
                <th>Ссылка</th>
            </tr>
        </thead>
        <tbody>
            @foreach($brief->documents as $document)
                @php
                    $fileName = '';
                    $fileUrl = '';
                    
                    // Безопасно получаем имя файла
                    if (isset($document->original_name) && is_string($document->original_name)) {
                        $fileName = $document->original_name;
                    } elseif (isset($document->file_path) && is_string($document->file_path)) {
                        $fileName = basename($document->file_path);
                    } else {
                        $fileName = 'Неизвестный файл';
                    }
                    
                    // Безопасно получаем URL файла
                    if (isset($document->full_url) && is_string($document->full_url)) {
                        $fileUrl = $document->full_url;
                    } elseif (isset($document->file_path) && is_string($document->file_path)) {
                        $fileUrl = $document->file_path;
                    } else {
                        $fileUrl = 'Ссылка недоступна';
                    }
                @endphp
                <tr>
                    <td>{{ $fileName }}</td>
                    <td>{{ $fileUrl }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
