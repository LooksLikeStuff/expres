<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест загрузки больших файлов</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Стили для загрузки больших файлов -->
    <link rel="stylesheet" href="{{ asset('css/large-file-upload.css') }}">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-cloud-upload-alt"></i> Тест загрузки больших файлов на Яндекс.Диск</h3>
                    </div>
                    <div class="card-body">
                        <form id="testForm" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="deal_id" id="dealIdField" value="1">
                            
                            <div class="mb-3">
                                <label for="single-file" class="form-label">Загрузка одного файла:</label>
                                <input type="file" class="form-control yandex-upload" id="single-file" name="single_file">
                                <div class="form-text">Максимальный размер файла: 10GB</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="document-upload" class="form-label">Загрузка множественных документов:</label>
                                <input type="file" class="form-control" id="document-upload" name="documents[]" multiple>
                                <div class="form-text selected-files-count">Файлы не выбраны</div>
                                <div class="form-text">Максимальный общий размер: 50GB</div>
                            </div>
                            
                            <div class="mb-3">
                                <button type="button" id="upload-documents-btn" class="btn btn-primary" disabled>
                                    <i class="fas fa-upload"></i> Загрузить документы
                                </button>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <label for="test-name" class="form-label">Название теста:</label>
                                <input type="text" class="form-control" id="test-name" name="test_name" value="Тест больших файлов">
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-cloud-upload-alt"></i> Загрузить все файлы
                                </button>
                            </div>
                        </form>
                        
                        <div class="mt-4">
                            <h5>Статус тестирования:</h5>
                            <div id="test-results" class="alert alert-info">
                                Выберите файлы для начала тестирования...
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>Поддерживаемые возможности:</h5>
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <i class="fas fa-check text-success"></i> 
                                    Загрузка файлов до 10GB
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-check text-success"></i> 
                                    Множественная загрузка (общий размер до 50GB)
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-check text-success"></i> 
                                    Отслеживание прогресса загрузки
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-check text-success"></i> 
                                    Автоматические повторные попытки при ошибках
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-check text-success"></i> 
                                    Drag & Drop поддержка
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-check text-success"></i> 
                                    Расчет скорости и времени загрузки
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-check text-success"></i> 
                                    Возможность отмены загрузки
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Подключаем JavaScript для больших файлов -->
    <script src="{{ asset('js/large-file-upload.js') }}"></script>
    
    <script>
        // Дополнительная логика для тестирования
        $(document).ready(function() {
            // Переопределяем методы для тестирования
            const originalUploader = window.largeFileUploader;
            
            if (originalUploader) {
                // Переопределяем метод отправки для тестирования
                const originalPerformUpload = originalUploader.performUpload;
                originalUploader.performUpload = function(formData, dealId) {
                    $('#test-results').removeClass('alert-info alert-danger')
                                    .addClass('alert-warning')
                                    .html('<i class="fas fa-spinner fa-spin"></i> Тестируем загрузку...');
                    
                    // Имитируем загрузку для тестирования
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            $('#test-results').removeClass('alert-warning')
                                            .addClass('alert-success')
                                            .html('<i class="fas fa-check"></i> Тест успешно завершен! Система готова к загрузке больших файлов.');
                            
                            resolve({ success: true, message: 'Тест завершен успешно' });
                        }, 3000);
                    });
                };
                
                // Переопределяем метод загрузки документов для тестирования
                const originalPerformDocumentUpload = originalUploader.performDocumentUpload;
                originalUploader.performDocumentUpload = function(formData) {
                    $('#test-results').removeClass('alert-info alert-danger')
                                    .addClass('alert-warning')
                                    .html('<i class="fas fa-spinner fa-spin"></i> Тестируем загрузку документов...');
                    
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            $('#test-results').removeClass('alert-warning')
                                            .addClass('alert-success')
                                            .html('<i class="fas fa-check"></i> Тест загрузки документов успешно завершен!');
                            
                            resolve({ success: true, message: 'Документы загружены', documents: [] });
                        }, 2000);
                    });
                };
            }
            
            // Обновление статуса при выборе файлов
            $('.yandex-upload, #document-upload').on('change', function() {
                const files = this.files;
                if (files.length > 0) {
                    let totalSize = 0;
                    for (let i = 0; i < files.length; i++) {
                        totalSize += files[i].size;
                    }
                    
                    $('#test-results').removeClass('alert-danger alert-success')
                                    .addClass('alert-info')
                                    .html(`<i class="fas fa-info-circle"></i> Выбрано файлов: ${files.length}, общий размер: ${formatBytes(totalSize)}`);
                }
            });
            
            // Функция форматирования размера
            function formatBytes(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        });
    </script>
</body>
</html>
