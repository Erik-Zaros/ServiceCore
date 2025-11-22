$(document).ready(function () {
    carregarTickets();

    function carregarTickets(filtros = {}) {
        if ($.fn.DataTable.isDataTable('#ticketTable')) {
            $('#ticketTable').DataTable().destroy();
        }
        $.ajax({
            url: '../public/ticket/listar_nao_exportado.php',
            method: 'GET',
            data: filtros,
            dataType: 'json',
            success: function (data) {
                $('#ticketTable tbody').empty();
                data.forEach(function (item) {
                    $('#ticketTable tbody').append(`
                        <tr>
                            <td>${item.os}</td>
                            <td>${item.cliente}</td>
                            <td>${item.cpf}</td>
                            <td>${item.produto}</td>
                            <td>${item.data_abertura}</td>
                            <td>${item.data_agendamento} ${item.hora_inicio}</td>
                            <td>${item.tecnico}</td>
                            <td>
                                <button class="btn btn-danger btn-sm excluir" data-agendamento="${item.agendamento}">Excluir</button>
                                <button class="btn btn-success btn-sm exportar" data-os="${item.os}" data-agendamento="${item.agendamento}">Exportar</button>
                            </td>
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
                } else {
                    $('#ticketTable tbody').append(`
                        <tr>
                            <td colspan="9" class="text-center">NENHUM AGENDAMENTO PARA EXPORTAR</td>
                        </tr>
                    `);
                }
            }
        });
    }

    $('#filtroForm').on('submit', function (e) {
        e.preventDefault();
        carregarTickets($(this).serialize());
    });

    $('#limparFiltros').on('click', function () {
        $('#filtroForm')[0].reset();
        carregarTickets();
    });

    $(document).on('click', '.excluir', function () {
        let agendamento = $(this).data('agendamento');
        Swal.fire({
            title: 'Excluir agendamento?',
            icon: 'warning',
            text: 'Essa ação não pode ser desfeita.',
            showCancelButton: true,
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('../public/ticket/excluir_agendamento.php', { agendamento }, function (response) {
                    Swal.fire('Sucesso!', response.message, 'success');
                    carregarTickets();
                }, 'json');
            }
        });
    });

    $(document).on('click', '.exportar', function () {
        let os = $(this).data('os');
        let agendamento = $(this).data('agendamento');

        Swal.fire({
            title: `Deseja confirmar o agendamento da Ordem de Serviço ${os} e exportar a Ordem de Serviço para o Aplicativo?`,
            text: 'Essa ação não pode ser desfeita.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, exportar!',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('../public/ticket/exportar.php', { os, agendamento }, function (response) {
                    if (response.status === 'success') {
                        Swal.fire('Sucesso!', response.message, 'success');
                        carregarTickets();
                    } else {
                        Swal.fire('Erro!', response.message, 'error');
                    }
                }, 'json');
            }
        });
    });
});
