<?php

namespace App\Models;

use App\Casts\SacramentTypeCast;
use App\Constants\Sacraments;
use App\Traits\HasDataTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SacramentBook extends Model
{
    use HasDataTable;

    protected $fillable = [
        'number',
        'folios_number',
        'year_start',
        'year_finish',
        'acts_per_folio',
        'sacrament_type',
    ];

    public $timestamps = false;

    static $searchColumns = [
        'number',
    ];

    protected $casts = [
        'folios_number' => 'integer',
        'acts_per_folio' => 'integer',
        'number' => 'integer',
        'sacramentType' => SacramentTypeCast::class,
        'sacrament_type' => SacramentTypeCast::class,
    ];

    public function sacramentRecords()
    {
        return $this->hasMany(SacramentRecord::class);
    }

    static function selectItems($type, $search = null)
    {
        $query = self::select(
            'id as value',
            DB::raw('CONCAT( LPAD(number, 4, "0"), " (" , year_start, " - ", ifnull(year_finish, "Actualidad"), ")") as title')
        )
            ->where('sacrament_type', $type)
            ->orderBy('number');

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereRaw('LPAD(number, 4, "0") like ?', ["%$search%"]);
            });
        }

        return $query->get();
    }

    
}
