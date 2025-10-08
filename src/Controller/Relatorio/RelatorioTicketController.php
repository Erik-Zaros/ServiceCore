<?php

namespace App\Controller\Relatorio;

use App\Core\Db;

class RelatorioTicketController
{
    public static function gerarXLS($posto)
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

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=Relatorio_Ticket.xls");
        header("Cache-Control: max-age=0");

        echo "<table border='1'>";
        echo "<tr bgcolor='#2e2e48' style='color: #ffffff; font-weight: bold;'>
                <th>Ticket</th>
                <th>OS</th>
                <th>Status</th>
                <th>Nome Consumidor</th>
                <th>CPF Consumidor</th>
                <th>Produto</th>
                <th>Tecnico</th>
                <th>Data Agendamento</th>
                <th>Data Exportação</th>
              </tr>";

        while ($row = pg_fetch_assoc($res)) {
            echo "<tr>";
            echo "<td>{$row['ticket']}</td>";
            echo "<td>{$row['os']}</td>";
            echo "<td>{$row['status']}</td>";
            echo "<td>{$row['nome_consumidor']}</td>";
            echo "<td>{$row['cpf_consumidor']}</td>";
            echo "<td>{$row['produto']}</td>";
            echo "<td>{$row['tecnico']}</td>";
            echo "<td>{$row['data_agendamento']}</td>";
            echo "<td>{$row['data_exportacao']}</td>";
            echo "</tr>";
        }

        echo "</table>";
        exit;
    }
}
