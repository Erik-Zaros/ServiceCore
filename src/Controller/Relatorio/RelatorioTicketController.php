<?php

namespace App\Controller\Relatorio;

use App\Core\Db;

class RelatorioTicketController
{
    public static function gerarCSV($posto)
    {
        $con = Db::getConnection();
        $posto = intval($posto);

        $sqlVerifica = "SELECT COUNT(1) AS ticket FROM tbl_ticket WHERE posto = $posto";
        $resVerifica = pg_query($con, $sqlVerifica);

        $dados = pg_fetch_assoc($resVerifica);

        if ($dados['produto'] === 0) {
            header("Location: ../../view/consulta_ticket.php?alerta=true");
        }

        $sql = "SELECT tbl_ticket.ticket,
                       tbl_ticket.os,
                       tbl_ticket.status,
                       tbl_os.nome_consumidor,
                       tbl_os.cpf_consumidor,
                       concat(tbl_produto.codigo, ' - ', tbl_produto.descricao) as produto,
                       tbl_usuario.nome as tecnico,
                       to_char(tbl_agendamento.data_agendamento, 'DD/MM/YYYY') as data_agendamento,
                       to_char(tbl_ticket.data_input, 'DD/MM/YYYY') as data_exportacao
                FROM tbl_ticket
                INNER JOIN tbl_os ON tbl_os.os = tbl_ticket.os
                INNER JOIN tbl_produto ON tbl_produto.produto = tbl_os.produto
                INNER JOIN tbl_agendamento ON tbl_agendamento.agendamento = tbl_ticket.agendamento
                INNER JOIN tbl_usuario ON tbl_usuario.usuario = tbl_agendamento.tecnico
            ";
        $res = pg_query($con, $sql);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=relatorio_ticket.csv');

        $output = fopen('php://output', 'w');
	
		$cabecalho = ['Ticket', 'OS', 'Status', 'Nome Consumidor', 'CPF Consumidor', 'Produto', 'Técnico', 'Data Agendamento', 'Data Exportação'];
        fputcsv($output, $cabecalho, ';');

        while ($row = pg_fetch_assoc($res)) {

            fputcsv($output, [$row['ticket'], $row['os'], $row['status'], $row['nome_consumidor'], $row['cpf_consumidor'], $row['produto'], $row['tecnico'], $row['data_agendamento'], $row['data_exportacao']], ';');
        }

        fclose($output);
        exit;
    }
}
