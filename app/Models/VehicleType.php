<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use HasFactory;

    static $rules = [
        'tipo' => 'sometimes|required|unique:App\Models\VehicleType'
    ];

    protected $fillable = [
        'tipo'
    ];

    public function vehicles(){
        return $this->hasMany(Vehicle::class);
    }
}
