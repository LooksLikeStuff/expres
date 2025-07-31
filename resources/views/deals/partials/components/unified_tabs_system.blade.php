<!-- Единая система управления вкладками для модального окна сделок -->
<script>
/**
 * Упрощенная единая система управления вкладками
 * Оптимизированная версия без конфликтов и дублирования
 */

window.TabsSystem = {
    initialized: false,
    activeTab: null,
    tabs: {
        'Заказ': '#module-zakaz',
        'Работа над проектом': '#module-rabota', 
        'Финал проекта': '#module-final',
        'Документы': '#module-documents',
        'Бриф': '#module-brief'
    },
    
    /**
     * Инициализация системы
     */
    init: function() {
        if (this.initialized) {
            console.log('TabsSystem: уже инициализирована');
            return;
        }
        
        console.log('TabsSystem: инициализация единой системы');
        
        // Очищаем старые обработчики
        this.cleanup();
        
        // Устанавливаем новые обработчики
        $('.button__points button').on('click.tabSystem', this.handleTabClick.bind(this));
        
        // Показываем первую вкладку
        this.showTab('Заказ', false);
        
        this.initialized = true;
        console.log('TabsSystem: инициализация завершена');
    },
    
    /**
     * Очистка старых обработчиков и состояний
     */
    cleanup: function() {
        // Удаляем все обработчики событий
        $('.button__points button').off('click');
        
        // Сбрасываем состояние модулей
        $('.module__deal').removeClass('active module-visible show hidden fadeIn fadeOut').hide();
        
        // Сбрасываем состояние кнопок
        $('.button__points button').removeClass('buttonSealaActive active');
        
        // Удаляем инлайн стили
        $('.module__deal').removeAttr('style');
    },
    
    /**
     * Обработчик клика по кнопке вкладки
     */
    handleTabClick: function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const tabName = $(e.currentTarget).data('target');
        if (!tabName) return;
        
        console.log('TabsSystem: переключение на', tabName);
        this.showTab(tabName, true);
    },
    
    /**
     * Показать указанную вкладку
     */
    showTab: function(tabName, animate) {
        if (!this.tabs[tabName]) {
            console.error('TabsSystem: неизвестная вкладка', tabName);
            return;
        }
        
        // Обновляем кнопки
        $('.button__points button').removeClass('buttonSealaActive');
        $('.button__points button[data-target="' + tabName + '"]').addClass('buttonSealaActive');
        
        // Скрываем все модули
        $('.module__deal').removeClass('active module-visible').hide();
        
        // Показываем целевой модуль
        const targetModule = $(this.tabs[tabName]);
        if (animate !== false) {
            targetModule.addClass('active').show();
            setTimeout(() => {
                targetModule.addClass('module-visible');
            }, 50);
        } else {
            targetModule.addClass('active module-visible').show();
        }
        
        this.activeTab = tabName;
        this.onTabShown(tabName);
    },
    
    /**
     * Колбэк после показа вкладки
     */
    onTabShown: function(tabName) {
        console.log('TabsSystem: активна вкладка', tabName);
        
        // Специальная инициализация по типу вкладки
        switch(tabName) {
            case 'Документы':
                this.initDocumentsModule();
                break;
            case 'Бриф':
                this.initBriefModule();
                break;
        }
    },
    
    /**
     * Инициализация модуля документов
     */
    initDocumentsModule: function() {
        console.log('TabsSystem: инициализация модуля документов');
        
        if (typeof window.largeFileUploader === 'undefined' && typeof initLargeFileUploader === 'function') {
            try {
                initLargeFileUploader();
            } catch(e) {
                console.warn('TabsSystem: ошибка инициализации загрузчика файлов:', e);
            }
        }
    },
    
    /**
     * Инициализация модуля брифа
     */
    initBriefModule: function() {
        console.log('TabsSystem: инициализация модуля брифа');
        
        if (typeof initBriefSearch === 'function') {
            try {
                initBriefSearch();
            } catch(e) {
                console.warn('TabsSystem: ошибка инициализации поиска брифов:', e);
            }
        }
    },
    
    /**
     * Переинициализация
     */
    reinit: function() {
        console.log('TabsSystem: переинициализация');
        this.initialized = false;
        this.activeTab = null;
        this.init();
    },
    
    /**
     * Получить активную вкладку
     */
    getActiveTab: function() {
        return this.activeTab;
    }
};

// Функции для обратной совместимости
window.initTabHandlers = function() {
    window.TabsSystem.init();
};

window.showModule = function(selector) {
    for (const tabName in window.TabsSystem.tabs) {
        if (window.TabsSystem.tabs[tabName] === selector) {
            window.TabsSystem.showTab(tabName);
            return;
        }
    }
    console.warn('showModule: не найдена вкладка для селектора', selector);
};

window.showDocumentsTab = function() {
    window.TabsSystem.showTab('Документы');
};

// Автоматическая инициализация
$(document).ready(function() {
    if ($('#editModal').length > 0) {
        console.log('TabsSystem: обнаружено модальное окно, инициализация...');
        window.TabsSystem.init();
    }
});

// Инициализация после AJAX-загрузки модального окна
$(document).ajaxComplete(function(event, xhr, settings) {
    if (settings.url && settings.url.includes('/deal/') && settings.url.includes('/modal')) {
        console.log('TabsSystem: AJAX завершен, переинициализация...');
        setTimeout(function() {
            if ($('#editModal').is(':visible')) {
                window.TabsSystem.reinit();
            }
        }, 200);
    }
});

</script>
