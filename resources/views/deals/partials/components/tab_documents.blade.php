<!-- Модуль: Документы -->
<div class="module__deal" id="module-documents" style="display: none;">
    <style>
        #module-documents {
            background: linear-gradient(135deg, #fefefe 0%, #f0f4f8 100%);
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 8px 32px rgba(79, 70, 229, 0.12);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(79, 70, 229, 0.1);
        }

        #module-documents::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #c026d3 100%);
            border-radius: 20px 20px 0 0;
        }

        #module-documents::after {
            content: '📄';
            position: absolute;
            top: 15px;
            right: 25px;
            font-size: 24px;
            opacity: 0.6;
        }

        .documents-upload-section {
            background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 28px;
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.08);
            border: 2px dashed #e2e8f0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .documents-upload-section:hover {
            border-color: #4f46e5;
            background: linear-gradient(135deg, #fafaff 0%, #f0f3ff 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(79, 70, 229, 0.15);
        }

        .documents-section-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .documents-section-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }

        .documents-section-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
            line-height: 1.3;
        }

        .documents-section-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin: 4px 0 0 0;
        }

        .documents-upload-area {
            text-align: center;
            padding: 24px;
            border-radius: 12px;
            background: rgba(79, 70, 229, 0.03);
            margin-bottom: 20px;
        }

        .documents-upload-icon {
            font-size: 48px;
            color: #4f46e5;
            margin-bottom: 16px;
            display: block;
        }

        .documents-upload-text {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .documents-upload-subtext {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .documents-upload-btn {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px 28px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 16px;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2);
        }

        .documents-upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3);
        }

        .documents-upload-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .documents-upload-info {
            font-size: 12px;
            color: #6b7280;
            margin-top: 12px;
        }

        .files-count-info {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            margin-top: 12px;
        }

        .documents-upload-area.drag-over {
            border-color: #4f46e5;
            background: rgba(79, 70, 229, 0.1);
        }

        /* Стили для списка документов */
        .documents-list-section {
            margin-top: 24px;
        }

        .documents-list-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .documents-list-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #059669 0%, #0d9488 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .documents-list-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }

        .document-item {
            background: white;
            border-radius: 12px;
            padding: 16px;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .document-item:hover {
            border-color: #4f46e5;
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.1);
            transform: translateY(-2px);
        }

        .document-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .document-item:hover::before {
            opacity: 1;
        }

        .document-item.deleting {
            opacity: 0.5;
            pointer-events: none;
        }

        .document-item.deleting::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #4f46e5;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .document-info {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .document-icon {
            width: 40px;
            height: 40px;
            background: #f3f4f6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 18px;
        }

        .document-details {
            flex: 1;
        }

        .document-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
            margin-bottom: 4px;
            word-break: break-word;
        }

        .document-size {
            font-size: 12px;
            color: #6b7280;
        }

        .document-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .document-action-btn {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            font-size: 14px;
        }

        .document-action-btn.download {
            background: #eff6ff;
            color: #2563eb;
        }

        .document-action-btn.download:hover {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .document-action-btn.delete {
            background: #fef2f2;
            color: #dc2626;
        }

        .document-action-btn.delete:hover {
            background: #fee2e2;
            color: #b91c1c;
        }

        /* Пустое состояние */
        .documents-empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .documents-empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .documents-empty-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 8px 0;
        }

        .documents-empty-text {
            font-size: 14px;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Скрытый input для загрузки файлов */
        .document-upload-input {
            display: none;
        }
    </style>

    <!-- Секция загрузки документов -->
    <div class="documents-upload-section">
        <div class="documents-section-header">
            <div class="documents-section-icon">
                <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <div>
                <h3 class="documents-section-title">Загрузка документов</h3>
                <p class="documents-section-subtitle">Добавьте файлы проекта для совместной работы</p>
            </div>
        </div>
        
        <div class="documents-upload-area" id="documentsUploadArea">
            <i class="documents-upload-icon fas fa-file-upload"></i>
            <div class="documents-upload-text">Выберите файлы или перетащите их сюда</div>
            <div class="documents-upload-subtext">Поддерживаются форматы: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, ZIP, RAR<br>Максимальный размер файла: 100 МБ</div>
            
            <button type="button" class="documents-upload-btn" id="uploadDocumentsBtn">
                <i class="fas fa-plus"></i>
                Выбрать файлы
            </button>
            
            <input type="file" 
                   id="documentUploadInput" 
                   class="document-upload-input" 
                   multiple 
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip,.rar">
            
            <div class="documents-upload-info">
                Вы можете загрузить несколько файлов одновременно
            </div>
            
            <!-- Информация о выбранных файлах -->
            <div id="filesCountInfo" class="files-count-info" style="display: none;">
                <span id="filesCountText">Файлов выбрано: 0</span>
            </div>
        </div>
    </div>

    <!-- Список загруженных документов -->
    @if(isset($documents) && count($documents) > 0)
        <div class="documents-list-section">
            <div class="documents-list-header">
                <div class="documents-list-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3 class="documents-list-title">Загруженные документы</h3>
            </div>
            
            <div class="documents-grid" id="static-documents-grid">
                @foreach($documents as $document)
                    <div class="document-item">
                        <div class="document-info">
                            <div class="document-icon">
                                <i class="fas {{ $document['icon'] ?? 'fa-file' }}"></i>
                            </div>
                            <div class="document-details">
                                <div class="document-name">{{ $document['name'] ?? 'Документ' }}</div>
                                @if(isset($document['size']))
                                    <div class="document-size">{{ $document['size'] }}</div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="document-actions">
                            @if(isset($document['url']))
                                <a href="{{ $document['url'] }}" 
                                   target="_blank" 
                                   class="document-action-btn download" 
                                   title="Скачать">
                                    <i class="fas fa-download"></i>
                                </a>
                            @endif
                            @if(in_array(Auth::user()->status, ['admin', 'coordinator']))
                                <button type="button" 
                                        class="document-action-btn delete" 
                                        data-document-id="{{ $document['id'] ?? 0 }}"
                                        onclick="deleteDocument('{{ $document['id'] ?? 0 }}')"
                                        title="Удалить">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- Скрытый контейнер для документов -->
        <div class="documents-placeholder" style="display: none;">
            <div class="documents-list-section">
                <div class="documents-list-header">
                    <div class="documents-list-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3 class="documents-list-title">Загруженные документы</h3>
                </div>
                
                <div class="documents-grid" id="dynamic-documents-grid">
                    <!-- Документы будут добавлены динамически -->
                </div>
            </div>
        </div>
        
        <!-- Пустое состояние -->
        <div class="documents-empty-state">
            <div class="documents-empty-icon">
                <i class="fas fa-folder-open"></i>
            </div>
            <h3 class="documents-empty-title">Документы не загружены</h3>
            <p class="documents-empty-text">
                <i class="fas fa-info-circle"></i>
                Загрузите документы для работы с проектом
            </p>
        </div>
    @endif
</div>
