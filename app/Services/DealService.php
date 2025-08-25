<?php

namespace App\Services;

use App\Models\Brief;
use App\Models\Deal;

class DealService
{
    public function __construct(

    )
    {
    }

    public function bindChatToDeal(int $chatId, int $dealId)
    {
        return Deal::where('id', $dealId)
            ->update(['chat_id' => $chatId]);
    }

}
