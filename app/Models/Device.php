<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    static $rules = [
        'sim' => 'sometimes|required|unique:App\Models\Device'
    ];

    protected $fillable = ['sim'];

    public function driver(){
        return $this->hasOne(Vehicle::class);
    }

    public function vehicle(){
        return $this->hasOne(Vehicle::class);
    }
}
