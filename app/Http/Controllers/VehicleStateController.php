<?php

namespace App\Http\Controllers;

use App\Models\VehicleState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VehicleStateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $states = VehicleState::all();

        if($request->has('searchqry')){
            $query = $request->searchqry;

            $states = VehicleState::where('estado', 'LIKE', "%{$query}%");
        } 

        $sortCol = $request->input('sort.col');
        $sortDir = $request->input('sort.dir');

        if ($sortCol && $sortDir) {
            $states->orderBy($sortCol, $sortDir);
        }

        return $request->has('page') ? $states->paginate(12) : $states;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        request()->validate(VehicleState::$rules);

        return VehicleState::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return VehicleState::where('id',$id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $state = VehicleState::where('id',$id)->first();

        $state->update($request->all());

        return $state;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        VehicleState::where('id',$id)->first()->delete();
        return response()->json(null, 204);
    }
}
