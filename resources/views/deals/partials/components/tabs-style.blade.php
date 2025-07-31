<!-- Стили для переструктурированных компонентов документов и брифов -->
<style>
/* === ДОКУМЕНТЫ === */
.upload-section {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.upload-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    min-height: 45px;
    border: 2px dashed #ddd;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.upload-label:hover {
    border-color: var(--blue);
    background: #f0f7ff;
    color: var(--blue);
}

.upload-label i {
    font-size: 1.2em;
}

.upload-text {
    flex-grow: 1;
    font-weight: 500;
}

.files-count {
    font-size: 0.9em;
    color: #6c757d;
}

.upload-btn {
    align-self: flex-start;
    min-width: auto;
    opacity: 0.6;
    cursor: not-allowed;
}

.upload-btn:not([disabled]) {
    opacity: 1;
    cursor: pointer;
}

.upload-info {
    margin-top: 10px;
    padding: 8px 12px;
    background: #e8f4fd;
    border-radius: 4px;
    border-left: 3px solid var(--blue);
}

.upload-info small {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #495057;
}

/* Сетка документов */
.documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.document-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.document-card:hover {
    border-color: var(--blue);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.document-link {
    display: flex;
    align-items: center;
    padding: 12px;
    text-decoration: none;
    color: inherit;
    gap: 12px;
}

.document-link:hover {
    text-decoration: none;
    color: var(--blue);
}

.document-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 6px;
    font-size: 1.5em;
    color: var(--blue);
}

.document-info {
    flex-grow: 1;
    min-width: 0;
}

.document-name {
    display: block;
    font-weight: 500;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.document-size {
    color: #6c757d;
    font-size: 0.85em;
}

/* === БРИФ === */
.search-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    color: #495057;
    margin-bottom: 0;
}

.form-label i {
    color: var(--blue);
}

.brief-actions {
    display: flex;
    gap: 10px;
}

.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 20px;
    text-align: center;
}

.spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid var(--blue);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.results-container {
    margin-top: 15px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.results-header h5 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1em;
}

.btn-close {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.btn-close:hover {
    background: #e9ecef;
    color: #495057;
}

.results-list {
    max-height: 300px;
    overflow-y: auto;
    padding: 10px;
}

.notification {
    margin-top: 15px;
    padding: 12px;
    border-radius: 6px;
    border-left: 4px solid;
}

.notification.success {
    background: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.notification.error {
    background: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
}

.notification.info {
    background: #d1ecf1;
    border-color: #17a2b8;
    color: #0c5460;
}

/* === АДАПТИВНОСТЬ === */
@media (max-width: 768px) {
    .documents-grid {
        grid-template-columns: 1fr;
    }
    
    .document-link {
        padding: 10px;
    }
    
    .document-icon {
        width: 35px;
        height: 35px;
        font-size: 1.3em;
    }
    
    .brief-actions {
        flex-direction: column;
    }
}

/* === СОСТОЯНИЯ === */
.text-center {
    text-align: center;
}

.text-muted {
    color: #6c757d !important;
}

/* Переопределение стандартных переменных для совместимости */
:root {
    --blue: #007bff;
    --green: #28a745;
    --red: #dc3545;
    --orange: #fd7e14;
}

/* Обеспечение правильного отображения в модальном окне */
.modal .documents-grid {
    margin-top: 10px;
}

.modal .document-card {
    background: #f8f9fa;
}

.modal .upload-label {
    background: #fff;
}
</style>
