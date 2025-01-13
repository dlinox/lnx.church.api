<?php

namespace App\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;

class PrintingRecord extends Model
{

    protected $fillable = [
        'data',
        'sacrament_record_id',
        'user_id',
    ];

    protected $casts = [
        'data' => 'json',
        'sacrament_record_id' => 'integer',
        'user_id' => 'integer',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    //registra
    static function register($data, $userId)
    {
        $item = self::create([
            'data' => $data,
            'sacrament_record_id' => $data['id'],
            'user_id' => $userId,
        ]);
        return $item;
    }

    //obtener el ultimo registro
    static function existsPrintingRecord($sacramentRecordId)
    {
        $item = self::where('sacrament_record_id', $sacramentRecordId)->exists();
        return $item;
    }
}
