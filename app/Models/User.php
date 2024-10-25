<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserStatusEnum;
use App\Models\Traits\UserRoleTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable,SoftDeletes, UserRoleTrait;


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getRoleFromToken()
    {
        return  JWTAuth::parseToken()->getClaim('role_id');
    }
    protected $guard_name = 'web';

    protected $guarded = false;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'user_status_id' => UserStatusEnum::class
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function objects(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_users', 'user_id', 'article_id')->withPivot('role_id');
    }

    public function inspectorObjects(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id', 'id');
    }

    public function regulations(): HasMany
    {
        return $this->hasMany(Regulation::class)
        ->where('regulations.user_id', $this->id)
        ->orWhere('regulations.created_by_user_id', $this->id)
        ->orWhere('regulations.role_id', $this->getRoleFromToken())
        ->orWhere('regulations.created_by_role_id', $this->getRoleFromToken());
    }

    public function monitorings(): HasMany
    {
        return $this->hasMany(Monitoring::class, 'created_by');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(UserStatus::class, 'user_status_id', 'id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'user_id');
    }








//    public function questions(): HasMany
//    {
//        return $this->hasMany(Question::class, 'author_id', 'id');
//    }

}


