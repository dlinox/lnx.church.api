<?php

namespace App\Models\Core;

use App\Traits\HasDataTable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use  Notifiable, HasRoles, HasApiTokens, HasDataTable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }
}
