<?php

namespace App\Models\Core;

use App\Traits\HasDataTable;
use Spatie\Permission\Models\Permission as ModelsPermission;

class Permission extends ModelsPermission
{
    use HasDataTable;

    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'display_name',
        'guard_name',
        'parent_id',
        'type',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    static $searchColumns = ['permissions.display_name'];
}
