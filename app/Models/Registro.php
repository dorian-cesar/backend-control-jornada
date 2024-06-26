<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    use HasFactory;

    protected $table = 'registros';

    protected $fillable = [
        'rut',
        'tipo',
       
        'metodo',
        'patente',
    ];

    public $timestamps = true;

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'rut', 'rut');
    }
}
