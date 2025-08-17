importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

// 🔥 Инициализация Firebase в воркере
firebase.initializeApp({
    apiKey: "AIzaSyBFrkGJgs8g3OzVCv-g1J8pCkZo-QLTZqY",
    authDomain: "mypersonal-38208.firebaseapp.com",
    projectId: "mypersonal-38208",
    messagingSenderId: "444177232931",
    appId: "1:444177232931:web:503d0aa632374e236f2d96",
    measurementId: "G-T5V0Z8E2B8"
});

const messaging = firebase.messaging();

// Обработчик фоновых сообщений
messaging.onBackgroundMessage(function (payload) {
    console.log('[firebase-messaging-sw.js] Получено сообщение:', payload);

    const notificationTitle = payload.notification?.title || 'Новое уведомление';
    const notificationOptions = {
        body: payload.notification?.body || '',
        icon: '/favicon.ico',
    };

    console.log('[firebase-messaging-sw.js] Заголовок:', notificationTitle);
    console.log('[firebase-messaging-sw.js] Опции уведомления:', notificationOptions);

    self.registration.showNotification(notificationTitle, notificationOptions)
        .then(() => {
            console.log('[firebase-messaging-sw.js] Уведомление показано успешно');
        })
        .catch(err => {
            console.error('[firebase-messaging-sw.js] Ошибка показа уведомления:', err);
        });
});
// Обработка клика по уведомлению
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
            for (const client of windowClients) {
                if (client.url === event.notification.data.url && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(event.notification.data.url);
            }
        })
    );
});

// Авто-обновление воркера
self.addEventListener('install', event => self.skipWaiting());
self.addEventListener('activate', event => event.waitUntil(self.clients.claim()));
