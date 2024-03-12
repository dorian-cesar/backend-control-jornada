<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLog extends Model
{
    use HasFactory;

    static $rules = [
        'coordenadas' => 'required',
        'driver_id' => 'required',
        'event_id' => 'required'
    ];

    protected $fillable = [
        'coordenadas',
        'driver_id',
        'event_id',
        'velocidad'
    ];

    public function driver(){
        return $this->belongsTo(Driver::class);
    }

    public function event(){
        return $this->belongsTo(Event::class);
    }
}
