<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualConfirmedClaim extends Model
{
    use HasFactory;

    protected $table = 'claim_manual_confirmed_objects';

    protected $guarded = false;
}
