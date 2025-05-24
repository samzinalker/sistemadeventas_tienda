<?php

class CategoriaModel {
    private $pdo;
    // $URL no es necesaria aquí ya que las categorías no tienen recursos externos como imágenes.

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todas las categorías pertenecientes a un usuario específico.
     * @param int $id_usuario El ID del usuario logueado.
     * @return array Lista de categorías del usuario.
     */
    public function getCategoriasByUsuarioId(int $id_usuario): array {
        $sql = "SELECT id_categoria, nombre_categoria, fyh_creacion 
                FROM tb_categorias 
                WHERE id_usuario = :id_usuario 
                ORDER BY nombre_categoria ASC";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una categoría específica por su ID, verificando que pertenezca al usuario.
     * @param int $id_categoria
     * @param int $id_usuario
     * @return array|false Datos de la categoría o false si no se encuentra o no pertenece al usuario.
     */
    public function getCategoriaByIdAndUsuarioId(int $id_categoria, int $id_usuario) {
        $sql = "SELECT id_categoria, nombre_categoria, id_usuario, fyh_creacion, fyh_actualizacion 
                FROM tb_categorias 
                WHERE id_categoria = :id_categoria AND id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si un nombre de categoría ya existe para un usuario específico.
     * @param string $nombre_categoria
     * @param int $id_usuario
     * @param int|null $excludeIdCategoria Para excluir una ID de categoría (útil al actualizar).
     * @return bool True si el nombre ya existe para ese usuario, false en caso contrario.
     */
    public function nombreCategoriaExisteParaUsuario(string $nombre_categoria, int $id_usuario, ?int $excludeIdCategoria = null): bool {
        $sql = "SELECT COUNT(*) FROM tb_categorias 
                WHERE nombre_categoria = :nombre_categoria AND id_usuario = :id_usuario";
        if ($excludeIdCategoria !== null) {
            $sql .= " AND id_categoria != :id_categoria_exclude";
        }
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':nombre_categoria', $nombre_categoria, PDO::PARAM_STR);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        if ($excludeIdCategoria !== null) {
            $query->bindParam(':id_categoria_exclude', $excludeIdCategoria, PDO::PARAM_INT);
        }
        $query->execute();
        return $query->fetchColumn() > 0;
    }

    /**
     * Crea una nueva categoría para un usuario.
     * @param string $nombre_categoria
     * @param int $id_usuario
     * @param string $fyh_creacion
     * @return bool|string ID de la nueva categoría en caso de éxito, false en caso contrario.
     */
    public function crearCategoria(string $nombre_categoria, int $id_usuario, string $fyh_creacion) {
        $sql = "INSERT INTO tb_categorias (nombre_categoria, id_usuario, fyh_creacion, fyh_actualizacion) 
                VALUES (:nombre_categoria, :id_usuario, :fyh_creacion, :fyh_actualizacion)";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':nombre_categoria', $nombre_categoria, PDO::PARAM_STR);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->bindParam(':fyh_creacion', $fyh_creacion, PDO::PARAM_STR);
        $query->bindParam(':fyh_actualizacion', $fyh_creacion, PDO::PARAM_STR); // Al crear, fyh_actualizacion es igual a fyh_creacion
        
        if ($query->execute()) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Actualiza una categoría existente, verificando pertenencia.
     * @param int $id_categoria
     * @param string $nombre_categoria
     * @param int $id_usuario_sesion ID del usuario logueado para verificar pertenencia.
     * @param string $fyh_actualizacion
     * @return bool True si se actualizó, false si no o si no pertenece al usuario.
     */
    public function actualizarCategoria(int $id_categoria, string $nombre_categoria, int $id_usuario_sesion, string $fyh_actualizacion): bool {
        // Primero, verificar que la categoría pertenezca al usuario
        $categoria_existente = $this->getCategoriaByIdAndUsuarioId($id_categoria, $id_usuario_sesion);
        if (!$categoria_existente) {
            return false; // No pertenece al usuario o no existe
        }

        $sql = "UPDATE tb_categorias 
                SET nombre_categoria = :nombre_categoria, fyh_actualizacion = :fyh_actualizacion 
                WHERE id_categoria = :id_categoria AND id_usuario = :id_usuario_sesion"; // Doble check de usuario
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':nombre_categoria', $nombre_categoria, PDO::PARAM_STR);
        $query->bindParam(':fyh_actualizacion', $fyh_actualizacion, PDO::PARAM_STR);
        $query->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
        $query->bindParam(':id_usuario_sesion', $id_usuario_sesion, PDO::PARAM_INT);
        
        return $query->execute();
    }

    /**
     * Verifica si una categoría está siendo utilizada en productos del almacén por un usuario específico.
     * @param int $id_categoria
     * @param int $id_usuario
     * @return bool True si está en uso, false en caso contrario.
     */
    public function categoriaEnUsoPorUsuario(int $id_categoria, int $id_usuario): bool {
        $sql = "SELECT COUNT(*) FROM tb_almacen 
                WHERE id_categoria = :id_categoria AND id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchColumn() > 0;
    }

    /**
     * Elimina una categoría, verificando pertenencia.
     * @param int $id_categoria
     * @param int $id_usuario_sesion ID del usuario logueado para verificar pertenencia.
     * @return bool True si se eliminó, false si no o si no pertenece al usuario.
     */
    public function eliminarCategoria(int $id_categoria, int $id_usuario_sesion): bool {
        // Primero, verificar que la categoría pertenezca al usuario
        $categoria_existente = $this->getCategoriaByIdAndUsuarioId($id_categoria, $id_usuario_sesion);
        if (!$categoria_existente) {
            return false; // No pertenece al usuario o no existe
        }

        // Verificar si está en uso ANTES de intentar eliminar (ya lo haces en el controlador, pero es buena práctica aquí también)
        if ($this->categoriaEnUsoPorUsuario($id_categoria, $id_usuario_sesion)) {
            return false; // Categoría en uso, no se puede eliminar
        }

        $sql = "DELETE FROM tb_categorias 
                WHERE id_categoria = :id_categoria AND id_usuario = :id_usuario_sesion";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
        $query->bindParam(':id_usuario_sesion', $id_usuario_sesion, PDO::PARAM_INT);
        
        return $query->execute();
    }
}
?>