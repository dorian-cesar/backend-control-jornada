<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Vehicle extends Model
{
    use HasFactory;

    static $rules = [

        'patente' => 'sometimes|required|regex:/[a-zA-Z]{2}[a-zA-Z0-9]{2}-?[0-9]{2}/|unique:App\models\Vehicle',
    ];

    protected $fillable = [
        'patente',
        'vehicle_type_id',
        'device_id',
        'track_id',
        'imei',
        'company_id',
    ];

    public function device(){
        return $this->belongsTo(Device::class);
    }

    public function driver(){
        return $this->hasOne(Driver::class);
    }

    public function vehicle_logs(){
        return $this->hasMany(VehicleLog::class, 'vehicle_id', 'track_id');
    }

    public function latest_log(){
        return $this->hasOne(VehicleLog::class, 'vehicle_id', 'track_id')->latest();
    }

    public function vehicle_type(){
        return $this->belongsTo(VehicleType::class);
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }
}
