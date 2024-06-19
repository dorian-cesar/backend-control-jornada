<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\VehicleLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleLogController extends Controller
{
    public function store(Request $request){
        request()->validate(VehicleLog::$rules);

        $log = VehicleLog::create($request->all());

        if($log->velocidad > 45){
            $severity = 0;
            $speed = 45;
            if($log->velocidad > 50){
                $severity = 1;
                $speed = 50;
            }
            if($log->velocidad > 55){
                $severity = 2;
                $speed = 55;
            }
            Notification::create([
                'content' => "Velocidad sobre los " . $speed . " Km/h.",
                'severity' => $severity,
                'vehicle_log_id' => $log->id,
            ]);
        }

        return $log;
    }

    public function getLogs(){
        $logs = VehicleLog::with('vehicle','vehicle.driver','vehicle.company')->get();

        return $logs;
    }

    public function getLogsID(Request $request, $id){
        $logs = VehicleLog::with('vehicle','vehicle.driver','vehicle.company')->where('vehicle_id',$id);

        // Logica de sorting
        $sortCol = $request->input('sort.col');
        $sortDir = $request->input('sort.dir');

        if($sortCol==='patente' && $sortDir) {
            // Left Join para traer aquellos datos
            $logs->leftJoin('vehicles', 'vehicle_logs.vehicle_id', '=', 'vehicles.track_id')
            // Seleccionamos la fila que nos interesa
            ->select('vehicle_logs.*', 'vehicles.patente as patente')
            // Order By que pone los valores NULL al final
            ->orderByRaw('ISNULL(patente), patente ' . $sortDir);
        } else if($sortCol==='connection' && $sortDir) {
            $logs->leftJoin('vehicles', 'vehicle_logs.vehicle_id', '=', 'vehicles.track_id')
            ->select('vehicle_logs.*', 'vehicles.conexion as conexion')
            ->orderByRaw('ISNULL(conexion), conexion ' . $sortDir);
        } else if($sortCol==='status' && $sortDir) {
            $logs->leftJoin('vehicles', 'vehicle_logs.vehicle_id', '=', 'vehicles.track_id')
            ->select('vehicle_logs.*', 'vehicles.estado as estado')
            ->orderByRaw('ISNULL(estado), estado ' . $sortDir);
        } else if ($sortCol === 'driver' && $sortDir) {
            $logs->leftJoin('vehicles', 'vehicle_logs.vehicle_id', '=', 'vehicles.track_id')
                ->leftJoin('drivers', 'vehicles.id', '=', 'drivers.vehicle_id')
                ->select('vehicle_logs.*', 'drivers.nombre as nombre')
                ->orderByRaw('ISNULL(nombre), nombre ' . $sortDir);
        } else if ($sortCol && $sortDir) {
            $logs->orderBy($sortCol, $sortDir);
        }

        return $request->has('page') ? $logs->paginate(12) : $logs->get();
    }

    public function getLogsLatest(Request $request){
        $latestLogs = VehicleLog::select('vehicle_id', DB::raw('MAX(updated_at) as latest_updated_at'))
                    ->groupBy('vehicle_id');

        $logs = VehicleLog::joinSub($latestLogs, 'latest_logs', function($join) {
                        $join->on('vehicle_logs.vehicle_id', '=', 'latest_logs.vehicle_id')
                             ->on('vehicle_logs.updated_at', '=', 'latest_logs.latest_updated_at');})
                             ->with('vehicle','vehicle.driver','vehicle.company');

        // Logica de sorting
        $sortCol = $request->input('sort.col');
        $sortDir = $request->input('sort.dir');

        if($sortCol==='patente' && $sortDir) {
            // Left Join para traer aquellos datos
            $logs->leftJoin('vehicles', 'vehicle_logs.vehicle_id', '=', 'vehicles.track_id')
            // Seleccionamos la fila que nos interesa
            ->select('vehicle_logs.*', 'vehicles.patente as patente')
            // Order By que pone los valores NULL al final
            ->orderByRaw('ISNULL(patente), patente ' . $sortDir);
        } else if($sortCol==='connection' && $sortDir) {
            $logs->leftJoin('vehicles', 'vehicle_logs.vehicle_id', '=', 'vehicles.track_id')
            ->select('vehicle_logs.*', 'vehicles.conexion as conexion')
            ->orderByRaw('ISNULL(conexion), conexion ' . $sortDir);
        } else if($sortCol==='status' && $sortDir) {
            $logs->leftJoin('vehicles', 'vehicle_logs.vehicle_id', '=', 'vehicles.track_id')
            ->select('vehicle_logs.*', 'vehicles.estado as estado')
            ->orderByRaw('ISNULL(estado), estado ' . $sortDir);
        } else if ($sortCol === 'driver' && $sortDir) {
            $logs->leftJoin('vehicles', 'vehicle_logs.vehicle_id', '=', 'vehicles.track_id')
                ->leftJoin('drivers', 'vehicles.id', '=', 'drivers.vehicle_id')
                ->select('vehicle_logs.*', 'drivers.nombre as nombre')
                ->orderByRaw('ISNULL(nombre), nombre ' . $sortDir);
        } else if ($sortCol && $sortDir) {
            $logs->orderBy($sortCol, $sortDir);
        }

        return $request->has('page') ? $logs->paginate(12) : $logs->get();
    }
}
