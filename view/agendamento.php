<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth\Autenticador;
use App\Core\Db;

Autenticador::iniciar();

$title     = 'Agenda';
$pageTitle = 'AGENDA DE AGENDAMENTOS';

$con = Db::getConnection();
ob_start();
?>
  
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/pt-br.global.min.js"></script>

<style>
  #calendar { min-height: 720px; background:#fff; padding:12px; border-radius:10px; }
</style>

<div class="card shadow-sm mb-4">
  <div class="card-header bg-primary text-white">
    <i class="bi bi-calendar-event"></i> Agenda de Agendamentos
  </div>
  <div class="card-body">
    <div id="calendar"></div>
  </div>
</div>

<!-- Modal: Novo Agendamento -->
<div class="modal fade" id="agendamentoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formAgendamento" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Novo Agendamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="data" name="data">
        <input type="hidden" id="hora_inicio_hidden" name="hora_inicio">
        <input type="hidden" id="hora_fim_hidden" name="hora_fim">

        <div class="mb-3">
          <label class="form-label">Intervalo selecionado</label>
          <input id="intervaloLabel" class="form-control" disabled>
        </div>

        <div class="mb-3">
          <label for="tecnico" class="form-label">Técnico</label>
          <select id="tecnico" name="tecnico" class="form-select" required>
            <option value="">Selecione...</option>
            <?php
            $qTec = pg_query($con, "SELECT usuario, nome FROM tbl_usuario WHERE tecnico = true AND ativo = true ORDER BY nome");
            while ($tec = pg_fetch_assoc($qTec)) {
              echo "<option value='{$tec['usuario']}'>".htmlspecialchars($tec['nome'])."</option>";
            }
            ?>
          </select>
        </div>

        <div class="mb-3">
          <label for="os" class="form-label">Ordem de Serviço</label>
          <select id="os" name="os" class="form-select" required>
            <option value="">Selecione...</option>
            <?php
            $qOs = pg_query($con, "SELECT os, nome_consumidor FROM tbl_os WHERE finalizada = false AND cancelada = false ORDER BY os DESC");
            while ($row = pg_fetch_assoc($qOs)) {
              $label = "OS {$row['os']} - ".htmlspecialchars($row['nome_consumidor']);
              echo "<option value='{$row['os']}'>{$label}</option>";
            }
            ?>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-success">Salvar</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const calendarEl = document.getElementById('calendar');

  const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'pt-br',
    initialView: 'dayGridMonth',
    height: 'auto',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
    },
    navLinks: true,
    selectable: true,
    selectMirror: true,
    nowIndicator: true,
    slotMinTime: '07:00:00',
    slotMaxTime: '20:00:00',
    expandRows: true,

    // carrega eventos do backend
    events: 'agendamentos_listar.php',

    // ao selecionar um intervalo, abre modal
    select: (info) => {
      // info.start / info.end são Date; vamos formatar
      const pad = n => String(n).padStart(2,'0');
      const toYmd = d => d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate());
      const toHm  = d => pad(d.getHours()) + ':' + pad(d.getMinutes());

      const data = toYmd(info.start);
      const hIni = toHm(info.start);
      // FullCalendar usa end exclusivo em views de grade; ajusta só para exibir bonito
      const hFim = toHm(info.end);

      document.getElementById('data').value = data;
      document.getElementById('hora_inicio_hidden').value = hIni;
      document.getElementById('hora_fim_hidden').value = hFim;
      document.getElementById('intervaloLabel').value = `${data} ${hIni} - ${hFim}`;

      new bootstrap.Modal(document.getElementById('agendamentoModal')).show();
    },

    // permite arrastar/redimensionar no futuro (implementar no backend depois)
    editable: true,
    eventDrop: async (info) => {
      // TODO: crie um endpoint para atualizar data/hora pelo id
      // fetch('agendamentos_atualizar.php', { method:'POST', body: ... })
      // Se falhar: info.revert();
    },
    eventResize: async (info) => {
      // TODO: idem acima
    }
  });

  calendar.render();

  // submit do formulário (salvar) via fetch
  const form = document.getElementById('formAgendamento');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    try {
      const resp = await fetch('agendamentos_salvar.php', {
        method: 'POST',
        body: formData
      });
      const json = await resp.json();

      if (json && json.success) {
        bootstrap.Modal.getInstance(document.getElementById('agendamentoModal')).hide();
        calendar.refetchEvents();
        form.reset();
      } else {
        alert('Erro ao salvar: ' + (json && json.message ? json.message : 'verifique os dados'));
      }
    } catch (err) {
      alert('Falha na requisição: ' + err);
    }
  });
});
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
