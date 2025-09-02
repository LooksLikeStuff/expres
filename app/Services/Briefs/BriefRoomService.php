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

    public function saveRoomsForBrief(Brief $brief, BriefRoomDTO $briefRoomDTO): array
    {
        $ids = [];

        foreach ($briefRoomDTO->rooms as $roomData) {
            $ids[] = $brief->rooms()->updateOrCreate(['key' => $roomData['key']], $roomData)->id;
        }

        return $ids;
    }


    /**
     * Обновляет существующие комнаты и создает новые для коммерческого брифа
     * Возвращает массив ID новых созданных комнат
     */
    public function updateAndCreateRoomsForCommercialBrief(Brief $brief, BriefRoomDTO $briefRoomDTO): array
    {
        $newRoomIds = [];

        // Обновляем существующие комнаты
        foreach ($briefRoomDTO->rooms as $roomData) {
            $brief->rooms()
                ->where('id', $roomData['id'])
                ->update([
                    'title' => $roomData['title'],
                ]);
        }

        // Создаем новые комнаты и собираем их ID
        foreach ($briefRoomDTO->newRooms as $roomData) {
            $newRoom = $brief->rooms()->create([
                'title' => $roomData['title'],
                'key' => $roomData['key'],
            ]);
            $newRoomIds[] = $newRoom->id;
        }

        return $newRoomIds;
    }

    public function updateExistingRooms(BriefRoomDTO $briefRoomDTO)
    {
        foreach ($briefRoomDTO->rooms as $roomData) {
            BriefRoom::where('id', $roomData['id'])
                ->update($roomData);
        }
    }
}
