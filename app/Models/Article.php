<?php

namespace App\Models;

use App\Enums\ObjectStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'object_status_id' => ObjectStatusEnum::class
    ];

    public function articleBlocks(): HasMany
    {
        return $this->hasMany(Block::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'article_users', 'article_id', 'user_id')->withPivot('role_id');
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(DxaResponse::class, 'dxa_response_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'article_users', 'article_id', 'role_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function objectStatus(): BelongsTo
    {
        return $this->belongsTo(ObjectStatus::class);
    }

    public function difficulty(): BelongsTo
    {
        return $this->belongsTo(DifficultyCategory::class, 'difficulty_category_id');
    }

    public function fundingSource(): BelongsTo
    {
        return $this->belongsTo(FundingSource::class, 'funding_source_id');
    }

    public function objectSector(): BelongsTo
    {
        return $this->belongsTo(ObjectSector::class, 'object_sector_id');
    }

    public function scopeSearchByName($query, $searchTerm)
    {
        $searchTerm = strtolower($searchTerm);

        return $query->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%']);

    }
    public function scopeSearchByTaskId($query, $searchTerm)
    {
        return $query->where('task_id', 'like', '%' . $searchTerm . '%')
                ->orWhere('id', 'like', '%' . $searchTerm . '%');
    }


    public function regulations(): HasMany
    {
        return $this->hasMany(Regulation::class, 'object_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function sphere(): BelongsTo
    {
        return $this->belongsTo(Sphere::class, 'sphere_id');
    }
    public function objectType(): BelongsTo
    {
        return $this->belongsTo(ObjectType::class, 'object_type_id');
    }



}
