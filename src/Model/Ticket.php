<?php

namespace App\Model;

use App\Core\Db;

class Ticket
{
    public static function listarNaoExportados($posto, array $filtros = [])
    {
        $con = Db::getConnection();
        $posto = intval($posto);

        $sql = "
            SELECT
                os.os,
                os.nome_consumidor AS cliente,
                os.cpf_consumidor AS cpf,
                to_char(os.data_abertura, 'DD/MM/YYYY') AS data_abertura,
                CONCAT(p.codigo, ' - ', p.descricao) AS produto,
                ag.agendamento,
                to_char(ag.data_agendamento, 'DD/MM/YYYY') AS data_agendamento,
                ag.hora_inicio,
                u.nome AS tecnico
            FROM tbl_agendamento ag
            INNER JOIN tbl_os os ON ag.os = os.os
            INNER JOIN tbl_produto p ON os.produto = p.produto
            LEFT JOIN tbl_usuario u ON ag.tecnico = u.usuario
            WHERE os.posto = {$posto}
              AND NOT EXISTS (
                  SELECT 1 FROM tbl_ticket t
                  WHERE t.os = os.os AND t.agendamento = ag.agendamento
              )
        ";

        if (!empty($filtros['os'])) {
            $os = (int) $filtros['os'];
            $sql .= " AND os.os = {$os}";
        }

        if (!empty($filtros['nomeCliente'])) {
            $nome = pg_escape_string($con, $filtros['nomeCliente']);
            $sql .= " AND os.nome_consumidor ILIKE '%{$nome}%'";
        }

        if (!empty($filtros['dataInicio']) && !empty($filtros['dataFim'])) {
            $dataInicio = pg_escape_string($con, $filtros['dataInicio']);
            $dataFim    = pg_escape_string($con, $filtros['dataFim']);
            $sql .= " AND os.data_abertura BETWEEN '{$dataInicio}' AND '{$dataFim}'";
        }

        $sql .= " ORDER BY os.os ASC";

        $res = pg_query($con, $sql);
        $lista = [];

        if ($res && pg_num_rows($res) > 0) {
            while ($row = pg_fetch_assoc($res)) {
                $lista[] = $row;
            }
        }

        return $lista;
    }

    public static function listarExportados($posto, array $filtros = [])
    {
        $con = Db::getConnection();
        $posto = intval($posto);

        $sql = "
            SELECT
            	ticket.ticket,
                os.os,
                ticket.status,
                os.nome_consumidor AS cliente,
                os.cpf_consumidor AS cpf,
                to_char(os.data_abertura, 'DD/MM/YYYY') AS data_abertura,
                CONCAT(p.codigo, ' - ', p.descricao) AS produto,
                ag.agendamento,
                to_char(ag.data_agendamento, 'DD/MM/YYYY') AS data_agendamento,
                ag.hora_inicio,
                u.nome AS tecnico,
                to_char(ticket.data_input, 'DD/MM/YYYY') as data_exportado
            FROM tbl_ticket ticket
            INNER JOIN tbl_os os ON ticket.os = os.os
            INNER JOIN tbl_agendamento ag ON ag.agendamento = ticket.agendamento
            INNER JOIN tbl_produto p ON os.produto = p.produto
            LEFT JOIN tbl_usuario u ON ag.tecnico = u.usuario
            WHERE ticket.posto = {$posto}
            -- AND ticket.exportado IS TRUE
        ";

        if (!empty($filtros['ticket'])) {
            $ticket = (int) $filtros['ticket'];
            $sql .= " AND ticket.ticket = {$ticket}";
        }

        if (!empty($filtros['os'])) {
            $os = (int) $filtros['os'];
            $sql .= " AND os.os = {$os}";
        }

        if (!empty($filtros['nomeCliente'])) {
            $nome = pg_escape_string($con, $filtros['nomeCliente']);
            $sql .= " AND os.nome_consumidor ILIKE '%{$nome}%'";
        }

        if (!empty($filtros['dataInicio']) && !empty($filtros['dataFim'])) {
            $dataInicio = pg_escape_string($con, $filtros['dataInicio']);
            $dataFim    = pg_escape_string($con, $filtros['dataFim']);
            $sql .= " AND ticket.data_input BETWEEN '{$dataInicio}' AND '{$dataFim}'";
        }

        if (!empty($filtros['status'])) {
            $status = $filtros['status'];
            $sql .= " AND ticket.status = '{$status}'";
        }

        $sql .= " ORDER BY ticket.ticket ASC";

        $res = pg_query($con, $sql);
        $lista = [];

        if ($res && pg_num_rows($res) > 0) {
            while ($row = pg_fetch_assoc($res)) {
                $lista[] = $row;
            }
        }

        return $lista;
    }

    public static function contarStatusTicket($posto)
    {
        $con = Db::getConnection();
        $posto = intval($posto);

        $sql = "
            SELECT status, COUNT(*) AS total
            FROM tbl_ticket
            WHERE posto = {$posto}
            GROUP BY status
        ";

        $res = pg_query($con, $sql);
        $contagens = [
            'ABERTO' => 0,
            'EM_ANDAMENTO' => 0,
            'FINALIZADO' => 0,
            'CANCELADO' => 0
        ];

        if ($res && pg_num_rows($res) > 0) {
            while ($row = pg_fetch_assoc($res)) {
                $contagens[$row['status']] = (int) $row['total'];
            }
        }

        return $contagens;
    }

	public static function excluirAgendamento($agendamento, $posto)
	{
	    $con = Db::getConnection();
	    $agendamento = intval($agendamento);
	    $posto = intval($posto);

	    $sql = "DELETE FROM tbl_agendamento
	            USING tbl_os
	            WHERE tbl_agendamento.os = tbl_os.os
	              AND tbl_agendamento.agendamento = {$agendamento}
	              AND tbl_os.posto = {$posto}";

	    $res = pg_query($con, $sql);

	    if ($res) {
	        return ['status' => 'success', 'message' => 'Agendamento excluído com sucesso!'];
	    }
	    return ['status' => 'error', 'message' => 'Erro ao excluir agendamento.'];
	}

	public static function exportar($os, $agendamento, $posto)
	{
	    $con = Db::getConnection();
	    $os = intval($os);
	    $agendamento = intval($agendamento);
	    $posto = intval($posto);

	    $sql = "
	        SELECT
	            os.os,
	            os.data_abertura,
	            os.nome_consumidor,
	            os.cpf_consumidor,
	            c.cep,
	            c.endereco,
	            c.bairro,
	            c.numero,
	            c.cidade,
	            c.estado,
	            p.codigo AS produto_codigo,
	            p.descricao AS produto_descricao,
	            ag.agendamento,
	            ag.data_agendamento,
	            ag.hora_inicio,
	            ag.hora_fim,
	            u.nome AS tecnico
	        FROM tbl_os os
	        INNER JOIN tbl_produto p ON os.produto = p.produto
	        LEFT JOIN tbl_cliente c ON os.cliente = c.cliente
	        INNER JOIN tbl_agendamento ag ON ag.os = os.os
	        LEFT JOIN tbl_usuario u ON ag.tecnico = u.usuario
	        WHERE os.os = {$os}
	          AND ag.agendamento = {$agendamento}
	          AND os.posto = {$posto}
	    ";

	    $res = pg_query($con, $sql);
	    if (pg_num_rows($res) === 0) {
	        return ['status' => 'error', 'message' => 'OS ou agendamento não encontrados.'];
	    }

	    $dados = pg_fetch_assoc($res);
	    $json = json_encode($dados);

	    $sqlInsert = "
	        INSERT INTO tbl_ticket (os, agendamento, posto, status, request)
	        VALUES ({$os}, {$agendamento}, {$posto}, 'ABERTO', '{$json}'::jsonb)
	    ";

	    $resInsert = pg_query($con, $sqlInsert);

		if($resInsert) {
			$sqlUpdateAgendamento = "UPDATE tbl_agendamento SET status = 'CONFIRMADO'
									 WHERE agendamento = $agendamento AND posto = $posto";
			$resUpdateAgendamento = pg_query($con, $sqlUpdateAgendamento);

			if ($resUpdateAgendamento) {
				return ['status' => 'success', 'message' => 'Ticket exportado com sucesso!'];
			}
		}

	    return ['status' => 'error', 'message' => 'Erro ao exportar ticket.'];
	}
}
