<?php

namespace App\Models;

use App\Casts\DocumentTypeCast;
use App\Casts\GenderCast;
use App\Casts\MinisterTypeCast;
use App\Traits\HasDataTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Minister extends Model
{
    use HasDataTable;

    protected $fillable = [
        'document_type',
        'document_number',
        'name',
        'paternal_last_name',
        'maternal_last_name',
        'gender',
        'birth_date',
        'birth_country',
        'birth_location',
        'birth_location_detail',
        'phone_number',
        'email',
        'type',
        'status'
    ];

    protected $casts = [
        'gender' => GenderCast::class,
        'type' => MinisterTypeCast::class,
        'documentType' => DocumentTypeCast::class,
        'document_type' => DocumentTypeCast::class,
        'status' => 'boolean',
        'birth_date' => 'date:Y-m-d',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    static $dataTableColumns = [
        'id',
        'document_type as documentType',
        'document_number as documentNumber',
        'name',
        'paternal_last_name as paternalLastName',
        'maternal_last_name as maternalLastName',
        'phone_number as phoneNumber',
        'birth_date as birthDate',
        'email',
        'gender',
        'type',
        'status',
    ];

    static $searchColumns = [
        'ministers.document_number',
        'ministers.name',
        'ministers.paternal_last_name',
        'ministers.maternal_last_name',
        'ministers.email',
    ];

    public function sacraments()
    {
        return $this->hasMany(Sacrament::class);
    }

    static function selectItems($search = null)
    {
        $query = self::select(
            'id as value',
            DB::raw('CONCAT_WS(" ", name, paternal_last_name, maternal_last_name) as title')
        );

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereRaw('concat_ws(" ", name, paternal_last_name, maternal_last_name) like ?', ["%$search%"]);
            });
        }

        return $query->get();
    }
}
