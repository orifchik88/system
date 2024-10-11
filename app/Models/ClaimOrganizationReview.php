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
}
