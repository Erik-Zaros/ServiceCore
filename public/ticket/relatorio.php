<?php

require '../../vendor/autoload.php';

use App\Controller\Relatorio\RelatorioTicketController;
use App\Auth\Autenticador;

Autenticador::iniciar();

$posto = Autenticador::getPosto();
RelatorioTicketController::gerarCSV($posto);
