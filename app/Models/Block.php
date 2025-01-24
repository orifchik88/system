<?php

namespace App\Models;

use App\Enums\BlockModeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Block extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'block_mode_id' => BlockModeEnum::class
    ];

    protected $appends = ['end_date'];

    public function getEndDateAttribute()
    {
        $monitoring = ClaimMonitoring::query()
            ->where('object_id', $this->article_id)
            ->whereJsonContains('blocks', $this->id)
            ->orderBy('id', 'desc')
            ->first();

        if(!$monitoring)
            return "-";

        $object = Claim::query()->where('id', $monitoring->claim_id)->first();

        return $object->end_date;
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function responses(): BelongsTo
    {
        return $this->belongsTo(DxaResponse::class,  'dxa_response_id');
    }

    public function mode(): BelongsTo
    {
        return $this->belongsTo(BlockMode::class, 'block_mode_id');
    }

    public function getClaimChecklistCount($claim_id)
    {
        return CheckListAnswer::query()->where(['monitoring_id' => $claim_id, 'type' => 2, 'block_id' => $this->id])->select('question_id')->groupBy('question_id')->get()->count();
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(BlockType::class, 'block_type_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function actViolationBlock(): BelongsTo
    {
        return $this->belongsTo(ActViolationBlock::class, 'block_id');
    }

    public function appearanceType(): BelongsTo
    {
        return $this->belongsTo(AppearanceType::class, 'appearance_type');
    }

}
