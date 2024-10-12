<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Claim extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'claims';

    protected $guarded = [];
    protected $appends = ['expiry_day', 'blocks'];

    public function getExpiryDayAttribute()
    {
        $date = Carbon::parse($this->expiry_date);
        $now = Carbon::now();

        $diff = $now->diffInDays($date, false);

        return ($diff > 0) ? $diff : 0;
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region', 'soato')->select('name_uz', 'soato');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district', 'soato')->select('name_uz', 'soato');
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'object_id', 'id')->select('id', 'name');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ClaimOrganizationReview::class);
    }

    public function monitoring(): BelongsTo
    {
        return $this->belongsTo(ClaimMonitoring::class, 'id', 'claim_id');
    }

    public function getBlocksAttribute()
    {
        if ($this->monitoring()->first() != null) {
            $blockArray = json_decode($this->monitoring()->first()->blocks, true);
            $blocks = Block::with(['type', 'mode'])->whereIn('id', $blockArray)->get();
            $blockResponseArr = [];
            foreach ($blocks as $block) {
                $countChecklist = $block->getClaimChecklistCount($this->id);
                $blockArr = $block->toArray();
                $blockArr['is_filled'] = $countChecklist == 7;
                $blockResponseArr[] = $blockArr;
            }

            return $blockResponseArr;
        } else return null;
    }
}
