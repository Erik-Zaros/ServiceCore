$(document).ready(function () {
    carregarTickets();
    carregarStatusTicket();

    function carregarTickets(filtros = {}) {
        if ($.fn.DataTable.isDataTable('#ticketTable')) {
            $('#ticketTable').DataTable().destroy();
        }
        $.ajax({
            url: '../public/ticket/listar_exportado.php',
            method: 'GET',
            data: filtros,
            dataType: 'json',
            success: function (data) {
                $('#ticketTable tbody').empty();
                data.forEach(function (item) {
                    $('#ticketTable tbody').append(`
                        <tr>
                            <td>${item.ticket}</td>
                            <td>${item.os}</td>
                            <td><span class="badge ${
                                item.status === 'ABERTO' ? 'bg-primary' :
                                item.status === 'EM_ANDAMENTO' ? 'bg-warning text-dark' :
                                item.status === 'EM ANDAMENTO' ? 'bg-warning text-dark' :
                                item.status === 'FINALIZADO' ? 'bg-success' :
                                'bg-danger'
                            }">${item.status}</span></td>
                            <td>${item.cliente}</td>
                            <td>${item.cpf}</td>
                            <td>${item.produto}</td>
                            <td>${item.tecnico}</td>
                            <td>${item.data_agendamento}</td>
                            <td>${item.data_exportado}</td>
                        </tr>
                    `);
                });
                if (data.length > 0) {
                    $('#ticketTable').DataTable({
                        responsive: true,
                        scrollX: true,
                        autoWidth: false,
                        language: {
                            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
                        }
                    });
                }
            }
        });
    }

    function carregarStatusTicket() {
        $.ajax({
            url: '../public/ticket/contar_status.php',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                $('#em_aberto').text(data.ABERTO);
                $('#em_andamento').text(data.EM_ANDAMENTO);
                $('#finalizado').text(data.FINALIZADO);
                $('#cancelado').text(data.CANCELADO);
            }
        });
    }

    $('.card-status').on('click', function () {
        const status = $(this).data('status');
        carregarTickets({ status: status });
        $('.card-status').removeClass('border border-light border-3');
        $(this).addClass('border border-light border-3');
    });

    $('#filtroForm').on('submit', function (e) {
        e.preventDefault();
        carregarTickets($(this).serialize());
    });

    $('#limparFiltros').on('click', function () {
        $('#filtroForm')[0].reset();
        carregarTickets();
    });
});
