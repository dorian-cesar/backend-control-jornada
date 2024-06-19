<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $devices = Device::with('vehicle');

        if($request->has('searchqry')){
            $query = $request->searchqry;

            $devices = $devices->where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('devices.sim', 'LIKE', "%{$query}%")
                ->orWhereHas('vehicle', function ($queryBuilder) use ($query) {
                    $queryBuilder->where('patente', 'LIKE', "%{$query}%");
                });
            });
        }

        $sortCol = $request->input('sort.col');
        $sortDir = $request->input('sort.dir');

        if($sortCol==='vehicle' && $sortDir) {
            $devices->leftJoin('vehicles', 'devices.id', '=', 'vehicles.device_id')
            ->select('devices.*', 'vehicles.patente as patente')
            ->orderByRaw('ISNULL(patente), patente ' . $sortDir);

        } else if ($sortCol && $sortDir) {
            $devices->orderBy($sortCol, $sortDir);
        }

        return $request->has('page') ? $devices->paginate(12) : $devices->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        request()->validate(Device::$rules);

        return Device::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Device::with('vehicle')->where('id',$id)->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $device = Device::findOrFail($id);

        $rules = [
            'sim' => [
                'sometimes',
                'required',
                Rule::unique('devices')->ignore($device->id)->where(function ($query) use ($request, $device) {
                    return $query->where('sim', $request->sim)
                                 ->where('id', '!=', $device->id);
                }),
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $device->update($request->all());

        return $device;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Device::where('id', $id)->first()->delete();
        return response()->json(null, 204);
    }
}
