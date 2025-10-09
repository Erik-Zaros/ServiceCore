<?php

namespace App\Controller\Relatorio;

use App\Core\Db;

class RelatorioListaBasicaController
{
    public static function gerarXLS($produtoId, $posto)
    {
        $con = Db::getConnection();
        $produtoId = intval($produtoId);
        $posto = intval($posto);

        $sqlProduto = "
            SELECT codigo, descricao 
            FROM tbl_produto 
            WHERE produto = $produtoId
        ";
        $resProduto = pg_query($con, $sqlProduto);
        $produto = pg_fetch_assoc($resProduto);

        if (!$produto) {
            echo "Produto não encontrado.";
            exit;
        }

        $sql = "
            SELECT p.codigo, p.descricao, 
                   CASE WHEN p.ativo THEN 'Ativo' ELSE 'Inativo' END AS status,
                   to_char(p.data_input, 'DD/MM/YYYY') AS data_cadastro
            FROM tbl_lista_basica lb
            INNER JOIN tbl_peca p ON lb.peca = p.peca
            WHERE lb.produto = $produtoId AND lb.posto = $posto
            ORDER BY p.descricao ASC
        ";
        $res = pg_query($con, $sql);

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=ListaBasica_Produto_{$produto['codigo']}.xls");
        header("Cache-Control: max-age=0");

        echo "<table border='1'>";
        echo "<tr bgcolor='#2e2e48' style='color: #ffffff; font-weight: bold;'>
                <th colspan='4'>Lista Básica - {$produto['codigo']} - {$produto['descricao']}</th>
              </tr>";
        echo "<tr bgcolor='#cccccc'>
                <th>Código Peça</th>
                <th>Descrição Peça</th>
                <th>Status</th>
                <th>Data Cadastro</th>
              </tr>";

        if (pg_num_rows($res) === 0) {
            echo "<tr><td colspan='4' align='center'>Nenhuma peça amarrada para este produto</td></tr>";
        } else {
            while ($row = pg_fetch_assoc($res)) {
                echo "<tr>";
                echo "<td>{$row['codigo']}</td>";
                echo "<td>{$row['descricao']}</td>";
                echo "<td>{$row['status']}</td>";
                echo "<td>{$row['data_cadastro']}</td>";
                echo "</tr>";
            }
        }

        echo "</table>";
        exit;
    }
}
