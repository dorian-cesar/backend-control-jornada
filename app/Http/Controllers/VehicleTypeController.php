<?php

namespace App\Http\Controllers;

use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VehicleTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $types = VehicleType::all();

        if($request->has('searchqry')){
            $query = $request->searchqry;

            $types = VehicleType::where('tipo', 'LIKE', "%{$query}%");
        }

        $sortCol = $request->input('sort.col');
        $sortDir = $request->input('sort.dir');

        if ($sortCol && $sortDir) {
            $types->orderBy($sortCol, $sortDir);
        }

        return $request->has('page') ? $types->paginate(12) : $types;
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return VehicleType::where('id',$id)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        request()->validate(VehicleType::$rules);

        return VehicleType::create($request->all());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $type = VehicleType::where('id',$id)->first();

        $rules = [
            'tipo' => [
                'required',
                Rule::unique('vehicle_types')->ignore($type->id)->where(function ($query) use ($request, $type) {
                    return $query->where('tipo', $request->tipo)
                                 ->where('id', '!=', $type->id);
                }),
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $type->update($request->all());

        return $type;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        VehicleType::where('id',$id)->first()->delete();
        return response()->json(null, 204);
    }
}
