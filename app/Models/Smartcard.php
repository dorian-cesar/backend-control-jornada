<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Smartcard extends Model
{
    use HasFactory;

    static $rules = [
        'number' => 'sometimes|required|unique:App\Models\Smartcard',
        'driver_id' => 'sometimes'
    ];

    protected $fillable = ['number'];

    public function driver(){
        return $this->hasOne(Driver::class);
    }
}
