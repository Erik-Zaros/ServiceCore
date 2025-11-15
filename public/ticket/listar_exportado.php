<?php
require '../../vendor/autoload.php';

use App\Controller\TicketController;
use App\Auth\Autenticador;

Autenticador::iniciar();
$posto = Autenticador::getPosto();

$filtros = $_GET ?? [];
$result = TicketController::listarExportados($posto, $filtros);

echo json_encode($result);
