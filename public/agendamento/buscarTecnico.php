<?php
require '../../vendor/autoload.php';
use App\Core\Db;
use App\Auth\Autenticador;

Autenticador::iniciar();
$con = Db::getConnection();

$sql = "SELECT usuario, nome FROM tbl_usuario WHERE tecnico is true AND ativo is true ORDER BY nome";
$res = pg_query($con, $sql);

$out = [];
while ($r = pg_fetch_assoc($res)) $out[] = $r;

header('Content-Type: application/json');
echo json_encode($out);
