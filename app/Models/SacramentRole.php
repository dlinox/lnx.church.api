<?php

namespace App\Models;

use App\Models\Core\Person;
use Illuminate\Database\Eloquent\Model;

class SacramentRole extends Model
{

    protected $fillable = [
        'role',
        'person_id',
        'sacrament_record_id',
    ];

    protected $casts = [
        'role' => 'integer',
        'person_id' => 'integer',
        'sacrament_record_id' => 'integer',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id', 'id');
    }

    public function familyRelationships()
    {
        return $this->hasMany(FamilyRelationship::class, 'person_id', 'person_id')->with('relatedPerson');
    }
}
