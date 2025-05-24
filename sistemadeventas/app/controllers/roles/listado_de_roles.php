<?php 



//pdo es el ob jeto de conexion a la base de da o s



$sql_roles = "SELECT * FROM tb_roles ";
$query_roles = $pdo->prepare($sql_roles);
$query_roles->execute();
$roles_datos = $query_roles->fetchAll(PDO::FETCH_ASSOC);