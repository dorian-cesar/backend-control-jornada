<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleState extends Model
{
    use HasFactory;

    static $rules = [
        'estado' => 'sometimes|required|unique:App\Models\VehicleState'
    ];

    protected $fillable = [
        'estado'
    ];

    public function vehicles(){
        return $this->hasMany(Vehicle::class);
    }
}
