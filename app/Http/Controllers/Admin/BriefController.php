<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Common;
use App\Models\Commercial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Убедитесь, что класс наследуется от правильного базового контроллера
class BriefController extends BaseAdminController
{
    /**
     * Редактирование общего брифа
     */
    public function editCommon($id)
    {
        $title_site = "Редактировать общий бриф | Личный кабинет Экспресс-дизайн";
        $brief = Common::findOrFail($id);  // Get the Common brief by ID
        
        // Декодируем данные JSON полей
        $zones = $brief->rooms ? json_decode($brief->rooms, true) : [];
        
        // Обработка ответов на вопросы - улучшенная логика
        $answers = [];
        
        // 1. Сначала проверяем наличие JSON-поля answers и правильно декодируем его
        if (!empty($brief->answers)) {
            $decodedAnswers = json_decode($brief->answers, true);
            if (is_array($decodedAnswers)) {
                $answers = $decodedAnswers;
                Log::info('Найдены ответы в JSON-поле answers', [
                    'brief_id' => $id,
                    'answers_count' => count($answers)
                ]);
            } else {
                Log::warning('JSON-поле answers не содержит валидный массив', [
                    'brief_id' => $id,
                    'answers_raw' => $brief->answers
                ]);
            }
        }
        
        // 2. Если ответов в JSON-поле нет или они неполные, собираем их из индивидуальных полей и дополняем
        
        // Структура ответов по страницам
        if (!isset($answers['page1'])) $answers['page1'] = [];
        if (!isset($answers['page2'])) $answers['page2'] = [];
        if (!isset($answers['page3'])) $answers['page3'] = [];
        if (!isset($answers['page4'])) $answers['page4'] = [];
        if (!isset($answers['page5'])) $answers['page5'] = [];
        
        // Названия вопросов по страницам
        $questionTitles = [
            1 => [
                'question_1_1' => 'Сколько человек будет проживать в квартире',
                'question_1_2' => 'Есть ли домашние животные и растения',
                'question_1_3' => 'Есть ли у членов семьи особые увлечения или хобби',
                'question_1_4' => 'Какие потребности у семьи',
                'question_1_5' => 'Как часто вы встречаете гостей',
                'question_1_6' => 'Адрес'
            ],
            2 => [
                'question_2_1' => 'Какой стиль Вы хотите видеть в своем интерьере',
                'question_2_2' => 'Референсы интерьера',
                'question_2_3' => 'Какую атмосферу вы хотите ощущать',
                'question_2_4' => 'Предметы обстановки для нового интерьера',
                'question_2_5' => 'Что не должно быть в интерьере',
                'question_2_6' => 'Ценовой сегмент ремонта',
                'question_2_7' => 'Предпочтения по цветам и материалам'
            ],
            3 => [
                'question_3_1' => 'Прихожая',
                'question_3_2' => 'Детская',
                'question_3_3' => 'Кладовая',
                'question_3_4' => 'Кухня и гостиная',
                'question_3_5' => 'Гостевой санузел',
                'question_3_6' => 'Гостиная',
                'question_3_7' => 'Рабочее место',
                'question_3_8' => 'Столовая',
                'question_3_9' => 'Ванная комната',
                'question_3_10' => 'Кухня',
                'question_3_11' => 'Кабинет',
                'question_3_12' => 'Спальня',
                'question_3_13' => 'Гардеробная',
                'question_3_14' => 'Другое'
            ],
            4 => [
                'question_4_1' => 'Напольные покрытия',
                'question_4_2' => 'Двери',
                'question_4_3' => 'Отделка стен',
                'question_4_4' => 'Освещение и электрика',
                'question_4_5' => 'Потолки',
                'question_4_6' => 'Дополнительные пожелания по отделке'
            ],
            5 => [
                'question_5_1' => 'Пожелания по звукоизоляции',
                'question_5_2' => 'Теплые полы',
                'question_5_3' => 'Предпочтения по размещению и типу радиаторов',
                'question_5_4' => 'Водоснабжение',
                'question_5_5' => 'Кондиционирование и вентиляция',
                'question_5_6' => 'Сети и коммуникации',
                'question_5_7' => 'Системы безопасности',
                'question_5_8' => 'Умный дом'
            ]
        ];
        
        // Проходимся по всем полям брифа
        foreach ($brief->getAttributes() as $key => $value) {
            // Ищем поля типа question_X_Y
            if (preg_match('/^question_(\d+)_(\d+)$/', $key, $matches) && !empty($value)) {
                $page = $matches[1];
                $question = $matches[2];
                
                // Получаем заголовок вопроса из массива или используем ключ
                $questionTitle = $questionTitles[$page][$key] ?? "Вопрос $question";
                
                // Добавляем ответ в соответствующую страницу, если его еще нет
                if (!isset($answers['page'.$page][$questionTitle])) {
                    $answers['page'.$page][$questionTitle] = $value;
                    Log::info("Добавлен ответ из поля $key", [
                        'brief_id' => $id,
                        'question' => $questionTitle,
                        'answer' => substr($value, 0, 30) . (strlen($value) > 30 ? '...' : '')
                    ]);
                }
            }
        }
        
        // 3. Проверяем поля для отдельных вопросов (если они есть)
        $specificQuestionFields = [
            'style_preferences',
            'floor_preferences',
            'wall_preferences',
            'ceiling_preferences',
            'door_preferences',
            'lighting_preferences',
            'bathroom_preferences',
            'kitchen_preferences'
            // можно добавить другие поля, если они есть
        ];
        
        foreach ($specificQuestionFields as $field) {
            if (!empty($brief->$field)) {
                // В зависимости от имени поля определяем, к какой странице относится вопрос
                $page = $this->mapFieldToPage($field);
                $questionTitle = $this->getQuestionTitleForField($field);
                
                if ($page && $questionTitle) {
                    $answers['page'.$page][$questionTitle] = $brief->$field;
                    Log::info("Добавлен ответ из специального поля $field", [
                        'brief_id' => $id,
                        'page' => $page,
                        'question' => $questionTitle
                    ]);
                }
            }
        }
        
        // 4. Если после всех проверок ответов все равно нет, создаём пустую структуру
        $hasAnswers = false;
        foreach ($answers as $page => $pageAnswers) {
            if (!empty($pageAnswers)) {
                $hasAnswers = true;
                break;
            }
        }
        
        if (!$hasAnswers) {
            Log::warning('Не найдены ответы на вопросы для брифа', [
                'brief_id' => $id
            ]);
        } else {
            Log::info('Обработаны ответы на вопросы', [
                'brief_id' => $id,
                'pages_with_answers' => array_keys(array_filter($answers, function($page) {
                    return !empty($page);
                }))
            ]);
        }
        
        // Загружаем пользователя с проверкой
        $user = null;
        if ($brief->user_id) {
            $user = User::find($brief->user_id);
        }
        
        return view('admin.brief_edit_common', compact('brief', 'title_site', 'zones', 'answers', 'user'));
    }
    
    /**
     * Обновить общий бриф
     */
    public function updateCommon(Request $request, $id)
    {
        // Подробное логирование запроса для диагностики
        \Illuminate\Support\Facades\Log::info('Обновление общего брифа', [
            'brief_id' => $id,
            'request_data' => $request->all()
        ]);

        $brief = Common::findOrFail($id);

        try {
            // Validate the incoming request - упрощаем валидацию для более гибкого принятия данных
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'nullable',
                'status' => 'required|string',
            ]);

            // Update the basic details of the brief
            $brief->title = $request->input('title');
            $brief->description = $request->input('description');
            $brief->status = $request->input('status');

            // Обработка цены - учитываем разные форматы ввода
            if ($request->has('price')) {
                $price = $request->input('price');
                if (is_string($price)) {
                    // Обработка строки: удаление всех нецифровых символов
                    $price = preg_replace('/[^0-9]/', '', $price);
                }
                $brief->price = !empty($price) ? $price : null;
            }

            // Если предоставлена страница, обновляем её
            if ($request->has('current_page')) {
                $brief->current_page = $request->input('current_page');
            }

            // Обработка комнат (ключ может быть rooms или zones)
            if ($request->has('rooms')) {
                $brief->rooms = json_encode($request->input('rooms'));
                \Illuminate\Support\Facades\Log::info('Обновлены комнаты', [
                    'brief_id' => $id,
                    'rooms' => $request->input('rooms')
                ]);
            }

            // Обработка ответов на вопросы
            if ($request->has('answers')) {
                $answers = $request->input('answers');
                \Illuminate\Support\Facades\Log::info('Получены ответы на вопросы', [
                    'brief_id' => $id,
                    'answers' => $answers
                ]);
                
                // ИСПРАВЛЕНИЕ: Проверяем существование колонки answers перед сохранением
                if (\Illuminate\Support\Facades\Schema::hasColumn('commons', 'answers')) {
                    $brief->answers = json_encode($answers);
                    \Illuminate\Support\Facades\Log::info('Колонка answers существует, сохранены ответы', ['brief_id' => $id]);
                } else {
                    \Illuminate\Support\Facades\Log::warning('Колонка answers не существует, сохраняем ответы в отдельные поля', ['brief_id' => $id]);
                }
                
                // Также обновляем отдельные поля в базе данных, если они существуют
                foreach ($answers as $pageKey => $pageAnswers) {
                    foreach ($pageAnswers as $questionKey => $answer) {
                        // Карта соответствия названий вопросов и полей базы данных
                        $fieldMapping = [
                            'Сколько человек будет проживать в квартире' => 'question_1_1',
                            'Есть ли домашние животные и растения' => 'question_1_2',
                            'Есть ли у членов семьи особые увлечения или хобби' => 'question_1_3',
                            'Какие потребности у семьи' => 'question_1_4',
                            'Как часто вы встречаете гостей' => 'question_1_5',
                            'Адрес' => 'question_1_6',
                            
                            'Какой стиль Вы хотите видеть в своем интерьере' => 'question_2_1',
                            'Референсы интерьера' => 'question_2_2',
                            'Какую атмосферу вы хотите ощущать' => 'question_2_3',
                            'Предметы обстановки для нового интерьера' => 'question_2_4',
                            'Что не должно быть в интерьере' => 'question_2_5',
                            'Ценовой сегмент ремонта' => 'question_2_6',
                            'Предпочтения по цветам и материалам' => 'question_2_7',
                            
                            'Прихожая' => 'question_3_1',
                            'Детская' => 'question_3_2',
                            'Кладовая' => 'question_3_3',
                            'Кухня и гостиная' => 'question_3_4',
                            'Гостевой санузел' => 'question_3_5',
                            'Гостиная' => 'question_3_6',
                            'Рабочее место' => 'question_3_7',
                            'Столовая' => 'question_3_8',
                            'Ванная комната' => 'question_3_9',
                            'Кухня' => 'question_3_10',
                            'Кабинет' => 'question_3_11',
                            'Спальня' => 'question_3_12',
                            'Гардеробная' => 'question_3_13',
                            'Другое' => 'question_3_14',
                            
                            'Напольные покрытия' => 'question_4_1',
                            'Двери' => 'question_4_2',
                            'Отделка стен' => 'question_4_3',
                            'Освещение и электрика' => 'question_4_4',
                            'Потолки' => 'question_4_5',
                            'Дополнительные пожелания по отделке' => 'question_4_6',
                            
                            'Пожелания по звукоизоляции' => 'question_5_1',
                            'Теплые полы' => 'question_5_2',
                            'Предпочтения по размещению и типу радиаторов' => 'question_5_3',
                            'Водоснабжение' => 'question_5_4',
                            'Кондиционирование и вентиляция' => 'question_5_5',
                            'Сети и коммуникации' => 'question_5_6',
                            'Системы безопасности' => 'question_5_7',
                            'Умный дом' => 'question_5_8'
                        ];
                        
                        // Если у нас есть соответствие для этого вопроса в базе данных
                        $fieldName = $fieldMapping[$questionKey] ?? null;
                        if ($fieldName && \Illuminate\Support\Facades\Schema::hasColumn('commons', $fieldName)) {
                            $brief->$fieldName = $answer;
                            \Illuminate\Support\Facades\Log::info("Обновлено поле $fieldName", [
                                'brief_id' => $id,
                                'answer' => substr($answer, 0, 50) . (strlen($answer) > 50 ? '...' : '')
                            ]);
                        } else {
                            \Illuminate\Support\Facades\Log::warning("Не найдено соответствие для вопроса '$questionKey'", ['brief_id' => $id]);
                        }
                    }
                }
            }

            // Обрабатываем прямой ввод вопросов (в случае, если данные приходят не через answers)
            foreach ($request->all() as $key => $value) {
                if (strpos($key, 'question_') === 0 && \Illuminate\Support\Facades\Schema::hasColumn('commons', $key)) {
                    $brief->$key = $value;
                    \Illuminate\Support\Facades\Log::info("Обновлено прямое поле $key", [
                        'brief_id' => $id
                    ]);
                }
            }

            // Обработка дополнительных полей, если они есть
            $additionalFields = [
                'style_preferences', 'floor_preferences', 'wall_preferences', 'ceiling_preferences',
                'door_preferences', 'lighting_preferences', 'bathroom_preferences', 'kitchen_preferences'
            ];
            
            foreach ($additionalFields as $field) {
                if ($request->has($field) && \Illuminate\Support\Facades\Schema::hasColumn('commons', $field)) {
                    $brief->$field = $request->input($field);
                }
            }

            // Save the changes
            $brief->save();

            \Illuminate\Support\Facades\Log::info('Общий бриф успешно обновлен', [
                'brief_id' => $brief->id
            ]);

            // Если указан URL для возврата, используем его, иначе возвращаемся на страницу редактирования
            $redirectUrl = $request->input('_redirect_back') ?: route('admin.brief.common.edit', $id);

            return redirect($redirectUrl)->with('success', 'Бриф успешно обновлен');
                
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Ошибка при обновлении общего брифа', [
                'brief_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Если указан URL для возврата, используем его, иначе возвращаемся на страницу редактирования
            $redirectUrl = $request->input('_redirect_back') ?: route('admin.brief.common.edit', $id);

            return redirect($redirectUrl)->with('error', 'Произошла ошибка при обновлении брифа: ' . $e->getMessage());
        }
    }
    
    /**
     * Удалить общий бриф
     */
    public function destroyCommon($id)
    {
        try {
            $brief = Common::findOrFail($id);
            
            // Удаляем бриф
            $brief->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении общего брифа: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Редактировать коммерческий бриф
     */
    public function editCommercial($id)
    {
        $title_site = "Редактировать коммерческий бриф | Личный кабинет Экспресс-дизайн";
        $brief = Commercial::findOrFail($id);  // Find the commercial brief by ID

        // Добавляем массив названий вопросов для зон
        $titles = [
            'zone_1' => "Зоны и их функционал",
            'zone_2' => "Метраж зон",
            'zone_3' => "Зоны и их стиль оформления",
            'zone_4' => "Мебилировка зон",
            'zone_5' => "Предпочтения отделочных материалов",
            'zone_6' => "Освещение зон",
            'zone_7' => "Кондиционирование зон",
            'zone_8' => "Напольное покрытие зон",
            'zone_9' => "Отделка стен зон",
            'zone_10' => "Отделка потолков зон",
            'zone_11' => "Категорически неприемлемо или нет",
            'zone_12' => "Бюджет на помещения",
            'zone_13' => "Пожелания и комментарии",
        ];

        // Decode the questions or zones (depending on your structure)
        $questions = json_decode($brief->questions, true) ?? [];
        $preferences = json_decode($brief->preferences, true) ?? [];
        $zones = json_decode($brief->zones, true) ?? [];

        // Инициализация массивов зон и их бюджетов
        $zones = json_decode($brief->zones ?? '[]', true);
        $zoneBudgets = []; // Массив бюджетов для каждой зоны
        $preferences = []; // Массив предпочтений для каждой зоны
        
        // Заполнение массивов бюджетов, если имеются данные
        // Это нужно адаптировать под вашу структуру данных
        if (!empty($zones)) {
            foreach ($zones as $index => $zone) {
                // Пример: получение бюджета из поля budget каждой зоны
                $zoneBudgets[$index] = $zone['budget'] ?? null;
                
                // Пример: получение предпочтений из поля preferences каждой зоны
                $preferences[$index] = $zone['preferences'] ?? [];
            }
        }

        return view('admin.brief_edit_commercial', compact('brief', 'title_site', 'questions', 'preferences', 'zones', 'titles', 'zoneBudgets'));
    }
    
    /**
     * Обновить коммерческий бриф
     */
    public function updateCommercial(Request $request, $id)
    {
        // Включаем подробное логирование для диагностики
        \Illuminate\Support\Facades\Log::info('Обновление коммерческого брифа', [
            'brief_id' => $id,
            'request_data' => $request->all()
        ]);

        $brief = Commercial::findOrFail($id);

        try {
            // Валидация данных - делаем более гибкой
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'nullable',
                'status' => 'required|string',
            ]);

            // Обновляем базовые поля брифа
            $brief->title = $request->input('title');
            $brief->description = $request->input('description');
            $brief->status = $request->input('status');

            // Обработка цены - учитываем различные форматы ввода
            if ($request->has('price')) {
                $price = $request->input('price');
                if (is_string($price)) {
                    $price = preg_replace('/[^0-9]/', '', $price);
                }
                $brief->price = !empty($price) ? $price : null;
                
                \Illuminate\Support\Facades\Log::info('Обработана цена', [
                    'brief_id' => $id,
                    'original' => $request->input('price'),
                    'processed' => $brief->price
                ]);
            }

            // Обработка зон с тщательной проверкой
            if ($request->has('zones')) {
                $zones = $request->input('zones');
                
                // Удостоверимся, что это массив
                if (is_array($zones)) {
                    // Добавляем информацию о площадях из отдельных инпутов
                    foreach ($zones as $index => &$zone) {
                        $totalArea = $request->input("zones.{$index}.total_area");
                        if ($totalArea !== null) {
                            $zone['total_area'] = $totalArea;
                        }
                        
                        $projectedArea = $request->input("zones.{$index}.projected_area");
                        if ($projectedArea !== null) {
                            $zone['projected_area'] = $projectedArea;
                        }
                    }
                    
                    $brief->zones = json_encode($zones);
                    
                    \Illuminate\Support\Facades\Log::info('Обновлены зоны коммерческого брифа', [
                        'brief_id' => $id,
                        'zones_count' => count($zones)
                    ]);
                } else {
                    \Illuminate\Support\Facades\Log::warning('Поле zones не является массивом', [
                        'brief_id' => $id, 
                        'zones' => $zones
                    ]);
                }
            }

            // Обработка бюджетов зон
            if ($request->has('zone_budgets')) {
                $zoneBudgets = $request->input('zone_budgets');
                if (is_array($zoneBudgets)) {
                    $brief->zone_budgets = json_encode($zoneBudgets);
                    
                    \Illuminate\Support\Facades\Log::info('Обновлены бюджеты зон', [
                        'brief_id' => $id,
                        'budgets_count' => count($zoneBudgets)
                    ]);
                }
            }

            // Обработка предпочтений (ответов) двумя способами
            if ($request->has('zone_preferences_data')) {
                $rawPreferences = $request->input('zone_preferences_data');
                
                // Проверка валидности JSON, если это строка
                if (is_string($rawPreferences)) {
                    try {
                        $preferences = json_decode($rawPreferences, true);
                        if (is_array($preferences)) {
                            $brief->preferences = $rawPreferences; // Сохраняем как есть, так как это уже JSON строка
                            
                            \Illuminate\Support\Facades\Log::info('Обновлены предпочтения из JSON-строки', [
                                'brief_id' => $id,
                                'preferences_count' => count($preferences)
                            ]);
                        } else {
                            \Illuminate\Support\Facades\Log::warning('Некорректный JSON в zone_preferences_data', [
                                'brief_id' => $id
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Ошибка при декодировании JSON', [
                            'brief_id' => $id,
                            'error' => $e->getMessage()
                        ]);
                    }
                } else if (is_array($rawPreferences)) {
                    // Если это уже массив, кодируем его
                    $brief->preferences = json_encode($rawPreferences);
                    
                    \Illuminate\Support\Facades\Log::info('Обновлены предпочтения из массива', [
                        'brief_id' => $id,
                        'preferences_count' => count($rawPreferences)
                    ]);
                }
            } elseif ($request->has('preferences')) {
                $preferences = $request->input('preferences');
                if (is_array($preferences)) {
                    $brief->preferences = json_encode($preferences);
                    
                    \Illuminate\Support\Facades\Log::info('Обновлены предпочтения из формы', [
                        'brief_id' => $id,
                        'preferences_count' => count($preferences)
                    ]);
                }
            }

            // Сохраняем обновленный бриф
            $brief->save();

            \Illuminate\Support\Facades\Log::info('Коммерческий бриф успешно обновлен', [
                'brief_id' => $brief->id
            ]);

            // Если указан URL для возврата, используем его, иначе возвращаемся на страницу редактирования
            $redirectUrl = $request->input('_redirect_back') ?: route('admin.brief.commercial.edit', $id);

            // Редирект с сообщением об успехе
            return redirect($redirectUrl)->with('success', 'Коммерческий бриф успешно обновлен');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Ошибка при обновлении коммерческого брифа', [
                'brief_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Если указан URL для возврата, используем его, иначе возвращаемся на страницу редактирования
            $redirectUrl = $request->input('_redirect_back') ?: route('admin.brief.commercial.edit', $id);

            return redirect($redirectUrl)->with('error', 'Произошла ошибка при обновлении брифа: ' . $e->getMessage());
        }
    }
    
    /**
     * Удалить коммерческий бриф
     */
    public function destroyCommercial($id)
    {
        try {
            $brief = Commercial::findOrFail($id);
            
            // Удаляем бриф
            $brief->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении коммерческого брифа: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Определяет, к какой странице относится специальное поле
     */
    private function mapFieldToPage($field)
    {
        $mapping = [
            'style_preferences' => 2,
            'floor_preferences' => 4,
            'wall_preferences' => 4,
            'ceiling_preferences' => 4,
            'door_preferences' => 4,
            'lighting_preferences' => 4,
            'bathroom_preferences' => 3,
            'kitchen_preferences' => 3
        ];
        
        return $mapping[$field] ?? null;
    }
    
    /**
     * Возвращает заголовок вопроса для специального поля
     */
    private function getQuestionTitleForField($field)
    {
        $mapping = [
            'style_preferences' => 'Какой стиль Вы хотите видеть в своем интерьере',
            'floor_preferences' => 'Напольные покрытия',
            'wall_preferences' => 'Отделка стен',
            'ceiling_preferences' => 'Потолки',
            'door_preferences' => 'Двери',
            'lighting_preferences' => 'Освещение и электрика',
            'bathroom_preferences' => 'Ванная комната',
            'kitchen_preferences' => 'Кухня'
        ];
        
        return $mapping[$field] ?? null;
    }

    /**
     * Отображение всех брифов конкретного пользователя
     *
     * @param int $userId
     * @return \Illuminate\View\View
     */
    public function userBriefs($userId)
    {
        // Получаем пользователя
        $user = \App\Models\User::findOrFail($userId);
        
        // Получаем брифы пользователя
        $commonBriefs = \App\Models\Common::where('user_id', $userId)->get();
        $commercialBriefs = \App\Models\Commercial::where('user_id', $userId)->get();
        
        return view('admin.user_briefs', compact('user', 'commonBriefs', 'commercialBriefs'));
    }
}
