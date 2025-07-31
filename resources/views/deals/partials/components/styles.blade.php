<!-- Стили для секции документов и других компонентов -->
<style>
    /* Стили для кнопок удаления */
    .delete-deal-btn img {
        width: 24px;
        height: 24px;
    }
    
    .delete-deal-button {
        background: #fff;
        color: #e74c3c;
        border: 1px solid #e74c3c;
        border-radius: 4px;
        padding: 10px 15px;
        margin-left: 10px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .delete-deal-button:hover {
        background: #e74c3c !important;
        color: #fff;
    }
    
    /* Стили для поля имени клиента */
    input[name="client_name"] {
        font-weight: 500;
        border-color: #3498db;
    }
    
    input[name="client_name"]:focus {
        border-color: #2980b9;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    /* Группировка полей информации о клиенте */
    .form-group-deal label[for="client_name"],
    .form-group-deal label[for="client_phone"],
    .form-group-deal label[for="client_email"] {
        color: #3498db;
    }

    /* ====== ЕДИНАЯ ОПТИМИЗИРОВАННАЯ СИСТЕМА СТИЛЕЙ ДЛЯ ВКЛАДОК ====== */
    
    /* Контейнер кнопок вкладок - базовые стили */
    .button__points {
        display: flex !important;
        min-height: 110px;
        position: sticky;
        top: 0px;
        z-index: 9999;
        gap: var(--g10);
        background: var(--fff);
        border-radius: var(--radius);
        padding: var(--p20);
        align-items: center;
        justify-content: space-between;
        width: 100%;
        box-sizing: border-box;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Кнопки вкладок - оптимизированные стили */
    .button__points button {
        flex: 1;
        min-width: max-content;
        background: var(--blockbody) !important;
        color: var(--block) !important;
        border: none !important;
        border-radius: var(--radius) !important;
        padding: var(--p10) !important;
        font-size: 16px !important;
        font-weight: 500 !important;
        height: 42px !important;
        line-height: 22.4px !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
        text-transform: capitalize !important;
        box-sizing: border-box;
    }
    
    /* Активная кнопка вкладки */
    .button__points button.buttonSealaActive {
        background: var(--bluetext) !important;
        color: var(--fff) !important;
        box-shadow: 0 2px 8px rgba(1, 172, 255, 0.3) !important;
        transform: none !important;
    }
    
    /* Состояние наведения для неактивных кнопок */
    .button__points button:hover:not(.buttonSealaActive) {
        background: var(--fff) !important;
        color: var(--bluetext) !important;
        border: 1px solid var(--bluetext) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 2px 6px rgba(1, 172, 255, 0.2) !important;
    }
    
    /* Контейнеры модулей - базовое скрытое состояние */
    .module__deal {
        display: none !important;
        opacity: 0;
        visibility: hidden;
        position: relative;
        width: 100%;
        box-sizing: border-box;
        transition: all 0.3s ease-in-out;
        transform: translateY(10px);
        padding: 0;
        margin: 0;
        overflow: visible;
        z-index: 1;
    }
    .document-info {
    display: flex;
}
    /* Активное состояние модуля */
    .module__deal.active {
 display: flex !important
;
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) !important;
    flex-direction: row;
    align-items: stretch !important;
    justify-content: flex-start !important;
    margin-top: 15px !important;
    flex-wrap: wrap;    gap: 20px;
    }
    
    /* Дополнительный класс для анимации */
    .module__deal.module-visible {
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateY(0) !important;
    }
    
    
    /* Специфичные стили для отдельных модулей */
    #module-documents,
    #module-brief {
        z-index: 2 !important;
    }
    
    /* Форматирование форм внутри модулей */
    .module__deal form {
        width: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        gap: var(--g20) !important;
    }
    
    /* Переопределяем старые стили fieldset */
    fieldset.module__deal {
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        background: transparent !important;
    }
    
    /* Мобильная адаптация */
    @media (max-width: 768px) {
        .button__points {
            flex-wrap: wrap !important;
            min-height: auto !important;
            gap: 8px !important;
            padding: 15px !important;
        }
        
        .button__points button {
            min-width: 100px !important;
            font-size: 14px !important;
            padding: 8px 12px !important;
            height: 38px !important;
        }
    }
    
    /* Стили для компонентов внутри модулей */
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
    
    /* Стили для документов */
    .documents-container {
        padding: 15px 0;
    }
    
    .document-upload-section {
        margin-bottom: 20px;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 5px;
        border: 1px solid #e9ecef;
    }
    
    .document-upload-section h4 {
        margin-top: 0;
        margin-bottom: 10px;
        font-size: 18px;
        color: #333;
    }
    
    .document-upload-section p.description {
        margin-bottom: 15px;
        color: #6c757d;
        font-size: 14px;
    }
    
    .document-upload-form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .document-upload-input {
        position: absolute;
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        z-index: -1;
    }
    
    .document-upload-label {
        cursor: pointer;
        padding: 8px 16px;
        background-color: #007bff;
        color: white;
        border-radius: 4px;
        margin-right: 10px;
        font-size: 14px;
        transition: background-color 0.3s;
        display: inline-flex;
        align-items: center;
    }
    
    .document-upload-label:hover {
        background-color: #0069d9;
    }
    
    .document-upload-label i {
        margin-right: 8px;
    }
    
    .upload-info {
        display: flex;
        align-items: center;
        flex: 1;
        justify-content: space-between;
    }
    
    .selected-files-count {
        font-size: 14px;
        color: #6c757d;
    }
    
    .btn-upload-documents {
        padding: 8px 16px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        transition: background-color 0.3s;
    }
    
    .btn-upload-documents:disabled {
        background-color: #6c757d;
        cursor: not-allowed;
    }
    
    .btn-upload-documents i {
        margin-right: 8px;
    }
    
    /* СТАРЫЕ стили для прогресса УДАЛЕНЫ - используется новая система больших файлов */
    /* Новая система предоставляет собственные улучшенные стили в large-file-upload.css */
    
    .documents-list {
        margin-top: 20px;
    }
    
    .documents-list h4 {
        margin-bottom: 15px;
        font-size: 18px;
        color: #333;
    }
    
    .document-items {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }
    
    .document-item {
          padding: 10px 15px;
    background-color: #f8f9fa;
    border-radius: 4px;
    display: flex
;
    margin-bottom: 10px;
    transition: background-color 0.3s;
    flex-direction: row;
    flex-wrap: nowrap;
    }
    
    .document-item:hover {
        background-color: #e9ecef;
    }
    
    .document-item:last-child {
        margin-bottom: 0;
    }
    
    .document-link {
        display: flex;
        align-items: center;
        color: #212529;
        text-decoration: none;
    }
    
    .document-link:hover {
        color: #007bff;
    }
    
    .document-link i {
        margin-right: 10px;
        font-size: 20px;
    }
    
    .document-name {
        margin-right: 5px;
    }
    
    .document-extension {
        font-weight: bold;
        color: #6c757d;
    }
    
    .no-documents {
        text-align: center;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 5px;
        color: #6c757d;
    }
    
    /* Стили для предпросмотра документов */
    .document-preview {
        opacity: 0.7;
        background-color: #e9ecef;
    }
    
    .document-preview .document-link {
        cursor: default;
    }
    
    /* Анимации для вкладок модального окна */
    .module-visible {
        opacity: 1;
        transition: opacity 0.3s ease-in-out;
    }
    
    /* Мобильная версия для документов */
    @media (max-width: 768px) {
        .document-upload-form {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .document-upload-label {
            margin-bottom: 10px;
            width: 100%;
            text-align: center;
            justify-content: center;
        }
        
        .upload-info {
            flex-direction: column;
            align-items: flex-start;
            width: 100%;
        }
        
        .selected-files-count {
            margin-bottom: 10px;
        }
        
        .btn-upload-documents {
            width: 100%;
            justify-content: center;
        }
    }
    
    /* === СТИЛИ ДЛЯ ДИНАМИЧЕСКИХ ДОКУМЕНТОВ === */
    .documents-container .document-items {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .documents-container .document-item {
        margin-bottom: 8px;
        padding: 8px;
        background: #f8f9fa;
        border-radius: 4px;
        border-left: 3px solid var(--blue);
    }
    
    .documents-container .document-link {
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        color: #495057;
        font-size: 14px;
        transition: color 0.3s ease;
    }
    
    .documents-container .document-link:hover {
        color: var(--blue);
        text-decoration: none;
    }
    
    .documents-container .document-link i {
        color: var(--blue);
        width: 16px;
    }
    
    .documents-container .document-name {
        font-weight: 500;
    }
    
    .documents-container .document-extension {
        color: #6c757d;
        font-size: 12px;
        font-weight: normal;
    }
    
    .documents-placeholder {
        display: none !important;
    }
    
    .documents-placeholder.visible {
        display: block !important;
    }
</style>
