<?php

namespace App\Traits;

trait ToArrayTrait
{
    public function toArray(): array
    {
        $vars = get_object_vars($this);
        $result = [];
        foreach ($vars as $key => $value) {
            // lower_snake_case
            $snake = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));
            $result[$snake] = $value;
        }
        return $result;
    }
}
