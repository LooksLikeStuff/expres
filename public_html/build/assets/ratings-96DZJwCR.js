(function(){document.addEventListener("DOMContentLoaded",function(){console.log("[Рейтинги] Инициализация системы рейтингов");const i=document.getElementById("rating-modal");if(!i){console.error("[Рейтинги] Не найден элемент модального окна #rating-modal");return}const c=i.querySelectorAll(".rating-stars .star"),a=document.getElementById("submit-rating"),l=document.getElementById("skip-rating"),x=document.getElementById("rating-modal-close"),p=document.getElementById("close-all-ratings"),h=document.getElementById("rating-modal-backdrop"),B=document.getElementById("rating-comment"),v=document.getElementById("comment-char-count");let u=0,d=[],g=0,f=null,I=!1,P=!1;function X(){P||(console.log("[Рейтинги] Инициализация обработчиков модального окна"),x&&x.addEventListener("click",R),p&&p.addEventListener("click",J),l&&l.addEventListener("click",F),h&&h.addEventListener("click",R),B&&v&&B.addEventListener("input",function(){const e=this.value.length;v.textContent=e,v.style.color=e>450?"#dc3545":"#6c757d"}),document.addEventListener("keydown",function(e){e.key==="Escape"&&i.classList.contains("show")&&R()}),P=!0)}function R(){if(!I){z("Пожалуйста, сначала оцените текущего специалиста или пропустите оценку","warning");return}S()}function J(){confirm("Вы уверены, что хотите закрыть все оценки? Несохраненные оценки будут потеряны.")&&(f&&(y(f),localStorage.removeItem(`pending_ratings_${f}`)),S(),O(),E("Все оценки закрыты","info"))}function F(){confirm("Вы уверены, что хотите пропустить оценку этого специалиста?")&&(g++,g<d.length?T():N())}function S(){i&&(i.classList.remove("show"),setTimeout(()=>{i&&(i.style.display="none"),Q()},300))}function N(){S(),f&&(y(f),localStorage.removeItem(`pending_ratings_${f}`)),O();const e=m();e.length>0?setTimeout(()=>{window.checkPendingRatings(e[0])},2e3):E("Все оценки завершены! Спасибо за вашу обратную связь 🎉","success")}function z(e,n="info"){const o=document.getElementById("rating-alert");o&&(o.className=`rating-alert ${n}`,o.innerHTML=`<i class="fas fa-${K(n)}"></i> ${e}`,o.style.animation="none",setTimeout(()=>{o.style.animation="rating-alert-flash 0.5s ease-in-out"},10))}function K(e){return{info:"info-circle",warning:"exclamation-triangle",error:"exclamation-circle",success:"check-circle"}[e]||"info-circle"}function D(){console.log("[Рейтинги] Проверка необходимости оценки при загрузке страницы"),A().then(e=>{if(console.log("[Рейтинги] Получены сделки, требующие оценки:",e),e&&e.length>0)for(const o of e)C(o);const n=m();if(console.log("[Рейтинги] ID завершенных сделок из localStorage после обновления:",n),n.length>0){const o=n[0];b(o).then(t=>{console.log("[Рейтинги] Проверка существования сделки:",o,"Результат:",t),t?(console.log("[Рейтинги] Запуск проверки оценок для сделки:",o),typeof window.checkPendingRatings=="function"?window.checkPendingRatings(o):(console.warn("[Рейтинги] Функция checkPendingRatings не найдена, попытка инициализации через таймаут"),setTimeout(()=>{typeof window.checkPendingRatings=="function"?(console.log("[Рейтинги] Функция найдена после таймаута, запуск"),window.checkPendingRatings(o)):console.error("[Рейтинги] Функция checkPendingRatings не определена при загрузке после таймаута")},1e3))):(console.warn("[Рейтинги] Сделка не существует, очистка данных из хранилища:",o),y(o),D())})}else console.log("[Рейтинги] Нет сохраненных ID завершенных сделок")}).catch(e=>{console.error("[Рейтинги] Ошибка при получении списка завершенных сделок:",e)})}function A(){return console.log("[Рейтинги] Запрос списка завершенных сделок, требующих оценки"),new Promise((e,n)=>{if(!document.querySelector('meta[name="csrf-token"]')){console.warn("[Рейтинги] Пользователь не авторизован (CSRF-токен не найден)"),e([]);return}const o=document.querySelector('meta[name="csrf-token"]').getAttribute("content"),t=new AbortController,r=setTimeout(()=>t.abort(),1e4);fetch("/ratings/find-completed-deals",{method:"GET",headers:{"X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":o||"",Accept:"application/json"},credentials:"same-origin",signal:t.signal}).then(s=>{if(clearTimeout(r),!s.ok)throw new Error(`HTTP error! Status: ${s.status}`);return s.json()}).then(s=>{console.log("[Рейтинги] Получен ответ с списком сделок:",s),e(s&&Array.isArray(s.deals)?s.deals:[])}).catch(s=>{clearTimeout(r),s.name==="AbortError"?console.warn("[Рейтинги] Запрос списка сделок был отменен из-за таймаута"):console.error("[Рейтинги] Ошибка при запросе списка завершенных сделок:",s),e([])})})}function m(){const e=localStorage.getItem("completed_deal_ids");return e?JSON.parse(e):[]}function C(e){const n=m();n.includes(e)||(n.push(e),localStorage.setItem("completed_deal_ids",JSON.stringify(n)))}function y(e){const o=m().filter(t=>t!==e);localStorage.setItem("completed_deal_ids",JSON.stringify(o)),localStorage.removeItem(`pending_ratings_${e}`)}function W(){document.body.classList.add("rating-in-progress"),document.addEventListener("keydown",$),window.onbeforeunload=function(){return"Пожалуйста, оцените всех специалистов перед закрытием страницы."}}function $(e){if(e.key==="Escape"||e.key==="Tab"){e.preventDefault();const n=document.querySelector(".rating-alert");n&&(n.style.animation="none",setTimeout(()=>{n.style.animation="rating-alert-flash 0.5s ease-in-out"},10))}}function O(){document.body.classList.remove("rating-in-progress"),document.removeEventListener("keydown",$),window.onbeforeunload=null,localStorage.removeItem("pendingRatingsState")}function G(){localStorage.setItem("pendingRatingsState",JSON.stringify({pendingRatings:d,currentIndex:g,dealId:f}))}c.forEach(e=>{e.addEventListener("mouseover",function(){const n=parseInt(this.dataset.value);k(n)}),e.addEventListener("mouseout",function(){k(u)}),e.addEventListener("click",function(){u=parseInt(this.dataset.value),k(u),w()})}),X();function w(){if(a){const e=u>0;a.disabled=!e,a.innerHTML=e?'<i class="fas fa-star"></i> Оценить':'<i class="fas fa-star-o"></i> Выберите оценку',I=e}}function k(e){c.forEach(n=>{parseInt(n.dataset.value)<=e?n.classList.add("active"):n.classList.remove("active")})}a&&a.addEventListener("click",function(){if(u===0){const t=document.getElementById("rating-alert");t&&(t.className="rating-alert error",t.innerHTML='<i class="fas fa-exclamation-triangle"></i> Пожалуйста, выберите оценку от 1 до 5 звезд!',setTimeout(()=>{t.className="rating-alert",M()},3e3));return}a.disabled=!0,a.innerHTML='<i class="fas fa-spinner fa-spin"></i> Отправка...';const e=d[g],n=document.getElementById("rating-comment").value,o=document.querySelector('meta[name="csrf-token"]').getAttribute("content");fetch("/ratings/store",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":o,Accept:"application/json"},body:JSON.stringify({deal_id:f,rated_user_id:e.user_id,score:u,comment:n,role:e.role})}).then(t=>t.json()).then(t=>{t.success?(E(`Оценка для ${e.name} успешно сохранена!`,"success"),g++,G(),g<d.length?T():N()):(E(t.message||"Произошла ошибка при сохранении оценки.","warning"),a.disabled=!1,w())}).catch(t=>{console.error("[Рейтинги] Ошибка при отправке оценки:",t),E("Произошла ошибка при сохранении оценки.","warning"),a.disabled=!1,w()})});function Y(){const e=document.getElementById("rating-progress-fill");if(e&&d.length>0){const n=(g+1)/d.length*100;e.style.width=`${n}%`}}function T(){if(g>=d.length)return;const e=d[g];document.getElementById("rating-user-name").textContent=e.name,document.getElementById("rating-user-role").textContent=U(e.role),document.getElementById("rating-user-avatar").src=e.avatar_url||"/storage/icon/profile.svg",document.getElementById("current-rating-index").textContent=g+1,document.getElementById("total-ratings").textContent=d.length,Y();const n=document.getElementById("rating-user-status");n&&(n.className=`rating-user-status ${e.isOnline?"online":"offline"}`);const o=document.querySelector("#rating-modal h2"),t=document.getElementById("rating-instruction");e.role==="coordinator"?(o.textContent="Оцените качество планировочных координатора",t.textContent="Оцените качество координации проекта от 1 до 5 звезд"):e.role==="architect"?(o.textContent="Оценка работы архитектора",t.textContent="Оцените качество планировочных решений от 1 до 5 звезд"):e.role==="designer"?(o.textContent="Оценка работы дизайнера",t.textContent="Оцените качество дизайнерских решений от 1 до 5 звезд"):e.role==="visualizer"?(o.textContent="Оценка работы визуализатора",t.textContent="Оцените качество визуализаций от 1 до 5 звезд"):(o.textContent="Оценка работы специалиста",t.textContent="Оцените качество работы специалиста от 1 до 5 звезд"),M(),u=0,k(0),w();const r=document.getElementById("rating-comment");if(r){r.value="";const s=document.getElementById("comment-char-count");s&&(s.textContent="0",s.style.color="#6c757d")}}function U(e){return{architect:"Архитектор",designer:"Дизайнер",visualizer:"Визуализатор",coordinator:"Координатор",partner:"Партнер"}[e]||e}function Q(){u=0,d=[],g=0,f=null,I=!1,k(0),w();const e=document.getElementById("rating-comment");e&&(e.value="");const n=document.getElementById("comment-char-count");n&&(n.textContent="0",n.style.color="#6c757d");const o=document.getElementById("rating-progress-fill");o&&(o.style.width="0%");const t=document.getElementById("rating-alert");t&&(t.className="rating-alert",t.textContent="Для продолжения работы необходимо оценить всех специалистов по данной сделке")}function E(e,n="info",o=4e3){const t=document.createElement("div");if(t.className=`notification notification-${n}`,t.innerHTML=`
                <div class="notification-content">
                    <i class="fas fa-${V(n)}"></i>
                    <span>${e}</span>
                    <button type="button" class="notification-close" onclick="this.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `,!document.getElementById("notification-styles")){const r=document.createElement("style");r.id="notification-styles",r.textContent=`
                    .notification {
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        z-index: 10001;
                        max-width: 400px;
                        padding: 0;
                        border-radius: 8px;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                        animation: slideInRight 0.3s ease-out;
                        margin-bottom: 10px;
                    }
                    
                    .notification-content {
                        padding: 15px 20px;
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        color: white;
                        border-radius: 8px;
                    }
                    
                    .notification-info .notification-content { background: #17a2b8; }
                    .notification-success .notification-content { background: #28a745; }
                    .notification-warning .notification-content { background: #ffc107; color: #212529; }
                    .notification-error .notification-content { background: #dc3545; }
                    
                    .notification-close {
                        background: none;
                        border: none;
                        color: inherit;
                        cursor: pointer;
                        padding: 5px;
                        border-radius: 4px;
                        margin-left: auto;
                    }
                    
                    .notification-close:hover {
                        background: rgba(255,255,255,0.2);
                    }
                    
                    @keyframes slideInRight {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                `,document.head.appendChild(r)}document.body.appendChild(t),setTimeout(()=>{t.parentNode&&(t.style.animation="slideInRight 0.3s ease-out reverse",setTimeout(()=>t.remove(),300))},o)}function V(e){return{info:"info-circle",success:"check-circle",warning:"exclamation-triangle",error:"exclamation-circle"}[e]||"info-circle"}function M(){const e=document.getElementById("rating-alert");if(e&&d.length>0){const n=g+1,o=d.length,t=d[g];e.className="rating-alert",e.innerHTML=`
                    <i class="fas fa-star"></i> 
                    Оцениваем ${n} из ${o}: ${(t==null?void 0:t.name)||"специалиста"}
                `}}if(typeof window.Laravel>"u"||!window.Laravel.user){console.error("[Рейтинги] Отсутствует объект window.Laravel или информация о пользователе");return}if(!window.Laravel.user.status||!window.Laravel.user.id){console.error("[Рейтинги] У пользователя отсутствует статус или ID");return}const j=["coordinator","partner","client","user"].includes(window.Laravel.user.status);if(console.log("[Рейтинги] Пользователь может оценивать:",j,"Статус:",window.Laravel.user.status),!j){console.log("[Рейтинги] Пользователь не может оценивать других по его статусу");return}window.checkPendingRatings=function(e){if(!e){console.warn("[Рейтинги] Вызов checkPendingRatings без dealId");return}console.log("[Рейтинги] Проверка ожидающих оценок для сделки:",e),b(e).then(n=>{var t;if(console.log("[Рейтинги] Проверка существования сделки перед запросом:",e,"Результат:",n),!n){console.warn("[Рейтинги] Сделка не существует, очистка данных из хранилища:",e),y(e);return}const o=(t=document.querySelector('meta[name="csrf-token"]'))==null?void 0:t.getAttribute("content");console.log("[Рейтинги] CSRF-токен для запроса:",o?"Получен":"Отсутствует"),fetch(`/ratings/check-pending?deal_id=${e}`,{method:"GET",headers:{"X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":o,Accept:"application/json","Content-Type":"application/json"},credentials:"same-origin"}).then(r=>{if(console.log("[Рейтинги] Статус ответа API:",r.status),!r.ok)throw new Error(`HTTP error! Status: ${r.status}`);return r.json()}).then(r=>{if(console.log("[Рейтинги] Получены данные о необходимых оценках:",r),r.pending_ratings&&r.pending_ratings.length>0){console.log("[Рейтинги] Найдены пользователи для оценки:",r.pending_ratings.length),f=e,d=r.pending_ratings,g=0,localStorage.setItem(`pending_ratings_${e}`,JSON.stringify(d)),C(e);const s=document.getElementById("rating-modal");s?(console.log("[Рейтинги] Отображаем модальное окно для оценок"),W(),T(),s?(s.style.display="flex",setTimeout(()=>{s&&s.classList.add("show")},10)):console.warn("[Рейтинги] Модальное окно оценок не найдено на странице")):console.error("[Рейтинги] Не найден элемент #rating-modal")}else{console.log("[Рейтинги] Нет пользователей для оценки или все уже оценены"),y(e);const s=m();s.length>0&&setTimeout(()=>{window.checkPendingRatings(s[0])},1e3)}}).catch(r=>{console.error("[Рейтинги] Ошибка при проверке ожидающих оценок:",r),y(e)})})};const q=document.createElement("style");q.textContent=`
            @keyframes rating-alert-flash {
                0% { transform: scale(1); }
                50% { transform: scale(1.03); background-color: #ffeeba; }
                100% { transform: scale(1); }
            }
            
            /* Стили для модального окна оценки */
            .rating-in-progress {
                overflow: hidden !important;
            }
            
            .rating-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.8);
                z-index: 10000;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .rating-modal-content {
                background: #fff;
                border-radius: 10px;
                padding: 30px;
                max-width: 500px;
                width: 90%;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            }
            
            .rating-user-info {
                display: flex;
                align-items: center;
                margin: 20px 0;
                padding: 10px;
                background: #f9f9f9;
                border-radius: 8px;
            }
            
            .rating-avatar {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                margin-right: 15px;
                object-fit: cover;
            }
            
            .rating-stars {
                display: flex;
                justify-content: center;
                font-size: 30px;
                margin: 20px 0;
            }
            
            .star {
                cursor: pointer;
                color: #ddd;
                margin: 0 5px;
                transition: transform 0.2s;
            }
            
            .star:hover {
                transform: scale(1.2);
            }
            
            .star.active {
                color: #ffbf00;
            }
            
            .rating-comment textarea {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                min-height: 100px;
                margin-top: 10px;
            }
            
            /* Информационные сообщения */
            .info-message {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                background: #e9f5ff;
                color: #0069d9;
                border: 1px solid #b8daff;
                border-radius: 4px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                z-index: 9999;
                animation: fadeIn 0.3s ease-out;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `,document.head.appendChild(q);const _=m();_.length>0?(console.log("[Рейтинги] Найдены ID завершенных сделок в localStorage:",_),setTimeout(()=>{console.log("[Рейтинги] Запуск проверки оценок для первой сделки из списка после таймаута"),window.checkPendingRatings(_[0])},1500)):console.log("[Рейтинги] Нет ID завершенных сделок в localStorage"),D(),H(),setInterval(()=>{console.log("[Рейтинги] Запуск периодической проверки новых завершенных сделок"),A().then(e=>{if(e&&e.length>0){const n=m();let o=!1;for(const t of e)n.includes(t)||(C(t),o=!0);if(o){const t=m();t.length>0&&window.checkPendingRatings(t[0])}}})},5*60*1e3)}),window.runRatingCheck=function(i){if(console.log("[Рейтинги] Вызов runRatingCheck для сделки:",i),!i){console.error("[Рейтинги] Вызов runRatingCheck без ID сделки");return}const c=L();c.includes(i)||(c.push(i),localStorage.setItem("completed_deal_ids",JSON.stringify(c))),typeof window.checkPendingRatings=="function"?(console.log("[Рейтинги] Запуск checkPendingRatings из runRatingCheck"),window.checkPendingRatings(i)):(console.error("[Рейтинги] Функция checkPendingRatings не определена"),setTimeout(()=>{typeof window.checkPendingRatings=="function"?(console.log("[Рейтинги] Функция найдена после таймаута, запуск"),window.checkPendingRatings(i)):console.error("[Рейтинги] Функция checkPendingRatings все еще не определена после таймаута")},2e3))};function L(){const i=localStorage.getItem("completed_deal_ids");return i?JSON.parse(i):[]}function H(){const i=[];for(let c=0;c<localStorage.length;c++){const a=localStorage.key(c);a&&(a.startsWith("pending_ratings_")||a==="completed_deal_ids")&&i.push(a)}i.forEach(c=>{if(c==="completed_deal_ids"){const a=JSON.parse(localStorage.getItem(c)||"[]"),l=[],x=a.map(p=>b(p).then(h=>{h&&l.push(p)}));Promise.all(x).then(()=>{localStorage.setItem("completed_deal_ids",JSON.stringify(l))})}else if(c.startsWith("pending_ratings_")){const a=c.replace("pending_ratings_","");b(a).then(l=>{if(!l){console.warn("[Рейтинги] Сделка не существует, очистка данных из хранилища:",a),localStorage.removeItem(c);const p=L().filter(h=>h!==a);localStorage.setItem("completed_deal_ids",JSON.stringify(p))}})}})}function b(i){return console.log("[Рейтинги] Проверка существования сделки:",i),i?new Promise(c=>{var a;fetch(`/deal/${i}/exists`,{method:"GET",headers:{"Content-Type":"application/json","X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":(a=document.querySelector('meta[name="csrf-token"]'))==null?void 0:a.getAttribute("content"),Accept:"application/json"},credentials:"same-origin"}).then(l=>l.ok?l.json():(console.warn("[Рейтинги] Ошибка проверки сделки, HTTP-статус:",l.status),{exists:!1})).then(l=>{console.log("[Рейтинги] Результат проверки сделки:",l),c(l.exists===!0)}).catch(l=>{console.error("[Рейтинги] Ошибка при проверке сделки:",l),c(!1)})}):(console.error("[Рейтинги] Вызов verifyDealExists без ID сделки"),Promise.resolve(!1))}})();
