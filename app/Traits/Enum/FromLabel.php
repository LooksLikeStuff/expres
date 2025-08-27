<?php

namespace App\Traits\Enum;

trait FromLabel
{
    public static function fromLabel(string $label): self
    {
        $label = trim(mb_strtolower($label));

        foreach (self::labels() as $value => $name) {
            $name = trim(mb_strtolower($name));

            if ($name === $label || str_contains($name, $label)) {
                return self::from($value);
            }
        }

        throw new \InvalidArgumentException("Unknown label: $label");
    }
}
