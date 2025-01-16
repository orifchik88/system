<?php

namespace App\Models;

use App\Enums\ObjectStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use function Laravel\Prompts\select;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $appends = ['cost'];

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    protected $casts = [
        'object_status_id' => ObjectStatusEnum::class,
    ];

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class);
    }

    public function getCostAttribute()
    {
        $totalPaid = $this->paymentLogs->sum(function ($log) {
            return isset($log->content->additionalInfo->amount)
                ? (float)$log->content->additionalInfo->amount
                : 0;
        });

        $totalAmount = (float) $this->price_supervision_service;

        $notPaid = $totalAmount - $totalPaid;

        if (trim($this->price_supervision_service) === '0.00') {
            return 'no_value';
        }

        return $notPaid;
    }

    public function inspector(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'article_users', 'article_id', 'user_id')->withPivot('role_id')->wherePivot('role_id', 3);
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

        return $query->whereRaw('LOWER(articles.name) LIKE ?', ['%' . $searchTerm . '%']);

    }

    public function scopeSearchByOrganization($query, $searchTerm)
    {
        $searchTerm = strtolower($searchTerm);

        return $query->whereRaw('LOWER(articles.organization_name) LIKE ?', ['%' . $searchTerm . '%']);

    }

    public function scopeSearchByCustomerName($query, $searchTerm)
    {
        $searchTerm = strtolower($searchTerm);

        return $query->whereRaw('LOWER(articles.organization_name) LIKE ?', ['%' . $searchTerm . '%'])
                    ->orWhereRaw('LOWER(articles.name) LIKE ?', ['%' . $searchTerm . '%']);

    }




    public function scopeSearchByTaskId($query, $searchTerm)
    {
        return $query->where('articles.task_id', 'like', '%' . $searchTerm . '%')
                ->orWhere('articles.id', 'like', '%' . $searchTerm . '%');
    }


    public function regulations(): HasMany
    {
        return $this->hasMany(Regulation::class, 'object_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(CheckListAnswer::class, 'object_id');
    }

    public function sphere(): BelongsTo
    {
        return $this->belongsTo(Sphere::class, 'sphere_id');
    }
    public function objectType(): BelongsTo
    {
        return $this->belongsTo(ObjectType::class, 'object_type_id');
    }

    public function violations(): HasManyThrough
    {
        return $this->hasManyThrough(Violation::class, Regulation::class, 'object_id');
    }

    public function monitorings(): HasMany
    {
        return $this->hasMany(Monitoring::class, 'object_id');
    }

    public function paymentLogs(): HasMany
    {
        return $this->hasMany(ArticlePaymentLog::class, 'gu_id');
    }

    public function totalAmount()
    {
        return $this->paymentLogs()
            ->get()
            ->sum(function ($log) {
                return $log->content->additionalInfo->amount ?? 0;
            });
    }
    public function scopeWithPaymentStats(Builder $query)
    {
        return $query->with(['paymentLogs' => function ($query) {
            $query->select('gu_id', DB::raw('SUM(CAST(content->additionalInfo->>amount AS DECIMAL)) as total_paid'))
                ->groupBy('gu_id');
        }]);
    }

    public function getPaymentStatusAttribute()
    {
        $totalPaid = $this->paymentLogs->sum('total_paid');
        $priceSupervisionService = (float)$this->price_supervision_service;

        if ($totalPaid === 0) {
            return 1;
        } elseif ($totalPaid < $priceSupervisionService) {
            return 2;
        } else {
            return 3;
        }
    }




}
