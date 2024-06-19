<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleLog extends Model
{
    use HasFactory;

    protected $appends = ['formatted_updated_at'];
    
    static $rules = [
        'lat' => 'sometimes|required',
        'lng' => 'sometimes|required',
        'vehicle_id' => 'required',
    ];

    protected $fillable = [
        'lat',
        'lng',
        'vehicle_id',
        'velocidad',
        'direccion',
        'ignicion',
        'estado',
        'conexion'
    ];

    public function vehicle(){
        return $this->belongsTo(Vehicle::class,'vehicle_id','track_id');
    }

    public function notifications(){
        return $this->hasMany(Notification::class,'vehicle_id','vehicle_log_id');
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at->format('d-m-Y H:i:s');
    }
}
