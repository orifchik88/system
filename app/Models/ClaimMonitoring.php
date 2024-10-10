<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimMonitoring extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'claim_monitoring';
}
