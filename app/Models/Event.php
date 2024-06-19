<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    static $rules = [
        'evento' => 'sometimes|required|unique:App\Models\Event'
    ];

    protected $fillable = [
        'evento'
    ];
}
