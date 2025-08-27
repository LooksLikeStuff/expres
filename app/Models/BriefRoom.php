<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BriefRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'brief_id',
        'key',
        'title',
    ];

    public const DEFAULT_ROOMS = [
        ['key' => 'room_prihod', 'title' => 'Прихожая'],
        ['key' => 'room_detskaya', 'title' => 'Детская'],
        ['key' => 'room_kladovaya', 'title' => 'Кладовая'],
        ['key' => 'room_kukhni_i_gostinaya', 'title' => 'Кухня и гостиная'],
        ['key' => 'room_gostevoi_sanuzel', 'title' => 'Гостевой санузел'],
        ['key' => 'room_gostinaya', 'title' => 'Гостиная'],
        ['key' => 'room_rabocee_mesto', 'title' => 'Рабочее место'],
        ['key' => 'room_stolovaya', 'title' => 'Столовая'],
        ['key' => 'room_vannaya', 'title' => 'Ванная комната'],
        ['key' => 'room_kukhnya', 'title' => 'Кухня', ],
        ['key' => 'room_kabinet', 'title' => 'Кабинет'],
        ['key' => 'room_spalnya', 'title' => 'Спальня'],
        ['key' => 'room_garderobnaya', 'title' => 'Гардеробная'],
    ];
}
