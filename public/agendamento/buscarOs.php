<?php
require '../../vendor/autoload.php';
use App\Core\Db;
use App\Auth\Autenticador;

Autenticador::iniciar();
$con = Db::getConnection();

$sql = "SELECT os, nome_consumidor FROM tbl_os WHERE finalizada IS FALSE AND cancelada IS FALSE ORDER BY os DESC";
$res = pg_query($con, $sql);

$out = [];
while ($r = pg_fetch_assoc($res)) $out[] = $r;

header('Content-Type: application/json');
echo json_encode($out);
