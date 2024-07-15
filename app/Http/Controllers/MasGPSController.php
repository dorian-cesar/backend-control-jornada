<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use DateInterval;
use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MasGPSController extends Controller
{
    public function getHash(){
        $user = 'lasCondes';
        $password = '123';

        // Retrieve user's hash from the external MySQL database
        $userRecord = DB::connection('tracker')->table('hash')
                        ->where('user', $user)
                        ->where('pasw', $password)
                        ->first();
        
        $userHash = $userRecord->hash;

        if(!$userHash){
            return [ 'error' => 'No se pudo obtener el hash.', 'success' => false ];
        }
        return [ 'hash' => $userHash, 'success' => true ];
    }

    public function getLogs(){
        $hash = $this->getHash();

        if(isset($hash['success'])){
            $trackers = Http::post('http://www.trackermasgps.com/api-v2/tracker/list',[
                'hash' => $hash['hash']
            ])->json();

            // Obtener fecha actual y restarle 24 horas
            $currentDate = new DateTime();

            // Fecha y hora
            $from = $currentDate->format('Y-m-d');

            $arreglo = Http::get('http://www.trackermasgps.com/api-v2/history/unread/list',[
                'hash' => $hash['hash'],
                'from' => $from ." 00:00:00",
                'limit' => 10,
            ])->json();

            return $arreglo;
        }
    }

    public function getEmployees(){
        $hash = $this->getHash();

        if(isset($hash['success'])){
            $trackers = Http::post('http://www.trackermasgps.com/api-v2/tracker/list',[
                'hash' => $hash['hash']
            ])->json('list');

            $employees = null;

            foreach ($trackers as $tracker) {
                if (isset($tracker['id'])) {
                    $idforlabel[] = $tracker['id'];
                }

                $driverdata = Http::get('http://www.trackermasgps.com/api-v2/tracker/employee/read',[
                    'hash' => $hash['hash'],
                    'tracker_id' => $tracker['id']
                ])->json('current');
                $employees[] = $driverdata;
            }

            return $employees;
        }

    }

    public function getList(){
        $hash = $this->getHash();
        if(isset($hash['success'])){
            $trackers = Http::post('http://www.trackermasgps.com/api-v2/tracker/list',[
                'hash' => $hash['hash']
            ])->json('list');

            return $trackers;

        }


    }

    public function getSpeedAlert(Request $request){
        $hash = $this->getHash();

        if(isset($hash['success'])){
            $trackers = Http::post('http://www.trackermasgps.com/api-v2/tracker/list',[
                'hash' => $hash['hash']
            ])->json('list');

            $idforlabel = null;

            if($request->has('patente')){
    
                foreach($trackers as $item){
                    if($item['label'] === $request->patente){
                        $idforlabel = [$item['id']];
                        break;
                    }
                }
            } else {
                foreach ($trackers as $tracker) {
                    if (isset($tracker['id'])) {
                        $idforlabel[] = $tracker['id'];
                    }

                }
            }

            $speed_limit = 44;

            if($request->has('speed')) {
                $speed_limit = $request->speed;
            }

            $plugin = [
                "hide_empty_tabs" => true,
                "plugin_id" => 27,
                "show_seconds" => true,
                "min_duration_minutes" => 1,
                "max_speed" => $speed_limit,
                "group_by_driver" => false,
                "filter" => true
            ];

            $currentDate = new DateTime();

            $fromDate = clone $currentDate;
            $fromDate->sub(new DateInterval('PT24H')); //Sustraer 24 horas

            // Solo tiempo
            $hourTo = $currentDate->format('H:i:s');
            $hourFrom = "00:00:00";

            // Fecha y hora
            $dateTo = $currentDate->format('Y-m-d H:i:s');
            $dateFrom = $fromDate->format('Y-m-d H:i:s');

            $time_filter = [
                "from" => $hourFrom,
                "to" => $hourTo,
                "weekdays" => [1, 2, 3, 4, 5, 6, 7]
            ];

            $arreglo = Http::post('http://www.trackermasgps.com/api-v2/report/tracker/generate',[
                'hash' => $hash['hash'],
                'title' => 'Informe de violación de velocidad',
                'trackers' => $idforlabel,
                'from' => $dateFrom,
                'to' => $dateTo,
                'time_filter' => $time_filter,
                'plugin' => $plugin
            ])->json();

            sleep(10);

            $report_id = $arreglo['id'];

            $retrieve = Http::get('http://www.trackermasgps.com/api-v2/report/tracker/retrieve',[
                'hash' => $hash['hash'],
                'report_id' => $report_id
            ])->throw();

            $data = $retrieve->json();

            if(isset($data['report']['sheets'])){
                $vehiculos = $data['report']['sheets'];
                $dataOut = [];
                foreach($vehiculos as $tracker){
                    if($tracker['entity_ids']==null){
                        $dataOut[] = [
                            'header' => 'No se encontraron datos',
                        ];
                    } else {
                        $id_tracker = $tracker['entity_ids'][0];
                        $fecha = $tracker['sections'][1]['data'][0]['header'];
                        $eventos = $tracker['sections'][1]['data'][0]['rows'];

                        foreach($eventos as $evento){
                            $start_time = $evento['start_time']['v'];
                            $duration = $evento['duration']['v'];
                            $max_speed = $evento['max_speed']['raw'];
                            $address = $evento['max_speed_address']['v'];
                            $lat = $evento['max_speed_address']['location']['lat'];
                            $lng = $evento['max_speed_address']['location']['lng'];

                            $vehicle = Vehicle::where('track_id', $id_tracker)->first();

                            $plate = "Sin Patente";
                            $driver = "Sin Conductor";

                            $driverdata = Http::get('http://www.trackermasgps.com/api-v2/tracker/employee/read',[
                                'hash' => $hash['hash'],
                                'tracker_id' => $id_tracker
                            ])->json('current');

                            if($vehicle){
                                $plate = $vehicle->patente;
                            }

                            if($driverdata){
                                $driver = $driverdata['first_name'] . " " . $driverdata['last_name'];
                            }
    
                            $dataOut[] = [
                                'id_tracker' => $id_tracker,
                                'patente' => $plate,
                                'conductor' => $driver,
                                'fecha' => explode(" ",$fecha)[0],
                                'start_time' => $start_time,
                                'duration' => $duration,
                                'speed' => $max_speed,
                                'address' => $address,
                                'location' => ['lat'=>$lat,'lng'=>$lng]
                            ];
                        }
                    }
                }

                // Ordenar por fecha
                usort($dataOut, function($a,$b){
                    $realDateA = substr($a['fecha'],-4)."-".substr($a['fecha'],3,2)."-".substr($a['fecha'],0,2);
                    $realDateB = substr($b['fecha'],-4)."-".substr($b['fecha'],3,2)."-".substr($b['fecha'],0,2);
                    $timeA = strtotime($realDateA." ".$a['start_time']);
                    $timeB = strtotime($realDateB." ".$b['start_time']);
                    
                    if ($timeA == $timeB) {
                        return 0;
                    }
                    return ($timeB < $timeA) ? -1 : 1;
                });

                return $dataOut;
            }
            
            return [ 'error' => 'Error al obtener las alertas.', 'success' => false ];
        }
    }

    public function getHistory($patente){
        $hash = $this->getHash();

        if(isset($hash['success'])){
            $trackers = Http::post('http://www.trackermasgps.com/api-v2/tracker/list',[
                'hash' => $hash['hash']
            ])->json('list');

            $idforlabel = 0;

            foreach($trackers as $item){
                if($item['label'] === $patente){
                    $idforlabel = $item['id'];
                    break;
                }
            }

            $plugin = [
                "show_seconds" => true,
                "plugin_id" => 91
            ];

            $currentDate = new DateTime();

            $fromDate = clone $currentDate;
            $fromDate->sub(new DateInterval('PT10M')); //Sustraer 15 minutos

            // Solo tiempo
            $hourTo = $currentDate->format('H:i:s');
            $hourFrom = $fromDate->format('H:i:s');

            // Fecha y hora
            $dateTo = $currentDate->format('Y-m-d H:i:s');
            $dateFrom = $fromDate->format('Y-m-d H:i:s');

            $time_filter = [
                "from" => $hourFrom,
                "to" => $hourTo,
                "weekdays" => [1, 2, 3, 4, 5, 6, 7]
            ];

            if($idforlabel!=0){
                $arreglo = Http::post('http://www.trackermasgps.com/api-v2/report/tracker/generate',[
                    'hash' => $hash['hash'],
                    'title' => 'Reporte de posiciones',
                    'trackers' => [$idforlabel],
                    'from' => $dateFrom,
                    'to' => $dateTo,
                    'time_filter' => $time_filter,
                    'plugin' => $plugin
                ])->throw();

                sleep(5);

                $report_id = $arreglo['id'];

                $retrieve = Http::post('http://www.trackermasgps.com/api-v2/report/tracker/retrieve',[
                    'hash' => $hash['hash'],
                    'report_id' => $report_id
                ])->throw();

                $data = $retrieve->json();
/*
                $driverdata = Http::get('http://www.trackermasgps.com/api-v2/tracker/employee/read',[
                    'hash' => $hash['hash'],
                    'tracker_id' => $idforlabel
                ])->throw();

                $driver = $driverdata->json();

                if ($driver) {
                    // Merge the driver data into $data
                    $data['driver'] = $driver;
                }
                        */
                return $data;
            }

            return [ 'error' => 'no se pudo obtener el id de tracker.', 'success' => false ];
        }
    }

    public function getWorkHours(Request $request){
        $hash = $this->getHash();

        if(isset($hash['success'])){
            $trackers = Http::post('http://www.trackermasgps.com/api-v2/tracker/list',[
                'hash' => $hash['hash']
            ])->json('list');

            $plugin = [
                "hide_empty_tabs" => true,
                "show_seconds" => false,
                "plugin_id" => 66
            ];

            $trackerlist = [];

            if($request->has('tracker')) {
                if(!is_numeric($request->tracker)){
                    foreach($trackers as $item){
                        if(str_contains($item['label'], $request->tracker)) {
                            $trackerlist[] = $item['id'];
                        }
                    }
                } else {
                    foreach($trackers as $item){
                        if($item['id'] == $request->tracker) {
                            $trackerlist[] = $item['id'];
                        }
                    }
                }
            } else {
                foreach($trackers as $item){
                    $trackerlist[] = $item['id'];
                }
            }

            $currentDate = new DateTime();

            $fromDate = clone $currentDate;
            $fromDate->sub(new DateInterval('PT24H')); //Sustraer 15 minutos

            // Solo tiempo
            $hourTo = $currentDate->format('H:i:s');
            $hourFrom = "00:00:00";

            // Fecha y hora
            $dateTo = $currentDate->format('Y-m-d H:i:s');
            $dateFrom = $fromDate->format('Y-m-d H:i:s');

            
            if($request->has('from')&&$request->has('to')){
                $dateFrom = $request->from." 00:00:00";
                $dateTo = $request->to." 23:59:59";

                $hourFrom = "00:00:00";
                $hourTo = "23:59:59";
            }

            $time_filter = [
                "from" => $hourFrom,
                "to" => $hourTo,
                "weekdays" => [1, 2, 3, 4, 5, 6, 7]
            ];

            if($trackerlist){
                $arreglo = Http::post('http://www.trackermasgps.com/api-v2/report/tracker/generate',[
                    'hash' => $hash['hash'],
                    'title' => 'Identificación de conductores',
                    'trackers' => $trackerlist,
                    'from' => $dateFrom,
                    'to' => $dateTo,
                    'time_filter' => $time_filter,
                    'plugin' => $plugin
                ])->json();

                sleep(10);

                $report_id = $arreglo['id'];

                $retrieve = Http::post('http://www.trackermasgps.com/api-v2/report/tracker/retrieve',[
                    'hash' => $hash['hash'],
                    'report_id' => $report_id
                ])->json('report');

                for($i = 0, $size = count($retrieve['sheets']); $i < $size; ++$i) {
                    $driver = Http::get('http://www.trackermasgps.com/api-v2/tracker/employee/read',[
                        'hash' => $hash['hash'],
                        'tracker_id' => $retrieve['sheets'][$i]['entity_ids'][0]
                    ])->json('current');

                    if(isset($driver['personnel_number'])){
                        $retrieve['sheets'][$i]['rut'] = $driver['personnel_number'];
                    }

                    if(isset($driver['hardware_key'])){
                        $retrieve['sheets'][$i]['key'] = $driver['hardware_key'];
                    }
                }

                return $retrieve;
            }

            return [ 'error' => 'no se pudo obtener el id de tracker.', 'success' => false ];
        }
    }

    public function getParaderos(){
        $hash = $this->getHash();

        if(isset($hash['success'])){
            $stops = DB::connection('tracker')->table('paraderos')->select(['patente','paradero','direccion','latitud','longitud'])->get();

            if(!$stops){
                return 'No se pudo obtener los paraderos.';
            }
            return $stops;
        }

        return 'No se pudo obtener los paraderos.';
    }

    public function getBuses(){
        $hash = $this->getHash();

        if(isset($hash['success'])){
            $trackers = Http::post('http://www.trackermasgps.com/api-v2/tracker/list',[
                'hash' => $hash['hash']
            ])->throw();


            $dataOut = [];

            foreach($trackers->json('list') as $item) {
                $id = $item['id'];
                $imei = $item['source']['device_id'];
                $srcid = $item['source']['id'];
            
                $response2 = Http::post('http://www.trackermasgps.com/api-v2/tracker/get_state', [
                    'hash' => $hash['hash'],
                    'tracker_id' => $id
                ])->throw();
            
                $json2 = $response2->json();
                $lat = $json2['state']['gps']['location']['lat'];
                $lng = $json2['state']['gps']['location']['lng'];
                $last_u = $json2['state']['last_update'];
                $plate = $item['label'];
                $speed = $json2['state']['gps']['speed'];
                $direccion = $json2['state']['gps']['heading'];
                $connection_status = $json2['state']['connection_status'];
                $movement_status = $json2['state']['movement_status'];
                $signal_level = $json2['state']['gps']['signal_level'];
                $ignicion = $json2['state']['inputs'][0];
            
                $dataOut[] = [
                    'id' => $id,
                    'imei' => $imei,
                    'patente' => $plate,
                    'lat' => $lat,
                    'lng' => $lng,
                    'speed' => $speed,
                    'direccion' => $direccion,
                    'connection_status' => $connection_status,
                    'signal_level' => $signal_level,
                    'movement_status' => $movement_status,
                    'ignicion' => $ignicion,
                    'ultima-conexion' => $last_u,
                    'source_id' => $srcid
                ];
            }

            return response()->json($dataOut,200);
        }
    }

    public function getBusesInternal(){
        $hash = $this->getHash();

        if(isset($hash['success'])){
            $trackers = Http::post('http://www.trackermasgps.com/api-v2/tracker/list',[
                'hash' => $hash['hash']
            ])->throw();


            $dataOut = [];

            foreach($trackers->json('list') as $item) {
                $id = $item['id'];
                $imei = $item['source']['device_id'];
                $srcid = $item['source']['id'];
            
                $response2 = Http::post('http://www.trackermasgps.com/api-v2/tracker/get_state', [
                    'hash' => $hash['hash'],
                    'tracker_id' => $id
                ])->throw();
            
                $json2 = $response2->json();
                $lat = $json2['state']['gps']['location']['lat'];
                $lng = $json2['state']['gps']['location']['lng'];
                $last_u = $json2['state']['last_update'];
                $plate = $item['label'];
                $speed = $json2['state']['gps']['speed'];
                $direccion = $json2['state']['gps']['heading'];
                $connection_status = $json2['state']['connection_status'];
                $movement_status = $json2['state']['movement_status'];
                $signal_level = $json2['state']['gps']['signal_level'];
                $ignicion = $json2['state']['inputs'][0];
            
                $dataOut[] = [
                    'id' => $id,
                    'imei' => $imei,
                    'patente' => $plate,
                    'lat' => $lat,
                    'lng' => $lng,
                    'speed' => $speed,
                    'direccion' => $direccion,
                    'connection_status' => $connection_status,
                    'signal_level' => $signal_level,
                    'movement_status' => $movement_status,
                    'ignicion' => $ignicion,
                    'ultima-conexion' => $last_u,
                    'source_id' => $srcid
                ];
            }

            return json_encode($dataOut,200);
        }
    }
}
