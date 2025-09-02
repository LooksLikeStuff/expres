<?php

namespace App\Services\Briefs;

use App\DTO\Briefs\BriefDTO;
use App\Models\Brief;
use App\Models\Deal;
use Illuminate\Support\Facades\Storage;

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
        //Ищем активную сделку для привязки брифа
        $availableDeal = Deal::whereNot('status', 'Проект завершен')
            ->whereHas('dealClient', fn($q) => $q->where('phone', $brief->user->phone))
            ->first();

        //Если сделка нашлась привязываем ее к брифу
        if ($availableDeal) {
            $brief->deal_id = $availableDeal->id;
            $brief->save();
        }
    }

    public function saveDocuments(Brief $brief, array $documents): void
    {
        foreach ($documents as $document) {
            $path = Storage::disk('yandex')->putFile('brief-documents', $document);

            $brief->documents()->create([
                'filepath' => $path,
                'original_name' => $document->getClientOriginalName(),
                'file_size' => $document->getSize(),
                'mime_type' => $document->getMimeType(),
            ]);
        }
    }

}
