{{-- Общий бюджет --}}
<h2>Общий бюджет: {{ number_format($brief->price ?? 0, 0, ',', ' ') }} ₽</h2>

{{-- Зоны --}}
@if(is_array($zones) && count($zones) > 0)
    <h2>Зоны</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Название</th>
                <th>Описание</th>
                <th>Общая площадь</th>
                <th>Проектируемая площадь</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($zones as $index => $zone)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $zone['name'] ?? 'Без названия' }}</td>
                    <td>{{ $zone['description'] ?? '' }}</td>
                    <td>{{ isset($zone['total_area']) ? $zone['total_area'] . ' м²' : 'Не указана' }}</td>
                    <td>{{ isset($zone['projected_area']) ? $zone['projected_area'] . ' м²' : 'Не указана' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

{{-- Вопросы и ответы по страницам --}}
@if(is_array($questions) && count($questions) > 0)
    @foreach($questions as $page => $pageQuestions)
        <h2>Страница {{ $page }}</h2>
        @include('briefs.components.pdf.questions-table', ['pageQuestions' => $pageQuestions])
    @endforeach
@endif

{{-- Ответы по зонам --}}
@if($zoneAnswers && $zoneAnswers->count() > 0)
    <h2>Ответы по зонам</h2>
    @foreach($zoneAnswers as $zoneId => $zoneAnswerGroup)
        @php
            $zone = collect($zones)->firstWhere('id', $zoneId);
            $zoneTitle = $zone['name'] ?? "Зона ID: {$zoneId}";
        @endphp
        
        <h3>{{ $zoneTitle }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Вопрос</th>
                    <th>Ответ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($zoneAnswerGroup as $answer)
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
