<?php
$senha = "pass"; // coloque a senha que deseja
$hash  = password_hash($senha, PASSWORD_DEFAULT);

echo "Senha: $senha\n";
echo "Hash:  $hash\n";
