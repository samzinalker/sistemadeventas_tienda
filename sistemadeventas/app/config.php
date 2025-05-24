<?php
if (!defined('SERVIDOR')) define('SERVIDOR','localhost');
if (!defined('USUARIO')) define('USUARIO','root');
if (!defined('PASSWORD')) define('PASSWORD','');
if (!defined('BD')) define('BD','sistemadeventas');

$servidor = "mysql:dbname=".BD.";host=".SERVIDOR;

try{
    $pdo = new PDO($servidor, USUARIO, PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES utf8"));
}catch(PDOException $e){
    echo "Error al conectar a la base de datos";
}

$URL = "http://localhost/sistemadeventas";

date_default_timezone_set('America/Guayaquil');
$fechaHora = date('Y-m-d H:i:s');