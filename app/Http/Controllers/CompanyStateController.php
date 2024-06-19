<?php

namespace App\Http\Controllers;

use App\Models\CompanyState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CompanyStateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CompanyState::with('companies')->get();
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return CompanyState::with('companies')->where('id',$id)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        request()->validate(CompanyState::$rules);

        return CompanyState::create($request->all());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $state = CompanyState::where('id', $id)->first();

        $rules = [
            'estado' => [
                'required',
                Rule::unique('company_states')->ignore($state->id)->where(function ($query) use ($request, $state) {
                    return $query->where('estado', $request->estado)
                                 ->where('id', '!=', $state->id);
                }),
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $state->update($request->all());

        return $state;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        CompanyState::where('id', $id)->first()->delete();
        return response()->json(null, 204);
    }
}
