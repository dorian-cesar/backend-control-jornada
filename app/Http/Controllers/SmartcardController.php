<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Smartcard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SmartcardController extends Controller
{
    /**
     * Todo, incluyendo datos de relacion driver
     */
    public function index(Request $request)
    {
        $cards = Smartcard::with('driver',
        'driver',
        'driver.vehicle',
        'driver.company');

        if($request->has('searchqry')){
            $query = $request->searchqry;

            $cards = $cards->where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('smartcards.number', 'LIKE', "%{$query}%")
                ->orWhereHas('driver', function ($queryBuilder) use ($query) {
                    $queryBuilder->where('nombre', 'LIKE', "%{$query}%")
                    ->orWhere('rut','LIKE',"%{$query}%");
                });
            });
        }

        $sortCol = $request->input('sort.col');
        $sortDir = $request->input('sort.dir');

        if($sortCol==='driver' && $sortDir) {
            $cards->leftJoin('drivers', 'smartcards.id', '=', 'drivers.smartcard_id')
            ->select('smartcards.*', 'drivers.nombre as nombre')
            ->orderByRaw('ISNULL(nombre), nombre ' . $sortDir);

        } else if ($sortCol && $sortDir) {
            $cards->orderBy($sortCol, $sortDir);
        }

        return $request->has('page') ? $cards->paginate(12) : $cards->get();
    }

    /**
     * ID especifico
     * api/smartcards/[id]
     */
    public function show($id)
    {
        return Smartcard::with('driver')->where('id',$id)->get();
    }

    /**
     * Insertar
     * number: numero de tarjeta
     */
    public function store(Request $request)
    {
        request()->validate(Smartcard::$rules);

        $smartcard = Smartcard::create($request->all());

        if($request->has('driver_id')){
            $driver = Driver::where('id',$request->driver_id)->first();

            $driver->smartcard_id=$smartcard->id;

            $smartcard->driver()->save($driver);
        }

        return $smartcard;
    }

    /**
     * Actualizar
     * api/smartcards/[id]
     */
    public function update(Request $request, $id)
    {
        if($request->driver_id){
            $driver = Driver::where('id',$request->driver_id)->first();

            if($driver){
                $driver->smartcard_id = $id;
                $driver->update();
            } else {
                return response()->json(['message' => 'Conductor no existe!'],404);
            }

            return $driver;
        } else {
            $smartcard = Smartcard::findOrFail($id);
    
            $rules = [
                'number' => [
                    'sometimes',
                    'required',
                    Rule::unique('smartcards')->ignore($smartcard->id)->where(function ($query) use ($request, $smartcard) {
                        return $query->where('number', $request->number)
                                     ->where('id', '!=', $smartcard->id);
                    }),
                ],
            ];
    
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
    
            $smartcard->update($request->all());
    
            if($request->has('driver_id')){
                $olddriver = $smartcard->driver;
                $olddriver->update(['smartcard_id' => null]);
    
                $driver = Driver::findOrFail($request->driver_id);
                $driver->smartcard_id = $smartcard->id;
    
                $driver->driver_logs()->create([
                    'event_id' => 3,
                    'coordenadas' => '-',
                    'velocidad' => 0
                ]);
                
                $driver->save();
            }
    
            return $smartcard;
        }
    }

    /**
     * Eliminar
     * api/smartcards/[id]
     */
    public function destroy(Smartcard $smartcard)
    {
        $smartcard->delete();
        return response()->json(null, 204);
    }
}
