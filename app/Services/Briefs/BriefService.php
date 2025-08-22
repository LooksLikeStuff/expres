<?php

namespace App\Services\Briefs;

use App\DTO\BriefDTO;
use App\Models\Brief;

class BriefService
{
    public function updateOrCreate(BriefDTO $briefDTO)
    {
        return Brief::updateOrCreate(
            ['title' => $briefDTO->title],
            $briefDTO->toArray());

    }
}
