<?php

namespace App\Controller;

use App\Model\Ticket;

class TicketController
{
    public static function listarNaoExportados($posto, array $filtros = [])
    {
        return Ticket::listarNaoExportados($posto, $filtros);
    }

    public static function exportar($os, $agendamento, $posto)
    {
        return Ticket::exportar($os, $agendamento, $posto);
    }

    public static function excluirAgendamento($agendamento, $posto)
    {
        return Ticket::excluirAgendamento($agendamento, $posto);
    }
}
