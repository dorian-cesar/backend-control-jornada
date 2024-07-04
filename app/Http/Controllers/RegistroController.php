<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registro;
use Illuminate\Support\Facades\DB;

class RegistroController extends Controller
{
    public function index()
    {
        // Obtener todos los registros con la información del conductor relacionada
        $registros = Registro::with('driver')->get();

        // Formatear la respuesta para incluir solo los campos necesarios
        $registros = $registros->map(function ($registro) {
            $fechaString = str_replace('/', '-', $registro->created_at);
            return [
                'id' => $registro->id,
                'rut' => $registro->rut,
                'tipo' => $registro->tipo,
                'created_at' => $fechaString,
                'metodo' => $registro->metodo,
                'patente' => $registro->patente,
                'conductor' => $registro->driver ? $registro->driver->nombre : null,
            ];
        });

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

    public function getEntradaSalidas()
    {
        $sql = "
            WITH ordered_records AS (
                SELECT
                    rut,
                    created_at,
                    DATE(created_at) AS work_date,
                    patente,
                    tipo,
                    metodo,
                    LAG(created_at) OVER (PARTITION BY rut, patente ORDER BY created_at) AS previous_created_at,
                    LAG(tipo) OVER (PARTITION BY rut, patente ORDER BY created_at) AS previous_tipo
                FROM
                    registros
            ),
            time_differences AS (
                SELECT
                    rut,
                    work_date,
                    patente,
                    previous_created_at AS entrada,
                    created_at AS salida,
                    CASE 
                        WHEN tipo = 'salida' AND previous_tipo = 'entrada' THEN
                            TIMESTAMPDIFF(MINUTE, previous_created_at, created_at)
                        ELSE 0
                    END AS diferencia_minutos
                FROM
                    ordered_records
            )
            SELECT
                td.rut,
                td.work_date,
                td.patente,
                MIN(td.entrada) AS primera_entrada,
                MAX(td.salida) AS ultima_salida,
                SUM(td.diferencia_minutos) AS total_minutos,
                d.nombre AS nombre_conductor
            FROM
                time_differences td
            LEFT JOIN
                drivers d ON td.rut = d.rut
            GROUP BY
                td.rut,
                td.work_date,
                td.patente,
                d.nombre
            ORDER BY
                td.rut,
                td.work_date,
                td.patente;
        ";

        $results = DB::select($sql);

        return response()->json($results);
    }

    public function getEventosEntreFechas(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

      
        $start_date = $request->start_date . ' 00:00:00';
        $end_date = $request->end_date . ' 23:59:59';

        $sql = "
            SELECT 
            r.*, 
            d.nombre AS nombre_conductor
        FROM 
            registros r
        LEFT JOIN 
            drivers d ON r.rut = d.rut
        WHERE 
            r.created_at BETWEEN ? AND ?
        ORDER BY 
            r.created_at ASC
        ";

        $events = DB::select($sql, [$start_date, $end_date]);

        return response()->json($events);
    }
}
