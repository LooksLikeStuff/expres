{{-- Выбранные комнаты --}}
@if($rooms && $rooms->count() > 0)
    <h3>Выбранные помещения</h3>
    <table>
        <thead>
            <tr>
                <th>Помещения</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @foreach($rooms as $index => $room)
                        {{ $room->title }}@if(!$loop->last), @endif
                    @endforeach
                </td>
            </tr>
        </tbody>
    </table>
@endif

{{-- Вопросы и ответы по страницам --}}
@if(is_array($questions) && count($questions) > 0)
    @foreach($questions as $page => $pageQuestions)
        @if(isset($pageTitles[$page - 1]))
            <h3>{{ $pageTitles[$page - 1] }}</h3>
        @else
            <h3>Страница {{ $page }}</h3>
        @endif
        
        @include('briefs.components.pdf.questions-table', ['pageQuestions' => $pageQuestions])
    @endforeach
@endif

{{-- Ответы по комнатам --}}
@if($roomAnswers && $roomAnswers->count() > 0)
    <h3>Ответы по помещениям</h3>
    @foreach($roomAnswers as $roomId => $roomAnswerGroup)
        @php
            $room = $rooms->firstWhere('id', $roomId);
            $roomTitle = $room ? $room->title : "Комната ID: {$roomId}";
        @endphp
        
        <h4>{{ $roomTitle }}</h4>
        <table>
            <thead>
                <tr>
                    <th>Вопрос</th>
                    <th>Ответ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roomAnswerGroup as $answer)
                    @if($answer->question && !empty(trim($answer->answer_text)))
                        <tr>
                            <td>{{ $answer->question->title ?? 'Без названия' }}</td>
                            <td>{{ $answer->answer_text }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endforeach
@endif
