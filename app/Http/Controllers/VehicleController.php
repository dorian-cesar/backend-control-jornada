<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Notification;
use App\Models\Vehicle;
use App\Models\VehicleLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vehicles = Vehicle::with('vehicle_type',
        'device',
        'vehicle_logs',
        'company',
        'latest_log',
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
                ->orWhereHas('company', function ($queryBuilder) use ($query) {
                    $queryBuilder->where('nombre', 'LIKE', "%{$query}%");
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

        } else if($sortCol==='company' && $sortDir) {
            $vehicles->leftJoin('companies', 'company_id', '=', 'companies.id')
            ->select('vehicles.*', 'companies.nombre as nombre')
            ->orderByRaw('ISNULL(nombre), nombre ' . $sortDir);

        } else if($sortCol==='device' && $sortDir) {
            $vehicles->leftJoin('devices', 'device_id', '=', 'devices.id')
            ->select('vehicles.*', 'devices.sim as sim')
            ->orderByRaw('ISNULL(sim), sim ' . $sortDir);

        } else if ($sortCol && $sortDir) {
            $vehicles->orderBy($sortCol, $sortDir);
        }

        return $request->has('page') ? $vehicles->paginate(12) : $vehicles->get();
    }

    public function getMiBus(){
        $user = 'lasCondes';
        $password = '123';

        // Retrieve user's hash from the external MySQL database
        $userRecord = DB::connection('tracker')->table('hash')
                        ->where('user', $user)
                        ->where('pasw', $password)
                        ->first();
        
        $userHash = $userRecord->hash;

        if(!$userHash){
            return json_encode('Error al obtener userhash');
        }

        $response = Http::post('http://www.trackermasgps.com/api-v2/tracker/list', [
            'hash' => $userHash
        ])->throw();
        
        $data = [];
        foreach ($response->json('list') as $item) {
            $id = $item['id'];
            $imei = $item['source']['device_id'];
        
            $response2 = Http::post('http://www.trackermasgps.com/api-v2/tracker/get_state', [
                'hash' => $userHash,
                'tracker_id' => $id
            ])->throw();
        
            $json2 = $response2->json();
            $lat = $json2['state']['gps']['location']['lat'];
            $lng = $json2['state']['gps']['location']['lng'];
            $last_u = $json2['state']['last_update'];
            $plate = $item['label'];
            $speed = $json2['state']['gps']['speed'];
            $direccion = $json2['state']['gps']['heading'];
            $connection_status = $json2['state']['connection_status'];
            $movement_status = $json2['state']['movement_status'];
            $signal_level = $json2['state']['gps']['signal_level'];
            $ignicion = $json2['state']['inputs'][0];
        
            $data[] = [
                'id' => $id,
                'imei' => $imei,
                'patente' => $plate,
                'lat' => $lat,
                'lng' => $lng,
                'speed' => $speed,
                'direccion' => $direccion,
                'connection_status' => $connection_status,
                'signal_level' => $signal_level,
                'movement_status' => $movement_status,
                'ignicion' => $ignicion,
                'ultima-conexion' => $last_u
            ];
        }
        
        return $data;
    }
        
    public function sync() {
        $vehicles = $this->getMiBus();
    
        if (!$vehicles) {
            return response()->json(['message' => 'Sin datos'], 204);
        }
    
        foreach ($vehicles as $vehicle) {
            $check = Vehicle::where('patente',$vehicle['patente'])->first();
            $patente = substr($vehicle['patente'], 0, 8);
            if($check){
                $check->update(
                    [
                        'patente' => $patente,
                        'vehicle_type_id' => 1,
                        'device_id' => null,
                        'track_id' => $vehicle['id'],
                        'imei' => $vehicle['imei'],
                    ]
                );
            } else {
                $check = Vehicle::create(
                    [
                        'patente' => $patente,
                        'vehicle_type_id' => 1,
                        'device_id' => null,
                        'track_id' => $vehicle['id'],
                        'imei' => $vehicle['imei'],
                    ]
                );
            }
    
            $log = VehicleLog::create([
                'vehicle_id' => $vehicle['id'],
                'lat' => $vehicle['lat'],
                'lng' => $vehicle['lng'],
                'ignicion' => $vehicle['ignicion'],
                'velocidad' => $vehicle['speed'],
                'direccion' => $vehicle['direccion'],
                'estado' => $vehicle['movement_status'],
                'conexion' => $vehicle['connection_status'],
            ]);
        }
    
        return $vehicles;
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
        return Vehicle::with('vehicle_type',
        'driver',
        'device',
        'vehicle_logs',
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
                'regex:/^[a-zA-Z]{2}[a-zA-Z0-9]{2}-?[0-9]{2}$/',
                Rule::unique('vehicles')->ignore($vehicle->id)->where(function ($query) use ($request, $vehicle) {
                    return $query->where('patente', $request->patente)
                                 ->where('id', '!=', $vehicle->id);
                }),
            ],
            'track_id' => [
                'sometimes',
                'required',
                Rule::unique('vehicles')->ignore($vehicle->id)->where(function ($query) use ($request, $vehicle) {
                    return $query->where('track_id', $request->track_id)
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
