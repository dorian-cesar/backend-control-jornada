<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\DriverLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    /**
     * Todo, incluyendo datos de relacion smartcard
     * Si se incluye el parametro "page", se paginará el resultado
     */
    public function index(Request $request)
    {
        // Traemos todos los datos, incluyendo las tablas relacionadas
        $drivers = Driver::with('smartcard',
            'company',
            'vehicle',
            'vehicle.latest_log',
            'vehicle.device',
            'vehicle.vehicle_type',
            'latestlog',);

        // Si tenemos una busqueda
        if($request->has('searchqry')){
            $query = $request->searchqry;
            // WHERE anidados para buscar en las tablas relacionadas
            $drivers = $drivers->where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('drivers.nombre', 'LIKE', '%'.$query.'%')
                    ->orWhere('drivers.rut', 'LIKE', '%'.$query.'%')
                    // Si tiene Tarjeta, buscar en el numero
                    ->orWhereHas('smartcard', function ($queryBuilder) use ($query) {
                        $queryBuilder->where('number', 'LIKE', '%'.$query.'%');
                    })
                    // Si tiene Empresa, buscar en el nombre
                    ->orWhereHas('company', function ($queryBuilder) use ($query) {
                        $queryBuilder->where('nombre', 'LIKE', '%'.$query.'%');
                    })
                    // Si tiene vehiculo, buscar en sub tablas
                    ->orWhereHas('vehicle', function ($queryBuilder) use ($query) {
                        $queryBuilder->where('patente', 'LIKE', '%'.$query.'%')
                            ->orWhereHas('vehicle_type', function ($queryBuilder) use ($query) {
                                $queryBuilder->where('tipo', 'LIKE', '%'.$query.'%');
                            })
                            ->orWhereHas('device', function ($queryBuilder) use ($query) {
                                $queryBuilder->where('sim', 'LIKE', '%'.$query.'%');
                            });
                    });
            });
        }

        // Logica de sorting
        $sortCol = $request->input('sort.col');
        $sortDir = $request->input('sort.dir');
        
        // Patrones espeficicos de sorting
        if($sortCol==='vehicle' && $sortDir) {
            // Left Join para traer aquellos datos
            $drivers->leftJoin('vehicles', 'vehicle_id', '=', 'vehicles.id')
            // Seleccionamos la fila que nos interesa
            ->select('drivers.*', 'vehicles.patente as patente')
            // Order By que pone los valores NULL al final
            ->orderByRaw('ISNULL(patente), patente ' . $sortDir);

            // Smartcards
        } else if($sortCol==='smartcard' && $sortDir) {
            $drivers->leftJoin('smartcards', 'smartcard_id', '=', 'smartcards.id')
            ->select('drivers.*', 'smartcards.number as number')
            ->orderByRaw('ISNULL(number), number ' . $sortDir);

            // Empresas
        } else if($sortCol==='company' && $sortDir) {
            $drivers->leftJoin('companies', 'company_id', '=', 'companies.id')
            ->select('drivers.*', 'companies.nombre as company_nombre')
            ->orderByRaw('ISNULL(company_nombre), company_nombre ' . $sortDir);

            // Logs
        } else if($sortCol==='date' && $sortDir) {
            $drivers->leftJoin('driver_logs', function($join) {
                $join->on('drivers.id', '=', 'driver_logs.driver_id')
                     ->on(DB::raw('(select MAX(id) from driver_logs where driver_logs.driver_id = drivers.id)'), '=', 'driver_logs.id');
            })
            ->select('drivers.*', 'driver_logs.created_at as created_date')
            ->orderByRaw('ISNULL(created_date), created_date ' . $sortDir);
        } else if ($sortCol && $sortDir) {
            $drivers->orderBy($sortCol, $sortDir);
        }

        return $request->has('page') ? $drivers->paginate(12) : $drivers->get();
    }


    /**
     * ID especifico
     * api/conductores/[id]
     */
    public function show($id)
    {
        return Driver::with('smartcard','vehicle')->where('id',$id)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        request()->validate(Driver::$rules);

        $dataIn = $request->all();

        if(isset($dataIn['rut'])){
            $dataIn['rut'] = str_replace('.','',strtoupper($dataIn['rut']));    
        }

        $driver = Driver::create($dataIn);

     
        return $driver;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $driver = Driver::with('latestlog')->where('id',$id)->first();

        $rules = [
            'rut' => [
                'sometimes',
                'required',
                'regex:/\d{1,2}\.?\d{3}\.?\d{3}[-][0-9Kk]/',
                Rule::unique('drivers')->ignore($driver->id)->where(function ($query) use ($request, $driver) {
                    return $query->where('rut', $request->rut)
                                 ->where('id', '!=', $driver->id);
                }),
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $dataIn = $request->all();

        if(isset($dataIn['rut'])){
            $dataIn['rut'] = str_replace('.','',strtoupper($dataIn['rut']));    
        }

        $driver->update($dataIn);

        return $driver;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Driver::where('id',$id)->first()->delete();
        return response()->json(null, 204);
    }
}
