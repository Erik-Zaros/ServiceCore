<?php
namespace App\Model;

use App\Core\Db;
use App\Auth\Autenticador;

class Agendamento
{
    private $dados;
    private $posto;

    public function __construct(array $dados, $posto)
    {
        $this->dados = $dados;
        $this->posto = $posto;
    }

    public function salvar()
    {
        $con = Db::getConnection();
        $posto = intval($this->posto);

        $data  = pg_escape_string($this->dados['data']);
        $ini   = pg_escape_string($this->dados['hora_inicio']);
        $fim   = pg_escape_string($this->dados['hora_fim']);
        $os    = intval($this->dados['os']);
        $tec   = intval($this->dados['tecnico']);

        $sql = "INSERT INTO tbl_agendamento (data_agendamento, hora_inicio, hora_fim, os, tecnico, status, posto)
                VALUES ('{$data}', '{$ini}', '{$fim}', {$os}, {$tec}, 'PENDENTE', $posto) RETURNING agendamento";
        $res = pg_query($con, $sql);

        if (pg_num_rows($res) > 0) {
            $sql_up_os = "UPDATE tbl_os
                                  SET tecnico = $tec
                                  WHERE os = $os
                                  AND posto = $posto
                                ";
            $res_up_os = pg_query($con, $sql_up_os);

            if ($res_up_os) {
                return ['success' => true, 'id' => pg_fetch_result($res, 0, 'agendamento')];
            } else {
                ['success' => false, 'message' => 'Erro ao salvar'];
            }
        }

        return ['success' => false, 'message' => 'Erro ao salvar'];
    }

    public static function listarTodos($posto)
    {
        $con = Db::getConnection();

        $start = $_GET['start'] ?? null;
        $end   = $_GET['end'] ?? null;
        $cond = ($start && $end) ? "AND a.data_agendamento BETWEEN '{$start}' AND '{$end}'" : "";

        $sql = "SELECT a.agendamento, a.data_agendamento, a.hora_inicio, a.hora_fim, a.status,
                       u.nome AS tecnico_nome, o.nome_consumidor
                FROM tbl_agendamento a
                JOIN tbl_usuario u ON u.usuario = a.tecnico
                JOIN tbl_os o ON o.os = a.os
                WHERE a.posto = $posto
                {$cond}
                ORDER BY a.data_agendamento";

        $res = pg_query($con, $sql);

        $eventos = [];
        while ($row = pg_fetch_assoc($res)) {
            $color = match ($row['status']) {
                'PENDENTE'   => '#f1c40f',
                'CONFIRMADO' => '#3498db',
                'CONCLUIDO'  => '#2ecc71',
                'CANCELADO'  => '#e74c3c',
                default      => '#7f8c8d',
            };

            $eventos[] = [
                'id'    => $row['agendamento'],
                'title' => "OS {$row['nome_consumidor']} ({$row['tecnico_nome']})",
                'start' => $row['data_agendamento'] . 'T' . $row['hora_inicio'],
                'end'   => $row['data_agendamento'] . 'T' . $row['hora_fim'],
                'status'=> $row['status'],
                'color' => $color
            ];
        }

        return $eventos;
    }
}
