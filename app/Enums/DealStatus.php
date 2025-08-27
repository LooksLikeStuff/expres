<?php

namespace App\Enums;

enum DealStatus: string
{
    case WAITING_TZ = 'waiting_tz';
    case PLANNING = 'planning';
    case COLLAGES = 'collages';
    case VISUALIZATION = 'visualization';
    case IN_PROGRESS = 'in_progress';
    case WORK_COLLECTION_IP = 'work_collection_ip';
    case PROJECT_READY = 'project_ready';
    case PROJECT_COMPLETED = 'project_completed';
    case PROJECT_ON_HOLD = 'project_on_hold';
    case RETURNED = 'returned';
    case FINISHED = 'finished';
    case LATER = 'later';
    case REGISTRATION = 'registration';
    case BRIEF_ATTACHED = 'brief_attached';
    case SUPPORT = 'support';
    case ACTIVE = 'active';

    public function label(): string
    {
        return match ($this) {
            self::WAITING_TZ => 'Ждем ТЗ',
            self::PLANNING => 'Планировка',
            self::COLLAGES => 'Коллажи',
            self::VISUALIZATION => 'Визуализация',
            self::IN_PROGRESS => 'В работе',
            self::WORK_COLLECTION_IP => 'Рабочка/сбор ИП',
            self::PROJECT_READY => 'Проект готов',
            self::PROJECT_COMPLETED => 'Проект завершен',
            self::PROJECT_ON_HOLD => 'Проект на паузе',
            self::RETURNED => 'Возврат',
            self::FINISHED => 'Завершенный',
            self::LATER => 'На потом',
            self::REGISTRATION => 'Регистрация',
            self::BRIEF_ATTACHED => 'Бриф прикреплен',
            self::SUPPORT => 'Поддержка',
            self::ACTIVE => 'Активный',
        };
    }
}
