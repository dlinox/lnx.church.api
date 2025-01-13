<?php

namespace App\Models;

use App\Models\Core\Country;
use App\Models\Core\Location;
use App\Traits\HasDataTable;
use Illuminate\Database\Eloquent\Model;

class Parish extends Model
{
    use HasDataTable;

    protected $fillable = ['name', 'address', 'phone_number', 'country', 'location'];

    protected $hidden = ['created_at', 'updated_at'];

    static $searchColumns = [
        'parishes.name',
    ];

    static $dataTableColumns = [
        'parishes.id',
        'parishes.name',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function sacraments()
    {
        return $this->hasMany(Sacrament::class);
    }

    static public function selectItems($search = null)
    {
        $query = self::select('parishes.id as value', 'parishes.name as title');

        if ($search) {
            $query->where('parishes.name', 'like', "%$search%");
        }

        return $query->get();
    }
}
