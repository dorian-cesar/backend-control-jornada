<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EventoController extends Controller
{
    // URLs de las APIs
    private $apiUrlEventos = 'https://control-jornada.wit.la/backend-control-jornada/public/api/gpsalert';
    private $apiUrlConductores = 'https://control-jornada.wit.la/backend-control-jornada/public/api/entradassalidas';

    public function obtenerEventos()
    {
        // Obtener los datos de las APIs
        $eventos = $this->obtenerDatosApi($this->apiUrlEventos);
        $conductores = $this->obtenerDatosApi($this->apiUrlConductores);

        // Cruce de información
        $resultado = [];

        $hoy = date("Y-m-d");

        foreach ($eventos as $evento) {
            $coincidenciaEncontrada = false;
            foreach ($conductores as $conductor) {
                if ($evento['patente'] == $conductor['patente']) {
                    if ($hoy == $conductor['work_date']) {
                        list($date1, $time1) = explode(' ', $conductor['primera_entrada']);
                        list($date2, $time2) = explode(' ', $conductor['ultima_salida']);

                        if ($evento['start_time'] >= $time1 && $evento['start_time'] <= $time2) {
                            $resultado[] = [
                                'id_tracker'=>$evento['id_tracker'],
                                'patente' => $evento['patente'],
                                'velocidad' => $evento['speed'],
                                'conductor' => $conductor['nombre_conductor'],
                                'fecha' => $evento['fecha'],
                                'time_evento' => $evento['start_time'],
                                'duration' => $evento['duration'],
                                'hora_entrada_conductor' => $time1,
                                'hora_salida_conductor' => $time2,
                                'location'=>$evento['location']
                            ];
                            $coincidenciaEncontrada = true;
                            break;
                        }
                    }
                }
            }
            if (!$coincidenciaEncontrada) {
                $resultado[] = [
                    'id_tracker'=>$evento['id_tracker'],
                    'patente' => $evento['patente'],
                    'velocidad' => $evento['speed'],
                    'conductor' => 'No identificado',
                    'fecha' => $evento['fecha'],
                    'time_evento' => $evento['start_time'],
                    'duration' => $evento['duration'],
                    'hora_entrada_conductor' => null,
                    'hora_salida_conductor' => null,
                    'location'=>$evento['location']
                ];
            }
        }

        // Devolver el resultado como JSON
        return response()->json($resultado);
    }

    private function obtenerDatosApi($url)
    {
        $response = Http::get($url);
        return $response->json();
    }
}
