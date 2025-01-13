<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{

    public $incrementing = false;

    protected $keyType = 'string';
    
    public $timestamps = false;
}
