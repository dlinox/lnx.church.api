<?php

namespace App\Models;

use App\Traits\HasDataTable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sacrament extends Model
{

    use HasDataTable;

    protected $fillable = [
        'date',
        'description',
        'type',
        'status',
        'is_external',
        'parish_id',
        'minister_id',
    ];

    protected $casts = [
        'date'  => 'date:Y-m-d',
        'status' => 'boolean',
        'is_external' => 'boolean',
        'parish_id' => 'integer',
        'minister_id' => 'integer',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'dateFormatted',
    ];

    public function getDateFormattedAttribute()
    {
        return Carbon::parse($this->attributes['date'])->locale('es')->isoFormat('D [de] MMMM, YYYY');
    }

    static $dataTableColumns = [
        'sacraments.id',
        'sacraments.date',
        'sacraments.description',
        'sacraments.type',
        'sacraments.status',
        'sacraments.is_external as isExternal',
        'sacraments.parish_id as parishId',
        'sacraments.minister_id as ministerId',
    ];

    public function minister()
    {
        return $this->belongsTo(
            Minister::class,
            'ministerId',
            'id'
        )->select([
            'id',
            DB::raw("CONCAT_WS(' ', name, paternal_last_name, maternal_last_name) as name"),
        ]);
    }

 

    public function parish()
    {
        return $this->belongsTo(Parish::class, 'parishId', 'id');
    }

    public function sacramentRecords()
    {
        return $this->hasMany(SacramentRecord::class);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
