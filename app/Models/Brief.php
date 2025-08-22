<?php

namespace App\Models;

use App\Enums\Briefs\BriefType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brief extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'deal_id',
        'type',
        'title',
        'description',
        'status',
        'article',
        'zones',
        'total_area',
        'price',
        'preferences',
    ];

    protected $casts = [
        'type' => BriefType::class,
    ];
}
