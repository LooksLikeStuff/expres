<?php

if (!function_exists('safe_date_format')) {
    /**
     * Безопасное форматирование даты
     * Проверяет тип переменной перед вызовом format()
     * 
     * @param mixed $date Дата (может быть объектом Carbon/DateTime или строкой)
     * @param string $format Формат даты (по умолчанию Y-m-d)
     * @param string $default Значение по умолчанию если дата пустая
     * @return string
     */
    function safe_date_format($date, $format = 'Y-m-d', $default = '')
    {
        if (empty($date)) {
            return $default;
        }
        
        // Если это объект с методом format (Carbon, DateTime)
        if (is_object($date) && method_exists($date, 'format')) {
            return $date->format($format);
        }
        
        // Если это строка, пытаемся её обработать
        if (is_string($date)) {
            try {
                // Если строка уже в нужном формате, возвращаем как есть
                if ($format === 'Y-m-d' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    return $date;
                }
                
                // Пытаемся создать Carbon из строки
                $carbonDate = \Carbon\Carbon::parse($date);
                return $carbonDate->format($format);
            } catch (\Exception $e) {
                // Если не удалось распарсить, возвращаем оригинальную строку или значение по умолчанию
                return is_string($date) ? $date : $default;
            }
        }
        
        return $default;
    }
}

if (!function_exists('safe_date_display')) {
    /**
     * Безопасное отображение даты в читаемом формате
     * 
     * @param mixed $date
     * @param string $default
     * @return string
     */
    function safe_date_display($date, $default = 'Не указана')
    {
        return safe_date_format($date, 'd.m.Y', $default);
    }
}

if (!function_exists('safe_date_input')) {
    /**
     * Безопасное форматирование даты для HTML input[type="date"]
     * 
     * @param mixed $date
     * @return string
     */
    function safe_date_input($date)
    {
        return safe_date_format($date, 'Y-m-d', '');
    }
}
