<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth\Autenticador;
use App\Core\Db;

Autenticador::iniciar();

$title     = 'Agenda';
$pageTitle = 'AGENDAMENTOS';

$con = Db::getConnection();
ob_start();
?>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/pt-br.global.min.js"></script>

<div class="card shadow-sm mb-4">
  <div class="card-header bg-primary text-white">
    <i class="bi bi-calendar-event"></i> Agendamentos
  </div>
  <div class="card-body">
    <div id="calendar"></div>
  </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
