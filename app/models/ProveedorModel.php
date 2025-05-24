<?php
// Resumen: Modelo para gestionar las operaciones CRUD de la tabla 'tb_proveedores'.
// Proporciona métodos para crear, leer, actualizar, eliminar y verificar proveedores,
// asegurando que las operaciones estén vinculadas al usuario logueado.

class ProveedorModel {
    private $pdo;
    private $URL; // Podría ser útil si los proveedores tuvieran alguna URL asociada o imagen en el futuro.

    public function __construct(PDO $pdo, string $URL) {
        $this->pdo = $pdo;
        $this->URL = $URL;
    }

    /**
     * Obtiene todos los proveedores de un usuario específico.
     * @param int $id_usuario
     * @return array
     */
    public function getProveedoresByUsuarioId(int $id_usuario): array {
        $sql = "SELECT * FROM tb_proveedores 
                WHERE id_usuario = :id_usuario 
                ORDER BY nombre_proveedor ASC";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un proveedor específico por su ID y el ID del usuario propietario.
     * @param int $id_proveedor
     * @param int $id_usuario
     * @return array|false
     */
    public function getProveedorByIdAndUsuarioId(int $id_proveedor, int $id_usuario) {
        $sql = "SELECT * FROM tb_proveedores 
                WHERE id_proveedor = :id_proveedor AND id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_INT);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si un nombre de proveedor ya existe para un usuario (opcional, si se requiere unicidad por nombre).
     * @param string $nombre_proveedor
     * @param int $id_usuario
     * @param int|null $excludeIdProveedor
     * @return bool
     */
    public function nombreProveedorExisteParaUsuario(string $nombre_proveedor, int $id_usuario, ?int $excludeIdProveedor = null): bool {
        $sql = "SELECT COUNT(*) FROM tb_proveedores 
                WHERE nombre_proveedor = :nombre_proveedor AND id_usuario = :id_usuario";
        if ($excludeIdProveedor !== null) {
            $sql .= " AND id_proveedor != :id_proveedor_exclude";
        }
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':nombre_proveedor', $nombre_proveedor, PDO::PARAM_STR);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        if ($excludeIdProveedor !== null) {
            $query->bindParam(':id_proveedor_exclude', $excludeIdProveedor, PDO::PARAM_INT);
        }
        $query->execute();
        return $query->fetchColumn() > 0;
    }

    /**
     * Crea un nuevo proveedor.
     * @param array $datos Datos del proveedor.
     * @return string|false El ID del proveedor creado o false en error.
     */
    public function crearProveedor(array $datos): ?string {
        $sql = "INSERT INTO tb_proveedores (nombre_proveedor, celular, telefono, empresa, email, direccion, id_usuario, fyh_creacion, fyh_actualizacion) 
                VALUES (:nombre_proveedor, :celular, :telefono, :empresa, :email, :direccion, :id_usuario, :fyh_creacion, :fyh_actualizacion)";
        
        $query = $this->pdo->prepare($sql);
        // Bind todos los parámetros desde el array $datos
        foreach ($datos as $key => $value) {
            $query->bindValue(":$key", $value, ($key === 'id_usuario' ? PDO::PARAM_INT : PDO::PARAM_STR));
        }

        if ($query->execute()) {
            return $this->pdo->lastInsertId();
        }
        return null;
    }

    /**
     * Actualiza un proveedor existente.
     * @param int $id_proveedor
     * @param int $id_usuario
     * @param array $datos
     * @return bool
     */
    public function actualizarProveedor(int $id_proveedor, int $id_usuario, array $datos): bool {
        // Verificar que el proveedor pertenezca al usuario antes de actualizar
        $proveedor_actual = $this->getProveedorByIdAndUsuarioId($id_proveedor, $id_usuario);
        if (!$proveedor_actual) {
            return false; // No encontrado o no pertenece al usuario
        }

        $set_parts = [];
        foreach (array_keys($datos) as $key) {
            // Asegurarse de no intentar actualizar 'id_proveedor' o 'id_usuario' o 'fyh_creacion'
            if ($key !== 'id_proveedor' && $key !== 'id_usuario' && $key !== 'fyh_creacion' && array_key_exists($key, $proveedor_actual)) {
                 $set_parts[] = "$key = :$key";
            }
        }
        if (empty($set_parts)) return false; // Nada que actualizar o campos no válidos

        $sql = "UPDATE tb_proveedores SET " . implode(', ', $set_parts) . 
               " WHERE id_proveedor = :id_proveedor_cond AND id_usuario = :id_usuario_cond";
        
        $query = $this->pdo->prepare($sql);
        
        foreach ($datos as $key => $value) {
             if ($key !== 'id_proveedor' && $key !== 'id_usuario' && $key !== 'fyh_creacion' && array_key_exists($key, $proveedor_actual)) {
                 $query->bindValue(":$key", $value, PDO::PARAM_STR);
            }
        }
        $query->bindValue(':id_proveedor_cond', $id_proveedor, PDO::PARAM_INT);
        $query->bindValue(':id_usuario_cond', $id_usuario, PDO::PARAM_INT);
        
        return $query->execute();
    }

    /**
     * Elimina un proveedor.
     * (Considerar si un proveedor puede eliminarse si está referenciado en tb_compras)
     * @param int $id_proveedor
     * @param int $id_usuario
     * @return bool
     */
    public function eliminarProveedor(int $id_proveedor, int $id_usuario): bool {
        // Opcional: Verificar si el proveedor está en uso en tb_compras
        // $sql_check = "SELECT COUNT(*) FROM tb_compras WHERE id_proveedor = :id_proveedor";
        // $query_check = $this->pdo->prepare($sql_check);
        // $query_check->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_INT);
        // $query_check->execute();
        // if ($query_check->fetchColumn() > 0) {
        //     return false; // Proveedor en uso, no se puede eliminar
        // }

        $sql = "DELETE FROM tb_proveedores 
                WHERE id_proveedor = :id_proveedor AND id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_INT);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        
        if ($query->execute()) {
            return $query->rowCount() > 0; // Devuelve true si se eliminó al menos una fila
        }
        return false;
    }
}
?>