<?php

class UsuarioModel {
    private $pdo;
    private $URL; // Para construir rutas a imágenes de perfil si es necesario

    public function __construct(PDO $pdo, string $URL) {
        $this->pdo = $pdo;
        $this->URL = $URL;
    }

    /**
     * Obtiene todos los usuarios con sus roles.
     * @return array Lista de usuarios.
     */
    public function getAllUsuarios(): array {
        $sql = "SELECT us.id_usuario, us.nombres, us.email, us.imagen_perfil, rol.rol as nombre_rol, us.fyh_creacion, us.fyh_actualizacion
                FROM tb_usuarios as us
                INNER JOIN tb_roles as rol ON us.id_rol = rol.id_rol";
        $query = $this->pdo->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un usuario específico por su ID.
     * @param int $id_usuario
     * @return array|false Datos del usuario o false si no se encuentra.
     */
    public function getUsuarioById(int $id_usuario) {
        // MODIFICACIÓN: Añadir us.password_user al SELECT
        $sql = "SELECT us.id_usuario, us.nombres, us.email, us.password_user, us.imagen_perfil, us.id_rol, rol.rol as nombre_rol, us.fyh_creacion, us.fyh_actualizacion
                FROM tb_usuarios as us
                INNER JOIN tb_roles as rol ON us.id_rol = rol.id_rol
                WHERE us.id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un usuario por su email.
     * Útil para login y para verificar si un email ya existe.
     * @param string $email
     * @return array|false
     */
    public function getUsuarioByEmail(string $email) {
        $sql = "SELECT us.id_usuario, us.nombres, us.email, us.password_user, us.imagen_perfil, us.id_rol, rol.rol as nombre_rol
                FROM tb_usuarios as us
                INNER JOIN tb_roles as rol ON us.id_rol = rol.id_rol
                WHERE us.email = :email";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si un email ya está registrado.
     * @param string $email
     * @param int|null $excludeIdUsuario Para excluir un usuario (ej. al actualizar)
     * @return bool
     */
    public function emailExiste(string $email, ?int $excludeIdUsuario = null): bool {
        $sql = "SELECT COUNT(*) FROM tb_usuarios WHERE email = :email";
        if ($excludeIdUsuario !== null) {
            $sql .= " AND id_usuario != :id_usuario";
        }
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        if ($excludeIdUsuario !== null) {
            $query->bindParam(':id_usuario', $excludeIdUsuario, PDO::PARAM_INT);
        }
        $query->execute();
        return $query->fetchColumn() > 0;
    }

    /**
     * Crea un nuevo usuario.
     * @param string $nombres
     * @param string $email
     * @param string $password_hash Hash de la contraseña
     * @param int $id_rol
     * @param string $fyh_creacion Fecha y hora de creación
     * @return bool True si se creó correctamente, false en caso contrario.
     */
    public function crearUsuario(string $nombres, string $email, string $password_hash, int $id_rol, string $fyh_creacion): bool {
        $sql = "INSERT INTO tb_usuarios (nombres, email, password_user, id_rol, fyh_creacion, fyh_actualizacion) 
                VALUES (:nombres, :email, :password_user, :id_rol, :fyh_creacion, :fyh_actualizacion)";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':nombres', $nombres, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':password_user', $password_hash, PDO::PARAM_STR);
        $query->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        $query->bindParam(':fyh_creacion', $fyh_creacion, PDO::PARAM_STR);
        $query->bindParam(':fyh_actualizacion', $fyh_creacion, PDO::PARAM_STR); // Mismo valor al crear

        return $query->execute();
    }

    /**
     * Actualiza los datos de un usuario (sin incluir contraseña o imagen).
     * @param int $id_usuario
     * @param string $nombres
     * @param string $email
     * @param int $id_rol
     * @param string $fyh_actualizacion
     * @return bool
     */
    public function actualizarUsuario(int $id_usuario, string $nombres, string $email, int $id_rol, string $fyh_actualizacion): bool {
        $sql = "UPDATE tb_usuarios 
                SET nombres = :nombres, email = :email, id_rol = :id_rol, fyh_actualizacion = :fyh_actualizacion
                WHERE id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':nombres', $nombres, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        $query->bindParam(':fyh_actualizacion', $fyh_actualizacion, PDO::PARAM_STR);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        
        return $query->execute();
    }

    /**
     * Actualiza la contraseña de un usuario.
     * @param int $id_usuario
     * @param string $password_hash
     * @param string $fyh_actualizacion
     * @return bool
     */
    public function actualizarPassword(int $id_usuario, string $password_hash, string $fyh_actualizacion): bool {
        $sql = "UPDATE tb_usuarios 
                SET password_user = :password_user, fyh_actualizacion = :fyh_actualizacion 
                WHERE id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':password_user', $password_hash, PDO::PARAM_STR);
        $query->bindParam(':fyh_actualizacion', $fyh_actualizacion, PDO::PARAM_STR);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

        return $query->execute();
    }
    
    /**
     * Actualiza la imagen de perfil de un usuario.
     * @param int $id_usuario
     * @param string $nombre_imagen
     * @param string $fyh_actualizacion
     * @return bool
     */
    public function actualizarImagenPerfil(int $id_usuario, string $nombre_imagen, string $fyh_actualizacion): bool {
        $sql = "UPDATE tb_usuarios 
                SET imagen_perfil = :imagen_perfil, fyh_actualizacion = :fyh_actualizacion 
                WHERE id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':imagen_perfil', $nombre_imagen, PDO::PARAM_STR);
        $query->bindParam(':fyh_actualizacion', $fyh_actualizacion, PDO::PARAM_STR);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

        return $query->execute();
    }
    
    public function actualizarFechaHoraLogin(int $id_usuario, string $fyh_actualizacion): bool {
        $sql = "UPDATE tb_usuarios SET fyh_actualizacion = :fyh_actualizacion WHERE id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':fyh_actualizacion', $fyh_actualizacion, PDO::PARAM_STR);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        return $query->execute();
    }

    /**
     * Elimina un usuario por su ID.
     * @param int $id_usuario
     * @return bool
     */
    public function eliminarUsuario(int $id_usuario): bool {
        // Considerar restricciones de clave foránea o lógica de eliminación suave si es necesario
        $sql = "DELETE FROM tb_usuarios WHERE id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        
        return $query->execute();
    }
}

?>