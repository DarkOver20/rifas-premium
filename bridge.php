<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/admin/includes/config.php';

$context = new ZMQContext();
$receiver = $context->getSocket(ZMQ::SOCKET_PULL);
$receiver->bind("tcp://*:5555");

$sender = $context->getSocket(ZMQ::SOCKET_PUSH);
$sender->connect("tcp://localhost:5556"); // Para comunicación interna

error_log("Puente WebSocket iniciado");

while (true) {
    try {
        $message = $receiver->recv();
        $data = json_decode($message, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error decodificando mensaje JSON: " . json_last_error_msg());
            continue;
        }

        // Validar datos mínimos
        if (!isset($data['evento_id']) || !isset($data['ticket_number']) || !isset($data['status'])) {
            error_log("Mensaje incompleto recibido: " . print_r($data, true));
            continue;
        }

        // Reenviar al servidor WebSocket principal
        $webSocket = new ZMQSocket($context, ZMQ::SOCKET_PUSH);
        $webSocket->connect("tcp://localhost:5556");
        $webSocket->send($message);
        
        error_log("Mensaje procesado: " . $message);
    } catch (Exception $e) {
        error_log("Error en el puente: " . $e->getMessage());
        sleep(1); // Esperar antes de reintentar
    }
}