<!-- Стили для fullscreen-loader и элементов страницы сделок -->
<style>
    input,
    textarea,
    select,
    .multiselect-selected {
        min-height: 38px !important;
        height: 38px;
    }
</style>

<!-- Стили для блоков сделок -->
<style>
    .faq_item__deal {
        min-height: 250px;
    }
    
    div#all-deals-container .faq_block__deal.faq_block-blur.brifs__button__create-faq_block__deal {
        min-height: 250px;
    }
</style>

<!-- Стили для модального окна поиска брифа -->
<style>
/* Уникальные стили для модального окна поиска брифа, которые не будут конфликтовать с другими стилями */
.brief-search-modal {
    font-family: 'Roboto', sans-serif;
}

.brief-search-modal .brief-modal-dialog {
    max-width: 600px;
    margin: 1.75rem auto;
    border-radius: 12px;
}
.brief-search-modal .brief-current button {
    width: max-content;
    min-height: 50px;
}
.brief-search-modal .brief-modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
    background: #fff;
    overflow: hidden;
}

.brief-search-modal .brief-modal-header {
    /* background: linear-gradient(135deg, #3498db, #2980b9); */
    color: rgb(0, 0, 0);
    display: flex;
    border: none;
    padding: 0;
    justify-content: space-between;
}

.brief-search-modal .brief-modal-title {
    font-weight: 600;
    font-size: 1.25rem;
    margin: 0;
    display: flex;
    align-items: center;
}

.brief-search-modal .brief-modal-title::before {
    content: "";
    display: inline-block;
    width: 24px;
    height: 24px;
    margin-right: 10px;
    background: url('/storage/icon/brif.svg') no-repeat center;
    background-size: contain;
}

.brief-search-modal .brief-close-button {
    background: none;
    border: 1px solid #000;
    color: #000;
    width: 50px;
    min-height: 50px;
    font-size: 1.5rem;
    line-height: 1;
    padding: 0;
    margin: 0;
    opacity: 0.7;
    cursor: pointer;
    transition: opacity 0.2s;
    font-weight: 300;
}

.brief-search-modal .brief-close-button:hover {
    opacity: 1;
}

.brief-search-modal .brief-modal-body {
    padding: 20px 0;
    min-height: 100px;
    max-height: 70vh;
    overflow-y: auto;
}

.brief-search-modal .brief-spinner-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 30px 0;
}

.brief-search-modal .brief-spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: brief-spin 1s linear infinite;
}

@keyframes brief-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.brief-search-modal .brief-spinner-text {
    margin-top: 15px;
    color: #555;
    font-size: 1rem;
}

.brief-search-modal .brief-results {
    margin-top: 10px;
}

.brief-search-modal .brief-section-title {
    color: #333;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 10px;
    padding-bottom: 5px;
    border-bottom: 1px solid #eee;
}

.brief-search-modal .brief-list {
    list-style: none;
    padding: 0;
    margin: 0 0 20px 0;
}

.brief-search-modal .brief-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    background-color: #f9f9f9;
    border-radius: 8px;
    margin-bottom: 8px;
    transition: all 0.2s;
}

.brief-search-modal .brief-item:hover {
    background-color: #f0f0f0;
    transform: translateX(3px);
}

.brief-search-modal .brief-item-info {
    flex: 1;
}

.brief-search-modal .brief-item-id {
    font-weight: 600;
    color: #2c3e50;
}

.brief-search-modal .brief-item-date {
    font-size: 0.85rem;
    color: #7f8c8d;
    display: block;
    margin-top: 3px;
}

.brief-search-modal .brief-link-btn {
    background: linear-gradient(135deg, #3498db, #2980b9);
    border: none;
    color: white;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s;
    text-transform: uppercase;
}

.brief-search-modal .brief-link-btn:hover {
    background: linear-gradient(135deg, #2980b9, #2471a3);
    transform: translateY(-2px);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.brief-search-modal .brief-link-btn:disabled {
    background: #bdc3c7;
    color: #7f8c8d;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.brief-search-modal .brief-modal-footer {
    padding: 15px 24px;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
}

.brief-search-modal .brief-close-modal-btn {
    background: #e0e0e0;
    color: #333;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 500;
}

.brief-search-modal .brief-close-modal-btn:hover {
    background: #d0d0d0;
}

.brief-search-modal .brief-alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-weight: 500;
}

.brief-search-modal .brief-alert-info {
    background-color: #e3f2fd;
    color: #0d47a1;
    border: 1px solid #bbdefb;
}

.brief-search-modal .brief-alert-danger {
    background-color: #ffebee;
    color: #b71c1c;
    border: 1px solid #ffcdd2;
}

.brief-search-modal .brief-commercial {
    border-left: 3px solid #2ecc71;
}

.brief-search-modal .brief-common {
    border-left: 3px solid #3498db;
}

.brief-search-modal .brief-type-badge {
    display: inline-block;
    font-size: 0.75rem;
    padding: 2px 6px;
    border-radius: 4px;
    margin-right: 5px;
    font-weight: 600;
    text-transform: uppercase;
}
.brief-search-modal .brief-common button { width: max-content;
    min-height: 50px;}
li.brief-item.brief-common.brief-linked button {
    width: max-content;
    min-height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    align-content: center;
}

.brief-item-info {
    display: flex;
    flex-direction: column;
    align-content: flex-start;
    align-items: flex-start;
}
.brief-search-modal .brief-type-common {
    background-color: #e3f2fd;
    color: #0d47a1;
}

.brief-search-modal .brief-type-commercial {
    background-color: #e8f5e9;
    color: #1b5e20;
}

/* Анимация появления модального окна */
.brief-search-modal.show .brief-modal-dialog {
    animation: briefModalIn 0.3s ease-out;
}

@keyframes briefModalIn {
    0% { transform: scale(0.8); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

/* Стили для блока найденных пользователей */
.brief-search-modal .brief-users-found {
    margin-bottom: 20px;
}

.brief-search-modal .brief-type-user {
    background-color: #e8f4fd;
    color: #0366d6;
}

.brief-search-modal .brief-user {
    border-left: 3px solid #0366d6;
}

.brief-search-modal .brief-item-email {
    font-size: 0.85rem;
    color: #0366d6;
    display: block;
    margin-top: 3px;
}

.brief-search-modal .brief-item-owner {
    font-size: 0.85rem;
    color: #555;
    display: block;
    margin-top: 3px;
    font-style: italic;
}

.brief-search-modal .brief-linked {
    opacity: 0.7;
    background-color: #f0f0f0;
    border-left: 3px solid #9e9e9e;
}

.brief-search-modal .brief-item-linked {
    display: inline-block;
    margin-top: 5px;
    font-size: 0.85rem;
    background-color: #e0e0e0;
    color: #424242;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 500;
}

.brief-search-modal .brief-link-btn:disabled {
    background: #bdc3c7;
    color: #7f8c8d;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.brief-search-modal .brief-current-block {
    margin-bottom: 20px;
    padding-bottom: 15px;
}

.brief-search-modal .brief-current {
    border-left-width: 5px !important;
    background-color: #f8f9fa !important;
}

.brief-search-modal .brief-divider {
    border: 0;
    border-top: 1px dashed #ddd;
    margin: 20px 0;
}

.brief-search-modal .brief-unlink-btn {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 14px;
       border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s;
    text-transform: uppercase;
}

.brief-search-modal .brief-unlink-btn:hover {
    background: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.brief-search-modal .brief-unlink-btn:disabled {
    background: #f8bac1;
    color: #7f8c8d;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* СТИЛИ ДЛЯ МОДУЛЕЙ ВКЛАДОК УПРАВЛЯЮТСЯ ИЗ ЕДИНОЙ СИСТЕМЫ */
/* См. /resources/views/deals/partials/components/styles.blade.php */
.form-group-deal, .fieldset-content, .fieldset-body, .module__deal {
    overflow: visible !important;
}
</style>

<!-- Добавляем fullscreen-loader для отображения загрузки файлов -->
<div id="fullscreen-loader" class="fullscreen-loader">
    <div class="loader-wrapper">
        <div class="loader-container">
            <div class="loader-animation">
                <div class="loader-circle"></div>
                <div class="loader-circle"></div>
                <div class="loader-circle"></div>
            </div>
            <div class="loader-text">
                <h4>Загрузка файлов</h4>
                <p>Пожалуйста, подождите. Ваши файлы загружаются на сервер.</p>
                <div class="loader-progress">
                    <div class="loader-progress-bar"></div>
                </div>
            </div>
        </div>
    </div>
</div>
