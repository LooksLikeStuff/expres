// Главный модуль для брифов
import { initBriefsTutorial } from './tutorial.js';
import { confirmDelete } from './actions.js';

// Инициализация модуля брифов
document.addEventListener('DOMContentLoaded', function() {
    // Инициализируем обучение
    initBriefsTutorial();
    
    console.log('Модуль брифов загружен');
});
