<?php

namespace App\Http\Controllers\Briefs;

use App\DTO\Briefs\BriefAnswerDTO;
use App\DTO\Briefs\BriefDTO;
use App\DTO\Briefs\BriefRoomDTO;
use App\Enums\Briefs\BriefType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Briefs\AnswerRequest;
use App\Http\Requests\Briefs\CreateRequest;
use App\Http\Requests\Briefs\StoreRoomsRequest;
use App\Models\Brief;
use App\Models\User;
use App\Services\Briefs\BriefAnswerService;
use App\Services\Briefs\BriefQuestionService;
use App\Services\Briefs\BriefRoomService;
use App\Services\Briefs\BriefService;
use App\Services\BriefPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class BriefController extends Controller
{
    public function __construct(
        private readonly BriefService $briefService,
        private readonly BriefQuestionService $briefQuestionService,
        private readonly BriefRoomService $briefRoomService,
        private readonly BriefAnswerService $briefAnswerService,
        private readonly BriefPdfService $briefPdfService,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $briefs = $this->briefService->getUserBriefs(auth()->id());

        //Если у пользователя нет брифов, показываем ему страницу создания брифа
        if ($briefs->isEmpty()) return view('briefs.create');

        $activeBriefs = $briefs->filter(fn($brief) => $brief->isActive());
        $inactiveBriefs = $briefs->filter(fn(Brief $brief) => $brief->isFinished());
        $commonBriefs = $briefs->filter(fn(Brief $brief) => $brief->isCommon());
        $commercialBriefs = $briefs->filter(fn(Brief $brief) => $brief->isCommercial());

        // Добавляем информацию о заполненности страниц для активных брифов
        $activeBriefs->each(function ($brief) {
            $brief->pagesStatus = $brief->getPagesCompletionStatus();
        });

        $briefTypes = BriefType::cases();

        return view('briefs.index', compact('briefs', 'activeBriefs', 'inactiveBriefs', 'commonBriefs', 'commercialBriefs', 'briefTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //Получаем тип из GET параметра и сразу проверяем - валидный ли он
        $type = $request->has('type') ? BriefType::tryFrom($request->get('type')) : null;

        return view('briefs.create', compact('type'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRequest $request)
    {
        $user = auth()->user();

        //Получаем тип брифа для создания
        $type = BriefType::from($request->validated('type'));

        //Создаем пустой бриф с выбранным типом для авторизованного пользователя
        $brief = $this->briefService->createEmptyBrief(BriefDTO::fromType($type, $user->id));

        //Если бриф общий, то редиректим на страницу заполнения комнат
        if ($brief->isCommon()) {
            return redirect()->route('briefs.rooms.create', ['brief' => $brief]);
        } else {
            // Иначе сразу редиректим на страницу с вопросами
            return redirect()->route('briefs.questions', ['brief' => $brief, 'page' => 1]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Brief $brief)
    {
        // Получаем структурированные данные через сервис
        $data = $this->briefService->getStructuredDataForShow($brief);

        // Дополнительные данные для конкретных типов брифов
        if ($brief->isCommon()) {
            $data['roomAnswers'] = $this->briefAnswerService->getRoomAnswersForCommonBrief($brief);
        }

        if ($brief->isCommercial()) {
            $data['zoneAnswers'] = $this->briefAnswerService->getZoneAnswersForCommercialBrief($brief);
        }

        return view('briefs.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brief $brief)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brief $brief)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brief $brief)
    {
        //
    }

    //Страница создания комнат для общего брифа
    public function createRooms(Brief $brief)
    {
        //Проверяем что бриф является общим
        if (!$brief->isCommon()) {
            return redirect()->route('briefs.index')->with('error', 'Комнаты необходимо указывать только для общего брифа.');
        }

        $rooms = $this->briefRoomService->getBriefAndDefaultRooms($brief->id);

        return view('briefs.rooms', compact('brief', 'rooms'));
    }

    public function storeRooms(Brief $brief, StoreRoomsRequest $request)
    {
        $this->briefRoomService->saveRoomsForBrief($brief, BriefRoomDTO::fromStoreRoomsRequest($request));

        return redirect()->route('briefs.questions', ['brief' => $brief, 'page' => 1]);
    }

    /**
     * Унифицированная страница вопросов брифа.
     */
    public function questions(Brief $brief, int $page)
    {
        if ($brief->isCompleted()) {
            return redirect()->route('briefs.show', $brief);
        }
        //Если нулевая страница и бриф общего типа, то перенаправляем на страницу с добавлением комнат
        if ($brief->isCommon() && $page <= 0) {
            return redirect()->route('briefs.rooms.create', ['brief' => $brief]);
        }

        //Если это последняя страница или у брифа статус "Есть пропущенные страницы",
        //то проверяем, есть ли пропущенные страницы
        if ($page > $brief->totalQuestionPages() || $brief->hasSkippedPages()) {
            $page = $this->briefQuestionService->getMinSkippedPage($brief);

            //Если есть пропущенные страницы, то устанавливаем статус, что есть пропущенные страницы, иначе устанавливаем статус Завершен
            if (!is_null($page)) {
                $brief->markAsHasSkippedPages();
            } else {
                $brief->markAsCompleted();

                return redirect()->route('user_deal');
            }
        }

        $questions = $this->briefQuestionService->getQuestionsByTypeAndPage($brief->type, $page);

        if ($brief->isCommon()) {
            //На третьей страницы общего брифа заполняется информация для комнат, поэтому подгружаем их
            if ($page === 3) {
                $brief->load('rooms');
                //Если у брифа нет ни одной комнаты, переводим на страницу добавления комнат
                if ($brief->rooms->isEmpty()) return redirect()->route('briefs.rooms.create', ['brief' => $brief]);

                $brief->rooms->map(fn ($room) => $room->setQuestion($brief->type, $questions)); //Устанавливаем атрибут с вопросом по поводу комнаты
            }

        } else {
            //Для коммерческого брифа почти все вопросы связаны с зонами, подгружаем их всегда
            $brief->load('rooms');
        }

        return view('briefs.questions', compact('questions', 'brief', 'page'));
    }


    public function answers(Brief $brief, AnswerRequest $request)
    {
        $page = $request->get('page');

        //Общий бриф
        if ($brief->isCommon()) {
            //На третьей странице добавляются комнаты
            if ($page == 3) $dto = BriefAnswerDTO::fromValidatedCommonRoomsArray($request->validated('rooms'));
            else $dto = BriefAnswerDTO::fromAnswerRequest($request);
        }
        else { //Коммерческий бриф
            if($page == 1) {
                $rooms = [];
                //Обновляем существующие комнаты если были внесены изменения
                if ($request->has('rooms')) {
                    $rooms = $request->validated('rooms');
                    $this->briefRoomService->updateExistingRooms(BriefRoomDTO::fromExistingCommercialRoomsData($rooms));
                }

                //Добавляем новые комнаты
                if ($request->has('addRooms')) {
                    $newRooms = $request->validated('addRooms');
                    $newRoomIds = $this->briefRoomService->saveRoomsForBrief($brief, BriefRoomDTO::fromNewCommercialRoomsData($newRooms));

                    // Переназначаем ключи, подставляем id новых комнат вместо $index, это нужно для того чтобы можно было удобно сохранить ответы на вопросы
                    foreach ($newRooms as $index => $room) {
                        $rooms[$newRoomIds[$index]] = $room;
                    }

                }
                $dto = BriefAnswerDTO::fromValidatedCommercialRoomsArray($rooms);
            } else {
                // Для других страниц коммерческого брифа используем стандартную логику
                $dto = BriefAnswerDTO::fromValidatedCommercialAnswersArray($request->validated('answers'));
            }
        }

        $this->briefAnswerService->updateOrCreate($brief, $dto);


        //Сохраняем документы брифа
        if ($request->has('documents')) {
            $this->briefService->saveDocuments($brief, $request->validated('documents'));
        }

        //Идем дальше на следующую страницу
       return redirect()->route('briefs.questions', ['brief' => $brief, 'page' => $page + 1]);
    }

    /**
     * Скачать PDF-версию брифа
     *
     * @param Brief $brief
     * @return RedirectResponse|Response
     */
    public function pdf(Brief $brief)
    {
        // Проверяем права доступа
        $this->authorize('view', $brief);

        try {
            return $this->briefPdfService->generatePdf($brief);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
