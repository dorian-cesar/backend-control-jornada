<?php

namespace App\Http\Controllers;

use App\Models\DriverLog;
use Illuminate\Http\Request;

class DriverLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $logs = DriverLog::with('driver',
        'driver.vehicle',
        'driver.company',
        'driver.smartcard');

        // Logica de sorting
        $sortCol = $request->input('sort.col');
        $sortDir = $request->input('sort.dir');

        if($sortCol==='driver' && $sortDir) {
            $logs->leftJoin('drivers','driver_logs.driver_id', '=', 'drivers.id')
            ->select('driver_logs.*', 'drivers.nombre as nombre')
            ->orderByRaw('ISNULL(nombre), nombre ' . $sortDir);
        } else if($sortCol==='smartcard' && $sortDir) {
            $logs->leftJoin('drivers', 'driver_logs.driver_id', '=', 'drivers.id')
                ->leftJoin('smartcards', 'smartcards.id', '=', 'drivers.smartcard_id')
                ->select('driver_logs.*', 'smartcards.number as number')
                ->orderByRaw('ISNULL(number), number ' . $sortDir);
        } else if($sortCol==='vehicle' && $sortDir) {
            $logs->leftJoin('drivers', 'driver_logs.driver_id', '=', 'drivers.id')
                ->leftJoin('vehicles', 'vehicles.id', '=', 'drivers.vehicle_id')
                ->select('driver_logs.*', 'vehicles.patente as patente')
                ->orderByRaw('ISNULL(patente), patente ' . $sortDir);
        } else if($sortCol==='company' && $sortDir) {
            $logs->leftJoin('drivers', 'driver_logs.driver_id', '=', 'drivers.id')
                ->leftJoin('vehicles', 'vehicles.id', '=', 'drivers.vehicle_id')
                ->leftJoin('companies', 'companies.id', '=', 'vehicles.company_id')
                ->select('driver_logs.*', 'companies.nombre as comnom')
                ->orderByRaw('ISNULL(comnom), comnom ' . $sortDir);
        } else if ($sortCol && $sortDir) {
            $logs->orderBy($sortCol, $sortDir);
        }

        return $request->has('page') ? $logs->paginate(12) : $logs->get();
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
