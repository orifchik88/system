<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DxaResponseStatus extends Model
{
    use HasFactory;

//    public function response(): BelongsTo
//    {
//      return $this->belongsTo(DxaResponse::class);
//    }
}
