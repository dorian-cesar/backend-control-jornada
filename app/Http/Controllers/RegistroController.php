<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registro;

class RegistroController extends Controller
{
    public function index()
    {
        $registros = Registro::all();
        return response()->json($registros);
    }

    public function store(Request $request)
    {
        $request->validate([
            'rut' => 'required|string|max:255',
            'tipo' => 'required|in:entrada,salida',
            'metodo' => 'required|in:manual,forzado',
            'patente' => 'required|string|max:10',
        ]);

         // Obtener el último registro del mismo RUT
         $lastRecord = Registro::where('rut', $request->rut)->orderBy('created_at', 'desc')->first();

         // Comprobar si el último registro es del mismo tipo (entrada o salida)
         if ($lastRecord && $lastRecord->tipo == $request->tipo) {
             return response()->json(['error' => 'No pueden existir dos registros consecutivos del mismo tipo para el mismo RUT.'], 422);
         }

        $registro = Registro::create([
            'rut' => $request->rut,
            'tipo' => $request->tipo,
            'timestamp' => now(),
            'metodo' => $request->metodo,
            'patente' => $request->patente,
        ]);

        return response()->json($registro, 201);
    }

    public function show($id)
    {
        $registro = Registro::findOrFail($id);
        return response()->json($registro);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'rut' => 'sometimes|required|string|max:255',
            'tipo' => 'sometimes|required|in:entrada,salida',
            'metodo' => 'sometimes|required|in:manual,forzado',
            'patente' => 'sometimes|required|string|max:10',
        ]);

        $registro = Registro::findOrFail($id);
        $registro->update($request->all());

        return response()->json($registro);
    }

    public function destroy($id)
    {
        $registro = Registro::findOrFail($id);
        $registro->delete();

        return response()->json(null, 204);
    }
}
