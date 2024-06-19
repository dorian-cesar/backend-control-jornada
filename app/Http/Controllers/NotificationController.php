<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function push(Request $request){
        request()->validate(Notification::$rules);

        return Notification::create($request->all());
    }

    public function index(){
        return Notification::with('vehicle_log','vehicle_log.vehicle')->whereDate('created_at', now()->toDateString())->orderBy('created_at', 'desc')->get();
    }
}
