<?php

namespace App\Model;

use App\Core\Db;

class Os
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
        pg_query($con, 'BEGIN');

        try {
            $cpf = pg_escape_string($this->dados['cpf_consumidor']);
            $nome = pg_escape_string($this->dados['nome_consumidor']);
            $produto = intval($this->dados['produto']);
            $posto = intval($this->posto);
            $data_abertura = pg_escape_string($this->dados['data_abertura']);
            $cep = pg_escape_string($this->dados['cep_consumidor'] ?? '');
            $endereco = pg_escape_string($this->dados['endereco_consumidor'] ?? '');
            $bairro = pg_escape_string($this->dados['bairro_consumidor'] ?? '');
            $numero = pg_escape_string($this->dados['numero_consumidor'] ?? '');
            $cidade = pg_escape_string($this->dados['cidade_consumidor'] ?? '');
            $estado = pg_escape_string($this->dados['estado_consumidor'] ?? '');
            $nota_fiscal = pg_escape_string($this->dados['nota_fiscal'] ?? '');

            $sqlCliente = "SELECT cliente, nome FROM tbl_cliente WHERE cpf = '{$cpf}' AND posto = {$posto}";
            $res = pg_query($con, $sqlCliente);

            if (pg_num_rows($res) > 0) {
                $cliente = pg_fetch_assoc($res);
                $cliente_id = $cliente['cliente'];

                if ($cliente['nome'] !== $nome) {
                    pg_query($con, "UPDATE tbl_cliente SET nome = '{$nome}' WHERE cliente = {$cliente_id}");
                }
            } else {
                $sqlInsertCliente = "INSERT INTO tbl_cliente (nome, cpf, cep, endereco, bairro, numero, cidade, estado, posto)
                                     VALUES ('{$nome}', '{$cpf}', '{$cep}', '{$endereco}', '{$bairro}', '{$numero}', '{$cidade}', '{$estado}', {$posto})
                                     RETURNING cliente";
                $res_insert = pg_query($con, $sqlInsertCliente);
                if (!$res_insert) throw new \Exception("Erro ao cadastrar cliente.");
                $cliente_row = pg_fetch_assoc($res_insert);
                $cliente_id = $cliente_row['cliente'];
            }

            $sqlInsertOS = "
                INSERT INTO tbl_os (
                    data_abertura, nome_consumidor, cpf_consumidor, produto, cliente, posto,
                    cep_consumidor, endereco_consumidor, bairro_consumidor, numero_consumidor,
                    cidade_consumidor, estado_consumidor, nota_fiscal
                )
                VALUES (
                    '{$data_abertura}', '{$nome}', '{$cpf}', {$produto}, {$cliente_id}, {$posto},
                    '{$cep}', '{$endereco}', '{$bairro}', '{$numero}', '{$cidade}', '{$estado}', '{$nota_fiscal}'
                )
                RETURNING os
            ";
            $res_os = pg_query($con, $sqlInsertOS);

            if (!$res_os) throw new \Exception('Erro ao cadastrar OS.');

            $row_os = pg_fetch_assoc($res_os);
            $os_id = $row_os['os'];

            if (!empty($this->dados['pecas'])) {

                $pecas = json_decode($this->dados['pecas'], true);

                if (is_array($pecas)) {
                    foreach ($pecas as $item) {
                        $peca = intval($item['peca']);
                        $quantidade = intval($item['quantidade']);

                        $sqlCheckPeca = "SELECT 1
                                         FROM tbl_lista_basica
                                         WHERE produto = {$produto}
                                         AND peca = {$peca}
                                         LIMIT 1
                                        ";
                        $resCheckPeca = pg_query($con, $sqlCheckPeca);

                        if (pg_num_rows($resCheckPeca) === 0) {
                            $sqlNomePeca = "SELECT concat(codigo, ' - ', descricao) as nome_peca FROM tbl_peca WHERE peca = {$peca}";
                            $resNomePeca = pg_query($con, $sqlNomePeca);

                            if (pg_num_rows($resNomePeca) > 0) {
                                $row = pg_fetch_assoc($resNomePeca);
                                $nomePeca = $row['nome_peca'];
                                throw new \Exception("A peça '{$nomePeca}' não pertence ao produto selecionado.");
                            }
                        }

                        $sqlItem = "
                            INSERT INTO tbl_os_item (os, peca, quantidade, posto)
                            VALUES ({$os_id}, {$peca}, {$quantidade}, {$posto})
                        ";
                        pg_query($con, $sqlItem);
                    }
                }
            }

            pg_query($con, 'COMMIT');
            return ['status' => 'success', 'message' => 'OS cadastrada com sucesso!', 'os' => $os_id];
        } catch (\Exception $e) {
            pg_query($con, 'ROLLBACK');
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function editar()
    {
        $con = Db::getConnection();
        pg_query($con, 'BEGIN');

        try {
            $os = intval($this->dados['os']);
            $cpf = pg_escape_string($this->dados['cpf_consumidor']);
            $nome = pg_escape_string($this->dados['nome_consumidor']);
            $produto = intval($this->dados['produto']);
            $posto = intval($this->posto);
            $data_abertura = pg_escape_string($this->dados['data_abertura']);
            $cep = pg_escape_string($this->dados['cep_consumidor'] ?? '');
            $endereco = pg_escape_string($this->dados['endereco_consumidor'] ?? '');
            $bairro = pg_escape_string($this->dados['bairro_consumidor'] ?? '');
            $numero = pg_escape_string($this->dados['numero_consumidor'] ?? '');
            $cidade = pg_escape_string($this->dados['cidade_consumidor'] ?? '');
            $estado = pg_escape_string($this->dados['estado_consumidor'] ?? '');
            $nota_fiscal = pg_escape_string($this->dados['nota_fiscal'] ?? '');

            $sqlCheck = "SELECT finalizada, cancelada FROM tbl_os WHERE os = {$os}";
            $res_check = pg_query($con, $sqlCheck);

            if (pg_num_rows($res_check) === 0) throw new \Exception("OS não encontrada.");
            $row = pg_fetch_assoc($res_check);

            if ($row['finalizada'] === 't') throw new \Exception("OS já finalizada. Não é possível editar.");
            if ($row['cancelada'] === 't') throw new \Exception("OS já cancelada. Não é possível editar.");

            $sqlCliente = "SELECT cliente, nome FROM tbl_cliente WHERE cpf = '{$cpf}' AND posto = {$posto}";
            $res_cliente = pg_query($con, $sqlCliente);

            if (pg_num_rows($res_cliente) > 0) {
                $cliente = pg_fetch_assoc($res_cliente);
                $cliente_id = $cliente['cliente'];

                if ($cliente['nome'] !== $nome) {
                    pg_query($con, "UPDATE tbl_cliente SET nome = '{$nome}' WHERE cliente = {$cliente_id}");
                }
            } else {
                $sqlInsert = "INSERT INTO tbl_cliente (nome, cpf, posto) 
                              VALUES ('{$nome}', '{$cpf}', {$posto}) RETURNING cliente";
                $res_insert = pg_query($con, $sqlInsert);
                $row_new = pg_fetch_assoc($res_insert);
                $cliente_id = $row_new['cliente'];
            }

            $sqlUpdateOS = "
                UPDATE tbl_os SET
                    data_abertura = '{$data_abertura}',
                    nome_consumidor = '{$nome}',
                    cpf_consumidor = '{$cpf}',
                    produto = {$produto},
                    cliente = {$cliente_id},
                    cep_consumidor = '{$cep}',
                    endereco_consumidor = '{$endereco}',
                    bairro_consumidor = '{$bairro}',
                    numero_consumidor = '{$numero}',
                    cidade_consumidor = '{$cidade}',
                    estado_consumidor = '{$estado}',
                    nota_fiscal = '{$nota_fiscal}'
                WHERE os = {$os}
            ";
            $res_update = pg_query($con, $sqlUpdateOS);

            if (!$res_update) throw new \Exception("Erro ao atualizar OS.");

            if (!empty($this->dados['pecas'])) {
                $pecas = json_decode($this->dados['pecas'], true);

                if (is_array($pecas)) {
                    foreach ($pecas as $item) {
                        $peca = intval($item['peca']);
                        $quantidade = intval($item['quantidade']);

                        $sqlCheckPeca = "SELECT 1
                                         FROM tbl_lista_basica
                                         WHERE produto = {$produto}
                                         AND peca = {$peca}
                                         LIMIT 1
                                        ";
                        $resCheckPeca = pg_query($con, $sqlCheckPeca);

                        if (pg_num_rows($resCheckPeca) === 0) {
                            $sqlNomePeca = "SELECT concat(codigo, ' - ', descricao) as nome_peca FROM tbl_peca WHERE peca = {$peca}";
                            $resNomePeca = pg_query($con, $sqlNomePeca);

                            if (pg_num_rows($resNomePeca) > 0) {
                                $row = pg_fetch_assoc($resNomePeca);
                                $nomePeca = $row['nome_peca'];
                                throw new \Exception("A peça '{$nomePeca}' não pertence ao produto selecionado.");
                            }
                        }

                        $sqlCheck = "SELECT os_item FROM tbl_os_item WHERE os = {$os} AND peca = {$peca}";
                        $resCheck = pg_query($con, $sqlCheck);

                        if (pg_num_rows($resCheck) > 0) {
                            $sqlUpdateItem = "
                                UPDATE tbl_os_item
                                SET quantidade = {$quantidade}
                                WHERE os = {$os} AND peca = {$peca}
                            ";
                            $resUpdateItem = pg_query($con, $sqlUpdateItem);
                        } else {
                            $sqlInsertItem = "
                                INSERT INTO tbl_os_item (os, peca, quantidade, posto)
                                VALUES ({$os}, {$peca}, {$quantidade}, {$posto})
                            ";
                            $resInsertIntem = pg_query($con, $sqlInsertItem);
                        }
                    }
                }
            }

            pg_query($con, 'COMMIT');
            return ['status' => 'success', 'message' => 'OS atualizada com sucesso!', 'os' => $os];
        } catch (\Exception $e) {
            pg_query($con, 'ROLLBACK');
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public static function filtrarOrdens(array $filtros, $posto)
    {
        $con = Db::getConnection();

        $sql = "SELECT
                    os.os,
                    os.nome_consumidor AS cliente,
                    os.cpf_consumidor AS cpf,
                    CONCAT(p.codigo, ' - ', p.descricao) AS produto,
                    os.data_abertura,
                    os.finalizada,
                    os.cancelada
                FROM tbl_os os
                INNER JOIN tbl_produto p ON os.produto = p.produto
                WHERE os.posto = $posto";

        if (!empty($filtros['os'])) {
            $os = (int) $filtros['os'];
            $sql .= " AND os.os = $os";
        }

        if (!empty($filtros['nomeCliente'])) {
            $nome = pg_escape_string($con, $filtros['nomeCliente']);
            $sql .= " AND os.nome_consumidor ILIKE '%$nome%'";
        }

        if (!empty($filtros['dataInicio']) && !empty($filtros['dataFim'])) {
            $dataInicio = pg_escape_string($con, $filtros['dataInicio']);
            $dataFim    = pg_escape_string($con, $filtros['dataFim']);
            $sql .= " AND os.data_abertura BETWEEN '$dataInicio' AND '$dataFim'";
        }

        $sql .= " ORDER BY os.os ASC";

        $result = pg_query($con, $sql);
        $ordens = [];

        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $ordens[] = [
                    'os'            => $row['os'],
                    'cliente'       => $row['cliente'],
                    'cpf'           => $row['cpf'],
                    'produto'       => $row['produto'],
                    'data_abertura' => date('d/m/Y', strtotime($row['data_abertura'])),
                    'finalizada'    => $row['finalizada'] === 't',
                    'cancelada'     => $row['cancelada'] === 't'
                ];
            }
        }

        return $ordens;
    }

    public static function buscar($os)
    {
        $con = Db::getConnection();
        $os = intval($os);
        $sql = "SELECT os.os, os.data_abertura, os.nome_consumidor, os.cpf_consumidor,
                       os.produto, p.descricao AS produto_descricao,
                       os.cep_consumidor, os.endereco_consumidor, os.bairro_consumidor,
                       os.numero_consumidor, os.cidade_consumidor, os.estado_consumidor,
                       os.nota_fiscal, os.finalizada, os.cancelada, u.nome as nome_tecnico
                FROM tbl_os os
                INNER JOIN tbl_produto p ON os.produto = p.produto
                LEFT JOIN tbl_usuario u ON os.tecnico = u.usuario
                WHERE os.os = {$os}
            ";

        $res = pg_query($con, $sql);
        return pg_num_rows($res) > 0 ? pg_fetch_assoc($res) : null;
    }

    public static function finalizar($os, $posto)
    {
        $con = Db::getConnection();
        $os = intval($os);
        $posto = intval($posto);
        $sql = "UPDATE tbl_os SET finalizada = true WHERE os = {$os} AND posto = {$posto}";
        $res = pg_query($con, $sql);

        return $res
            ? ['status' => 'success', 'message' => 'OS finalizada com sucesso!']
            : ['status' => 'error', 'message' => 'Erro ao finalizar OS.'];
    }

    public static function cancelar($os, $posto)
    {
        $con = Db::getConnection();
        $sql = "UPDATE tbl_os SET cancelada = true WHERE os = $os AND posto = $posto";
        $res = pg_query($con, $sql);

        return $res
            ? ['status' => 'success', 'message' => 'OS cancelada com sucesso!']
            : ['status' => 'error', 'message' => 'Erro ao cancelar OS.'];
    }

    public static function listarTodos($posto)
    {
        $con = Db::getConnection();
        $posto = intval($posto);

        $sql = "SELECT os,
                       nome_consumidor AS cliente,
                       cpf_consumidor AS cpf,
                       to_char(data_abertura, 'DD/MM/YYYY') AS data_abertura,
                       finalizada,
                       cancelada,
                       (SELECT CONCAT(codigo, ' - ', descricao) AS codigo_descricao
                        FROM tbl_produto
                        WHERE produto = tbl_os.produto) AS produto
                FROM tbl_os
                WHERE posto = {$posto}
                ORDER BY os ASC";

        $res = pg_query($con, $sql);
        $lista = [];

        while ($row = pg_fetch_assoc($res)) {
            $row['finalizada'] = $row['finalizada'] === 't';
            $row['cancelada'] = $row['cancelada'] === 't';
            $lista[] = $row;
        }

        return $lista;
    }

    public static function buscarPorNumero($os, $posto)
    {
        $con = Db::getConnection();
        $os = intval($os);
        $posto = intval($posto);

        $sql = "
            SELECT
                os.os,
                to_char(os.data_abertura, 'DD/MM/YYYY') AS data_abertura,
                os.nome_consumidor,
                os.cpf_consumidor,
                os.cep_consumidor,
                os.endereco_consumidor,
                os.bairro_consumidor,
                os.numero_consumidor,
                os.cidade_consumidor,
                os.estado_consumidor,
                os.nota_fiscal,
                CONCAT(p.codigo, ' - ', p.descricao) AS produto_codigo_descricao,
                os.finalizada,
                os.cancelada
            FROM tbl_os os
            INNER JOIN tbl_produto p ON os.produto = p.produto
            WHERE os.os = {$os} AND os.posto = {$posto}
        ";

        $res = pg_query($con, $sql);
        if (pg_num_rows($res) === 0) {
            return null;
        }

        $dados = pg_fetch_assoc($res);

        $sqlItens = "
            SELECT i.os_item, i.peca, i.quantidade, p.codigo, p.descricao
            FROM tbl_os_item i
            INNER JOIN tbl_peca p ON p.peca = i.peca
            WHERE i.os = {$os}
            ORDER BY p.descricao
        ";

        $resItens = pg_query($con, $sqlItens);
        $itens = [];
        if ($resItens && pg_num_rows($resItens) > 0) {
            while ($row = pg_fetch_assoc($resItens)) {
                $itens[] = $row;
            }
        }

        $dados['pecas'] = $itens;

        return $dados;
    }
}
