<?php

namespace App\Http\Controllers\chats;

use App\DTO\MessageDTO;
use App\Enums\MessageType;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRequest;
use App\Models\Message;
use App\Services\AttachmentService;
use App\Services\Chats\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private readonly MessageService $messageService,
        private readonly AttachmentService $attachmentService,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MessageRequest $request)
    {
        $data = $request->validated();

        //Устанавливаем тип сообщения
        $data['type'] = !empty($data['attachments']) ? MessageType::FILE : MessageType::TEXT;

        $message = $this->messageService->create(MessageDTO::fromArray($data));

        if (!empty($data['attachments'])) {
            $this->attachmentService->saveMessageAttachments($message, $data['attachments']);
        }

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['status' => 'Сообщение отправлено']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message)
    {
        //
    }
}
