<?php
require '../../vendor/autoload.php';
use App\Core\Db;
use App\Auth\Autenticador;

Autenticador::iniciar();
$con = Db::getConnection();

$sql = "SELECT os,
			   nome_consumidor
		FROM tbl_os
		WHERE finalizada IS FALSE
		AND cancelada IS FALSE
		AND NOT EXISTS (SELECT 1 FROM tbl_agendamento WHERE tbl_agendamento.os = tbl_os.os AND tbl_agendamento.status NOT IN('CANCELADO'))
		ORDER BY os ASC;
	";
$res = pg_query($con, $sql);

$out = [];
while ($r = pg_fetch_assoc($res)) $out[] = $r;

header('Content-Type: application/json');
echo json_encode($out);
