/**
 * Улучшенная система загрузки библиотек для исправления ошибок
 * Автор: AI Assistant
 * Дата: 2025-08-05
 */

(function() {
    'use strict';
    
    console.log('🔧 Загружается улучшенная система загрузки библиотек...');

    // Глобальный объект для отслеживания состояния библиотек
    window.LibrariesManager = {
        loaded: {
            jquery: typeof $ !== 'undefined' && typeof jQuery !== 'undefined',
            datatables: false,
            select2: false
        },
        callbacks: [],
        
        /**
         * Проверка загрузки всех библиотек
         */
        checkAll: function() {
            this.loaded.jquery = typeof $ !== 'undefined' && typeof jQuery !== 'undefined';
            this.loaded.datatables = this.loaded.jquery && typeof $.fn.DataTable !== 'undefined';
            this.loaded.select2 = this.loaded.jquery && typeof $.fn.select2 !== 'undefined';
            
            const allLoaded = this.loaded.jquery && this.loaded.datatables && this.loaded.select2;
            
            if (allLoaded) {
                console.log('✅ Все библиотеки загружены успешно');
                this.executeCallbacks();
            }
            
            return allLoaded;
        },
        
        /**
         * Добавление callback-функции
         */
        onReady: function(callback) {
            if (this.checkAll()) {
                callback();
            } else {
                this.callbacks.push(callback);
            }
        },
        
        /**
         * Выполнение всех callback-функций
         */
        executeCallbacks: function() {
            while (this.callbacks.length > 0) {
                const callback = this.callbacks.shift();
                try {
                    callback();
                } catch (error) {
                    console.error('❌ Ошибка в callback:', error);
                }
            }
        },
        
        /**
         * Принудительная загрузка всех библиотек
         */
        loadAll: function() {
            console.log('🔄 Принудительная загрузка всех библиотек...');
            
            const libraries = [
                {
                    name: 'jQuery',
                    test: () => typeof $ !== 'undefined' && typeof jQuery !== 'undefined',
                    css: null,
                    js: 'https://code.jquery.com/jquery-3.6.0.min.js'
                },
                {
                    name: 'DataTables',
                    test: () => typeof $.fn.DataTable !== 'undefined',
                    css: 'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css',
                    js: 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js',
                    depends: ['jQuery']
                },
                {
                    name: 'Select2',
                    test: () => typeof $.fn.select2 !== 'undefined',
                    css: 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css',
                    js: 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
                    depends: ['jQuery']
                }
            ];
            
            this.loadLibrariesSequentially(libraries);
        },
        
        /**
         * Последовательная загрузка библиотек с учетом зависимостей
         */
        loadLibrariesSequentially: function(libraries) {
            let currentIndex = 0;
            const self = this;
            
            function loadNext() {
                if (currentIndex >= libraries.length) {
                    console.log('🎉 Все библиотеки загружены');
                    self.checkAll();
                    return;
                }
                
                const lib = libraries[currentIndex];
                currentIndex++;
                
                if (lib.test()) {
                    console.log(`✅ ${lib.name} уже загружена`);
                    loadNext();
                    return;
                }
                
                console.log(`🔄 Загружаем ${lib.name}...`);
                
                // Загружаем CSS если есть
                if (lib.css) {
                    self.loadCSS(lib.css);
                }
                
                // Загружаем JS
                self.loadJS(lib.js, function() {
                    console.log(`✅ ${lib.name} загружена успешно`);
                    loadNext();
                }, function() {
                    console.error(`❌ Ошибка загрузки ${lib.name}`);
                    loadNext(); // Продолжаем даже при ошибке
                });
            }
            
            loadNext();
        },
        
        /**
         * Загрузка CSS файла
         */
        loadCSS: function(url) {
            if (document.querySelector(`link[href="${url}"]`)) {
                return; // Уже загружен
            }
            
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = url;
            document.head.appendChild(link);
        },
        
        /**
         * Загрузка JS файла
         */
        loadJS: function(url, onSuccess, onError) {
            const script = document.createElement('script');
            script.src = url;
            script.onload = onSuccess || function() {};
            script.onerror = onError || function() {};
            document.head.appendChild(script);
        }
    };
    
    // Автоматическая проверка при загрузке
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                window.LibrariesManager.checkAll();
            }, 100);
        });
    } else {
        setTimeout(function() {
            window.LibrariesManager.checkAll();
        }, 100);
    }
    
    console.log('✅ Система загрузки библиотек инициализирована');
})();
