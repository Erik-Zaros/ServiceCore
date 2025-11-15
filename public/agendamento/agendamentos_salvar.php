<?php
require '../../vendor/autoload.php';
use App\Controller\AgendamentoController;
use App\Auth\Autenticador;

Autenticador::iniciar();
$posto = Autenticador::getPosto();

header('Content-Type: application/json');
echo json_encode(AgendamentoController::salvar($_POST, $posto));
