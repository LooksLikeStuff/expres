<?php

namespace App\DTO\Briefs;

use App\Enums\Briefs\BriefStatus;
use App\Enums\Briefs\BriefType;
use App\Traits\ToArrayTrait;

class BriefDTO
{
    use ToArrayTrait;
    public function __construct(
        public readonly int $userId,
        public readonly ?int $dealId,
        public readonly BriefType $type,
        public readonly string $title,
        public readonly ?string $description,
        public readonly BriefStatus $status,
        public readonly ?string $article,
        public readonly ?string $zones,
        public readonly ?float $totalArea,
        public readonly ?int $price,
        public readonly ?array $preferences,
    ) {
    }

    public static function fromType(BriefType $type, int $userId): self
    {
        return new self(
            userId: $userId,
            dealId: null,
            type: $type,
            title: $type->label(),
            description: null,
            status: BriefStatus::ACTIVE,
            article: self::generateArticle(),
            zones: null,
            totalArea: null,
            price: null,
            preferences: null
        );
    }


    private static function generateArticle(): string
    {
        return bin2hex(random_bytes(16));
    }
}
