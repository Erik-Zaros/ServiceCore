$(document).ready(function () {
  const calendarEl = document.getElementById('calendar');

  const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'pt-br',
    initialView: 'dayGridMonth',
    height: 'auto',
    slotMinTime: '07:00:00',
    slotMaxTime: '20:00:00',
    nowIndicator: true,
    selectable: true,
    expandRows: true,
    navLinks: true,

    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
    },

    events: '/ServiceCore/public/agendamento/agendamentos_listar.php',

    select: function (info) {
      const pad = n => String(n).padStart(2, '0');
      const toYmd = d => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());

      const data = toYmd(info.start);

      Shadowbox.open({
        player: 'iframe',
        content: `/ServiceCore/view/modalAgendamento.php?data=${data}`,
        title: 'Novo Agendamento',
        width: 700,
        height: 400
      });
    },

    eventClick: function(info) {
      alert("Agendamento " + info.event.title + "\nStatus: " + info.event.extendedProps.status);
    }
  });

  calendar.render();
  window.refreshCalendar = () => calendar.refetchEvents();
});
