<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ratchet\Client\WebSocket;
use Ratchet\Client\Connector;
use App\Http\Controllers\MasGPSController;

class WebSocketController extends Controller
{
    protected $GPSController;

    public function __construct(MasGPSController $GPSController)
    {
        $this->GPSController = $GPSController;
    }

    public function listenToWebSocket()
    {
        $buses = $this->GPSController->getBusesInternal();

        $busdata = json_decode($buses,true);

        $trackers = array_map(function($object) {
            return $object['id'];
        }, $busdata);
        
        $hash = $this->GPSController->getHash()['hash'];

        $openingMessage = json_encode([
            'action' => 'subscribe',
            'hash' => $hash,
            'events' => ['state'],
            'trackers' => $trackers]);

            $connector = new Connector('ws://your_websocket_server_url');

            $connector->connect()->then(function (WebSocket $conn) {
                $conn->on('message', function ($msg) use ($conn) {
                    echo "Received: {$msg}\n";
                });
    
                // Send a message to the server
                $conn->send('Hello WebSocket Server!');
            }, function (\Exception $e) {
                echo "Could not connect: {$e->getMessage()}\n";
            });
    }
}
