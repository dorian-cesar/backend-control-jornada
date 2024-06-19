<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    static $rules = [
        'rut' => 'sometimes|required|regex:/\d{1,2}\.?\d{3}\.?\d{3}[-][0-9Kk]/|unique:App\Models\Company',
        'nombre' => 'sometimes|required',
        'estado' => 'sometimes',
    ];

    protected $fillable = [
        'rut',
        'nombre',
        'estado',
    ];

    public function company_state(){
        return $this->belongsTo(CompanyState::class);
    }

    public function vehicles(){
        return $this->hasMany(Vehicle::class);
    }

    public function drivers(){
        return $this->hasMany(Driver::class);
    }
}
