<?php

namespace App\Http\Controllers;

use App\Models\DriverLog;
use Illuminate\Http\Request;

class DriverLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DriverLog::with('driver',
        'event',
        'driver.vehicle',
        'driver.company',
        'driver.vehicle.vehicle_type',
        'driver.vehicle.device',
        'driver.smartcard')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        request()->validate(DriverLog::$rules);

        return DriverLog::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return DriverLog::with('driver',
        'event',
        'driver.vehicle',
        'driver.company',
        'driver.vehicle.vehicle_type',
        'driver.vehicle.device',
        'driver.smartcard')->where('id',$id)->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $log = DriverLog::where('id',$id)->first();
        $log->update($request->all());

        return $log;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DriverLog::where('id',$id)->first()->delete();
        return response()->json(null, 204);
    }
}
