<?php
require '../../vendor/autoload.php';

use App\Controller\TicketController;
use App\Auth\Autenticador;

Autenticador::iniciar();
$posto = Autenticador::getPosto();

$agendamento = $_POST['agendamento'] ?? null;

if (!$agendamento) {
    echo json_encode(['status' => 'error', 'message' => 'Agendamento inválido.']);
    exit;
}

$result = TicketController::excluirAgendamento($agendamento, $posto);

echo json_encode($result);
