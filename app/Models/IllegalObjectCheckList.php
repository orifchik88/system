<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IllegalObjectCheckList extends Model
{
    use HasFactory;

    protected $table = 'illegal_objects_checklist';
    protected $guarded = false;

    public function question(): BelongsTo
    {
        return $this->belongsTo(IllegalObjectQuestion::class, 'question_id', 'id');
    }
}
