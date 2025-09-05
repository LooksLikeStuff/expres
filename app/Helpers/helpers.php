<?php

if (! function_exists('normalizePhone')) {
    /**
     * Нормализует телефон к формату +7XXXXXXXXXX
     */
    function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '8')) {
            $digits = '7' . substr($digits, 1);
        }

        return '+' . $digits;
    }
}

if (! function_exists('formatPhone')) {
    /**
     * Форматирует телефон в красивый вид +7 (XXX) XXX-XX-XX
     */
    function formatPhone(string $phone): string
    {
        return preg_replace(
            '/\+7(\d{3})(\d{3})(\d{2})(\d{2})/',
            '+7 ($1) $2-$3-$4',
            $phone
        );
    }
}
