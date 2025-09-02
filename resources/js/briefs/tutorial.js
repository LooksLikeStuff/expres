// Интерактивное обучение для брифов
export function initBriefsTutorial() {
    window.onload = function() {
        console.log("Размер экрана:", window.innerWidth);

        if (window.innerWidth > 768) {
            console.log("Проверка обучения для десктопа...");
            if (!localStorage.getItem('tutorial_seen_desktop')) {
                console.log("Запуск обучения для десктопа...");
                const intro = introJs();
                intro.setOptions({
                    steps: [{
                            element: '#step-1',
                            intro: 'Модульный контент - это основная часть интерфейса.',
                            position: 'bottom'
                        },
                        {
                            element: '#step-2',
                            intro: 'Панель вкладок.',
                            position: 'right'
                        },
                        {
                            element: '#step-3',
                            intro: 'Главная страница.',
                            position: 'right'
                        },
                        {
                            element: '#step-4',
                            intro: 'Вкладка БРИФЫ.',
                            position: 'right'
                        },
                        {
                            element: '#step-5',
                            intro: 'Вкладка Сделка.',
                            position: 'right'
                        },
                        {
                            element: '#step-6',
                            intro: 'Вкладка Мой профиль.',
                            position: 'top'
                        },
                        {
                            element: '#step-7',
                            intro: 'Вкладка Поддержка.',
                            position: 'top'
                        },
                        {
                            element: '#step-8',
                            intro: 'Кнопки которые отвечают за зполнение бриф-опросника.',
                            position: 'top'
                        }
                    ],
                    showStepNumbers: true,
                    exitOnOverlayClick: false,
                    showButtons: true,
                    nextLabel: 'Далее',
                    prevLabel: 'Назад',
                });
                intro.start();
                localStorage.setItem('tutorial_seen_desktop', 'true');
            }
        } else {
            console.log("Проверка обучения для мобильных устройств...");
            if (!localStorage.getItem('tutorial_seen_mobile')) {
                console.log("Запуск обучения для мобильных устройств...");
                const intro = introJs();
                intro.setOptions({
                    steps: [{
                            element: '#step-mobile-1',
                            intro: 'Это основная часть интерфейса.',
                            position: 'bottom'
                        },
                        {
                            element: '#step-mobile-2',
                            intro: 'Панель навигации.',
                            position: 'bottom'
                        },
                        {
                            element: '#step-3',
                            intro: 'Главная страница.',
                            position: 'right'
                        },
                        {
                            element: '#step-mobile-4',
                            intro: 'Вкладка БРИФЫ.',
                            position: 'right'
                        },
                        {
                            element: '#step-mobile-5',
                            intro: 'Вкладка Сделка.',
                            position: 'right'
                        },
                        {
                            element: '#step-mobile-6',
                            intro: 'Вкладка Мой профиль.',
                            position: 'top'
                        },
                        {
                            element: '#step-mobile-7',
                            intro: 'Вкладка Поддержка.',
                            position: 'top'
                        },
                        {
                            element: '#step-8',
                            intro: 'Кнопки которые отвечают за зполнение бриф-опросника.',
                            position: 'top'
                        }
                    ],
                    showStepNumbers: true,
                    exitOnOverlayClick: false,
                    showButtons: true,
                    nextLabel: 'Далее',
                    prevLabel: 'Назад',
                });
                intro.start();
                localStorage.setItem('tutorial_seen_mobile', 'true');
            }
        }
    };
}

// Функция для сброса обучения
export function clearTutorialData() {
    console.log('Очистка данных обучения...');
    localStorage.removeItem('tutorial_seen_desktop');
    localStorage.removeItem('tutorial_seen_mobile');
    location.reload();
}

// Делаем функцию доступной глобально для HTML onclick
window.clearTutorialData = clearTutorialData;
