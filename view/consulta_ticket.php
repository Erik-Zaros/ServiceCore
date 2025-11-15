<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth\Autenticador;

Autenticador::iniciar();

$title = 'Consulta Ticket';
$pageTitle = 'CONSULTA DE TICKETS';

ob_start();
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-search"></i> Filtro
    </div>
    <div class="card-body">
        <form id="filtroForm">
            <div class="row">
                <div class="col-md-2">
                    <label for="ticket" class="form-label">Número Ticket</label>
                    <input type="text" class="form-control" id="ticket" name="ticket">
                </div>
                <div class="col-md-2">
                    <label for="os" class="form-label">Número OS</label>
                    <input type="text" class="form-control" id="os" name="os">
                </div>
                <div class="col-md-3">
                    <label for="nomeCliente" class="form-label">Nome do Cliente</label>
                    <input type="text" class="form-control" id="nomeCliente" name="nomeCliente">
                </div>
                <div class="col-md-2">
                    <label for="dataInicio" class="form-label">Data Exportação (Início)</label>
                    <input type="date" class="form-control" id="dataInicio" name="dataInicio">
                </div>
                <div class="col-md-2">
                    <label for="dataFim" class="form-label">Data Exportação (Fim)</label>
                    <input type="date" class="form-control" id="dataFim" name="dataFim">
                </div>
            </div>
            <div class="row">
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-success btn-sm">Filtrar</button>
                <button type="button" class="btn btn-secondary btn-sm" id="limparFiltros">Limpar</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
  <div class="col-lg-3 col-12">
    <div class="small-box bg-primary card-status" data-status="ABERTO">
      <div class="inner">
        <h3 id="em_aberto">—</h3>
        <p>Tickets Abertos</p>
      </div>
      <div class="icon"><i class="bi bi-folder2-open"></i></div>
      <span class="small-box-footer">Filtrar por abertos <i class="bi bi-arrow-right"></i></span>
    </div>
  </div>

  <div class="col-lg-3 col-12">
    <div class="small-box bg-warning card-status" data-status="EM_ANDAMENTO">
      <div class="inner">
        <h3 id="em_andamento">—</h3>
        <p>Tickets em Andamento</p>
      </div>
      <div class="icon"><i class="bi bi-hourglass-split"></i></div>
      <span class="small-box-footer">Filtrar por andamento <i class="bi bi-arrow-right"></i></span>
    </div>
  </div>

  <div class="col-lg-3 col-12">
    <div class="small-box bg-success card-status" data-status="FINALIZADO">
      <div class="inner">
        <h3 id="finalizado">—</h3>
        <p>Tickets Finalizados</p>
      </div>
      <div class="icon"><i class="bi bi-check-circle-fill"></i></div>
      <span class="small-box-footer">Filtrar por finalizados <i class="bi bi-arrow-right"></i></span>
    </div>
  </div>

  <div class="col-lg-3 col-12">
    <div class="small-box bg-danger card-status" data-status="CANCELADO">
      <div class="inner">
        <h3 id="cancelado">—</h3>
        <p>Tickets Cancelados</p>
      </div>
      <div class="icon"><i class="bi bi-x-octagon-fill"></i></div>
      <span class="small-box-footer">Filtrar por cancelados <i class="bi bi-arrow-right"></i></span>
    </div>
  </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-card-list"></i> Ticket
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="ticketTable">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>OS</th>
                    <th>Status Ticket</th>
                    <th>Cliente</th>
                    <th>CPF</th>
                    <th>Produto</th>
                    <th>Técnico</th>
                    <th>Data Agendamento</th>
                    <th>Data Exportação</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="text-center mt-4 mb-3">
    <a href="../public/ticket/relatorio.php" class="btn btn-success btn-sm">Download Excel</a>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
