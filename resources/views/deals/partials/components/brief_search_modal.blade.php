<!-- Модальное окно результатов поиска брифа -->
<div class="modal fade brief-search-modal" id="briefSearchModal" tabindex="-1" role="dialog" aria-labelledby="briefSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog brief-modal-dialog" role="document">
        <div class="modal-content brief-modal-content">
            <div class="modal-header brief-modal-header">
                <h5 class="modal-title brief-modal-title" id="briefSearchModalLabel">Поиск и привязка брифа</h5>
                <button type="button" class="brief-close-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body brief-modal-body" id="briefSearchResults">
                <div class="brief-spinner-container">
                    <div class="brief-spinner"></div>
                    <p class="brief-spinner-text">Поиск брифов по номеру телефона клиента...</p>
                </div>
            </div>
            <div class="modal-footer brief-modal-footer">
                <button type="button" class="brief-close-modal-btn" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
