<?php
require '../../vendor/autoload.php';

use App\Controller\TicketController;
use App\Auth\Autenticador;

Autenticador::iniciar();
$posto = Autenticador::getPosto();

$result = TicketController::contarStatusTicket($posto);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
