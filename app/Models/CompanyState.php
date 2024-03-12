<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyState extends Model
{
    use HasFactory;

    static $rules = [
        'estado' => 'required|unique:App\Models\CompanyState',
        'color' => 'regex:/([A-Fa-f0-9]{6})/'
    ];

    protected $fillable = [
        'estado',
        'color'
    ];

    public function companies(){
        return $this->hasMany(Company::class);
    }
}
