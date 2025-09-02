{{--@if ($question->type === 'textarea')--}}
    <textarea name="answers[{{ $question->key }}]"
              placeholder="{{ $question->placeholder }}"
              class="form-control required-field {{ $question->key == 'question_2_6' ? 'budget-input' : '' }}"
              data-original-placeholder="{{ $question->placeholder }}" maxlength="500">{{ ($answers ?? [])[$question->key] ?? '' }}</textarea>
{{--@else--}}
{{--    <input type="text" name="answers[{{ $question->key }}]"--}}
{{--           class="form-control required-field--}}
{{--                  {{ $question->format == 'price' ? 'price-input' : '' }}--}}
{{--                  {{ $question->class ?? '' }}"--}}
{{--           value="{{ $brief->{$question->key} ?? '' }}"--}}
{{--           placeholder="{{ $question->placeholder }}"--}}
{{--           data-original-placeholder="{{ $question->placeholder }}" maxlength="500">--}}
{{--@endif--}}
<span class="error-message">Это поле обязательно для заполнения</span>
