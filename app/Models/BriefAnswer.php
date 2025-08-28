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
}
