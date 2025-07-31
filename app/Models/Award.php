<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'category',
    ];

    /**
     * Пользователи, имеющие эту награду
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_awards')
            ->withPivot('awarded_by', 'comment', 'awarded_at')
            ->withTimestamps();
    }
}
