<?php

namespace App\Models\Core;

use App\Casts\DocumentTypeCast;
use App\Casts\GenderCast;
use App\Casts\JsonObjectCast;
use App\Models\FamilyRelationship;
use App\Models\SacramentRole;
use App\Traits\HasDataTable;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
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
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    protected $appends = [
        'fullName',
    ];

    protected $casts = [
        'birth_date' => 'date:Y-m-d',
        'document_type' => DocumentTypeCast::class,
        'documentType' => DocumentTypeCast::class,
        'gender' => GenderCast::class,

        'birth_country' => JsonObjectCast::class,
        'birthCountry' => JsonObjectCast::class,

        'birth_location' => JsonObjectCast::class,
        'birthLocation' => JsonObjectCast::class,

    ];

    static $dataTableColumns = [
        'people.id',
        'people.document_type as documentType',
        'people.document_number as documentNumber',
        'people.name as name',
        'people.paternal_last_name as paternalLastName',
        'people.maternal_last_name as maternalLastName',
        'people.gender',
        'people.birth_date as birthDate',
        'people.birth_location_detail as birthLocationDetail',
    ];

    static $searchColumns = [
        'people.document_number',
        'people.name',
        'people.paternal_last_name',
        'people.maternal_last_name',
    ];

    public function getFullNameAttribute()
    {
        return "{$this->name} {$this->paternalLastName} {$this->maternalLastName}";
    }

    public function familyRelationships()
    {
        return $this->hasMany(FamilyRelationship::class, 'person_id', 'id');
    }

    public function sacramentRoles()
    {
        return $this->hasMany(SacramentRole::class, 'person_id', 'id');
    }
}
