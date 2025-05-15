<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/admin/includes/config.php';
require __DIR__ . '/admin/includes/functions.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\Factory as LoopFactory;

class TicketWebSocket implements MessageComponentInterface {
    protected $clients;
    protected $eventRooms;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->eventRooms = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        
        $queryString = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryString, $queryParams);
        
        if (isset($queryParams['evento_id'])) {
            $evento_id = intval($queryParams['evento_id']);
            $conn->evento_id = $evento_id;
            
            if (!isset($this->eventRooms[$evento_id])) {
                $this->eventRooms[$evento_id] = new \SplObjectStorage;
            }
            $this->eventRooms[$evento_id]->attach($conn);
            
            error_log("Nueva conexión para evento {$evento_id} ({$conn->resourceId})");
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // No necesitamos procesar mensajes entrantes
    }

    public function onClose(ConnectionInterface $conn) {
        if (isset($conn->evento_id) && isset($this->eventRooms[$conn->evento_id])) {
            $this->eventRooms[$conn->evento_id]->detach($conn);
        }
        $this->clients->detach($conn);
        error_log("Conexión {$conn->resourceId} cerrada");
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        error_log("Error: {$e->getMessage()}");
        $conn->close();
    }

    public function notifyTicketUpdate($evento_id, $ticket_number, $status) {
        if (isset($this->eventRooms[$evento_id])) {
            $message = json_encode([
                'type' => 'ticket_update',
                'ticket_number' => $ticket_number,
                'status' => $status,
                'timestamp' => time()
            ]);

            foreach ($this->eventRooms[$evento_id] as $client) {
                try {
                    $client->send($message);
                } catch (\Exception $e) {
                    error_log("Error enviando mensaje: " . $e->getMessage());
                }
            }
        }
    }
}

// Configuración del servidor
$port = 8080;
$wsHandler = new TicketWebSocket();
$wsServer = new WsServer($wsHandler);

// Configurar el servidor con el event loop
$loop = LoopFactory::create();
$server = IoServer::factory(
    new HttpServer($wsServer),
    $port,
    '0.0.0.0',
    $loop
);

// Configurar keepalive manualmente
$loop->addPeriodicTimer(30, function() use ($wsHandler) {
    // Puedes implementar lógica de verificación de conexiones aquí
});

error_log("Servidor WebSocket iniciado en el puerto {$port}");
$server->run();