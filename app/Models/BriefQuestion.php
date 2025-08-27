<?php

namespace App\Models;

use App\Enums\Briefs\BriefType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BriefQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'brief_type',
        'title',
        'subtitle',
        'input_type',
        'placeholder',
        'format',
        'class',
        'page',
        'order',
        'is_active',
    ];

    protected $casts = [
        'brief_type' => BriefType::class,
    ];
}
