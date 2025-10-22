<?php

require '../../vendor/autoload.php';

use App\Controller\UsuarioController;
use App\Auth\Autenticador;

Autenticador::iniciar();
$posto = Autenticador::getPosto();

header('Content-Type: application/json; charset=utf-8');
echo json_encode(UsuarioController::cadastrar($_POST, $posto));
