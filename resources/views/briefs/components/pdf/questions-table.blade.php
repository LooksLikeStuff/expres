<table>
    <thead>
        <tr>
            <th>Вопрос</th>
            <th>Ответ</th>
        </tr>
    </thead>
    <tbody>
        @if(is_array($pageQuestions) && count($pageQuestions) > 0)
            @foreach($pageQuestions as $question)
                @php
                    $hasAnswer = false;
                    if (isset($question['answer'])) {
                        if (is_array($question['answer'])) {
                            $hasAnswer = !empty(array_filter($question['answer']));
                        } else {
                            $hasAnswer = !empty(trim((string) $question['answer']));
                        }
                    }
                @endphp
                @if($hasAnswer)
                    <tr>
                        <td>{{ $question['title'] ?? 'Без названия' }}</td>
                        <td>
                            @php
                                $answer = $question['answer'] ?? '';
                                if (is_array($answer)) {
                                    $displayAnswer = implode(', ', $answer);
                                } elseif (is_string($answer)) {
                                    $displayAnswer = $answer;
                                } else {
                                    $displayAnswer = (string) $answer;
                                }
                            @endphp
                            {{ $displayAnswer }}
                        </td>
                    </tr>
                @endif
            @endforeach
        @else
            <tr>
                <td colspan="2">Нет данных для этого раздела</td>
            </tr>
        @endif
    </tbody>
</table>
