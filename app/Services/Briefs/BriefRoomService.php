<?php

namespace App\Services\Briefs;

use App\DTO\Briefs\BriefRoomDTO;
use App\Models\Brief;
use App\Models\BriefRoom;

class BriefRoomService
{


    public function getBriefAndDefaultRooms(int $briefId)
    {
        $briefRooms = BriefRoom::select(['id', 'brief_id', 'key', 'title'])
            ->where('brief_id', $briefId)
            ->get();

        //Подготавливаем данные
        $rooms = [];
        foreach ($briefRooms as $briefRoom) {
            $rooms[] = [
                'key' => $briefRoom->key,
                'title' => $briefRoom->title,
            ];
        }
        //Объединяем в один массив
        return array_merge($rooms, BriefRoom::DEFAULT_ROOMS);
    }

    public function saveRoomsForBrief(Brief $brief, BriefRoomDTO $briefRoomDTO): bool
    {
        foreach ($briefRoomDTO->rooms as $room) {
            $brief->rooms()->create($room);
        }

        return true;
    }
}
