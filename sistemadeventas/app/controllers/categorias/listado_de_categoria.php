<?php 



//pdo es el ob jeto de conexion a la base de da o s


$sql_categorias = "SELECT * FROM tb_categorias ";
$query_categorias = $pdo->prepare($sql_categorias);
$query_categorias->execute();
$categorias_datos = $query_categorias->fetchAll(PDO::FETCH_ASSOC);