<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companies = Company::with('company_state',
        'drivers');

        if($request->has('searchqry')){
            $query = $request->searchqry;

            $companies = $companies->where('rut', 'LIKE', "%{$query}%")
                ->orWhere('nombre', 'LIKE', "%{$query}%");
        }

        $sortCol = $request->input('sort.col');
        $sortDir = $request->input('sort.dir');

        if ($sortCol && $sortDir) {
            $companies->orderBy($sortCol, $sortDir);
        }

        return $request->has('page') ? $companies->paginate(12) : $companies->get();
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Company::with('company_state')->where('id',$id)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        request()->validate(Company::$rules);

        $dataIn = $request->all();

        if(isset($dataIn['rut'])){
            $dataIn['rut'] = str_replace('.','',strtoupper($dataIn['rut']));    
        }

        return Company::create($dataIn);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $company = Company::where('id',$id)->first();

        $rules = [
            'rut' => [
                'regex:/\d{1,2}\.?\d{3}\.?\d{3}[-][0-9Kk]/',
                Rule::unique('companies')->ignore($company->id)->where(function ($query) use ($request, $company) {
                    return $query->where('rut', $request->rut)
                                 ->where('id', '!=', $company->id);
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

        $company->update($dataIn);

        return $company;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Company::where('id',$id)->first()->delete();
        return response()->json(null, 204);
    }
}
