<!-- Модуль: Бриф -->
<div class="module__deal" id="module-brief" style="display: none;">
    <style>
        #module-brief {
            background: linear-gradient(135deg, #fafbff 0%, #f0f3ff 100%);
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.12);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(99, 102, 241, 0.1);
        }

        #module-brief::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            border-radius: 20px 20px 0 0;
        }

        #module-brief::after {
            content: '📋';
            position: absolute;
            top: 15px;
            right: 25px;
            font-size: 24px;
            opacity: 0.6;
        }

        .brief-status-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(99, 102, 241, 0.1);
        }

        .brief-status-card:hover {
            transform: translateY(-4px) scale(1.01);
            box-shadow: 0 12px 40px rgba(99, 102, 241, 0.15);
            border-color: rgba(99, 102, 241, 0.2);
        }

        .brief-status-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .brief-status-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            position: relative;
        }

        .brief-status-icon.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .brief-status-icon.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .brief-status-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .brief-status-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin: 4px 0 0 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .brief-actions {
            margin-top: 16px;
        }

        .brief-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .brief-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .brief-search-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .brief-search-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid #f1f5f9;
        }

        .brief-search-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .brief-search-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .brief-form-group {
            margin-bottom: 20px;
        }

        .brief-form-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }

        .brief-form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .brief-form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: white;
        }

        .brief-search-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .brief-search-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .brief-loading {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            margin-top: 16px;
            color: #0369a1;
        }

        .brief-loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .brief-results {
            margin-top: 20px;
            background: #f8fafc;
            border-radius: 8px;
            overflow: hidden;
        }

        .brief-results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background: #1f2937;
            color: white;
        }

        .brief-results-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            font-weight: 500;
            margin: 0;
        }

        .brief-close-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .brief-close-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .brief-notifications {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .brief-warning-info {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #92400e;
            font-size: 14px;
        }
    </style>

    <!-- Статус привязки брифа -->
    <div class="brief-status-card">
        @if($deal->common_id || $deal->commercial_id)
            <div class="brief-status-header">
                <div class="brief-status-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3 class="brief-status-title">Бриф привязан к проекту</h3>
                    <p class="brief-status-subtitle">
                        <i class="fas fa-link"></i>
                        К сделке успешно привязан бриф
                    </p>
                </div>
            </div>
            <div class="brief-actions">
                <button type="button" 
                        class="brief-btn btn-detach-brief" 
                        data-deal-id="{{ $deal->id }}">
                    <i class="fas fa-unlink"></i>
                    Отвязать бриф
                </button>
            </div>
        @else
            <div class="brief-status-header">
                <div class="brief-status-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h3 class="brief-status-title">Бриф не привязан</h3>
                    <p class="brief-warning-info">
                        <i class="fas fa-lightbulb"></i>
                        Для работы с проектом рекомендуется привязать бриф
                    </p>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Поиск и привязка брифа -->
    <div class="brief-search-card">
        <div class="brief-search-header">
            <div class="brief-search-icon">
                <i class="fas fa-search"></i>
            </div>
            <h3 class="brief-search-title">Поиск брифа по телефону</h3>
        </div>
        
        <div class="brief-form-group">
            <label for="client_phone_search" class="brief-form-label">
                <i class="fas fa-phone"></i>
                Телефон клиента:
            </label>
            <input type="text" 
                   id="client_phone_search" 
                   class="brief-form-input" 
                   value="{{ $deal->client_phone ?? '' }}" 
                   readonly>
        </div>
        
        <button type="button" 
                class="brief-search-btn" 
                id="searchBriefBtn"
                data-deal-id="{{ $deal->id }}" 
                data-client-phone="{{ $deal->client_phone ?? '' }}">
            <i class="fas fa-search"></i>
            Найти бриф
        </button>
        
        <!-- Статус поиска -->
        <div id="brief-search-status" class="brief-loading" style="display: none;">
            <i class="fas fa-spinner"></i>
            <span>Поиск брифов...</span>
        </div>
        
        <!-- Результаты поиска -->
        <div id="brief-search-results" class="brief-results" style="display: none;">
            <div class="brief-results-header">
                <h5 class="brief-results-title">
                    <i class="fas fa-list"></i>
                    Найденные брифы
                </h5>
                <button type="button" class="brief-close-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="brief-results-list" class="results-list">
                <!-- Результаты будут загружены через JavaScript -->
            </div>
        </div>
        
        <!-- Уведомления -->
        <div id="brief-notifications" class="brief-notifications" style="display: none;">
            <!-- Уведомления будут показаны через JavaScript -->
        </div>
    </div>
</div>
