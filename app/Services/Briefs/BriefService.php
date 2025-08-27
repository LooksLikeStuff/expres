<?php

namespace App\Services\Briefs;

use App\DTO\Briefs\BriefDTO;
use App\Models\Brief;
use App\Models\Deal;

class BriefService
{
    public function updateOrCreate(BriefDTO $briefDTO)
    {
        return Brief::updateOrCreate(
            ['article' => $briefDTO->article],
            $briefDTO->toArray());
    }

    public function getUserBriefs(int $userId)
    {

        return Brief::where('user_id', $userId)
            ->orderBy('created_at')
            ->get();
    }

    public function createEmptyBrief(BriefDTO $briefDTO)
    {
        return Brief::create($briefDTO->toArray());
    }

    public function linkToAvailableDeal(Brief $brief): void
    {
        $availableDeal = Deal::where('client_phone', $brief->user->phone)
            ->first();

        if ($availableDeal) {
            $brief->deal_id = $availableDeal->id;
            $brief->save();
        }
    }
}
