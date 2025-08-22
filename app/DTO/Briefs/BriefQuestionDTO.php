<?php

namespace App\DTO\Briefs;

use App\Enums\Briefs\BriefQuestionFormat;

class BriefQuestionDTO
{
    public function __construct(
        public readonly string $key,
        public readonly string $briefType,
        public readonly string $title,
        public readonly ?string $subtitle,
        public readonly ?string $inputType,
        public readonly ?string $placeholder,
        public readonly ?BriefQuestionFormat $format,
        public readonly ?string $class,
        public readonly int $page,
        public readonly int $order,
        public readonly bool $isActive = true,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'],
            briefType: $data['brief_type'],
            title: $data['title'],
            subtitle: $data['subtitle'] ?? null,
            inputType: $data['input_type'] ?? null,
            placeholder: $data['placeholder'] ?? null,
            format: !empty($data['format']) ? BriefQuestionFormat::from($data['format']): null,
            class: $data['class'] ?? null,
            page: (int) $data['page'],
            order: (int) ($data['order'] ?? 1),
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'brief_type' => $this->briefType,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'input_type' => $this->inputType,
            'placeholder' => $this->placeholder,
            'format' => $this->format?->value,
            'class' => $this->class,
            'page' => $this->page,
            'order' => $this->order,
            'is_active' => $this->isActive,
        ];
    }
}


