<?php

namespace App\Models\Core;

use App\Traits\HasDataTable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as ModelsRole;
use Illuminate\Support\Str;

class Role extends ModelsRole
{
    use HasDataTable;

    static $searchColumns = ['roles.display_name'];

    protected $fillable = [
        'name',
        'display_name',
        'guard_name',
    ];

    public function setDisplayNameAttribute($value)
    {
        $this->attributes['display_name'] = $value;
        $this->attributes['name'] = Str::snake($value);
    }
 
    static function selectItems()
    {
        return self::select('id as value', 'display_name as title')
            ->where('name', '<>', 'super')
            ->orderBy('display_name')
            ->get();
    }
}
