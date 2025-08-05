<?php

namespace App\Http\Controllers\Chats;

use App\DTO\ReadReceiptDTO;
use App\Events\MessageRead;
use App\Exceptions\Chats\MessageReadException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chats\ReadReceiptRequest;
use App\Http\Requests\Chats\UpdateReadReceiptRequest;
use App\Models\Chats\ReadReceipt;
use App\Services\Chats\ReadReceiptService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ReadReceiptController extends Controller
{
    public function __construct(
        private readonly ReadReceiptService $readReceiptService,
    )
    {
    }

    public function readMessage(UpdateReadReceiptRequest $request)
    {
        $readReceipt = $this->readReceiptService->readMessage(ReadReceiptDTO::fromRequest($request));

        broadcast(new MessageRead($readReceipt))->toOthers();

        return response()->json([
            'status' => 'Сообщение прочитано'
        ]);
    }


}
