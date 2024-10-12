<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimMonitoring extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'claim_monitoring';
    protected $appends = ['operator_field'];
    protected $hidden = ['operator_answer'];
    public function claim()
    {
        return $this->belongsTo(Claim::class, 'claim_id', 'id')->with('region');
    }

    public function getOperatorFieldAttribute()
    {
        $answers = ($this->attributes['operator_answer'] != null) ? json_decode(gzuncompress(base64_decode($this->attributes['operator_answer'])), true) : null;

        return $answers;
    }
}
