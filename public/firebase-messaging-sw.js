importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyBFrkGJgs8g3OzVCv-g1J8pCkZo-QLTZqY",
    authDomain: "mypersonal-38208.firebaseapp.com",
    projectId: "mypersonal-38208",
    messagingSenderId: "444177232931",
    appId: "1:444177232931:web:503d0aa632374e236f2d96",
    measurementId: "G-T5V0Z8E2B8"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
    console.log('[firebase-messaging-sw.js] Получено сообщение:', payload);

    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/icon.png', // желательно иконку указать!
        data: {
            url: '/' // можно использовать для перехода по клику
        }
    };

    // 💡 ВАЖНО: Используем waitUntil, чтобы воркер дождался показа уведомления
    self.registration.showNotification(notificationTitle, notificationOptions);
});
