<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Commercial;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\Deal;
use Illuminate\Support\Facades\Http;
use App\Services\YandexDiskService;

class CommercialController extends Controller
{
    /**
     * CommercialController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Показать страницу с брифами.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function questions($id, $page)
    {
        $titles = [
            1 => "Название зоны",
            2 => "Метраж зон",
            3 => "Стиль оформления и меблировка зон",
            4 => "Отделочные материалы и поверхности",
            5 => "Инженерные системы и коммуникации",
            6 => "Предпочтения и ограничения",
            7 => "Бюджет и документы",
            8 => "Дополнительные пожелания"
        ];
        $descriptions = [
            1 => "Укажите название каждой зоны (например, гостиная, кухня, спальня).",
            2 => "Укажите примерный размер каждой зоны в квадратных метрах.",
            3 => "Опишите стиль оформления для каждой зоны (например, минимализм, классика, лофт) и предпочтения по меблировке.",
            4 => "Выберите материалы, которые хотите использовать для отделки пола, стен и потолка в каждой зоне.",
            5 => "Опишите требования к освещению, электрике, вентиляции и кондиционированию для каждой зоны.",
            6 => "Перечислите материалы или решения, которые вы категорически не хотите использовать.",
            7 => "Укажите общий бюджет на проект и загрузите необходимые документы.",
            8 => "Добавьте любые дополнительные пожелания или комментарии для каждой зоны."
        ];
        $title_site   = $titles[$page] ?? "Вопрос";
        $description  = $descriptions[$page] ?? "";
        $totalPages   = count($titles);

        // Ищем бриф по ID и текущему пользователю
        $brif = Commercial::where('id', $id)
                          ->where('user_id', auth()->id())
                          ->first();

        if (!$brif) {
            return redirect()->route('brifs.index')->with('error', 'Бриф не найден или не принадлежит вам.');
        }

        // Если бриф уже завершён
        if ($brif->status === 'Завершенный') {
            return redirect()->route('brifs.index')->with('info', 'Этот бриф уже завершён.');
        }

        // Получаем владельца брифа
        $user = User::find($brif->user_id);
        if (!$user) {
            $user = Auth::user();
        }

        $zones        = $brif->zones ? json_decode($brif->zones, true) : [];
        $preferences  = $brif->preferences ? json_decode($brif->preferences, true) : [];
        $budget       = $brif->price ?? 0;
        $zoneBudgets  = $brif->zone_budgets ? json_decode($brif->zone_budgets, true) : [];

        return view('commercial.questions', [
            'page'         => $page,
            'zones'        => $zones,
            'preferences'  => $preferences,
            'budget'       => $budget,
            'zoneBudgets'  => $zoneBudgets,
            'user'         => $user,
            'title_site'   => $title_site,
            'description'  => $description,
            'brif'         => $brif,
            'totalPages'   => $totalPages,
        ]);
    }

   /**
 * Сохранение ответов для коммерческого брифа.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  int  $id       ID коммерческого брифа (commercial)
 * @param  int  $page     Номер страницы (шага)
 * @return \Illuminate\Http\RedirectResponse
 */
public function saveAnswers(Request $request, $id, $page)
{
    // Увеличиваем лимит времени выполнения PHP скрипта до 300 секунд
    set_time_limit(300);
    
    // 1. Находим бриф
    $commercial = Commercial::where('id', $id)
        ->where('user_id', auth()->id())
        ->first();

    // Если бриф не найден или не принадлежит пользователю
    if (!$commercial) {
        return redirect()
            ->route('brifs.index')
            ->with('error', 'Commercial brief not found or does not belong to you.');
    }

    // Если бриф уже завершен
    if ($commercial->status === 'Завершенный') {
        return redirect()
            ->route('brifs.index')
            ->with('info', 'This commercial brief is already completed.');
    }

    // 2. Обрабатываем данные в зависимости от страницы (шаг)
    switch ($page) {
        // -----------------
        // Шаг 1: создание списка зон (name, description)
        case 1:
            $data = $request->validate([
                'zones'            => 'required|array',
                'zones.*.name'     => 'required|string|max:255',
                'zones.*.description' => 'nullable|string|max:1000',
            ]);
            $commercial->zones = json_encode($data['zones']);
            break;

        // -----------------
        // Шаг 2: площади зон (total_area, projected_area)
        case 2:
            $data = $request->validate([
                'zones'                            => 'required|array',
                'zones.*.total_area'               => 'required|min:0',
                'zones.*.projected_area'           => 'required|min:0',
            ]);

            // Извлекаем массив зон из брифа
            $zones = json_decode($commercial->zones, true) ?? [];
            // Обновляем значения площадей
            foreach ($data['zones'] as $index => $zoneData) {
                if (isset($zones[$index])) {
                    $zones[$index]['total_area']     = $zoneData['total_area'];
                    $zones[$index]['projected_area'] = $zoneData['projected_area'];
                }
            }
            $commercial->zones = json_encode($zones);
            break;        // -----------------
        // Шаг 7: Бюджет
        case 7:
            $data = $request->validate([
                'price'      => 'nullable|numeric|min:0',
                'budget'     => 'nullable|array',
            ]);

            // Сохраняем цену
            $commercial->price = $data['price'] ?? 0;

            // Сохраняем бюджеты по зонам
            if (isset($data['budget'])) {
                $zoneBudgets = [];
                foreach ($data['budget'] as $index => $budgetValue) {
                    // Убираем всё, кроме цифр
                    $cleanBudget = preg_replace('/\D/', '', $budgetValue);
                    if ($cleanBudget !== '') {
                        $zoneBudgets[$index] = floatval($cleanBudget);
                    }
                }
                $commercial->zone_budgets = json_encode($zoneBudgets);
            }
            break;        // -----------------
        // Шаг 8: Заключительный - дополнительные пожелания, загрузка документов и завершение
        case 8:
            $data = $request->validate([
                'preferences' => 'nullable|array',
                'preferences.*.answer' => 'nullable|string|max:1000',
                'documents'  => 'nullable|array',
                'documents.*' => 'file|max:51200|mimes:pdf,xlsx,xls,doc,docx,jpg,jpeg,png,heic,heif,mp4,mov,avi,wmv,flv,mkv,webm,3gp',
            ]);
            
            // Достаём из БД текущие preferences
            $preferences = json_decode($commercial->preferences, true) ?? [];
            foreach ($data['preferences'] ?? [] as $zoneIndex => $answers) {
                $preferences[$zoneIndex]['question_' . $page] = $answers['answer'] ?? null;
            }
            // Перезаписываем в бриф
            $commercial->preferences = json_encode($preferences);
            
            // Загрузка документов на Яндекс.Диск
            if ($request->hasFile('documents')) {
                $commercial->uploadDocuments($request->file('documents'));
            }
            
            // Завершаем бриф
            $commercial->status = 'Завершенный';
            $commercial->save();

            // Редирект на страницу сделки
            return redirect()
                ->route('user_deal')
                ->with('success', 'Бриф успешно заполнен!');

        // -----------------
        // По умолчанию (страницы 3-6) — сохраняем "preferences"
        default:
            $data = $request->validate([
                'preferences' => 'nullable|array',
                'preferences.*.answer' => 'nullable|string|max:1000',
            ]);
            // Достаём из БД текущие preferences
            $preferences = json_decode($commercial->preferences, true) ?? [];
            foreach ($data['preferences'] ?? [] as $zoneIndex => $answers) {
                $preferences[$zoneIndex]['question_' . $page] = $answers['answer'] ?? null;
            }
            // Перезаписываем в бриф
            $commercial->preferences = json_encode($preferences);
            break;
    }

    // Сохраняем бриф
    $commercial->save();
    // Переходим к следующей странице
    return redirect()->route('commercial.questions', [
        'id'   => $commercial->id,
        'page' => $page + 1
    ]);
}

/**
 * Удаляет файл из брифа и с Яндекс.Диска.
 *
 * @param  \Illuminate\Http\Request $request
 * @param  int  $id    ID коммерческого брифа
 * @return \Illuminate\Http\JsonResponse
 */
public function deleteFile(Request $request, $id)
{
    $commercial = Commercial::where('id', $id)
        ->where('user_id', auth()->id())
        ->first();

    if (!$commercial) {
        return response()->json(['success' => false, 'message' => 'Коммерческий бриф не найден'], 404);
    }

    $fileUrl = $request->input('file_url');
    if (!$fileUrl) {
        return response()->json(['success' => false, 'message' => 'Не указан URL файла'], 400);
    }

    $success = $commercial->deleteFileFromYandexDisk($fileUrl);

    return response()->json([
        'success' => $success,
        'message' => $success ? 'Файл успешно удален' : 'Не удалось удалить файл'
    ]);
}
}