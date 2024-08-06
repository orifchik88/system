<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Traits\UserRoleTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles,SoftDeletes, UserRoleTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $guard_name = 'web';

    protected $guarded = [];


//    protected $fillable = [
//        'name',
//        'email',
//        'password',
//    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getFullNameAttribute()
    {
        return ucwords("{$this->surname} {$this->name} {$this->middle_name}");
    }

    public function objects(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_users', 'user_id', 'article_id');
    }

    public function inspectorObjects(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id', 'id');
    }

    public function regulations(): HasMany
    {
        return $this->hasMany(Regulation::class, 'user_id', 'id');
    }

    public function scopeSearchByFullName($query, $searchTerm)
    {
        $searchTerm = strtolower($searchTerm);
        return $query->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
            ->orWhereRaw('LOWER(middle_name) LIKE ?', ['%' . $searchTerm . '%'])
            ->orWhereRaw('LOWER(surname) LIKE ?', ['%' . $searchTerm . '%']);
    }






//    public function questions(): HasMany
//    {
//        return $this->hasMany(Question::class, 'author_id', 'id');
//    }

}


