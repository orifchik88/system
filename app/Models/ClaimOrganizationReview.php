<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimOrganizationReview extends Model
{
    use HasFactory;

    protected $table = 'claim_organization_reviews';
    protected $guarded = [];

    public function monitoring()
    {
        return $this->belongsTo(ClaimMonitoring::class, 'monitoring_id', 'id')->with('claim');
    }

    public function getAnswerAttribute()
    {
        $answers = ($this->attributes['answer']) ? json_decode(gzuncompress(base64_decode($this->attributes['answer'])), true) : null;
        if ($answers != null) {
            foreach ($answers as $key => $value) {
                if (str_contains($key, 'conclusion_act'))
                    $answers['conclusion_act'] = $value;
            }
        }

        return ($answers != null) ? $answers['conclusion_act'] : null;
    }
}