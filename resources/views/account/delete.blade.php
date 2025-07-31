@extends('layouts.public')
@section('content')
<div class="container">
    <div class="delete-account-page">
        <div class="delete-form-section">
            <div class="delete-form-container">
                <div class="form-header flex center" style="flex-direction: column;">
                    <h1>Удаление аккаунта</h1>
                    <div class="warning-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <p class="auth__title_sub">Для удаления аккаунта введите ваш номер телефона.<br>На указанный номер будет отправлен код подтверждения.</p>
                
                <div id="phone-section" class="input-section">
                    <label for="phone" class="custom-input-wrapper">
                        <input type="phone" name="phone" id="phone" class="form-control maskphone custom-input" placeholder="Введите телефон" value="{{ old('phone') }}" maxlength="50" required>
                        <div class="input-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div id="phone-error" class="error-message"></div>
                    </label>
                    <button type="button" id="send-code-btn" class="btn btn-primary custom-btn">
                        <span class="btn-text">Получить код</span>
                        <span class="btn-icon"><i class="fas fa-arrow-right"></i></span>
                    </button>
                </div>
                
                <div id="code-section" class="hidden code-input-container">
                    <div class="code-title">Введите код из SMS</div>
                    <div class="code-inputs">
                        @for ($i = 0; $i < 4; $i++)
                            <input type="text" class="code-input" placeholder="" maxlength="1" required>
                        @endfor
                    </div>
                    <input type="hidden" name="code" id="code" value="">
                    <div id="code-error" class="error-message"></div> 
                    <div class="code-section-link">
                        <a href="#" id="resend-code-link" class="disabled-link">Отправить код повторно</a>
                        <p id="resend-timer" style="display: none;">Получить код повторно можно через <span id="resend-countdown">60</span> секунд.</p>
                    </div>
                </div>

                <div class="auth__form__link">
                    <div class="else__auth">---------- или ----------</div>
                    <a href="{{ route('login.password') }}" class="back-link"><i class="fas fa-chevron-left"></i> Вернуться на страницу входа</a>
                </div>
            </div>
        </div>
        
        <div class="info-section">
            <!-- Информация о приложении -->
            <div class="info-block app-info">
                <div class="info-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="info-content">
                    <h4>Информация о приложении</h4>
                    <div class="app-details">
                        <p><strong>Название приложения:</strong> Экспресс Дизайн</p>
                        <p><strong>Разработчик:</strong> Никита Анненков</p>
                    </div>
                </div>
            </div>

            <!-- Информация о данных -->
            <div class="info-block data-info">
                <div class="info-icon">
                    <i class="fas fa-database"></i>
                </div>
                <div class="info-content">
                    <h4>Информация о данных</h4>
                    <div class="data-categories">
                        <div class="data-category">
                            <h5><i class="fas fa-trash-alt"></i> Данные, которые будут удалены:</h5>
                            <ul class="data-list">
                                <li><i class="fas fa-user"></i> Персональные данные (имя, телефон, эл. почта)</li>
                                <li><i class="fas fa-image"></i> Фотография профиля</li>
                                <li><i class="fas fa-fingerprint"></i> Идентификаторы пользователя</li>
                                <li><i class="fas fa-key"></i> Пароль и данные аутентификации</li>
                                <li><i class="fas fa-bell"></i> Настройки уведомлений и предпочтения</li>
                            </ul>
                        </div>
                        <div class="data-category">
                            <h5><i class="fas fa-save"></i> Данные, которые будут сохранены:</h5>
                            <ul class="data-list">
                                <li><i class="fas fa-history"></i> История заказов (деперсонализированная)</li>
                                <li><i class="fas fa-file-alt"></i> Брифы дизайн-проектов (без личных данных)</li>
                                <li><i class="fas fa-star"></i> Отзывы (с удалением имени пользователя)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Сроки хранения данных -->
            <div class="info-block retention-info">
                <div class="info-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="info-content">
                    <h4>Сроки хранения данных</h4>
                    <ul class="retention-list">
                        <li><span class="retention-type">Персональные данные:</span> удаляются немедленно</li>
                        <li><span class="retention-type">Деперсонализированные данные:</span> хранятся 5 лет</li>
                        <li><span class="retention-type">Анонимная статистика:</span> хранится бессрочно</li>
                    </ul>
                </div>
            </div>

            <!-- Правовая информация -->
            <div class="info-block legal-info">
                <div class="info-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="info-content">
                    <h4>Правовая информация</h4>
                    <p>Удаление аккаунта осуществляется в соответствии с Политикой конфиденциальности и Пользовательским соглашением компании "Экспресс-дизайн".</p>
                    <p>Обработка персональных данных производится согласно Федеральному закону "О персональных данных" от 27.07.2006 N 152-ФЗ.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Общая структура страницы */
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .delete-account-page {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    @media (min-width: 992px) {
        .delete-account-page {
            flex-direction: row;
        }
        .delete-form-section {
            flex: 0 0 40%;
        }
        .info-section {
            flex: 0 0 60%;
        }
    }

    /* Стили формы удаления */
    .delete-form-container {
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        padding: 2rem;
        background: linear-gradient(to bottom, #ffffff, #f9f9f9);
        transition: all 0.3s ease;
    }

    .form-header {
        position: relative;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .warning-icon {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        background-color: #ffebee;
        color: #f44336;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        margin-bottom: 1rem;
        font-size: 1.5rem;
        border: 2px solid #ffcdd2;
        animation: pulse-warning 2s infinite;
    }

    @keyframes pulse-warning {
        0% {
            box-shadow: 0 0 0 0 rgba(244, 67, 54, 0.4);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(244, 67, 54, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(244, 67, 54, 0);
        }
    }

    .form-header h1 {
        color: #333;
        font-size: 24px;
        margin-bottom: 0.5rem;
    }

    .auth__title_sub {
        color: #666;
        font-size: 14px;
        text-align: center;
        margin-bottom: 2rem;
    }

    .input-section {
        margin-bottom: 1.5rem;
    }

    .custom-input-wrapper {
        position: relative;
        display: block;
        margin-bottom: 1.5rem;
    }

    .input-icon {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        left: 12px;
        color: #757575;
    }

    .custom-input {
        display: block;
        width: 100%;
        padding: 12px 12px 12px 40px;
        font-size: 16px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #fff;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .custom-input:focus {
        border-color: #4a90e2;
        box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.25);
        outline: none;
    }

    .custom-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 12px 20px;
        background: linear-gradient(45deg, #4a90e2, #5c6bc0);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .custom-btn:hover {
        background: linear-gradient(45deg, #5c6bc0, #3949ab);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(92, 107, 192, 0.4);
    }

    .custom-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(92, 107, 192, 0.4);
    }

    .btn-text {
        margin-right: 10px;
    }

    .btn-icon {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hidden {
        display: none;
    }

    .code-input-container {
        animation: fadeIn 0.5s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .code-title {
        text-align: center;
        font-weight: 600;
        margin-bottom: 15px;
        color: #333;
    }

    .code-inputs {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin: 20px 0;
    }

    .code-input {
        width: 55px;
        height: 60px;
        text-align: center;
        font-size: 24px;
        border: 2px solid #ddd;
        border-radius: 8px;
        background-color: #f9f9f9;
        transition: all 0.3s ease;
    }

    .code-input:focus {
        border-color: #4a90e2;
        box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.25);
        outline: none;
        background-color: #fff;
    }

    .error-message {
        color: #f44336;
        font-size: 0.875rem;
        margin-top: 5px;
        animation: shake 0.6s;
    }

    @keyframes shake {
        0%, 100% {transform: translateX(0);}
        20%, 60% {transform: translateX(-5px);}
        40%, 80% {transform: translateX(5px);}
    }

    .disabled-link {
        color: #9e9e9e;
        pointer-events: none;
        text-decoration: none;
    }

    .code-section-link {
        text-align: center;
        margin-top: 20px;
    }

    .code-section-link a {
        color: #4a90e2;
        text-decoration: none;
        transition: color 0.3s;
    }

    .code-section-link a:hover {
        color: #3949ab;
        text-decoration: underline;
    }

    #resend-timer {
        color: #757575;
        font-size: 0.875rem;
        margin-top: 10px;
    }

    .auth__form__link {
        margin-top: 1.5rem;
        text-align: center;
    }

    .else__auth {
        color: #9e9e9e;
        margin-bottom: 15px;
    }

    .back-link {
        color: #4a90e2;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        font-weight: 500;
        transition: all 0.3s;
    }

    .back-link:hover {
        color: #3949ab;
    }

    .back-link i {
        margin-right: 5px;
    }

    /* Стили информационной секции */
    .info-section {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .info-block {
        background-color: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        display: flex;
        transition: all 0.3s ease;
    }

    .info-block:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .info-icon {
        flex: 0 0 50px;
        height: 50px;
        background: linear-gradient(135deg, #4a90e2, #5c6bc0);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: white;
        font-size: 20px;
        box-shadow: 0 4px 8px rgba(74, 144, 226, 0.25);
    }

    .info-content {
        flex: 1;
    }

    .info-content h4 {
        color: #333;
        font-size: 18px;
        margin-top: 0;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #f0f0f0;
        position: relative;
    }

    .info-content h4:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 50px;
        height: 2px;
        background: linear-gradient(90deg, #4a90e2, #5c6bc0);
    }

    .info-content h5 {
        display: flex;
        align-items: center;
        font-size: 16px;
        color: #555;
        margin-bottom: 10px;
    }

    .info-content h5 i {
        margin-right: 8px;
        color: #5c6bc0;
    }

    .data-categories {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    @media (min-width: 768px) {
        .data-categories {
            flex-direction: row;
        }
        .data-category {
            flex: 1;
        }
    }

    .data-list {
        list-style-type: none;
        padding-left: 0;
        margin-bottom: 0;
    }

    .data-list li {
        position: relative;
        padding-left: 30px;
        margin-bottom: 8px;
        font-size: 14px;
        line-height: 1.5;
    }

    .data-list li i {
        position: absolute;
        left: 0;
        top: 2px;
        width: 20px;
        text-align: center;
        color: #5c6bc0;
    }

    .retention-list {
        list-style-type: none;
        padding-left: 0;
    }

    .retention-list li {
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #eee;
        font-size: 14px;
    }

    .retention-list li:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .retention-type {
        font-weight: 600;
        color: #424242;
        margin-right: 6px;
    }

    .legal-info p {
        font-size: 14px;
        line-height: 1.6;
        color: #555;
        margin-bottom: 10px;
    }

    .legal-info p:last-child {
        margin-bottom: 0;
    }

    .legal-info a {
        color: #4a90e2;
        text-decoration: none;
        transition: color 0.2s;
    }

    .legal-info a:hover {
        color: #3949ab;
        text-decoration: underline;
    }

    /* Адаптивность для мобильных устройств */
    @media (max-width: 767px) {
        .container {
            padding: 10px;
        }
        
        .delete-form-container {
            padding: 1.5rem;
        }
        
        .code-input {
            width: 45px;
            height: 50px;
            font-size: 20px;
        }
        
        .info-block {
            padding: 15px;
        }
        
        .info-icon {
            flex: 0 0 40px;
            height: 40px;
            font-size: 18px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sendCodeBtn = document.getElementById('send-code-btn');
    const phoneSection = document.getElementById('phone-section');
    const codeSection = document.getElementById('code-section');
    const resendLink = document.getElementById('resend-code-link');
    const resendTimer = document.getElementById('resend-timer');
    const countdownSpan = document.getElementById('resend-countdown');
    const codeInputs = document.querySelectorAll('.code-input');
    const codeField = document.getElementById('code');
    const phoneError = document.getElementById('phone-error');
    const codeError = document.getElementById('code-error');
    let countdownInterval = null;
    
    function sendCode(phone) {
        if (!phone) {
            phoneError.textContent = 'Введите номер телефона!';
            return Promise.reject('Номер телефона отсутствует.');
        }
        
        phoneError.textContent = '';  
        
        return fetch("{{ route('account.delete.send-code') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ phone })
        }).then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error(text || 'Ошибка на сервере');
                    }
                });
            }
            return response.json();
        });
    }
    
    function verifyCode(phone, code) {
        return fetch("{{ route('account.delete.verify-code') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ phone, code })
        }).then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error(text || 'Ошибка на сервере');
                    }
                });
            }
            return response.json();
        });
    }
    
    sendCodeBtn.addEventListener('click', function () {
        const phone = document.getElementById('phone').value;
        sendCode(phone)
            .then(data => {
                if (data.success) {
                    phoneSection.style.display = 'none';
                    codeSection.classList.remove('hidden');
                    startResendCooldown();
                    // Для отладки
                    console.log('Отправлен код:', data.debug_code);
                } else {
                    phoneError.textContent = data.message || 'Ошибка отправки кода.';
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                if (error.message) {
                    phoneError.textContent = error.message;
                } else {
                    phoneError.textContent = 'Произошла ошибка при отправке запроса. Пожалуйста, попробуйте снова.';
                }
            });
    });
    
    resendLink.addEventListener('click', function (e) {
        e.preventDefault();
        if (resendLink.classList.contains('disabled-link')) {
            return; 
        }
        
        const phone = document.getElementById('phone').value;
        sendCode(phone)
            .then(data => {
                if (data.success) {
                    alert('Код был отправлен повторно.');
                    startResendCooldown();
                    // Для отладки
                    console.log('Повторно отправлен код:', data.debug_code);
                } else {
                    codeError.textContent = data.message || 'Ошибка отправки кода.';
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                if (error.message) {
                    codeError.textContent = error.message;
                } else {
                    codeError.textContent = 'Произошла ошибка при отправке запроса. Пожалуйста, попробуйте снова.';
                }
            });
    });
    
    function startResendCooldown() {
        let remainingTime = 60; // 60 seconds
        resendLink.classList.add('disabled-link');
        resendTimer.style.display = 'block';
        countdownSpan.textContent = remainingTime;
        
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
        
        countdownInterval = setInterval(() => {
            remainingTime--;
            countdownSpan.textContent = remainingTime;
            
            if (remainingTime <= 0) {
                clearInterval(countdownInterval);
                resendLink.classList.remove('disabled-link');
                resendTimer.style.display = 'none';
            }
        }, 1000);
    }
    
    codeInputs.forEach((input, index) => {
        input.addEventListener('input', function () {
            if (input.value.length > 0 && index < codeInputs.length - 1) {
                codeInputs[index + 1].focus();
            }
            
            const code = Array.from(codeInputs).map(input => input.value).join('');
            codeField.value = code;
            
            if (code.length === 4) {
                const phone = document.getElementById('phone').value;
                verifyCode(phone, code)
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.redirect;
                        } else {
                            codeError.textContent = data.message || 'Неверный код.';
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка:', error);
                        if (error.message) {
                            codeError.textContent = error.message;
                        } else {
                            codeError.textContent = 'Произошла ошибка при проверке кода.';
                        }
                    });
            }
        });
        
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !input.value && index > 0) {
                codeInputs[index - 1].focus();
            }
        });
    });
});
</script>
@endsection
