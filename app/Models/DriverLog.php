<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLog extends Model
{
    use HasFactory;

    protected $appends = ['formatted_updated_at'];
    
    static $rules = [
        'driver_id' => 'required',
        'event' => 'required'
    ];

    protected $fillable = [
        'driver_id',
        'event',
    ];

    public function driver(){
        return $this->belongsTo(Driver::class);
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at->format('d-m-Y H:i:s');
    }
}
