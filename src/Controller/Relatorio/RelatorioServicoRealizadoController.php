<?php

namespace App\Controller\Relatorio;

use App\Core\Db;

class RelatorioServicoRealizadoController
{
    public static function gerarXLS($posto)
    {
        $con = Db::getConnection();
        $posto = intval($posto);

        $sqlVerifica = "SELECT COUNT(1) AS servico_realizado FROM tbl_servico_realizado WHERE posto = $posto";
        $resVerifica = pg_query($con, $sqlVerifica);

        $dados = pg_fetch_assoc($resVerifica);

        if ($dados['servico_realizado'] === 0) {
            header("Location: ../../view/servico_realizado?alerta=true");
        }

        $sql = " SELECT tbl_servico_realizado.descricao,
                        CASE WHEN tbl_servico_realizado.ativo IS TRUE
                             THEN 'Ativo'
                             ELSE 'Inativo'
                        END AS ativo,
                        CASE WHEN tbl_servico_realizado.usa_estoque IS TRUE
                             THEN 'Ativo'
                             ELSE 'Inativo'
                        END AS usa_estoque,
                        to_char(tbl_servico_realizado.data_input, 'DD/MM/YYYY') AS data_input
                    FROM tbl_servico_realizado
                    WHERE posto = $posto
                    ORDER BY descricao ASC
                ";
        $res = pg_query($con, $sql);

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=relatorio_servico_realizado.xls");
        header("Cache-Control: max-age=0");

        echo "<table border='1'>";
        echo "<tr bgcolor='#2e2e48' style='color: #ffffff; font-weight: bold;'>
                <th>Descrição</th>
                <th>Ativo</th>
                <th>Usa Estoque</th>
                <th>Data Cadastro</th>
              </tr>";

        while ($row = pg_fetch_assoc($res)) {

            echo "<tr>";
            echo "<td>{$row['descricao']}</td>";
            echo "<td>{$row['ativo']}</td>";
            echo "<td>{$row['usa_estoque']}</td>";
            echo "<td>{$row['data_input']}</td>";
            echo "</tr>";
        }

        echo "</table>";
        exit;
    }
}
