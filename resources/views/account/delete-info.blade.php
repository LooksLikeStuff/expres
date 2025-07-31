@extends('layouts.auth')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card info-card">
                <div class="card-header text-center">
                    <h1>Удаление аккаунта в приложении "Экспресс-дизайн"</h1>
                </div>
                <div class="card-body">
                    <div class="developer-info info-section">
                        <div class="section-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="section-content">
                            <h4>Информация о приложении:</h4>
                            <div class="app-info">
                                <div class="app-logo">
                                    <img src="{{ asset('img/logo.png') }}" alt="Экспресс-дизайн" onerror="this.src='https://via.placeholder.com/80x80?text=ЭД'; this.onerror='';">
                                </div>
                                <div class="app-details">
                                    <p><strong>Название приложения:</strong> Экспресс-дизайн</p>
                                    <p><strong>Разработчик:</strong> Никита Аненков</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="account-deletion-steps info-section">
                        <div class="section-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="section-content">
                            <h4>Пошаговая инструкция по удалению аккаунта:</h4>
                            <ol class="steps-list">
                                <li>
                                    <span class="step-number">1</span>
                                    <div class="step-content">
                                        <strong>Перейдите</strong> на <a href="{{ route('account.delete') }}" class="highlight-link">страницу удаления аккаунта</a>.
                                    </div>
                                </li>
                                <li>
                                    <span class="step-number">2</span>
                                    <div class="step-content">
                                        <strong>Введите</strong> номер телефона, связанный с вашим аккаунтом в поле "Введите телефон".
                                    </div>
                                </li>
                                <li>
                                    <span class="step-number">3</span>
                                    <div class="step-content">
                                        <strong>Нажмите</strong> кнопку "Получить код". На указанный номер телефона будет отправлен код подтверждения.
                                    </div>
                                </li>
                                <li>
                                    <span class="step-number">4</span>
                                    <div class="step-content">
                                        <strong>Введите</strong> полученный 4-значный код подтверждения в соответствующие поля.
                                    </div>
                                </li>
                                <li>
                                    <span class="step-number">5</span>
                                    <div class="step-content">
                                        <strong>Дождитесь</strong> подтверждения. После успешной проверки кода вы будете перенаправлены на страницу входа, и процесс удаления аккаунта будет инициирован.
                                    </div>
                                </li>
                            </ol>
                            <div class="support-info">
                                <i class="fas fa-headset"></i>
                                <p>Если у вас возникли проблемы с удалением аккаунта, обратитесь в службу поддержки по электронной почте: <a href="mailto:support@express-diz.ru" class="highlight-link">support@express-diz.ru</a></p>
                            </div>
                        </div>
                    </div>

                    <div class="data-retention info-section">
                        <div class="section-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="section-content">
                            <h4>Информация о данных и сроках хранения:</h4>
                            
                            <div class="data-category">
                                <h5><i class="fas fa-trash-alt"></i> Данные, которые будут удалены:</h5>
                                <ul class="data-list deleted-data">
                                    <li><i class="fas fa-user"></i> Персональные данные (имя, телефон, электронная почта)</li>
                                    <li><i class="fas fa-image"></i> Фотография профиля</li>
                                    <li><i class="fas fa-fingerprint"></i> Уникальные идентификаторы пользователя в системе</li>
                                    <li><i class="fas fa-key"></i> Пароль и данные аутентификации</li>
                                    <li><i class="fas fa-bell"></i> Настройки уведомлений и предпочтения</li>
                                </ul>
                            </div>

                            <div class="data-category">
                                <h5><i class="fas fa-save"></i> Данные, которые будут сохранены:</h5>
                                <ul class="data-list retained-data">
                                    <li><i class="fas fa-history"></i> История заказов и сделок (деперсонализированная)</li>
                                    <li><i class="fas fa-file-alt"></i> Созданные брифы дизайн-проектов (без личных данных)</li>
                                    <li><i class="fas fa-star"></i> Отзывы и оценки (с удалением имени пользователя)</li>
                                </ul>
                            </div>

                            <div class="data-category">
                                <h5><i class="fas fa-clock"></i> Сроки хранения данных:</h5>
                                <ul class="data-list retention-periods">
                                    <li><span class="retention-label">Персональные данные:</span> <span class="retention-value">Удаляются немедленно при подтверждении запроса на удаление аккаунта.</span></li>
                                    <li><span class="retention-label">Деперсонализированные данные о заказах:</span> <span class="retention-value">Хранятся в течение 5 лет в соответствии с требованиями законодательства.</span></li>
                                    <li><span class="retention-label">Анонимизированная статистика:</span> <span class="retention-value">Может храниться бессрочно для аналитических целей.</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="legal-info info-section">
                        <div class="section-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <div class="section-content">
                            <h4>Правовая информация:</h4>
                            <div class="legal-text">
                                <p>Удаление аккаунта осуществляется в соответствии с <a href="#" class="highlight-link">Политикой конфиденциальности</a> и <a href="#" class="highlight-link">Пользовательским соглашением</a> компании "Экспресс-дизайн".</p>
                                <p>Обработка персональных данных производится согласно Федеральному закону "О персональных данных" от 27.07.2006 N 152-ФЗ.</p>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="{{ route('account.delete') }}" class="btn btn-danger delete-btn">
                            <i class="fas fa-user-slash"></i> Перейти к удалению аккаунта
                        </a>
                        <a href="{{ route('login.password') }}" class="btn btn-outline-secondary back-btn">
                            <i class="fas fa-arrow-left"></i> Вернуться на страницу входа
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Основные стили страницы */
    .info-card {
        margin: 30px 0;
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        background-color: #fff;
    }

    .card-header {
        background: linear-gradient(135deg, #4a90e2, #5c6bc0);
        padding: 25px 20px;
        border: none;
    }

    .card-header h1 {
        color: white;
        font-size: 24px;
        font-weight: 600;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .card-body {
        padding: 30px;
    }

    /* Стили для секций информации */
    .info-section {
        display: flex;
        margin-bottom: 40px;
        position: relative;
        padding-bottom: 30px;
    }

    .info-section:not(:last-child):after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50px;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, #e0e0e0 10%, #e0e0e0 90%, transparent);
    }

    .section-icon {
        flex: 0 0 50px;
        height: 50px;
        margin-right: 20px;
        background: linear-gradient(135deg, #4a90e2, #5c6bc0);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        box-shadow: 0 4px 10px rgba(92, 107, 192, 0.3);
    }

    .section-content {
        flex: 1;
    }

    .section-content h4 {
        color: #333;
        font-size: 20px;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
        position: relative;
    }

    .section-content h4:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 60px;
        height: 2px;
        background: linear-gradient(90deg, #4a90e2, #5c6bc0);
    }

    /* Информация о приложении */
    .app-info {
        display: flex;
        align-items: center;
        background-color: #f9f9f9;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    .app-logo {
        flex: 0 0 80px;
        margin-right: 20px;
    }

    .app-logo img {
        width: 80px;
        height: 80px;
        border-radius: 16px;
        object-fit: cover;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .app-details {
        flex: 1;
    }

    .app-details p {
        margin: 5px 0;
        font-size: 15px;
    }

    /* Пошаговая инструкция */
    .steps-list {
        counter-reset: step;
        list-style-type: none;
        padding-left: 0;
    }

    .steps-list li {
        display: flex;
        margin-bottom: 20px;
        align-items: flex-start;
    }

    .step-number {
        flex: 0 0 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4a90e2, #5c6bc0);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 15px;
        box-shadow: 0 3px 6px rgba(92, 107, 192, 0.3);
    }

    .step-content {
        flex: 1;
        padding: 8px 0;
    }

    .step-content strong {
        color: #4a90e2;
    }

    .highlight-link {
        color: #4a90e2;
        text-decoration: none;
        font-weight: 500;
        position: relative;
        transition: all 0.3s;
    }

    .highlight-link:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        height: 1px;
        background-color: #4a90e2;
        transform: scaleX(0);
        transition: transform 0.3s;
    }

    .highlight-link:hover {
        color: #3949ab;
    }

    .highlight-link:hover:after {
        transform: scaleX(1);
    }

    .support-info {
        display: flex;
        align-items: center;
        background-color: #e8f0fe;
        padding: 15px;
        border-radius: 10px;
        margin-top: 20px;
    }

    .support-info i {
        font-size: 24px;
        margin-right: 15px;
        color: #4a90e2;
    }

    .support-info p {
        margin: 0;
        font-size: 14px;
    }

    /* Данные и сроки хранения */
    .data-category {
        margin-bottom: 25px;
    }

    .data-category h5 {
        display: flex;
        align-items: center;
        color: #424242;
        font-size: 17px;
        margin-bottom: 15px;
    }

    .data-category h5 i {
        margin-right: 10px;
        color: #5c6bc0;
    }

    .data-list {
        list-style-type: none;
        padding-left: 10px;
        margin-bottom: 20px;
    }

    .data-list li {
        position: relative;
        padding-left: 30px;
        margin-bottom: 10px;
        font-size: 15px;
        line-height: 1.6;
    }

    .data-list li i {
        position: absolute;
        left: 0;
        top: 4px;
        width: 20px;
        text-align: center;
        color: #5c6bc0;
    }

    .deleted-data li i {
        color: #ef5350;
    }

    .retained-data li i {
        color: #66bb6a;
    }

    .retention-periods li {
        display: flex;
        flex-wrap: wrap;
    }

    .retention-label {
        font-weight: 600;
        color: #424242;
        margin-right: 6px;
    }

    .retention-value {
        color: #616161;
    }

    /* Правовая информация */
    .legal-text {
        background-color: #f5f5f5;
        padding: 15px;
        border-radius: 10px;
        border-left: 4px solid #5c6bc0;
    }

    .legal-text p {
        margin-bottom: 10px;
        font-size: 14px;
        line-height: 1.6;
    }

    .legal-text p:last-child {
        margin-bottom: 0;
    }

    /* Кнопки действий */
    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 30px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn i {
        margin-right: 8px;
    }

    .delete-btn {
        background: linear-gradient(45deg, #f44336, #e53935);
        border: none;
        color: white;
        box-shadow: 0 4px 10px rgba(244, 67, 54, 0.3);
    }

    .delete-btn:hover {
        background: linear-gradient(45deg, #e53935, #d32f2f);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(244, 67, 54, 0.4);
    }

    .back-btn {
        border: 1px solid #bdbdbd;
        color: #616161;
    }

    .back-btn:hover {
        background-color: #f5f5f5;
        color: #424242;
        border-color: #9e9e9e;
    }

    /* Адаптивность для мобильных устройств */
    @media (max-width: 767px) {
        .card-header h1 {
            font-size: 20px;
        }
        
        .info-section {
            flex-direction: column;
        }
        
        .section-icon {
            margin-bottom: 15px;
            margin-right: 0;
        }
        
        .app-info {
            flex-direction: column;
            text-align: center;
        }
        
        .app-logo {
            margin-right: 0;
            margin-bottom: 15px;
        }
        
        .retention-periods li {
            flex-direction: column;
        }
        
        .retention-label {
            margin-bottom: 5px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .action-buttons .btn {
            width: 100%;
        }
    }
</style>
@endsection
