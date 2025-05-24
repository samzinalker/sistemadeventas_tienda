<?php

class RolModel {
    private $pdo;
    // No necesitamos $URL aquí ya que los roles no tienen imágenes asociadas u otros recursos externos.

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todos los roles.
     * @return array Lista de roles.
     */
    public function getAllRoles(): array {
        $sql = "SELECT id_rol, rol, fyh_creacion, fyh_actualizacion FROM tb_roles ORDER BY rol ASC";
        $query = $this->pdo->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un rol específico por su ID.
     * @param int $id_rol
     * @return array|false Datos del rol o false si no se encuentra.
     */
    public function getRolById(int $id_rol) {
        $sql = "SELECT id_rol, rol, fyh_creacion, fyh_actualizacion FROM tb_roles WHERE id_rol = :id_rol";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si un nombre de rol ya existe.
     * @param string $nombre_rol
     * @param int|null $excludeIdRol Para excluir un ID de rol (útil al actualizar para no compararse consigo mismo).
     * @return bool True si el nombre ya existe, false en caso contrario.
     */
    public function nombreRolExiste(string $nombre_rol, ?int $excludeIdRol = null): bool {
        $sql = "SELECT COUNT(*) FROM tb_roles WHERE rol = :rol";
        if ($excludeIdRol !== null) {
            $sql .= " AND id_rol != :id_rol";
        }
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':rol', $nombre_rol, PDO::PARAM_STR);
        if ($excludeIdRol !== null) {
            $query->bindParam(':id_rol', $excludeIdRol, PDO::PARAM_INT);
        }
        $query->execute();
        return $query->fetchColumn() > 0;
    }

    /**
     * Crea un nuevo rol.
     * @param string $nombre_rol Nombre del rol.
     * @param string $fyh_creacion Fecha y hora de creación.
     * @return bool True si se creó correctamente, false en caso contrario.
     */
    public function crearRol(string $nombre_rol, string $fyh_creacion): bool {
        $sql = "INSERT INTO tb_roles (rol, fyh_creacion, fyh_actualizacion) 
                VALUES (:rol, :fyh_creacion, :fyh_actualizacion)";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':rol', $nombre_rol, PDO::PARAM_STR);
        $query->bindParam(':fyh_creacion', $fyh_creacion, PDO::PARAM_STR);
        $query->bindParam(':fyh_actualizacion', $fyh_creacion, PDO::PARAM_STR); // Mismo valor al crear
        
        return $query->execute();
    }

    /**
     * Actualiza un rol existente.
     * @param int $id_rol ID del rol a actualizar.
     * @param string $nombre_rol Nuevo nombre del rol.
     * @param string $fyh_actualizacion Fecha y hora de la actualización.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     */
    public function actualizarRol(int $id_rol, string $nombre_rol, string $fyh_actualizacion): bool {
        $sql = "UPDATE tb_roles 
                SET rol = :rol, fyh_actualizacion = :fyh_actualizacion
                WHERE id_rol = :id_rol";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        $query->bindParam(':rol', $nombre_rol, PDO::PARAM_STR);
        $query->bindParam(':fyh_actualizacion', $fyh_actualizacion, PDO::PARAM_STR);
        
        return $query->execute();
    }

    /**
     * Elimina un rol por su ID.
     * ¡PRECAUCIÓN! Esto podría fallar si hay usuarios asignados a este rol y no hay ON DELETE SET NULL/CASCADE.
     * Sería mejor verificar si el rol está en uso antes de permitir la eliminación.
     * @param int $id_rol ID del rol a eliminar.
     * @return bool True si se eliminó, false en caso contrario.
     */
    public function eliminarRol(int $id_rol): bool {
        // Antes de eliminar, sería bueno verificar si algún usuario tiene este rol.
        // Si es así, la eliminación podría fallar debido a restricciones de clave foránea
        // o dejar usuarios en un estado inconsistente si la FK permite SET NULL.
        // Por simplicidad, aquí solo intentamos eliminar.
        // En una aplicación real, se añadiría lógica para manejar esto (ej. no permitir borrar roles en uso).

        $sql_check_usage = "SELECT COUNT(*) FROM tb_usuarios WHERE id_rol = :id_rol";
        $query_check = $this->pdo->prepare($sql_check_usage);
        $query_check->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        $query_check->execute();
        if ($query_check->fetchColumn() > 0) {
            // El rol está en uso, no se puede eliminar directamente sin manejar los usuarios.
            // Podrías lanzar una excepción, devolver un código de error específico, o simplemente false.
            return false; // Indicando que no se eliminó porque está en uso.
        }
        
        $sql = "DELETE FROM tb_roles WHERE id_rol = :id_rol";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        
        return $query->execute();
    }
}
?>