<!-- Подключение улучшенных стилей и скриптов для вкладок документов и брифов -->

<!-- CSS стили -->
<link rel="stylesheet" href="{{ asset('css/tabs-improved.css') }}">

<!-- JavaScript функциональность -->
<script src="{{ asset('js/tabs-working.js') }}" defer></script>

<!-- Исправления для функциональности вкладок -->
<script src="{{ asset('js/tabs-fix.js') }}" defer></script>

<!-- Дополнительные стили для интеграции с существующим дизайном -->
<style>
/* Переопределения для совместимости с существующими стилями */
.module__deal .faq_item__deal {
    background: var(--fff);
    border-radius: var(--radius);
    margin-bottom: 15px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.module__deal .create__group {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    background: var(--blue);
    color: var(--fff);
    border-radius: var(--radius) var(--radius) 0 0;
    font-weight: 500;
}

.module__deal .create__group i {
    font-size: 1.2em;
}

/* Адаптация загрузочной области к стилю проекта */
.upload-area {
    padding: 10px 0;
}

.upload-label {
    border-color: var(--blue-light, #e3f2fd);
    transition: all 0.3s ease;
}

.upload-label:hover {
    border-color: var(--blue);
    background: var(--blue-bg, #f0f8ff);
}

.upload-btn {
    background: var(--blue) !important;
    color: var(--fff) !important;
    border: none;
    min-height: 45px;
    font-weight: 500;
}

.upload-btn:disabled {
    background: var(--grey, #ccc) !important;
    cursor: not-allowed;
}

/* Адаптация для форм брифов */
.brief-search-form .form-control {
    min-height: 45px;
    border: 1px solid #ddd;
    border-radius: var(--radius);
}

.search-brief-btn {
    background: var(--blue) !important;
    color: var(--fff) !important;
    border: none;
    min-height: 45px;
    font-weight: 500;
}

/* Стили уведомлений */
.brief-notifications {
    border-radius: var(--radius);
    padding: 15px;
    margin-top: 15px;
}

.brief-notifications.success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.brief-notifications.error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.brief-notifications.info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}

/* Адаптация результатов поиска */
.search-results {
    border: 1px solid #ddd;
    border-radius: var(--radius);
    background: var(--fff);
    margin-top: 15px;
}

.results-header {
    background: #f8f9fa;
    border-bottom: 1px solid #ddd;
    padding: 15px 20px;
    border-radius: var(--radius) var(--radius) 0 0;
}

/* Мобильная адаптация */
@media (max-width: 768px) {
    .module__deal .create__group {
        padding: 12px 15px;
        font-size: 14px;
    }
    
    .upload-label {
        padding: 20px 15px;
    }
    
    .faq_text__deal {
        padding: 10px 15px;
    }
}
</style>
