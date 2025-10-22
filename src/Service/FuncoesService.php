<?php

namespace App\Service;

use App\Core\Db;
use App\Auth\Autenticador;

class FuncoesService
{
    public static function buscaNomePosto($postoId)
    {
        $con = Db::getConnection();
        $sql = "SELECT UPPER(nome) AS nome_posto
                FROM tbl_posto
                WHERE posto = $postoId
            ";
        $res = pg_query($con, $sql);

        if (pg_num_rows($res) > 0) {
            $nome_posto = pg_fetch_result($res, 0, 'nome_posto');
            return $nome_posto;
        }

        return null;
    }

    public static function usuarioMaster($usuarioId)
    {
        $con   = Db::getConnection();
        $posto = Autenticador::getPosto();

        $sql = "SELECT master
                FROM tbl_usuario
                WHERE posto = $posto
                AND usuario = $usuarioId
            ";
        $res = pg_query($con, $sql);

        if (pg_num_rows($res) > 0) {
            $master = pg_fetch_result($res, 0, 'master');

            if ($master == 't') {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }
}
