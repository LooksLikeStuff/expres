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
use Illuminate\Http\Request;
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

        return view('briefs.index', compact('briefs', 'activeBriefs', 'inactiveBriefs', 'commonBriefs', 'commercialBriefs'));
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
        //
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
        //Если нулевая страница и бриф общего типа, то перенаправляем на страницу с комнатами
        if ($brief->isCommon() && $page <= 0) {
           return redirect()->route('briefs.rooms.create');
        }

        $questions = $this->briefQuestionService->getQuestionsByTypeAndPage($brief->type, $page);

        if ($brief->isCommon()) {
            if ($page === 3) $brief->load('rooms');

            return view('briefs.questions', ['questions' => $questions, 'page' => $page, 'totalPages' => 5, 'brief' => $brief]);
        } else {
            $user = User::find($brief->user_id) ?: Auth::user();
            $zones = $brief->getZonesData();
            $preferences = $brief->getPreferencesData();
            $budget = $brief->price ?? 0;

            return view('briefs.questions', [
                'page' => $page,
                'zones' => $zones,
                'preferences' => $preferences,
                'budget' => $budget,
                'user' => $user,
                'title_site' => 'Коммерческий бриф',
                'brief' => $brief,
                'totalPages' => 13,
                'questions' => $questions,
            ]);
        }
    }

    public function answers(Brief $brief, AnswerRequest $request)
    {
       $this->briefAnswerService->create($brief, BriefAnswerDTO::fromStoreRoomsRequest($request));

       dd($request->get('page'));
    }

    /**
     * Скачать PDF-версию брифа
     *
     * @param Brief $brief
     * @return \Illuminate\Http\Response
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
