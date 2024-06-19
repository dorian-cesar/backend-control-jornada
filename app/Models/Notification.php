<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $appends = ['formatted_updated_at'];

    static $rules = [
        'content' => 'required',
        'severity' => 'required',
        'vehicle_log_id' => 'required',
    ];

    protected $fillable = [
        'content',
        'severity',
        'vehicle_log_id'
    ];

    public function vehicle_log(){
        return $this->belongsTo(VehicleLog::class,'vehicle_log_id','id');
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at->format('d-m-Y H:i:s');
    }
}
