<?php

namespace App\Models;

use App\Enums\BlockModeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BlockType extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function modes(): BelongsTo
    {
        return $this->belongsTo(BlockMode::class);
    }

    protected $casts = [
        'block_mode_id' => BlockModeEnum::class
    ];


}
