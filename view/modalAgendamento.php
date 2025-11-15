<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Novo Agendamento</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-3">
    <form id="formAgendamento">
      <input type="hidden" id="data" name="data">

      <div class="mb-3">
        <label class="form-label">Data selecionada</label>
        <input id="dataLabel" class="form-control" disabled>
      </div>

      <div class="row">
        <div class="col">
          <label class="form-label">Hora Início</label>
          <input type="time" id="hora_inicio" name="hora_inicio" class="form-control" required>
        </div>
        <div class="col">
          <label class="form-label">Hora Fim</label>
          <input type="time" id="hora_fim" name="hora_fim" class="form-control" required>
        </div>
      </div>


      <div class="mb-3">
        <label class="form-label">Técnico</label>
        <select id="tecnico" name="tecnico" class="form-select" required></select>
      </div>

      <div class="mb-3">
        <label class="form-label">Ordem de Serviço</label>
        <select id="os" name="os" class="form-select" required></select>
      </div>

      <div class="text-end">
        <button type="button" class="btn btn-secondary" onclick="parent.Shadowbox.close()">Cancelar</button>
        <button type="submit" class="btn btn-success">Salvar</button>
      </div>
    </form>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../public/js/modalAgendamento.js"></script>
</body>
</html>
