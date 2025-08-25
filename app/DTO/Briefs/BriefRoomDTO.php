<?php

namespace App\DTO\Briefs;

use App\Http\Requests\Briefs\StoreRoomsRequest;
use App\Models\BriefRoom;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BriefRoomDTO
{
    public function __construct(
        public readonly Collection $rooms,
    )
    {
    }

    public static function fromStoreRoomsRequest(StoreRoomsRequest $request): self
    {
        return new self(
            rooms: self::prepareRooms($request->validated('rooms')),
        );
    }


    private static function prepareRooms(array $roomNames): Collection
    {
        $preparedRooms = collect();

        $defaultRooms = collect(BriefRoom::DEFAULT_ROOMS);


        //Формируем массив вида ['title' => title, 'key' => key] для вставки в бд
        foreach ($roomNames as $roomName) {
            $room = $defaultRooms->firstWhere('title', $roomName);

            //Дефолтная комната
            if ($room) {
                $preparedRooms->push($room);
            } else { //Если кастомная комната, то создаем для нее ключ по названию
                $preparedRooms->push(['key' => self::createKeyFromRoomName($roomName), 'title' => $roomName]);
            }
        }

        return $preparedRooms;
    }


    private static function createKeyFromRoomName(string $roomName): string
    {
        return Str::slug($roomName, '_');
    }


}
