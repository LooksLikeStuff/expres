<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BriefAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'brief_id',
        'room_id',
        'question_key',
        'answer_text',
        'answer_json',
    ];

    /**
     * Отношение к брифу
     */
    public function brief()
    {
        return $this->belongsTo(Brief::class);
    }

    /**
     * Отношение к комнате/зоне
     */
    public function room()
    {
        return $this->belongsTo(BriefRoom::class, 'room_id');
    }

    /**
     * Отношение к вопросу по ключу
     */
    public function question()
    {
        return $this->belongsTo(BriefQuestion::class, 'question_key', 'key');
    }
}
