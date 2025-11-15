<?php
require '../../vendor/autoload.php';

use App\Controller\TicketController;
use App\Auth\Autenticador;

Autenticador::iniciar();
$posto = Autenticador::getPosto();

$os = $_POST['os'] ?? null;
$agendamento = $_POST['agendamento'] ?? null;

if (!$os || !$agendamento) {
    echo json_encode(['status' => 'error', 'message' => 'Parâmetros inválidos.']);
    exit;
}

$result = TicketController::exportar($os, $agendamento, $posto);

echo json_encode($result);
