<?php

namespace App\Models;

use App\Traits\HasPermissions;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasPermissions;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'tenant_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Permission role ikut dikirim saat model di-serialize (login, /user).
     */
    protected $appends = [
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
