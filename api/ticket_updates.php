<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../admin/includes/config.php';
require_once __DIR__ . '/../admin/includes/functions.php';

class TicketUpdateHandler implements \Ratchet\MessageComponentInterface {
    protected $clients;
    protected $evento_id;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(\Ratchet\ConnectionInterface $conn) {
        $this->clients->attach($conn);
        
        // Obtener evento_id de la query string
        $queryString = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryString, $queryParams);
        $this->evento_id = isset($queryParams['evento_id']) ? intval($queryParams['evento_id']) : 0;
        
        echo "Nueva conexión: {$conn->resourceId}\n";
    }

    public function onMessage(\Ratchet\ConnectionInterface $from, $msg) {
        // No necesitamos recibir mensajes de los clientes en este caso
    }

    public function onClose(\Ratchet\ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Conexión cerrada: {$conn->resourceId}\n";
    }

    public function onError(\Ratchet\ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }

    // Método para notificar a los clientes sobre cambios en los boletos
    public function notifyTicketUpdate($ticket_number, $status) {
        $message = json_encode([
            'type' => 'ticket_update',
            'ticket_number' => $ticket_number,
            'status' => $status
        ]);

        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }
}

// Configuración del servidor WebSocket
$server = new \Ratchet\App('localhost', 8080);
$server->route('/ticket_updates', new TicketUpdateHandler(), ['*']);
$server->run();