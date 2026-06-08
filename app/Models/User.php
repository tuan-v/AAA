<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Company;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    const TYPE_SYSTEM = 'system';
    const TYPE_USER = 'user';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_BLOCKED = 'blocked';
    const STATUS_PENDING = 'pending';
    protected $table = 'users';
    protected $fillable = [
        'email',
        'username',
        'phone',
        'password',
        'type',
        'google_id',
        'company_id',
        'department_id',
        'address',
        'avatar',
        'creater_id',
        'status',
        'zalo_verified',
        'zalo_verified_at',
        'zalo_user_id',
        'last_login_at',
        'last_login_ip',
        'name',
        'is_employee',
    ];

    // protected static function booted()
    // {
    //     static::addGlobalScope(new \App\Scopes\CompanyScope);
    // }
    public function isSystem(): bool
    {
        return $this->type === self::TYPE_SYSTEM;
    }
    public function scopeVisibleFor($query, $user)
    {
        if ($user->type === self::TYPE_SYSTEM) {
            return $query;
        }

        return $query->whereHas('companies', function ($q) use ($user) {
            $q->where('companies.id', $user->company_id);
        });
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        // 'role_name'
    ];
    // public function getRoleNameAttribute()
    // {
    //     return $this->roles->first()?->name; //quan hệ role
    // }
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'zalo_verified' => 'boolean',
            'zalo_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the companies for the user.
     */
    public function companies()
    {
        return $this->morphToMany(
            Company::class,
            'model',
            'model_has_company',
            'model_id',
            'company_id'
        )->withTimestamps();
    }
    /**
     * Get the company that the user owns.
     */
    public function ownedCompanies()
    {
        return $this->hasMany(Company::class, 'user_id');
    }

    /**
     * Get all user_company records for this user.
     */
    public function userCompanies()
    {
        return $this->hasMany(UserCompany::class)->where('status', 'active')->with('company', 'department', 'position');
    }

    /**
     * Get the current company context (first company by default).
     */
    public function currentCompany()
    {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }

    /**
     * Get departments the user belongs to through companies.
     */
    public function departments()
    {
        return $this->hasManyThrough(
            Department::class,
            UserCompany::class,
            'user_id',
            'id',
            'id',
            'department_id'
        );
    }

    /**
     * Get positions the user has through companies.
     */
    public function positions()
    {
        return $this->hasManyThrough(
            Position::class,
            UserCompany::class,
            'user_id',
            'id',
            'id',
            'position_id'
        );
    }

    /**
     * Get the social accounts for the user.
     */
    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Get the company that the user belongs to.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the creator of the user.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creater_id');
    }

    /**
     * Get the users created by this user.
     */
    public function createdUsers()
    {
        return $this->hasMany(User::class, 'creater_id');
    }

    /**
     * Get the sessions for the user.
     */
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive users.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope a query to only include blocked users.
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    /**
     * Scope a query to only include pending users.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is blocked.
     */
    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    public function getPositionsInCompany($companyId)
    {
        return $this->positions()
            ->whereHas('userCompanies', function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->where('user_id', $this->id);
            })
            ->first();
    }
    public function getDepartmentsInCompany($companyId)
    {
        return $this->departments()
            ->whereHas('userCompanies', function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->where('user_id', $this->id);
            })
            ->first();
    }

    /**
     * Get departments in the authenticated user's company (accessor).
     */
    public function getDepartmentAttribute()
    {
        if (!Auth::check()) {
            return null;
        }
        return $this->getDepartmentsInCompany(Auth::user()->company_id);
    }

    /**
     * Get positions in the authenticated user's company (accessor).
     */
    public function getPositionAttribute()
    {
        if (!Auth::check()) {
            return null;
        }
        return $this->getPositionsInCompany(Auth::user()->company_id);
    }
}
