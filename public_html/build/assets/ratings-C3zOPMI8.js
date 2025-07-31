(function(){document.addEventListener("DOMContentLoaded",function(){console.log("[Рейтинги] Инициализация системы рейтингов");const s=document.getElementById("rating-modal");if(!s){console.error("[Рейтинги] Не найден элемент модального окна #rating-modal");return}console.log("[Рейтинги] Модальное окно найдено:",s);const r=s.querySelectorAll(".rating-stars .star"),c=document.getElementById("submit-rating"),l=document.getElementById("skip-rating"),y=document.getElementById("rating-modal-close"),f=document.getElementById("close-all-ratings"),h=document.getElementById("rating-modal-backdrop"),R=document.getElementById("rating-comment"),v=document.getElementById("comment-char-count");console.log("[Рейтинги] Элементы найдены:",{stars:r.length,submitBtn:!!c,skipBtn:!!l,closeBtn:!!y,closeAllBtn:!!f,backdrop:!!h,commentTextarea:!!R,charCount:!!v}),["rating-user-name","rating-user-role","rating-user-avatar","current-rating-index","total-ratings","rating-instruction","rating-alert"].forEach(e=>{document.getElementById(e)||console.warn(`[Рейтинги] Не найден критический элемент: #${e}`)});let u=0,d=[],g=0,m=null,S=!1,D=!1;function z(){D||(console.log("[Рейтинги] Инициализация обработчиков модального окна"),y&&y.addEventListener("click",C),f&&f.addEventListener("click",W),l&&l.addEventListener("click",K),h&&h.addEventListener("click",C),R&&v&&R.addEventListener("input",function(){const e=this.value.length;v.textContent=e,v.style.color=e>450?"#dc3545":"#6c757d"}),document.addEventListener("keydown",function(e){e.key==="Escape"&&s.classList.contains("show")&&C()}),D=!0)}function C(){if(!S){G("Пожалуйста, сначала оцените текущего специалиста или пропустите оценку","warning");return}T()}function W(){confirm("Вы уверены, что хотите закрыть все оценки? Несохраненные оценки будут потеряны.")&&(m&&(x(m),localStorage.removeItem(`pending_ratings_${m}`)),T(),O(),b("Все оценки закрыты","info"))}function K(){confirm("Вы уверены, что хотите пропустить оценку этого специалиста?")&&(g++,g<d.length?B():j())}function T(){s&&(s.classList.remove("show"),setTimeout(()=>{s&&(s.style.display="none"),Z()},300))}function j(){T(),m&&(x(m),localStorage.removeItem(`pending_ratings_${m}`)),O();const e=p();e.length>0?setTimeout(()=>{window.checkPendingRatings(e[0])},2e3):(b("Все оценки завершены! Спасибо за вашу обратную связь 🎉","success"),setTimeout(()=>{location.reload()},3e3))}function G(e,t="info"){const n=document.getElementById("rating-alert");n&&(n.className=`rating-alert ${t}`,n.innerHTML=`<i class="fas fa-${Y(t)}"></i> ${e}`,n.style.animation="none",setTimeout(()=>{n.style.animation="rating-alert-flash 0.5s ease-in-out"},10))}function Y(e){return{info:"info-circle",warning:"exclamation-triangle",error:"exclamation-circle",success:"check-circle"}[e]||"info-circle"}function A(){console.log("[Рейтинги] Проверка необходимости оценки при загрузке страницы"),$().then(e=>{if(console.log("[Рейтинги] Получены сделки, требующие оценки:",e),e&&e.length>0)for(const n of e)_(n);const t=p();if(console.log("[Рейтинги] ID завершенных сделок из localStorage после обновления:",t),t.length>0){const n=t[0];I(n).then(o=>{console.log("[Рейтинги] Проверка существования сделки:",n,"Результат:",o),o?(console.log("[Рейтинги] Запуск проверки оценок для сделки:",n),typeof window.checkPendingRatings=="function"?window.checkPendingRatings(n):(console.warn("[Рейтинги] Функция checkPendingRatings не найдена, попытка инициализации через таймаут"),setTimeout(()=>{typeof window.checkPendingRatings=="function"?(console.log("[Рейтинги] Функция найдена после таймаута, запуск"),window.checkPendingRatings(n)):console.error("[Рейтинги] Функция checkPendingRatings не определена при загрузке после таймаута")},1e3))):(console.warn("[Рейтинги] Сделка не существует, очистка данных из хранилища:",n),x(n),A())})}else console.log("[Рейтинги] Нет сохраненных ID завершенных сделок")}).catch(e=>{console.error("[Рейтинги] Ошибка при получении списка завершенных сделок:",e)})}function $(){return console.log("[Рейтинги] Запрос списка завершенных сделок, требующих оценки"),new Promise((e,t)=>{if(!document.querySelector('meta[name="csrf-token"]')){console.warn("[Рейтинги] Пользователь не авторизован (CSRF-токен не найден)"),e([]);return}const n=document.querySelector('meta[name="csrf-token"]').getAttribute("content"),o=new AbortController,i=setTimeout(()=>o.abort(),1e4);fetch("/ratings/find-completed-deals",{method:"GET",headers:{"X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":n||"",Accept:"application/json"},credentials:"same-origin",signal:o.signal}).then(a=>{if(clearTimeout(i),!a.ok)throw new Error(`HTTP error! Status: ${a.status}`);return a.json()}).then(a=>{console.log("[Рейтинги] Получен ответ с списком сделок:",a),e(a&&Array.isArray(a.deals)?a.deals:[])}).catch(a=>{clearTimeout(i),a.name==="AbortError"?console.warn("[Рейтинги] Запрос списка сделок был отменен из-за таймаута"):console.error("[Рейтинги] Ошибка при запросе списка завершенных сделок:",a),e([])})})}function p(){const e=localStorage.getItem("completed_deal_ids");return e?JSON.parse(e):[]}function _(e){const t=p();t.includes(e)||(t.push(e),localStorage.setItem("completed_deal_ids",JSON.stringify(t)))}function x(e){const n=p().filter(o=>o!==e);localStorage.setItem("completed_deal_ids",JSON.stringify(n)),localStorage.removeItem(`pending_ratings_${e}`)}function U(){document.body.classList.add("rating-in-progress"),document.addEventListener("keydown",M),window.onbeforeunload=function(){return"Пожалуйста, оцените всех специалистов перед закрытием страницы."}}function M(e){if(e.key==="Escape"||e.key==="Tab"){e.preventDefault();const t=document.querySelector(".rating-alert");t&&(t.style.animation="none",setTimeout(()=>{t.style.animation="rating-alert-flash 0.5s ease-in-out"},10))}}function O(){document.body.classList.remove("rating-in-progress"),document.removeEventListener("keydown",M),window.onbeforeunload=null,localStorage.removeItem("pendingRatingsState")}function Q(){localStorage.setItem("pendingRatingsState",JSON.stringify({pendingRatings:d,currentIndex:g,dealId:m}))}r.forEach(e=>{e.addEventListener("mouseover",function(){const t=parseInt(this.dataset.value);k(t)}),e.addEventListener("mouseout",function(){k(u)}),e.addEventListener("click",function(){u=parseInt(this.dataset.value),k(u),w()})}),z();function w(){if(c){const e=u>0;c.disabled=!e,c.innerHTML=e?'<i class="fas fa-star"></i> Оценить':'<i class="fas fa-star-o"></i> Выберите оценку',S=e}}function k(e){r&&r.length>0&&r.forEach(t=>{parseInt(t.dataset.value)<=e?t.classList.add("active"):t.classList.remove("active")})}c&&c.addEventListener("click",function(){if(u===0){const i=document.getElementById("rating-alert");i&&(i.className="rating-alert error",i.innerHTML='<i class="fas fa-exclamation-triangle"></i> Пожалуйста, выберите оценку от 1 до 5 звезд!',setTimeout(()=>{i.className="rating-alert",q()},3e3));return}c.disabled=!0,c.innerHTML='<i class="fas fa-spinner fa-spin"></i> Отправка...';const e=d[g],t=document.getElementById("rating-comment"),n=t?t.value:"",o=document.querySelector('meta[name="csrf-token"]').getAttribute("content");fetch("/ratings/store",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":o,Accept:"application/json"},body:JSON.stringify({deal_id:m,rated_user_id:e.user_id,score:u,comment:n,role:e.role})}).then(i=>i.json()).then(i=>{i.success?(b(`Оценка для ${e.name} успешно сохранена!`,"success"),g++,Q(),g<d.length?B():j()):(b(i.message||"Произошла ошибка при сохранении оценки.","warning"),c.disabled=!1,w())}).catch(i=>{console.error("[Рейтинги] Ошибка при отправке оценки:",i),b("Произошла ошибка при сохранении оценки.","warning"),c.disabled=!1,w()})});function B(){if(g>=d.length)return;const e=d[g],t=document.getElementById("rating-user-name"),n=document.getElementById("rating-user-role"),o=document.getElementById("rating-user-avatar"),i=document.getElementById("current-rating-index"),a=document.getElementById("total-ratings");t&&(t.textContent=e.name),n&&(n.textContent=V(e.role)),o&&(o.src=e.avatar_url||"/storage/icon/profile.svg"),i&&(i.textContent=g+1),a&&(a.textContent=d.length),m&&te(m);const E=document.querySelector("#rating-modal h3");E&&(e.role==="coordinator"?E.textContent="Оценка работы координатора":e.role==="architect"?E.textContent="Оценка работы архитектора":e.role==="designer"?E.textContent="Оценка работы дизайнера":e.role==="visualizer"?E.textContent="Оценка работы визуализатора":E.textContent="Оценка работы специалиста"),q(),u=0,k(0),w();const J=document.getElementById("rating-comment");if(J){J.value="";const P=document.getElementById("comment-char-count");P&&(P.textContent="0",P.style.color="#6c757d")}}function V(e){return{architect:"Архитектор",designer:"Дизайнер",visualizer:"Визуализатор",coordinator:"Координатор",partner:"Партнер"}[e]||e}function Z(){u=0,d=[],g=0,m=null,S=!1,k(0),w();const e=document.getElementById("rating-comment");e&&(e.value="");const t=document.getElementById("comment-char-count");t&&(t.textContent="0",t.style.color="#6c757d");const n=document.getElementById("rating-progress-fill");n&&(n.style.width="0%");const o=document.getElementById("rating-alert");o&&(o.className="rating-alert",o.textContent="Для продолжения работы необходимо оценить всех специалистов по данной сделке")}function b(e,t="info",n=4e3){const o=document.createElement("div");if(o.className=`notification notification-${t}`,o.innerHTML=`
                <div class="notification-content">
                    <i class="fas fa-${ee(t)}"></i>
                    <span>${e}</span>
                    <button type="button" class="notification-close" onclick="this.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `,!document.getElementById("notification-styles")){const i=document.createElement("style");i.id="notification-styles",i.textContent=`
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
                `,document.head.appendChild(i)}document.body.appendChild(o),setTimeout(()=>{o.parentNode&&(o.style.animation="slideInRight 0.3s ease-out reverse",setTimeout(()=>o.remove(),300))},n)}function ee(e){return{info:"info-circle",success:"check-circle",warning:"exclamation-triangle",error:"exclamation-circle"}[e]||"info-circle"}function te(e){fetch(`/deal/${e}/data`,{method:"GET",headers:{"X-Requested-With":"XMLHttpRequest",Accept:"application/json"},credentials:"same-origin"}).then(t=>t.json()).then(t=>{if(t.success&&t.deal){const n=t.deal,o=document.getElementById("deal-project-number"),i=document.getElementById("deal-client-info"),a=document.getElementById("deal-client-phone");o&&(o.textContent=n.project_number||"не указан"),i&&(i.textContent=n.client_name||"не указан"),a&&(a.textContent=n.client_phone||"не указан")}}).catch(t=>{console.warn("[Рейтинги] Не удалось получить информацию о сделке:",t);const n=document.getElementById("deal-project-number"),o=document.getElementById("deal-client-info"),i=document.getElementById("deal-client-phone");n&&(n.textContent=`№ ${e}`),o&&(o.textContent="информация недоступна"),i&&(i.textContent="не указан")})}function q(){const e=document.getElementById("rating-alert");if(e&&d.length>0){const t=g+1,n=d.length,o=d[g];e.className="rating-alert",e.innerHTML=`
                    <i class="fas fa-star"></i> 
                    Оцениваем ${t} из ${n}: ${(o==null?void 0:o.name)||"специалиста"}
                `}}if(typeof window.Laravel>"u"||!window.Laravel.user){console.error("[Рейтинги] Отсутствует объект window.Laravel или информация о пользователе");return}if(!window.Laravel.user.status||!window.Laravel.user.id){console.error("[Рейтинги] У пользователя отсутствует статус или ID");return}const H=["coordinator","partner","client","user"].includes(window.Laravel.user.status);if(console.log("[Рейтинги] Пользователь может оценивать:",H,"Статус:",window.Laravel.user.status),!H){console.log("[Рейтинги] Пользователь не может оценивать других по его статусу");return}window.checkPendingRatings=function(e){if(!e){console.warn("[Рейтинги] Вызов checkPendingRatings без dealId");return}console.log("[Рейтинги] Проверка ожидающих оценок для сделки:",e),I(e).then(t=>{var o;if(console.log("[Рейтинги] Проверка существования сделки перед запросом:",e,"Результат:",t),!t){console.warn("[Рейтинги] Сделка не существует, очистка данных из хранилища:",e),x(e);return}const n=(o=document.querySelector('meta[name="csrf-token"]'))==null?void 0:o.getAttribute("content");console.log("[Рейтинги] CSRF-токен для запроса:",n?"Получен":"Отсутствует"),fetch(`/ratings/check-pending?deal_id=${e}`,{method:"GET",headers:{"X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":n,Accept:"application/json","Content-Type":"application/json"},credentials:"same-origin"}).then(i=>{if(console.log("[Рейтинги] Статус ответа API:",i.status),!i.ok)throw new Error(`HTTP error! Status: ${i.status}`);return i.json()}).then(i=>{if(console.log("[Рейтинги] Получены данные о необходимых оценках:",i),i.pending_ratings&&i.pending_ratings.length>0){console.log("[Рейтинги] Найдены пользователи для оценки:",i.pending_ratings.length),m=e,d=i.pending_ratings,g=0,localStorage.setItem(`pending_ratings_${e}`,JSON.stringify(d)),_(e);const a=document.getElementById("rating-modal");a?(console.log("[Рейтинги] Отображаем модальное окно для оценок"),U(),B(),a?(a.style.display="flex",setTimeout(()=>{a&&a.classList.add("show")},10)):console.warn("[Рейтинги] Модальное окно оценок не найдено на странице")):console.error("[Рейтинги] Не найден элемент #rating-modal")}else{console.log("[Рейтинги] Нет пользователей для оценки или все уже оценены"),x(e);const a=p();a.length>0&&setTimeout(()=>{window.checkPendingRatings(a[0])},1e3)}}).catch(i=>{console.error("[Рейтинги] Ошибка при проверке ожидающих оценок:",i),x(e)})})};const X=document.createElement("style");X.textContent=`
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
        `,document.head.appendChild(X);const L=p();L.length>0?(console.log("[Рейтинги] Найдены ID завершенных сделок в localStorage:",L),setTimeout(()=>{console.log("[Рейтинги] Запуск проверки оценок для первой сделки из списка после таймаута"),window.checkPendingRatings(L[0])},1500)):console.log("[Рейтинги] Нет ID завершенных сделок в localStorage"),A(),F(),setInterval(()=>{console.log("[Рейтинги] Запуск периодической проверки новых завершенных сделок"),$().then(e=>{if(e&&e.length>0){const t=p();let n=!1;for(const o of e)t.includes(o)||(_(o),n=!0);if(n){const o=p();o.length>0&&window.checkPendingRatings(o[0])}}})},5*60*1e3)}),window.runRatingCheck=function(s){if(console.log("[Рейтинги] Вызов runRatingCheck для сделки:",s),!s){console.error("[Рейтинги] Вызов runRatingCheck без ID сделки");return}const r=N();r.includes(s)||(r.push(s),localStorage.setItem("completed_deal_ids",JSON.stringify(r))),typeof window.checkPendingRatings=="function"?(console.log("[Рейтинги] Запуск checkPendingRatings из runRatingCheck"),window.checkPendingRatings(s)):(console.error("[Рейтинги] Функция checkPendingRatings не определена"),setTimeout(()=>{typeof window.checkPendingRatings=="function"?(console.log("[Рейтинги] Функция найдена после таймаута, запуск"),window.checkPendingRatings(s)):console.error("[Рейтинги] Функция checkPendingRatings все еще не определена после таймаута")},2e3))};function N(){const s=localStorage.getItem("completed_deal_ids");return s?JSON.parse(s):[]}function F(){const s=[];for(let r=0;r<localStorage.length;r++){const c=localStorage.key(r);c&&(c.startsWith("pending_ratings_")||c==="completed_deal_ids")&&s.push(c)}s.forEach(r=>{if(r==="completed_deal_ids"){const c=JSON.parse(localStorage.getItem(r)||"[]"),l=[],y=c.map(f=>I(f).then(h=>{h&&l.push(f)}));Promise.all(y).then(()=>{localStorage.setItem("completed_deal_ids",JSON.stringify(l))})}else if(r.startsWith("pending_ratings_")){const c=r.replace("pending_ratings_","");I(c).then(l=>{if(!l){console.warn("[Рейтинги] Сделка не существует, очистка данных из хранилища:",c),localStorage.removeItem(r);const f=N().filter(h=>h!==c);localStorage.setItem("completed_deal_ids",JSON.stringify(f))}})}})}function I(s){return console.log("[Рейтинги] Проверка существования сделки:",s),s?new Promise(r=>{var c;fetch(`/deal/${s}/exists`,{method:"GET",headers:{"Content-Type":"application/json","X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":(c=document.querySelector('meta[name="csrf-token"]'))==null?void 0:c.getAttribute("content"),Accept:"application/json"},credentials:"same-origin"}).then(l=>l.ok?l.json():(console.warn("[Рейтинги] Ошибка проверки сделки, HTTP-статус:",l.status),{exists:!1})).then(l=>{console.log("[Рейтинги] Результат проверки сделки:",l),r(l.exists===!0)}).catch(l=>{console.error("[Рейтинги] Ошибка при проверке сделки:",l),r(!1)})}):(console.error("[Рейтинги] Вызов verifyDealExists без ID сделки"),Promise.resolve(!1))}})();
