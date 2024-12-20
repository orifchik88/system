<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IllegalObject extends Model
{
    use HasFactory;

    protected $table = 'illegal_objects';
    protected $guarded = false;

    public function images()
    {
        return $this->hasMany(IllegalObjectImage::class, 'illegal_object_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id')->select('name_uz', 'soato', 'id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id', 'id')->select('name_uz', 'soato', 'id');
    }
}
