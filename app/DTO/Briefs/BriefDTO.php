<?php

namespace App\DTO;

use App\Enums\Briefs\BriefType;
use App\Http\Requests\Briefs\CreateRequest;

class BriefDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $dealId,
        public readonly BriefType $type,
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $status,
        public readonly ?string $article,
        public readonly ?string $zones,
        public readonly ?float $totalArea,
        public readonly ?int $price,
        public readonly array $preferences,
    ) {
    }

    public static function fromCreateRequest(CreateRequest $request)
    {
        return new self(

        );
    }
}
