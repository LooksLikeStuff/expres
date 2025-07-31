<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommonBriefHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'common_id',
        'old_data',
        'new_data',
        'changes_description',
        'edited_by',
    ];

    /**
     * Связь с общим брифом
     */
    public function common()
    {
        return $this->belongsTo(Common::class);
    }
    
    /**
     * Связь с пользователем, который редактировал бриф
     */
    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
