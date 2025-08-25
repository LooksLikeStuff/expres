<form id="createBriefForm" action="{{ route('briefs.store') }}" method="POST" class="div__create_form">
    @csrf
    <input type="hidden" name="type" id="createBriefType" value="">
    <div class="div__create_block">
        <h1>
            <span class="Jikharev">Добро пожаловать!</span>
        </h1>
        <p><strong>Дорогой клиент,</strong> для продолжения требуется пройти <strong>бриф-опросник</strong> </p>
        <div class="button__create__brifs flex gap3" id="step-8">
            <button type="submit" class="button__icon create__brief-btn" data-type="common"><span>Создать Общий бриф </span> <img src="/storage/icon/plus.svg" alt=""></button>
            <button type="submit" class="button__icon create__brief-btn" data-type="commercial"><span>Создать Коммерческий бриф </span> <img src="/storage/icon/plus.svg" alt=""></button>
        </div>
    </div>

</form>
