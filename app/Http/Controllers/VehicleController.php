<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vehicles = Vehicle::with('vehicle_state',
        'vehicle_type',
        'device',
        'driver');

        if($request->has('searchqry')){
            $query = $request->searchqry;

            $vehicles = $vehicles->where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('vehicles.patente', 'LIKE', "%{$query}%")
                ->orWhereHas('driver', function ($queryBuilder) use ($query) {
                    $queryBuilder->where('nombre', 'LIKE', "%{$query}%")
                    ->orWhere('rut','LIKE',"%{$query}%");
                })
                ->orWhereHas('vehicle_type', function ($queryBuilder) use ($query) {
                    $queryBuilder->where('tipo', 'LIKE', "%{$query}%");
                })
                ->orWhereHas('vehicle_state', function ($queryBuilder) use ($query) {
                    $queryBuilder->where('estado', 'LIKE', "%{$query}%");
                })
                ->orWhereHas('device', function ($queryBuilder) use ($query) {
                    $queryBuilder->where('sim', 'LIKE', "%{$query}%");
                });
            });
        }

        $sortCol = $request->input('sort.col');
        $sortDir = $request->input('sort.dir');

        if($sortCol==='driver' && $sortDir) {
            $vehicles->leftJoin('drivers', 'vehicles.id', '=', 'drivers.vehicle_id')
            ->select('vehicles.*', 'drivers.nombre as nombre')
            ->orderByRaw('ISNULL(nombre), nombre ' . $sortDir);

        } else if($sortCol==='type' && $sortDir) {
            $vehicles->leftJoin('vehicle_types', 'vehicle_type_id', '=', 'vehicle_types.id')
            ->select('vehicles.*', 'vehicle_types.tipo as tipo')
            ->orderByRaw('ISNULL(tipo), tipo ' . $sortDir);

        } else if($sortCol==='state' && $sortDir) {
            $vehicles->leftJoin('vehicle_states', 'vehicle_state_id', '=', 'vehicle_states.id')
            ->select('vehicles.*', 'vehicle_states.estado as estado')
            ->orderByRaw('ISNULL(estado), estado ' . $sortDir);

        } else if($sortCol==='device' && $sortDir) {
            $vehicles->leftJoin('devices', 'device_id', '=', 'devices.id')
            ->select('vehicles.*', 'devices.sim as sim')
            ->orderByRaw('ISNULL(sim), sim ' . $sortDir);

        } else if ($sortCol && $sortDir) {
            $vehicles->orderBy($sortCol, $sortDir);
        }

        return $request->has('page') ? $vehicles->paginate(12) : $vehicles->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        request()->validate(Vehicle::$rules);

        return Vehicle::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Vehicle::with('vehicle_state',
        'vehicle_type',
        'driver',
        'device',
        'company')
        ->where('id',$id)
        ->first();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $rules = [
            'patente' => [
                'sometimes',
                'required',
                'regex:/^[a-zA-Z]{2}[a-zA-Z0-9]{2}[0-9]{2}$/',
                Rule::unique('vehicles')->ignore($vehicle->id)->where(function ($query) use ($request, $vehicle) {
                    return $query->where('patente', $request->patente)
                                 ->where('id', '!=', $vehicle->id);
                }),
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vehicle->update($request->all());

        if ($request->has('driver_id')) {
            $olddriver = $vehicle->driver;
            $olddriver->update(['vehicle_id' => null]);

            $driver = Driver::findOrFail($request->driver_id);
            $driver->vehicle_id = $vehicle->id;

            $driver->driver_logs()->create([
                'event_id' => 2,
                'coordenadas' => '-',
                'velocidad' => 0
            ]);

            $driver->save();
        }

        return $vehicle;
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Vehicle::where('id', $id)->first()->delete();
        return response()->json(null, 204);
    }
}
