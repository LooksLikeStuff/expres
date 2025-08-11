/**
 * Унифицированная система ссылок Яндекс.Диска v5.0
 * Объединяет и заменяет все предыдущие версии
 * Автор: AI Assistant
 * Дата: 2025-08-04
 */

class YandexUnifiedLinkSystem {
    constructor() {
        this.initialized = false;
        this.fileFields = [
            'work_act', 'chat_screenshot', 'plan_final', 'final_collage', 
            'measurements_file', 'final_floorplan', 'final_project_file', 
            'archicad_file', 'contract_attachment', 'execution_order_file',
            'screenshot_work_1', 'screenshot_work_2', 'screenshot_work_3', 
            'screenshot_work_4', 'screenshot_work_5', 'screenshot_final'
        ];
        this.debug = true;
        
        this.log('🔧 Инициализация унифицированной системы ссылок Яндекс.Диска v5.0');
    }

    /**
     * Логирование с префиксом
     */
    log(message, data = null) {
        if (this.debug) {
            if (data) {
                console.log(`[YandexLinks] ${message}`, data);
            } else {
                console.log(`[YandexLinks] ${message}`);
            }
        }
    }

    /**
     * Инициализация системы
     */
    init() {
        if (this.initialized) {
            this.log('⚠️ Система уже инициализирована');
            return;
        }

        this.bindEvents();
        this.initialized = true;
        this.log('✅ Система инициализирована');
    }

    /**
     * Привязка событий
     */
    bindEvents() {
        const self = this;

        // Событие обновления сделки
        $(document).on('dealUpdated', function(event, dealData) {
            self.log('📝 Получено событие dealUpdated');
            self.updateAllFileLinks(dealData);
        });

        // Событие завершения загрузки файла
        $(document).on('fileUploadComplete', function(event, data) {
            self.log('📤 Получено событие fileUploadComplete', data);
            if (data.field && data.yandexUrl) {
                self.updateFileLink(data.field, data.yandexUrl, data.originalName || 'Просмотр файла');
            }
        });

        // Событие открытия модального окна
        $('#editModal').on('shown.bs.modal', function() {
            self.log('🔄 Модальное окно открыто, обновляем ссылки');
            setTimeout(() => self.forceUpdateFromModal(), 300);
        });

        // Событие загрузки модального окна через AJAX
        $(document).on('dealModalLoaded', function(event, dealData) {
            self.log('🔄 Получено событие dealModalLoaded');
            if (dealData) {
                self.updateAllFileLinks(dealData);
            } else {
                self.forceUpdateFromModal();
            }
        });

        // Событие переключения на вкладку "Финал проекта"
        $(document).on('click', '.button__points button[data-target="Финал проекта"]', function() {
            setTimeout(() => {
                self.log('📂 Переключение на вкладку "Финал проекта"');
                self.forceUpdateFromModal();
            }, 500);
        });

        this.log('🔗 События привязаны');
    }

    /**
     * Обновление всех файловых ссылок из данных сделки
     */
    updateAllFileLinks(dealData) {
        if (!dealData) {
            this.log('⚠️ Данные сделки не переданы');
            return;
        }

        this.log('🔄 Обновляем все файловые ссылки', dealData);

        this.fileFields.forEach(fieldName => {
            const yandexUrlField = `yandex_url_${fieldName}`;
            const originalNameField = `original_name_${fieldName}`;
            
            const yandexUrl = dealData[yandexUrlField];
            const originalName = dealData[originalNameField] || 'Просмотр файла';

            if (yandexUrl && yandexUrl.trim() !== '') {
                this.updateFileLink(fieldName, yandexUrl, originalName);
            }
        });
    }

    /**
     * Обновление ссылки для конкретного поля
     */
    updateFileLink(fieldName, yandexUrl, originalName = 'Просмотр файла') {
        this.log(`🔗 Обновляем ссылку для поля ${fieldName}`, { yandexUrl, originalName });

        const $container = this.findFileContainer(fieldName);
        if (!$container || $container.length === 0) {
            this.log(`⚠️ Контейнер для поля ${fieldName} не найден`);
            return false;
        }

        // Удаляем существующие ссылки
        this.removeExistingLinks($container);

        if (yandexUrl && yandexUrl.trim() !== '') {
            this.createFileLink($container, yandexUrl, originalName);
            this.log(`✅ Ссылка для поля ${fieldName} создана`);
            return true;
        }

        return false;
    }

    /**
     * Поиск контейнера файла
     */
    findFileContainer(fieldName) {
        const selectors = [
            `input[name="${fieldName}"]`,
            `.file-upload-container[data-field="${fieldName}"]`,
            `#upload-status-${fieldName}`,
            `[data-field="${fieldName}"]`,
            `#${fieldName}_container`,
            `.form-group-deal input[name="${fieldName}"]`
        ];

        for (const selector of selectors) {
            const $element = $(selector);
            if ($element.length > 0) {
                if (selector.includes('input')) {
                    // Если это input, возвращаем родительский контейнер
                    const $parent = $element.closest('.form-group-deal, .file-upload-container, .enhanced-upload');
                    return $parent.length > 0 ? $parent : $element.parent();
                } else {
                    return $element.first();
                }
            }
        }

        return null;
    }

    /**
     * Удаление существующих ссылок
     */
    removeExistingLinks($container) {
        $container.find('.file-success, .yandex-file-link, .file-link, .upload-status, .file-link-container').remove();
    }

    /**
     * Создание ссылки на файл
     */
    createFileLink($container, yandexUrl, originalName) {
        const fileSuccessHtml = `
            <div class="file-success yandex-unified-link" style="margin-top: 8px; padding: 12px; background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-left: 4px solid #28a745; border-radius: 8px; box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15); animation: slideInUp 0.4s ease-out;">
                <div class="success-info" style="display: flex; align-items: center;">
                    <div class="file-icon" style="margin-right: 12px;">
                        <i class="fas fa-cloud text-success" style="font-size: 1.3em; animation: pulse 2s infinite;"></i>
                    </div>
                    <div class="file-details" style="flex: 1;">
                        <div class="file-link">
                            <a href="${yandexUrl}" target="_blank" class="yandex-file-link" style="color: #28a745; text-decoration: none; font-weight: 600; font-size: 0.95em; transition: all 0.3s ease; display: inline-block;">
                                <i class="fas fa-external-link-alt" style="margin-right: 6px;"></i>${originalName}
                            </a>
                        </div>
                        <div class="file-description" style="font-size: 0.8em; color: #6c757d; margin-top: 3px;">
                            <i class="fab fa-yandex" style="margin-right: 4px;"></i>Файл на Яндекс.Диске
                        </div>
                    </div>
                </div>
            </div>
        `;

        $container.append(fileSuccessHtml);

        // Добавляем hover эффект
        $container.find('.yandex-file-link').hover(
            function() { $(this).css('color', '#1e7e34'); },
            function() { $(this).css('color', '#28a745'); }
        );
    }

    /**
     * Принудительное обновление из модального окна
     */
    forceUpdateFromModal() {
        this.log('🔄 Принудительное обновление ссылок из модального окна');

        const dealId = $('#dealIdField').val();
        if (!dealId) {
            this.log('⚠️ ID сделки не найден');
            return;
        }

        // Пытаемся получить данные из глобальной переменной
        if (window.currentDealData) {
            this.updateAllFileLinks(window.currentDealData);
            return;
        }

        // Получаем данные через AJAX
        $.get(`/deal/${dealId}/data`)
            .done((response) => {
                if (response.success && response.deal) {
                    this.updateAllFileLinks(response.deal);
                    window.currentDealData = response.deal; // Кэшируем данные
                } else {
                    this.log('⚠️ Некорректный ответ сервера', response);
                }
            })
            .fail(() => {
                this.log('❌ Ошибка загрузки данных сделки');
            });
    }

    /**
     * Диагностика контейнеров (для отладки)
     */
    debugContainers(fieldName) {
        this.log(`🔍 Диагностика контейнеров для поля: ${fieldName}`);
        
        const selectors = [
            `input[name="${fieldName}"]`,
            `.file-upload-container[data-field="${fieldName}"]`,
            `#upload-status-${fieldName}`,
            `[data-field="${fieldName}"]`,
            `#${fieldName}_container`,
            `.form-group-deal input[name="${fieldName}"]`
        ];
        
        selectors.forEach(selector => {
            const $elements = $(selector);
            if ($elements.length > 0) {
                this.log(`✅ Найден: ${selector} (${$elements.length} элементов)`);
            } else {
                this.log(`❌ Не найден: ${selector}`);
            }
        });
    }

    /**
     * Очистка системы
     */
    destroy() {
        $(document).off('dealUpdated');
        $(document).off('fileUploadComplete');
        $(document).off('dealModalLoaded');
        $('#editModal').off('shown.bs.modal');
        
        $('.yandex-unified-link').remove();
        
        this.initialized = false;
        this.log('🗑️ Система очищена');
    }
}

// CSS стили для анимации
const unifiedStyles = `
<style>
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.yandex-unified-link:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.25) !important;
}

.yandex-unified-link .yandex-file-link:hover {
    text-decoration: underline !important;
}
</style>
`;

// Добавляем стили в head
if (!document.querySelector('#yandex-unified-styles')) {
    const styleElement = document.createElement('div');
    styleElement.id = 'yandex-unified-styles';
    styleElement.innerHTML = unifiedStyles;
    document.head.appendChild(styleElement);
}

// Инициализация глобального объекта
window.yandexLinkSystem = new YandexUnifiedLinkSystem();

// Автоматическая инициализация при загрузке DOM
$(document).ready(function() {
    if (!window.yandexLinkSystem.initialized) {
        window.yandexLinkSystem.init();
    }
});

// Глобальные функции для обратной совместимости
window.updateYandexFileLink = function(fieldName, yandexUrl, originalName) {
    return window.yandexLinkSystem.updateFileLink(fieldName, yandexUrl, originalName);
};

window.updateFileLinksInDealModal = function(dealData) {
    return window.yandexLinkSystem.updateAllFileLinks(dealData);
};

window.forceUpdateFileLinks = function() {
    return window.yandexLinkSystem.forceUpdateFromModal();
};

console.log('🚀 Унифицированная система ссылок Яндекс.Диска v5.0 загружена');
