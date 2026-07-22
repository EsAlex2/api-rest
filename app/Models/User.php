<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'cedula',
        'gender',
        'mobile_phone',
        'email',
        'username',
        'password',
        'role_id',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'role',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getRoleAttribute()
    {
        return $this->roleRelation?->name_role;
    }

    public function setRoleAttribute($value)
    {
        if (is_string($value)) {
            $role = Role::where('name_role', $value)->first();
            if ($role) {
                $this->attributes['role_id'] = $role->id;
            }
        } elseif (is_numeric($value)) {
            $this->attributes['role_id'] = $value;
        }
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->roleRelation && $this->roleRelation->permissions()->where('name_permission', $permission)->exists();
    }
}
