/**
 * Расширенная система валидации форматов файлов для загрузки
 * Поддерживает максимальное количество форматов для различных типов контента
 */
window.EXTENDED_FILE_FORMATS = {
    // Документы и текстовые файлы
    documents: {
        'pdf': { 
            mime: ['application/pdf'], 
            icon: '📄', 
            description: 'PDF документы' 
        },
        'doc': { 
            mime: ['application/msword'], 
            icon: '📝', 
            description: 'Microsoft Word (старый)' 
        },
        'docx': { 
            mime: ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'], 
            icon: '📝', 
            description: 'Microsoft Word' 
        },
        'odt': { 
            mime: ['application/vnd.oasis.opendocument.text'], 
            icon: '📝', 
            description: 'OpenDocument Text' 
        },
        'rtf': { 
            mime: ['application/rtf', 'text/rtf'], 
            icon: '📝', 
            description: 'Rich Text Format' 
        },
        'txt': { 
            mime: ['text/plain'], 
            icon: '📄', 
            description: 'Текстовые файлы' 
        },
        'md': { 
            mime: ['text/markdown', 'text/x-markdown'], 
            icon: '📄', 
            description: 'Markdown файлы' 
        }
    },

    // Таблицы и данные
    spreadsheets: {
        'xls': { 
            mime: ['application/vnd.ms-excel'], 
            icon: '📊', 
            description: 'Microsoft Excel (старый)' 
        },
        'xlsx': { 
            mime: ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'], 
            icon: '📊', 
            description: 'Microsoft Excel' 
        },
        'ods': { 
            mime: ['application/vnd.oasis.opendocument.spreadsheet'], 
            icon: '📊', 
            description: 'OpenDocument Spreadsheet' 
        },
        'csv': { 
            mime: ['text/csv', 'application/csv'], 
            icon: '📊', 
            description: 'CSV данные' 
        },
        'tsv': { 
            mime: ['text/tab-separated-values'], 
            icon: '📊', 
            description: 'TSV данные' 
        }
    },

    // Презентации
    presentations: {
        'ppt': { 
            mime: ['application/vnd.ms-powerpoint'], 
            icon: '📽️', 
            description: 'PowerPoint (старый)' 
        },
        'pptx': { 
            mime: ['application/vnd.openxmlformats-officedocument.presentationml.presentation'], 
            icon: '📽️', 
            description: 'PowerPoint' 
        },
        'odp': { 
            mime: ['application/vnd.oasis.opendocument.presentation'], 
            icon: '📽️', 
            description: 'OpenDocument Presentation' 
        }
    },

    // Изображения (все возможные форматы)
    images: {
        'jpg': { 
            mime: ['image/jpeg'], 
            icon: '🖼️', 
            description: 'JPEG изображения' 
        },
        'jpeg': { 
            mime: ['image/jpeg'], 
            icon: '🖼️', 
            description: 'JPEG изображения' 
        },
        'png': { 
            mime: ['image/png'], 
            icon: '🖼️', 
            description: 'PNG изображения' 
        },
        'gif': { 
            mime: ['image/gif'], 
            icon: '🎞️', 
            description: 'GIF анимации' 
        },
        'bmp': { 
            mime: ['image/bmp', 'image/x-ms-bmp'], 
            icon: '🖼️', 
            description: 'BMP изображения' 
        },
        'webp': { 
            mime: ['image/webp'], 
            icon: '🖼️', 
            description: 'WebP изображения' 
        },
        'svg': { 
            mime: ['image/svg+xml'], 
            icon: '🎨', 
            description: 'SVG векторная графика' 
        },
        'tiff': { 
            mime: ['image/tiff', 'image/tif'], 
            icon: '🖼️', 
            description: 'TIFF изображения' 
        },
        'tif': { 
            mime: ['image/tiff', 'image/tif'], 
            icon: '🖼️', 
            description: 'TIFF изображения' 
        },
        'ico': { 
            mime: ['image/x-icon', 'image/vnd.microsoft.icon'], 
            icon: '🔲', 
            description: 'Иконки' 
        }
    },

    // Профессиональная графика и дизайн
    design: {
        'psd': { 
            mime: ['application/x-photoshop', 'image/vnd.adobe.photoshop'], 
            icon: '🎨', 
            description: 'Adobe Photoshop' 
        },
        'ai': { 
            mime: ['application/postscript', 'application/illustrator'], 
            icon: '🎨', 
            description: 'Adobe Illustrator' 
        },
        'eps': { 
            mime: ['application/postscript'], 
            icon: '🎨', 
            description: 'Encapsulated PostScript' 
        },
        'indd': { 
            mime: ['application/x-indesign'], 
            icon: '📖', 
            description: 'Adobe InDesign' 
        },
        'sketch': { 
            mime: ['application/x-sketch'], 
            icon: '🎨', 
            description: 'Sketch дизайн' 
        },
        'fig': { 
            mime: ['application/x-figma'], 
            icon: '🎨', 
            description: 'Figma дизайн' 
        },
        'xd': { 
            mime: ['application/vnd.adobe.xd'], 
            icon: '🎨', 
            description: 'Adobe XD' 
        }
    },

    // CAD и архитектурные файлы
    cad: {
        'dwg': { 
            mime: ['application/acad', 'image/vnd.dwg'], 
            icon: '📐', 
            description: 'AutoCAD чертежи' 
        },
        'dxf': { 
            mime: ['application/dxf', 'image/vnd.dxf'], 
            icon: '📐', 
            description: 'AutoCAD DXF' 
        },
        'dwf': { 
            mime: ['application/x-dwf'], 
            icon: '📐', 
            description: 'Design Web Format' 
        },
        'pln': { 
            mime: ['application/x-archicad'], 
            icon: '🏗️', 
            description: 'ArchiCAD проекты' 
        },
        'rvt': { 
            mime: ['application/x-revit'], 
            icon: '🏗️', 
            description: 'Autodesk Revit' 
        },
        'ifc': { 
            mime: ['application/x-step', 'model/ifc'], 
            icon: '🏗️', 
            description: 'Industry Foundation Classes' 
        },
        'step': { 
            mime: ['application/step'], 
            icon: '📐', 
            description: 'STEP 3D модели' 
        },
        'stp': { 
            mime: ['application/step'], 
            icon: '📐', 
            description: 'STEP 3D модели' 
        }
    },

    // 3D модели и анимация
    models3d: {
        '3ds': { 
            mime: ['application/x-3ds'], 
            icon: '🎭', 
            description: '3D Studio Max' 
        },
        'max': { 
            mime: ['application/x-3dsmax'], 
            icon: '🎭', 
            description: '3ds Max файлы' 
        },
        'obj': { 
            mime: ['application/x-tgif'], 
            icon: '🎭', 
            description: 'Wavefront OBJ' 
        },
        'fbx': { 
            mime: ['application/x-fbx'], 
            icon: '🎭', 
            description: 'Autodesk FBX' 
        },
        'dae': { 
            mime: ['model/vnd.collada+xml'], 
            icon: '🎭', 
            description: 'COLLADA 3D' 
        },
        'blend': { 
            mime: ['application/x-blender'], 
            icon: '🎭', 
            description: 'Blender проекты' 
        },
        'ma': { 
            mime: ['application/x-maya'], 
            icon: '🎭', 
            description: 'Maya ASCII' 
        },
        'mb': { 
            mime: ['application/x-maya-binary'], 
            icon: '🎭', 
            description: 'Maya Binary' 
        },
        'c4d': { 
            mime: ['application/x-cinema4d'], 
            icon: '🎭', 
            description: 'Cinema 4D' 
        },
        'skp': { 
            mime: ['application/x-sketchup'], 
            icon: '🎭', 
            description: 'SketchUp' 
        }
    },

    // Видео файлы
    videos: {
        'mp4': { 
            mime: ['video/mp4'], 
            icon: '🎬', 
            description: 'MP4 видео' 
        },
        'avi': { 
            mime: ['video/x-msvideo'], 
            icon: '🎬', 
            description: 'AVI видео' 
        },
        'mov': { 
            mime: ['video/quicktime'], 
            icon: '🎬', 
            description: 'QuickTime видео' 
        },
        'wmv': { 
            mime: ['video/x-ms-wmv'], 
            icon: '🎬', 
            description: 'Windows Media Video' 
        },
        'flv': { 
            mime: ['video/x-flv'], 
            icon: '🎬', 
            description: 'Flash Video' 
        },
        'webm': { 
            mime: ['video/webm'], 
            icon: '🎬', 
            description: 'WebM видео' 
        },
        'mkv': { 
            mime: ['video/x-matroska'], 
            icon: '🎬', 
            description: 'Matroska Video' 
        },
        'mpg': { 
            mime: ['video/mpeg'], 
            icon: '🎬', 
            description: 'MPEG видео' 
        },
        'mpeg': { 
            mime: ['video/mpeg'], 
            icon: '🎬', 
            description: 'MPEG видео' 
        },
        '3gp': { 
            mime: ['video/3gpp'], 
            icon: '📱', 
            description: '3GPP мобильное видео' 
        }
    },

    // Аудио файлы
    audio: {
        'mp3': { 
            mime: ['audio/mpeg'], 
            icon: '🎵', 
            description: 'MP3 аудио' 
        },
        'wav': { 
            mime: ['audio/wav', 'audio/x-wav'], 
            icon: '🎵', 
            description: 'WAV аудио' 
        },
        'flac': { 
            mime: ['audio/flac'], 
            icon: '🎵', 
            description: 'FLAC аудио' 
        },
        'aac': { 
            mime: ['audio/aac'], 
            icon: '🎵', 
            description: 'AAC аудио' 
        },
        'ogg': { 
            mime: ['audio/ogg'], 
            icon: '🎵', 
            description: 'OGG аудио' 
        },
        'm4a': { 
            mime: ['audio/m4a'], 
            icon: '🎵', 
            description: 'M4A аудио' 
        },
        'wma': { 
            mime: ['audio/x-ms-wma'], 
            icon: '🎵', 
            description: 'Windows Media Audio' 
        }
    },

    // Архивы и сжатые файлы
    archives: {
        'zip': { 
            mime: ['application/zip'], 
            icon: '📦', 
            description: 'ZIP архивы' 
        },
        'rar': { 
            mime: ['application/x-rar-compressed'], 
            icon: '📦', 
            description: 'RAR архивы' 
        },
        '7z': { 
            mime: ['application/x-7z-compressed'], 
            icon: '📦', 
            description: '7-Zip архивы' 
        },
        'tar': { 
            mime: ['application/x-tar'], 
            icon: '📦', 
            description: 'TAR архивы' 
        },
        'gz': { 
            mime: ['application/gzip'], 
            icon: '📦', 
            description: 'GZIP архивы' 
        },
        'bz2': { 
            mime: ['application/x-bzip2'], 
            icon: '📦', 
            description: 'BZIP2 архивы' 
        },
        'xz': { 
            mime: ['application/x-xz'], 
            icon: '📦', 
            description: 'XZ архивы' 
        }
    },

    // Программные файлы
    code: {
        'js': { 
            mime: ['application/javascript', 'text/javascript'], 
            icon: '💻', 
            description: 'JavaScript файлы' 
        },
        'css': { 
            mime: ['text/css'], 
            icon: '🎨', 
            description: 'CSS стили' 
        },
        'html': { 
            mime: ['text/html'], 
            icon: '🌐', 
            description: 'HTML страницы' 
        },
        'php': { 
            mime: ['application/x-php', 'text/x-php'], 
            icon: '💻', 
            description: 'PHP скрипты' 
        },
        'py': { 
            mime: ['text/x-python'], 
            icon: '🐍', 
            description: 'Python скрипты' 
        },
        'java': { 
            mime: ['text/x-java-source'], 
            icon: '☕', 
            description: 'Java исходники' 
        },
        'cpp': { 
            mime: ['text/x-c++src'], 
            icon: '💻', 
            description: 'C++ исходники' 
        },
        'c': { 
            mime: ['text/x-csrc'], 
            icon: '💻', 
            description: 'C исходники' 
        },
        'json': { 
            mime: ['application/json'], 
            icon: '📋', 
            description: 'JSON данные' 
        },
        'xml': { 
            mime: ['application/xml', 'text/xml'], 
            icon: '📋', 
            description: 'XML данные' 
        },
        'sql': { 
            mime: ['application/sql'], 
            icon: '🗄️', 
            description: 'SQL скрипты' 
        }
    },

    // Специальные и прочие форматы
    special: {
        'eml': { 
            mime: ['message/rfc822'], 
            icon: '📧', 
            description: 'Email файлы' 
        },
        'msg': { 
            mime: ['application/vnd.ms-outlook'], 
            icon: '📧', 
            description: 'Outlook сообщения' 
        },
        'vcf': { 
            mime: ['text/vcard'], 
            icon: '👤', 
            description: 'Контакты vCard' 
        },
        'ics': { 
            mime: ['text/calendar'], 
            icon: '📅', 
            description: 'Календарные события' 
        },
        'torrent': { 
            mime: ['application/x-bittorrent'], 
            icon: '🌐', 
            description: 'BitTorrent файлы' 
        },
        'iso': { 
            mime: ['application/x-iso9660-image'], 
            icon: '💿', 
            description: 'ISO образы дисков' 
        },
        'dmg': { 
            mime: ['application/x-apple-diskimage'], 
            icon: '💿', 
            description: 'macOS образы дисков' 
        }
    }
};

/**
 * Класс расширенной валидации файлов
 */
class ExtendedFileValidator {
    constructor() {
        this.allFormats = this.combineAllFormats();
        this.maxFileSize = 500 * 1024 * 1024; // 500MB
        this.allowedCategories = ['all']; // По умолчанию все категории
    }

    /**
     * Объединение всех форматов в один объект
     */
    combineAllFormats() {
        const combined = {};
        Object.values(window.EXTENDED_FILE_FORMATS).forEach(category => {
            Object.assign(combined, category);
        });
        return combined;
    }

    /**
     * Получение списка всех расширений
     */
    getAllExtensions() {
        return Object.keys(this.allFormats);
    }

    /**
     * Получение списка расширений по категориям
     */
    getExtensionsByCategories(categories = ['all']) {
        if (categories.includes('all')) {
            return this.getAllExtensions();
        }

        const extensions = [];
        categories.forEach(category => {
            if (window.EXTENDED_FILE_FORMATS[category]) {
                extensions.push(...Object.keys(window.EXTENDED_FILE_FORMATS[category]));
            }
        });
        return [...new Set(extensions)]; // Убираем дубликаты
    }

    /**
     * Получение списка MIME типов
     */
    getAllMimeTypes() {
        const mimeTypes = [];
        Object.values(this.allFormats).forEach(format => {
            mimeTypes.push(...format.mime);
        });
        return [...new Set(mimeTypes)]; // Убираем дубликаты
    }

    /**
     * Валидация файла
     */
    validateFile(file, allowedCategories = ['all']) {
        const result = {
            valid: false,
            error: null,
            warning: null,
            fileInfo: {
                name: file.name,
                size: file.size,
                type: file.type,
                extension: this.getFileExtension(file.name),
                icon: '📄',
                description: 'Неизвестный тип файла'
            }
        };

        // Проверка размера файла
        if (file.size > this.maxFileSize) {
            result.error = `Файл слишком большой. Максимальный размер: ${this.formatFileSize(this.maxFileSize)}`;
            return result;
        }

        if (file.size === 0) {
            result.error = 'Файл пустой';
            return result;
        }

        // Получение расширения файла
        const extension = this.getFileExtension(file.name).toLowerCase();
        if (!extension) {
            result.error = 'Файл без расширения';
            return result;
        }

        // Проверка разрешенных расширений
        const allowedExtensions = this.getExtensionsByCategories(allowedCategories);
        if (!allowedExtensions.includes(extension)) {
            result.error = `Недопустимый формат файла. Разрешены: ${allowedExtensions.slice(0, 10).join(', ')}${allowedExtensions.length > 10 ? '...' : ''}`;
            return result;
        }

        // Получение информации о формате
        const formatInfo = this.allFormats[extension];
        if (formatInfo) {
            result.fileInfo.icon = formatInfo.icon;
            result.fileInfo.description = formatInfo.description;

            // Проверка MIME типа
            if (file.type && !formatInfo.mime.includes(file.type)) {
                result.warning = `MIME тип не соответствует расширению файла`;
            }
        }

        result.valid = true;
        return result;
    }

    /**
     * Получение расширения файла
     */
    getFileExtension(filename) {
        return filename.split('.').pop() || '';
    }

    /**
     * Форматирование размера файла
     */
    formatFileSize(bytes) {
        const sizes = ['Байт', 'КБ', 'МБ', 'ГБ'];
        if (bytes === 0) return '0 Байт';
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
    }

    /**
     * Получение accept атрибута для input файла
     */
    getAcceptAttribute(categories = ['all']) {
        const extensions = this.getExtensionsByCategories(categories);
        return extensions.map(ext => `.${ext}`).join(',');
    }

    /**
     * Получение HTML списка поддерживаемых форматов
     */
    getFormatsListHTML(categories = ['all']) {
        const categoriesToShow = categories.includes('all') ? 
            Object.keys(window.EXTENDED_FILE_FORMATS) : categories;

        let html = '<div class="supported-formats-list">';
        
        categoriesToShow.forEach(categoryKey => {
            const category = window.EXTENDED_FILE_FORMATS[categoryKey];
            if (!category) return;

            html += `<div class="format-category">`;
            html += `<h6 class="category-title">${this.getCategoryTitle(categoryKey)}</h6>`;
            html += `<div class="format-items">`;
            
            Object.entries(category).forEach(([ext, info]) => {
                html += `<span class="format-item" title="${info.description}">`;
                html += `${info.icon} .${ext}`;
                html += `</span>`;
            });
            
            html += `</div></div>`;
        });
        
        html += '</div>';
        return html;
    }

    /**
     * Получение заголовка категории
     */
    getCategoryTitle(categoryKey) {
        const titles = {
            'documents': '📄 Документы',
            'spreadsheets': '📊 Таблицы',
            'presentations': '📽️ Презентации',
            'images': '🖼️ Изображения',
            'design': '🎨 Дизайн',
            'cad': '📐 CAD файлы',
            'models3d': '🎭 3D модели',
            'videos': '🎬 Видео',
            'audio': '🎵 Аудио',
            'archives': '📦 Архивы',
            'code': '💻 Код',
            'special': '🔧 Специальные'
        };
        return titles[categoryKey] || categoryKey;
    }

    /**
     * Установка максимального размера файла
     */
    setMaxFileSize(sizeInBytes) {
        this.maxFileSize = sizeInBytes;
    }
}

// Создание глобального экземпляра валидатора
window.extendedFileValidator = new ExtendedFileValidator();

// Функция для обновления существующих систем загрузки файлов
function updateFileUploadSystems() {
    console.log('🔄 [ExtendedFileValidator] Обновление систем загрузки файлов...');

    // Обновляем все input[type="file"]
    document.querySelectorAll('input[type="file"]').forEach(input => {
        if (!input.hasAttribute('data-extended-validation')) {
            // Устанавливаем расширенный accept
            const accept = window.extendedFileValidator.getAcceptAttribute();
            input.setAttribute('accept', accept);
            input.setAttribute('data-extended-validation', 'true');

            // Добавляем обработчик валидации
            input.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                files.forEach(file => {
                    const validation = window.extendedFileValidator.validateFile(file);
                    if (!validation.valid) {
                        console.error('❌ [FileValidation]', validation.error);
                        alert(`Ошибка валидации файла "${file.name}": ${validation.error}`);
                        e.target.value = ''; // Очищаем поле
                    } else {
                        console.log('✅ [FileValidation]', `Файл "${file.name}" (${validation.fileInfo.description}) прошел валидацию`);
                        if (validation.warning) {
                            console.warn('⚠️ [FileValidation]', validation.warning);
                        }
                    }
                });
            });

            console.log('✅ [ExtendedFileValidator] Обновлен input:', input);
        }
    });

    // Обновляем drag-and-drop зоны
    document.querySelectorAll('[data-drag-drop="true"], .drag-drop-zone').forEach(zone => {
        if (!zone.hasAttribute('data-extended-validation')) {
            zone.setAttribute('data-extended-validation', 'true');

            // Переопределяем обработчики drop события
            const originalDropHandler = zone.ondrop;
            zone.ondrop = function(e) {
                e.preventDefault();
                const files = Array.from(e.dataTransfer.files);
                
                // Валидируем каждый файл
                const validFiles = [];
                files.forEach(file => {
                    const validation = window.extendedFileValidator.validateFile(file);
                    if (validation.valid) {
                        validFiles.push(file);
                        console.log('✅ [DragDropValidation]', `Файл "${file.name}" готов к загрузке`);
                    } else {
                        console.error('❌ [DragDropValidation]', validation.error);
                        alert(`Ошибка: ${validation.error}`);
                    }
                });

                // Передаем только валидные файлы в оригинальный обработчик
                if (validFiles.length > 0 && originalDropHandler) {
                    const modifiedEvent = Object.assign({}, e);
                    modifiedEvent.dataTransfer = {
                        ...e.dataTransfer,
                        files: validFiles
                    };
                    originalDropHandler.call(this, modifiedEvent);
                }
            };

            console.log('✅ [ExtendedFileValidator] Обновлена drag-drop зона:', zone);
        }
    });
}

// Автоматическое обновление при загрузке DOM
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(updateFileUploadSystems, 500);
});

// Обновление при показе модальных окон
if (typeof $ !== 'undefined') {
    $(document).on('shown.bs.modal', function() {
        setTimeout(updateFileUploadSystems, 300);
    });
}

// Наблюдение за изменениями DOM
const fileInputObserver = new MutationObserver(function(mutations) {
    let needsUpdate = false;
    
    mutations.forEach(function(mutation) {
        if (mutation.addedNodes.length) {
            for (let node of mutation.addedNodes) {
                if (node.nodeType === 1 && (
                    node.tagName === 'INPUT' && node.type === 'file' ||
                    node.querySelector && node.querySelector('input[type="file"]')
                )) {
                    needsUpdate = true;
                    break;
                }
            }
        }
    });
    
    if (needsUpdate) {
        setTimeout(updateFileUploadSystems, 100);
    }
});

fileInputObserver.observe(document.body, {
    childList: true,
    subtree: true
});

// Экспорт функций для глобального использования
window.updateFileUploadSystems = updateFileUploadSystems;
window.ExtendedFileValidator = ExtendedFileValidator;
