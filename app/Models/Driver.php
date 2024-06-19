<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    static $rules = [
        'rut' => 'sometimes|required|regex:/\d{1,2}\.?\d{3}\.?\d{3}[-][0-9Kk]/|unique:App\Models\Driver',
        'nombre' => 'sometimes|required',
        'activo' => 'sometimes|required|boolean',
        'smartcard_id' => 'nullable',
        'vehicle_id' => 'nullable'
    ];

    protected $fillable = [
        'rut',
        'nombre',
        'activo',
        'smartcard_id',
        'vehicle_id',
        'company_id',
    ];

    public function smartcard(){
        return $this->belongsTo(Smartcard::class);
    }

    public function vehicle(){
        return $this->belongsTo(Vehicle::class);
    }

    public function driver_logs(){
        return $this->hasMany(DriverLog::class);
    }

    public function latestlog(){
        return $this->hasOne(DriverLog::class)->latestOfMany();
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }
}
