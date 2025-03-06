<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IllegalObjectQuestion extends Model
{
    use HasFactory;

    protected $table = 'illegal_objects_questions';
    protected $guarded = false;

    public function type(): BelongsTo
    {
        return $this->belongsTo(IllegalQuestionType::class, 'illegal_question_type_id');
    }
}
