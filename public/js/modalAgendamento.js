$(document).ready(function () {
  const params = new URLSearchParams(window.location.search);
  $("#data").val(params.get("data"));
  $("#dataLabel").val(params.get("data"));

  $.getJSON("/ServiceCore/public/agendamento/buscarTecnico.php", function (tecnicos) {
    $("#tecnico").append('<option value="">Selecione...</option>');
    tecnicos.forEach(t => $("#tecnico").append(`<option value="${t.usuario}">${t.nome}</option>`));
  });

  $.getJSON("/ServiceCore/public/agendamento/buscarOs.php", function (osList) {
    $("#os").append('<option value="">Selecione...</option>');
    osList.forEach(o => $("#os").append(`<option value="${o.os}">OS ${o.os} - ${o.nome_consumidor}</option>`));
  });

  $("#formAgendamento").on("submit", function (e) {
    e.preventDefault();

    if (!$("#hora_inicio").val() || !$("#hora_fim").val()) {
      alert("Informe hora de início e fim!");
      return;
    }

    $.post("/ServiceCore/public/agendamento/agendamentos_salvar.php", $(this).serialize(), function (resp) {
      if (resp.success) {
        parent.refreshCalendar();
        parent.Shadowbox.close();
      } else {
        alert("Erro: " + resp.message);
      }
    }, "json");
  });
});
