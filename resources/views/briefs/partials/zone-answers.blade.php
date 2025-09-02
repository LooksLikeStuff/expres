<!-- Ответы по зонам для коммерческого брифа -->
<div class="card mb-4">
    <div class="card-header">
        <h2>Ответы по зонам</h2>
    </div>
    <div class="card-body">
        @if($zoneAnswers->isNotEmpty())
            @foreach($zoneAnswers as $roomId => $answers)
                @php
                    $zone = $answers->first()->room ?? null;
                @endphp
                
                <div class="zone-section mb-5">
                    <h4 class="text-success border-bottom pb-2">
                        <i class="fas fa-building me-2"></i>
                        {{ $zone->title ?? 'Неизвестная зона' }}
                    </h4>
                    
                    <div class="row">
                        @foreach($answers->groupBy('question.page') as $page => $pageAnswers)
                            <div class="col-md-6 mb-3">
                                <div class="card border-light">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Страница {{ $page }}</h6>
                                    </div>
                                    <div class="card-body">
                                        @foreach($pageAnswers as $answer)
                                            <div class="mb-3">
                                                <strong class="d-block">{{ $answer->question->title ?? 'Без названия' }}</strong>
                                                @if($answer->question->subtitle)
                                                    <small class="text-muted d-block mb-1">{{ $answer->question->subtitle }}</small>
                                                @endif
                                                
                                                <div class="answer-content">
                                                    @if($answer->answer_text)
                                                        {!! nl2br(e($answer->answer_text)) !!}
                                                    @elseif($answer->answer_json)
                                                        @php
                                                            $jsonAnswer = json_decode($answer->answer_json, true);
                                                        @endphp
                                                        @if(is_array($jsonAnswer))
                                                            @foreach($jsonAnswer as $key => $value)
                                                                <div><em>{{ ucfirst($key) }}:</em> {{ $value }}</div>
                                                            @endforeach
                                                        @else
                                                            {{ $jsonAnswer }}
                                                        @endif
                                                    @else
                                                        <span class="text-muted fst-italic">Нет ответа</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if(!$loop->last)
                                                <hr class="my-2">
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                @if(!$loop->last)
                    <hr class="my-4">
                @endif
            @endforeach
        @else
            <div class="text-center text-muted">
                <p>Нет ответов по зонам</p>
            </div>
        @endif
    </div>
</div>
