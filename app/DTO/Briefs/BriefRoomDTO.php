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

    /**
     * Создает DTO для коммерческого брифа с существующими и новыми комнатами
     */
    public static function fromNewCommercialRoomsData(array $rooms): self
    {
        return new self(
            rooms: self::prepareNewRooms($rooms),
        );
    }

    public static function fromExistingCommercialRoomsData(array $rooms): self
    {
        return new self(
            rooms: self::prepareExistingRooms($rooms),
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

    /**
     * Подготавливает данные для обновления существующих комнат
     */
    private static function prepareExistingRooms(array $rooms): Collection
    {
        $preparedRooms = collect();

        foreach ($rooms as $roomId => $roomData) {
            if (isset($roomData['title']) && !empty(trim($roomData['title']))) {
                $preparedRooms->push([
                    'id' => $roomId,
                    'title' => trim($roomData['title']),
                    'key' => self::createKeyFromRoomName(trim($roomData['title'])),
                ]);
            }
        }

        return $preparedRooms;
    }

    /**
     * Подготавливает данные для создания новых комнат
     */
    private static function prepareNewRooms(array $addRooms): Collection
    {
        $preparedRooms = collect();

        foreach ($addRooms as $roomId => $roomData) {
            $preparedRooms->push([
                'id' => $roomId,
                'title' => trim($roomData['title']),
                'key' => self::createKeyFromRoomName($roomData['title']),
            ]);
        }

        return $preparedRooms;
    }

}
