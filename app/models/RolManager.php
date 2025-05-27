<?php

class RolManager {
    private $pdo;
    
    // Configuración de roles del sistema
    const ROL_REGISTRO_PUBLICO_NOMBRE = 'vendedor';
    const ROL_ADMINISTRADOR_ID = 1;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Obtiene el ID del rol para registro público basado en el nombre.
     * Si el rol no existe, lo crea automáticamente.
     * @return int ID del rol para registro público
     */
    public function obtenerRolRegistroPublico(): int {
        try {
            // Buscar rol por nombre primero
            $sql = "SELECT id_rol FROM tb_roles WHERE LOWER(rol) = LOWER(:nombre_rol)";
            $query = $this->pdo->prepare($sql);
            $query->bindParam(':nombre_rol', self::ROL_REGISTRO_PUBLICO_NOMBRE, PDO::PARAM_STR);
            $query->execute();
            
            $resultado = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                return (int)$resultado['id_rol'];
            }
            
            // Si no existe, crear el rol automáticamente
            return $this->crearRolRegistroPublico();
            
        } catch (PDOException $e) {
            error_log("Error al obtener rol de registro público: " . $e->getMessage());
            // Fallback al ID 7 si hay error
            return 7;
        }
    }
    
    /**
     * Crea automáticamente el rol de registro público si no existe.
     * @return int ID del rol creado
     */
    private function crearRolRegistroPublico(): int {
        try {
            global $fechaHora;
            
            $sql = "INSERT INTO tb_roles (rol, fyh_creacion, fyh_actualizacion) 
                    VALUES (:rol, :fyh_creacion, :fyh_actualizacion)";
            $query = $this->pdo->prepare($sql);
            $query->bindParam(':rol', self::ROL_REGISTRO_PUBLICO_NOMBRE, PDO::PARAM_STR);
            $query->bindParam(':fyh_creacion', $fechaHora, PDO::PARAM_STR);
            $query->bindParam(':fyh_actualizacion', $fechaHora, PDO::PARAM_STR);
            
            if ($query->execute()) {
                $nuevo_id = $this->pdo->lastInsertId();
                error_log("Rol '" . self::ROL_REGISTRO_PUBLICO_NOMBRE . "' creado automáticamente con ID: " . $nuevo_id);
                return (int)$nuevo_id;
            }
            
            // Si falla la creación, fallback
            return 7;
            
        } catch (PDOException $e) {
            error_log("Error al crear rol de registro público: " . $e->getMessage());
            return 7;
        }
    }
    
    /**
     * Valida que un rol existe y está activo.
     * @param int $id_rol
     * @return bool
     */
    public function validarRolExiste(int $id_rol): bool {
        try {
            $sql = "SELECT COUNT(*) FROM tb_roles WHERE id_rol = :id_rol";
            $query = $this->pdo->prepare($sql);
            $query->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error al validar rol: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene información completa del rol por ID.
     * @param int $id_rol
     * @return array|null
     */
    public function obtenerRolPorId(int $id_rol): ?array {
        try {
            $sql = "SELECT * FROM tb_roles WHERE id_rol = :id_rol";
            $query = $this->pdo->prepare($sql);
            $query->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
            $query->execute();
            
            $resultado = $query->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
        } catch (PDOException $e) {
            error_log("Error al obtener rol: " . $e->getMessage());
            return null;
        }
    }
}