<?php

require '../../vendor/autoload.php';

use App\Controller\UsuarioController;
use App\Auth\Autenticador;

Autenticador::iniciar();
$posto = Autenticador::getPosto();

echo json_encode(UsuarioController::editar($_POST, $posto));
