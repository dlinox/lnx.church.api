<?php

namespace App\Models;

use App\Models\Core\Person;
use Illuminate\Database\Eloquent\Model;

class FamilyRelationship extends Model
{
    protected $fillable = [
        'relationship',
        'person_id',
        'related_person_id',
    ];

    protected $casts = [
        'relationship' => 'integer',
        'person_id' => 'integer',
        'related_person_id' => 'integer',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id', 'id');
    }

    public function relatedPerson()
    {
        return $this->belongsTo(Person::class, 'related_person_id', 'id');
    }
}
