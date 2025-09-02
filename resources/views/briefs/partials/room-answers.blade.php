@if($roomAnswers->isNotEmpty())
    @foreach($roomAnswers as $roomId => $answers)
        @php
            $room = $answers->first()->room ?? null;
        @endphp
        
        <div class="room-section mb-4">
            <h4 class="text-primary">
                <i class="fas fa-home me-2"></i>
                {{ $room->title ?? 'Неизвестная комната' }}
            </h4>
            
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="40%">Вопрос</th>
                        <th>Ответ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($answers as $answer)
                        <tr>
                            <td>
                                <strong>{{ $answer->question->title ?? 'Без названия' }}</strong>
                                @if($answer->question->subtitle)
                                    <br><small class="text-muted">{{ $answer->question->subtitle }}</small>
                                @endif
                            </td>
                            <td>
                                @if($answer->answer_text)
                                    {!! nl2br(e($answer->answer_text)) !!}
                                @elseif($answer->answer_json)
                                    @php
                                        $jsonAnswer = json_decode($answer->answer_json, true);
                                    @endphp
                                    @if(is_array($jsonAnswer))
                                        @foreach($jsonAnswer as $key => $value)
                                            <div><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</div>
                                        @endforeach
                                    @else
                                        {{ $jsonAnswer }}
                                    @endif
                                @else
                                    <span class="text-muted">Нет ответа</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
@else
    <div class="text-center text-muted">
        <p>Нет ответов по комнатам</p>
    </div>
@endif
