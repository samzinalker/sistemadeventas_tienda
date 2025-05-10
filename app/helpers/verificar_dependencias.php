<?php
/**
 * Verifica si una categoría está siendo utilizada en productos
 * @param PDO $pdo Conexión a la base de datos
 * @param int $id_categoria ID de la categoría a verificar
 * @return bool true si está en uso, false si no está en uso
 */
function categoria_en_uso($pdo, $id_categoria) {
    $consulta = $pdo->prepare("SELECT COUNT(*) as total FROM tb_almacen WHERE id_categoria = :id_categoria");
    $consulta->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
    $consulta->execute();
    $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
    
    return ($resultado['total'] > 0);
}

/**
 * Verifica si un usuario es propietario de un registro
 * @param PDO $pdo Conexión a la base de datos
 * @param string $tabla Nombre de la tabla a verificar
 * @param string $campo_id Nombre del campo que contiene el ID del registro
 * @param int $id_registro ID del registro a verificar
 * @param int $id_usuario ID del usuario a verificar
 * @return bool true si es propietario, false si no lo es
 */
function es_propietario($pdo, $tabla, $campo_id, $id_registro, $id_usuario) {
    $consulta = $pdo->prepare("SELECT id_usuario FROM $tabla WHERE $campo_id = :id_registro");
    $consulta->bindParam(':id_registro', $id_registro, PDO::PARAM_INT);
    $consulta->execute();
    $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
    
    return ($resultado && $resultado['id_usuario'] == $id_usuario);
}