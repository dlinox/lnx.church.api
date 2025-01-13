<?php

namespace App\Models;

use App\Casts\ZeroPaddingCast;
use App\Traits\HasDataTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SacramentRecord extends Model
{
    use HasDataTable;

    protected $fillable = [
        'folio_number',
        'act_number',
        'observation',
        'issue_date',
        'sacrament_book_id',
        'sacrament_id',
        'canonical',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'datetime:Y-m-d H:i:s',
        'sacrament_book_id' => 'integer',
        'sacrament_id' => 'integer',
        'folioNumber' => ZeroPaddingCast::class . ':4',
        'actNumber' => ZeroPaddingCast::class . ':4',
        'bookNumber' => ZeroPaddingCast::class . ':4',
        'canonical' => 'boolean',
        'status' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    static $dataTableColumns = [
        'sacrament_records.id',
        'sacrament_records.folio_number as folioNumber',
        'sacrament_records.act_number as actNumber',
        'sacrament_records.observation as observation',
        'sacrament_records.issue_date as issueDate',
        'sacrament_records.sacrament_id as sacramentId',
        'sacrament_records.canonical',
        'sacrament_records.status',
    ];

    static $searchColumns = [
        'sacrament_records.folio_number',
        'sacrament_records.act_number',
        'sacrament_books.number',
        'fellow.name',
        'fellow.paternal_last_name',
        'fellow.maternal_last_name',
        'husband.name',
        'husband.paternal_last_name',
        'husband.maternal_last_name',
        'wife.name',
        'wife.paternal_last_name',
        'wife.maternal_last_name',

    ];

    public function sacrament()
    {
        return $this->belongsTo(Sacrament::class, 'sacramentId', 'id');
    }

    public function sacramentBook()
    {
        return $this->belongsTo(SacramentBook::class, 'sacramentBookId', 'id');
    }

    public function sacramentRoles()
    {
        return $this->hasMany(SacramentRole::class);
    }
}
