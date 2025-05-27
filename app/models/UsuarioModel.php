<?php

class UsuarioModel {
    private $pdo;
    private $URL;

    public function __construct(PDO $pdo, string $URL) {
        $this->pdo = $pdo;
        $this->URL = $URL;
    }

    /**
     * Obtiene todos los usuarios activos con sus roles.
     * @param bool $incluirEliminados Si incluir usuarios eliminados
     * @return array Lista de usuarios.
     */
    public function getAllUsuarios(bool $incluirEliminados = false): array {
        $whereClause = $incluirEliminados ? "" : "WHERE us.estado = 'activo'";
        
        $sql = "SELECT us.id_usuario, us.nombres, us.usuario, us.email, us.imagen_perfil, us.estado, 
                       us.fecha_eliminacion, elim.nombres as eliminado_por_nombre,
                       rol.rol as nombre_rol, us.fyh_creacion, us.fyh_actualizacion
                FROM tb_usuarios as us
                INNER JOIN tb_roles as rol ON us.id_rol = rol.id_rol
                LEFT JOIN tb_usuarios as elim ON us.eliminado_por = elim.id_usuario
                {$whereClause}
                ORDER BY us.estado ASC, us.fyh_creacion DESC";
        
        $query = $this->pdo->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un usuario específico por su ID (solo activos por defecto).
     * @param int $id_usuario
     * @param bool $incluirEliminados Si buscar también entre eliminados
     * @return array|false Datos del usuario o false si no se encuentra.
     */
    public function getUsuarioById(int $id_usuario, bool $incluirEliminados = false) {
        $whereClause = $incluirEliminados ? 
            "WHERE us.id_usuario = :id_usuario" : 
            "WHERE us.id_usuario = :id_usuario AND us.estado = 'activo'";
            
        $sql = "SELECT us.id_usuario, us.nombres, us.usuario, us.email, us.password_user, us.imagen_perfil, 
                       us.id_rol, us.estado, us.fecha_eliminacion, us.eliminado_por,
                       rol.rol as nombre_rol, us.fyh_creacion, us.fyh_actualizacion
                FROM tb_usuarios as us
                INNER JOIN tb_roles as rol ON us.id_rol = rol.id_rol
                {$whereClause}";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un usuario por su nombre de usuario (para login).
     * @param string $usuario
     * @return array|false
     */
    public function getUsuarioByUsername(string $usuario) {
        $sql = "SELECT us.id_usuario, us.nombres, us.usuario, us.email, us.password_user, us.imagen_perfil, 
                       us.id_rol, rol.rol as nombre_rol
                FROM tb_usuarios as us
                INNER JOIN tb_roles as rol ON us.id_rol = rol.id_rol
                WHERE us.usuario = :usuario AND us.estado = 'activo'";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un usuario por su email (solo para información/contacto).
     * @param string $email
     * @return array|false
     */
    public function getUsuarioByEmail(string $email) {
        $sql = "SELECT us.id_usuario, us.nombres, us.usuario, us.email, us.password_user, us.imagen_perfil, 
                       us.id_rol, rol.rol as nombre_rol
                FROM tb_usuarios as us
                INNER JOIN tb_roles as rol ON us.id_rol = rol.id_rol
                WHERE us.email = :email AND us.estado = 'activo'";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si un nombre de usuario ya existe.
     * @param string $usuario
     * @param int|null $excludeIdUsuario Para excluir un usuario (ej. al actualizar)
     * @return bool
     */
    public function usuarioExiste(string $usuario, ?int $excludeIdUsuario = null): bool {
        $sql = "SELECT COUNT(*) FROM tb_usuarios WHERE usuario = :usuario AND estado = 'activo'";
        if ($excludeIdUsuario !== null) {
            $sql .= " AND id_usuario != :id_usuario";
        }
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        if ($excludeIdUsuario !== null) {
            $query->bindParam(':id_usuario', $excludeIdUsuario, PDO::PARAM_INT);
        }
        $query->execute();
        return $query->fetchColumn() > 0;
    }

    /**
     * Verifica si un email ya está registrado entre usuarios activos.
     * @param string $email
     * @param int|null $excludeIdUsuario Para excluir un usuario (ej. al actualizar)
     * @return bool
     */
    public function emailExiste(string $email, ?int $excludeIdUsuario = null): bool {
        $sql = "SELECT COUNT(*) FROM tb_usuarios WHERE email = :email AND estado = 'activo'";
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
     * Obtiene el rol por defecto para registro público de manera segura.
     * @return int ID del rol para registro público
     */
    public function obtenerRolRegistroPublicoSeguro(): int {
        try {
            // Primero intentar con la configuración
            $id_rol_config = defined('ROL_REGISTRO_PUBLICO') ? ROL_REGISTRO_PUBLICO : 7;
            
            // Validar que el rol existe
            if ($this->validarRolExiste($id_rol_config)) {
                return $id_rol_config;
            }
            
            // Si no existe, buscar rol "vendedor" por nombre
            $sql = "SELECT id_rol FROM tb_roles WHERE LOWER(rol) = 'vendedor' LIMIT 1";
            $query = $this->pdo->prepare($sql);
            $query->execute();
            $resultado = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                return (int)$resultado['id_rol'];
            }
            
            // Como último recurso, crear el rol
            return $this->crearRolVendedorSiNoExiste();
            
        } catch (PDOException $e) {
            error_log("Error al obtener rol de registro público: " . $e->getMessage());
            return 7; // Fallback absoluto
        }
    }

    /**
     * Valida que un rol existe en la base de datos.
     * @param int $id_rol
     * @return bool
     */
    private function validarRolExiste(int $id_rol): bool {
        try {
            $sql = "SELECT COUNT(*) FROM tb_roles WHERE id_rol = :id_rol";
            $query = $this->pdo->prepare($sql);
            $query->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Crea el rol "vendedor" si no existe (para casos extremos).
     * @return int ID del rol creado o 7 como fallback
     */
    private function crearRolVendedorSiNoExiste(): int {
        try {
            global $fechaHora; // Asegúrate que $fechaHora esté disponible globalmente o pásala como argumento
            
            $sql = "INSERT INTO tb_roles (rol, fyh_creacion, fyh_actualizacion) 
                    VALUES ('vendedor', :fyh_creacion, :fyh_actualizacion)";
            $query = $this->pdo->prepare($sql);
            $current_datetime = $fechaHora ?? date('Y-m-d H:i:s');
            $query->bindParam(':fyh_creacion', $current_datetime, PDO::PARAM_STR);
            $query->bindParam(':fyh_actualizacion', $current_datetime, PDO::PARAM_STR);
            
            if ($query->execute()) {
                $nuevo_id = $this->pdo->lastInsertId();
                error_log("Rol 'vendedor' creado automáticamente para registro público con ID: " . $nuevo_id);
                return (int)$nuevo_id;
            }
            
            return 7;
            
        } catch (PDOException $e) {
            error_log("Error al crear rol vendedor automáticamente: " . $e->getMessage());
            return 7;
        }
    }

    /**
     * Crea un nuevo usuario con validación robusta de rol.
     * @param string $nombres
     * @param string $usuario Nombre de usuario para login
     * @param string $email Email para contacto/información
     * @param string $password_hash Hash de la contraseña
     * @param int|null $id_rol ID del rol (si es null, usa rol de registro público)
     * @param string|null $fyh_creacion Fecha y hora de creación
     * @return bool True si se creó correctamente, false en caso contrario.
     */
    public function crearUsuario(string $nombres, string $usuario, string $email, string $password_hash, ?int $id_rol = null, ?string $fyh_creacion = null): bool {
        if ($id_rol === null) {
            $id_rol = $this->obtenerRolRegistroPublicoSeguro();
        }
        
        if (!$this->validarRolExiste($id_rol)) {
            error_log("Intento de crear usuario con rol inexistente ID: $id_rol");
            $id_rol = $this->obtenerRolRegistroPublicoSeguro(); 
        }
        
        global $fechaHora; // Asegúrate que $fechaHora esté disponible
        $current_datetime = $fyh_creacion ?? $fechaHora ?? date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO tb_usuarios (nombres, usuario, email, password_user, id_rol, estado, fyh_creacion, fyh_actualizacion) 
                VALUES (:nombres, :usuario, :email, :password_user, :id_rol, 'activo', :fyh_creacion, :fyh_actualizacion)";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':nombres', $nombres, PDO::PARAM_STR);
        $query->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':password_user', $password_hash, PDO::PARAM_STR);
        $query->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        $query->bindParam(':fyh_creacion', $current_datetime, PDO::PARAM_STR);
        $query->bindParam(':fyh_actualizacion', $current_datetime, PDO::PARAM_STR);

        return $query->execute();
    }

    /**
     * Actualiza los datos de un usuario activo.
     * Acepta un array asociativo para mayor flexibilidad.
     * @param int $id_usuario
     * @param array $datos_actualizar Array asociativo con los campos a actualizar y sus nuevos valores.
     *          Ej: ['nombres' => 'Nuevo Nombre', 'email' => 'nuevo@email.com', 'usuario' => 'nuevousername']
     *          El campo 'fyh_actualizacion' se manejará automáticamente si no se provee.
     *          El campo 'id_rol' también puede ser incluido si se permite su cambio.
     * @return bool
     */
    public function actualizarUsuario(int $id_usuario, array $datos_actualizar): bool {
        $campos_sql = [];
        $params_bind = [];

        // Campos permitidos para actualización y su tipo PDO (si es necesario especificar)
        $campos_permitidos = [
            'nombres' => PDO::PARAM_STR,
            'usuario' => PDO::PARAM_STR, // Username para login
            'email'   => PDO::PARAM_STR, // Email de contacto
            'id_rol'  => PDO::PARAM_INT
        ];

        foreach ($campos_permitidos as $campo => $tipo) {
            if (array_key_exists($campo, $datos_actualizar)) {
                $campos_sql[] = "{$campo} = :{$campo}";
                $params_bind[":{$campo}"] = $datos_actualizar[$campo];
            }
        }

        // Siempre actualizar fyh_actualizacion
        global $fechaHora; // Asegúrate que $fechaHora esté disponible
        $current_datetime = $datos_actualizar['fyh_actualizacion'] ?? $fechaHora ?? date('Y-m-d H:i:s');
        $campos_sql[] = "fyh_actualizacion = :fyh_actualizacion";
        $params_bind[':fyh_actualizacion'] = $current_datetime;

        if (empty($campos_sql)) {
            return false; // No hay campos válidos para actualizar
        }

        $sql_set_clause = implode(', ', $campos_sql);
        
        $sql = "UPDATE tb_usuarios 
                SET {$sql_set_clause}
                WHERE id_usuario = :id_usuario AND estado = 'activo'";
        
        $query = $this->pdo->prepare($sql);
        
        // Bind de los parámetros dinámicos
        foreach ($params_bind as $placeholder => $valor) {
            // Determinar el tipo PDO si no se especificó antes o usar STR por defecto
            $tipo_pdo = PDO::PARAM_STR; // Por defecto
            if ($placeholder === ':id_rol') $tipo_pdo = PDO::PARAM_INT;
            // Para otros campos, podrías tener un mapeo más explícito si fuera necesario
            
            $query->bindValue($placeholder, $valor, $tipo_pdo);
        }
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        
        return $query->execute();
    }

    /**
     * Actualiza la contraseña de un usuario activo.
     * @param int $id_usuario
     * @param string $password_hash
     * @param string $fyh_actualizacion
     * @return bool
     */
    public function actualizarPassword(int $id_usuario, string $password_hash, string $fyh_actualizacion): bool {
        $sql = "UPDATE tb_usuarios 
                SET password_user = :password_user, fyh_actualizacion = :fyh_actualizacion 
                WHERE id_usuario = :id_usuario AND estado = 'activo'";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':password_user', $password_hash, PDO::PARAM_STR);
        $query->bindParam(':fyh_actualizacion', $fyh_actualizacion, PDO::PARAM_STR);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

        return $query->execute();
    }
    
    /**
     * Actualiza la imagen de perfil de un usuario activo.
     * @param int $id_usuario
     * @param string $nombre_imagen
     * @param string $fyh_actualizacion
     * @return bool
     */
    public function actualizarImagenPerfil(int $id_usuario, string $nombre_imagen, string $fyh_actualizacion): bool {
        $sql = "UPDATE tb_usuarios 
                SET imagen_perfil = :imagen_perfil, fyh_actualizacion = :fyh_actualizacion 
                WHERE id_usuario = :id_usuario AND estado = 'activo'";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':imagen_perfil', $nombre_imagen, PDO::PARAM_STR);
        $query->bindParam(':fyh_actualizacion', $fyh_actualizacion, PDO::PARAM_STR);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

        return $query->execute();
    }
    
    /**
     * Actualiza la fecha y hora de último login de un usuario activo.
     * @param int $id_usuario
     * @param string $fyh_actualizacion
     * @return bool
     */
    public function actualizarFechaHoraLogin(int $id_usuario, string $fyh_actualizacion): bool {
        $sql = "UPDATE tb_usuarios 
                SET fyh_actualizacion = :fyh_actualizacion 
                WHERE id_usuario = :id_usuario AND estado = 'activo'";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':fyh_actualizacion', $fyh_actualizacion, PDO::PARAM_STR);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        return $query->execute();
    }

    /**
     * Verifica las dependencias de un usuario antes de eliminar.
     * @param int $id_usuario
     * @return array Información detallada de las dependencias
     */
    public function verificarDependenciasUsuario(int $id_usuario): array {
        $dependencias = [];
        
        // Verificar categorías
        $sql = "SELECT COUNT(*) as total FROM tb_categorias WHERE id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        $total_categorias = $query->fetchColumn();
        if ($total_categorias > 0) {
            $dependencias['categorias'] = $total_categorias;
        }
        
        // Verificar productos en almacén
        $sql = "SELECT COUNT(*) as total FROM tb_almacen WHERE id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        $total_productos = $query->fetchColumn();
        if ($total_productos > 0) {
            $dependencias['productos'] = $total_productos;
        }
        
        // Verificar ventas
        $sql = "SELECT COUNT(*) as total FROM tb_ventas WHERE id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        $total_ventas = $query->fetchColumn();
        if ($total_ventas > 0) {
            $dependencias['ventas'] = $total_ventas;
        }
        
        // Verificar clientes
        $sql = "SELECT COUNT(*) as total FROM tb_clientes WHERE id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        $total_clientes = $query->fetchColumn();
        if ($total_clientes > 0) {
            $dependencias['clientes'] = $total_clientes;
        }
        
        // Verificar compras (si existe la tabla)
        try {
            $sql = "SELECT COUNT(*) as total FROM tb_compras WHERE id_usuario = :id_usuario";
            $query = $this->pdo->prepare($sql);
            $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $query->execute();
            $total_compras = $query->fetchColumn();
            if ($total_compras > 0) {
                $dependencias['compras'] = $total_compras;
            }
        } catch (PDOException $e) {
            // Tabla no existe o error, ignorar
        }
        
        return $dependencias;
    }

    /**
     * Elimina un usuario usando soft delete (marca como eliminado).
     * @param int $id_usuario Usuario a eliminar
     * @param int $id_usuario_elimina Usuario que realiza la eliminación
     * @param string $fecha_eliminacion Fecha y hora de eliminación
     * @return array Resultado de la operación
     */
    public function eliminarUsuarioSoft(int $id_usuario, int $id_usuario_elimina, string $fecha_eliminacion): array {
        try {
            // Verificar que el usuario existe y está activo
            $usuario = $this->getUsuarioById($id_usuario, false);
            if (!$usuario) {
                return [
                    'success' => false,
                    'message' => 'El usuario no existe o ya está eliminado.',
                    'dependencias' => []
                ];
            }
            
            // Verificar dependencias para información
            $dependencias = $this->verificarDependenciasUsuario($id_usuario);
            
            // Marcar usuario como eliminado
            $sql = "UPDATE tb_usuarios 
                    SET estado = 'eliminado', 
                        fecha_eliminacion = :fecha_eliminacion,
                        eliminado_por = :eliminado_por,
                        fyh_actualizacion = :fyh_actualizacion
                    WHERE id_usuario = :id_usuario AND estado = 'activo'";
            
            $query = $this->pdo->prepare($sql);
            $query->bindParam(':fecha_eliminacion', $fecha_eliminacion, PDO::PARAM_STR);
            $query->bindParam(':eliminado_por', $id_usuario_elimina, PDO::PARAM_INT);
            $query->bindParam(':fyh_actualizacion', $fecha_eliminacion, PDO::PARAM_STR); // fyh_actualizacion se actualiza a la fecha de eliminación
            $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            
            $resultado = $query->execute();
            
            if ($resultado) {
                $mensaje = "Usuario '{$usuario['nombres']}' (@{$usuario['usuario']}) eliminado correctamente.";
                if (!empty($dependencias)) {
                    $deps = [];
                    foreach ($dependencias as $tipo => $cantidad) {
                        $deps[] = "$cantidad " . ucfirst($tipo);
                    }
                    $mensaje .= " Sus datos asociados (" . implode(', ', $deps) . ") se mantienen para auditoría.";
                }
                
                return [
                    'success' => true,
                    'message' => $mensaje,
                    'dependencias' => $dependencias,
                    'usuario_eliminado' => $usuario
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al marcar el usuario como eliminado.',
                    'dependencias' => $dependencias
                ];
            }
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage(),
                'dependencias' => []
            ];
        }
    }

    /**
     * Restaura un usuario eliminado.
     * @param int $id_usuario Usuario a restaurar
     * @param string $fyh_restauracion Fecha y hora de restauración
     * @return array Resultado de la operación
     */
    public function restaurarUsuario(int $id_usuario, string $fyh_restauracion): array {
        try {
            // Verificar que el usuario existe y está eliminado
            $usuario = $this->getUsuarioById($id_usuario, true); // Incluir eliminados para obtener sus datos
            if (!$usuario || $usuario['estado'] !== 'eliminado') {
                return [
                    'success' => false,
                    'message' => 'El usuario no existe o no está eliminado.'
                ];
            }
            
            $sql = "UPDATE tb_usuarios 
                    SET estado = 'activo',
                        fecha_eliminacion = NULL,
                        eliminado_por = NULL,
                        fyh_actualizacion = :fyh_restauracion
                    WHERE id_usuario = :id_usuario";
            
            $query = $this->pdo->prepare($sql);
            $query->bindParam(':fyh_restauracion', $fyh_restauracion, PDO::PARAM_STR);
            $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            
            $resultado = $query->execute();
            
            return [
                'success' => $resultado,
                'message' => $resultado ? 
                    "Usuario '{$usuario['nombres']}' (@{$usuario['usuario']}) restaurado correctamente." : 
                    'Error al restaurar el usuario.',
                'usuario_restaurado' => $usuario
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina un usuario creando respaldo completo y eliminando físicamente todos los datos.
     * @param int $id_usuario Usuario a eliminar
     * @param int $id_usuario_elimina Usuario que realiza la eliminación
     * @param string $fecha_eliminacion Fecha y hora de eliminación
     * @return array Resultado de la operación
     */
    public function eliminarUsuarioConRespaldoCompleto(int $id_usuario, int $id_usuario_elimina, string $fecha_eliminacion): array {
        // La dependencia BackupUsuarioModel debe estar disponible.
        // require_once __DIR__ . '/BackupUsuarioModel.php'; // O manejar autoloading
        
        try {
            $this->pdo->beginTransaction();
            
            // 1. Crear respaldo completo (asumiendo que BackupUsuarioModel está cargado)
            if (class_exists('BackupUsuarioModel')) {
                $backupModel = new BackupUsuarioModel($this->pdo, $this->URL);
                $resultadoRespaldo = $backupModel->crearRespaldoCompletoUsuario($id_usuario, $id_usuario_elimina, $fecha_eliminacion);
                
                if (!$resultadoRespaldo['success']) {
                    $this->pdo->rollBack();
                    return [
                        'success' => false,
                        'message' => 'Error al crear respaldo: ' . $resultadoRespaldo['message'],
                        'backup_info' => null
                    ];
                }
            } else {
                // No se puede hacer backup, proceder con cautela o abortar.
                // Por ahora, se omite el backup si la clase no existe y se procede a eliminar.
                // Considerar lanzar un error o log si el backup es mandatorio.
                error_log("Clase BackupUsuarioModel no encontrada. Eliminación sin respaldo completo.");
                $resultadoRespaldo = ['success' => false, 'message' => 'Backup no realizado.', 'backup_folder' => null, 'datos_respaldados' => []];
            }
            
            
            // 2. Eliminar todos los datos relacionados (CASCADE DELETE manual)
            // IMPORTANTE: El orden importa debido a las relaciones de claves foráneas
            
            // Primero eliminar detalles de ventas (depende de ventas y productos)
            $sql_dv = "DELETE dv FROM tb_detalle_ventas dv 
                       INNER JOIN tb_ventas v ON dv.id_venta = v.id_venta 
                       WHERE v.id_usuario = :id_usuario";
            $query_dv = $this->pdo->prepare($sql_dv);
            $query_dv->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $query_dv->execute();
            
            // Eliminar ventas del usuario
            $sql_v = "DELETE FROM tb_ventas WHERE id_usuario = :id_usuario";
            $query_v = $this->pdo->prepare($sql_v);
            $query_v->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $query_v->execute();
            
            // Eliminar productos del almacén
            $sql_a = "DELETE FROM tb_almacen WHERE id_usuario = :id_usuario";
            $query_a = $this->pdo->prepare($sql_a);
            $query_a->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $query_a->execute();
            
            // Eliminar clientes
            $sql_cl = "DELETE FROM tb_clientes WHERE id_usuario = :id_usuario";
            $query_cl = $this->pdo->prepare($sql_cl);
            $query_cl->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $query_cl->execute();
            
            // Eliminar categorías
            $sql_cat = "DELETE FROM tb_categorias WHERE id_usuario = :id_usuario";
            $query_cat = $this->pdo->prepare($sql_cat);
            $query_cat->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $query_cat->execute();
            
            // Intentar eliminar compras si existe la tabla
            try {
                $sql_co = "DELETE FROM tb_compras WHERE id_usuario = :id_usuario";
                $query_co = $this->pdo->prepare($sql_co);
                $query_co->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $query_co->execute();
            } catch (PDOException $e) {
                // Tabla no existe, continuar
            }
            
            // 3. Finalmente eliminar el usuario
            $sql_u = "DELETE FROM tb_usuarios WHERE id_usuario = :id_usuario";
            $query_u = $this->pdo->prepare($sql_u);
            $query_u->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $resultadoEliminacion = $query_u->execute();
            
            if (!$resultadoEliminacion) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el usuario de la base de datos.',
                    'backup_info' => $resultadoRespaldo
                ];
            }
            
            $this->pdo->commit();
            
            $backup_message = $resultadoRespaldo['success'] ? "Respaldo creado en: {$resultadoRespaldo['backup_folder']}" : "Respaldo no realizado: {$resultadoRespaldo['message']}";
            return [
                'success' => true,
                'message' => "Usuario eliminado completamente. {$backup_message}",
                'backup_info' => $resultadoRespaldo,
                'datos_eliminados' => $resultadoRespaldo['datos_respaldados'] ?? []
            ];
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage(),
                'backup_info' => null
            ];
        }
    }

    /**
     * Elimina un usuario (mantiene compatibilidad).
     * Por defecto ahora usa eliminación completa con respaldo.
     * @param int $id_usuario
     * @return bool
     */
    public function eliminarUsuario(int $id_usuario): bool {
        global $fechaHora; // Asegúrate que $fechaHora esté disponible
        // Asumir que el usuario que elimina es el admin (ID 1) si no se especifica de otra forma.
        // Esto debería ser manejado por el controlador que llama a esta función.
        $id_usuario_admin = $_SESSION['id_usuario'] ?? 1; 
        $current_datetime = $fechaHora ?? date('Y-m-d H:i:s');
        $resultado = $this->eliminarUsuarioConRespaldoCompleto($id_usuario, $id_usuario_admin, $current_datetime);
        return $resultado['success'];
    }

    /**
     * Obtiene estadísticas de usuarios.
     * @return array Estadísticas generales
     */
    public function getEstadisticasUsuarios(): array {
        $stats = [];
        
        // Total usuarios activos
        $sql_act = "SELECT COUNT(*) FROM tb_usuarios WHERE estado = 'activo'";
        $query_act = $this->pdo->prepare($sql_act);
        $query_act->execute();
        $stats['activos'] = $query_act->fetchColumn();
        
        // Total usuarios eliminados
        $sql_elim = "SELECT COUNT(*) FROM tb_usuarios WHERE estado = 'eliminado'";
        $query_elim = $this->pdo->prepare($sql_elim);
        $query_elim->execute();
        $stats['eliminados'] = $query_elim->fetchColumn();
        
        // Usuarios por rol (solo activos)
        $sql_rol = "SELECT r.rol, COUNT(u.id_usuario) as total 
                    FROM tb_roles r 
                    LEFT JOIN tb_usuarios u ON r.id_rol = u.id_rol AND u.estado = 'activo'
                    GROUP BY r.id_rol, r.rol";
        $query_rol = $this->pdo->prepare($sql_rol);
        $query_rol->execute();
        $stats['por_rol'] = $query_rol->fetchAll(PDO::FETCH_ASSOC);
        
        // Fecha del último usuario registrado
        $sql_reg = "SELECT MAX(fyh_creacion) as ultimo_registro FROM tb_usuarios WHERE estado = 'activo'";
        $query_reg = $this->pdo->prepare($sql_reg);
        $query_reg->execute();
        $stats['ultimo_registro'] = $query_reg->fetchColumn();
        
        // Fecha de la última actividad
        $sql_last_act = "SELECT MAX(fyh_actualizacion) as ultima_actividad FROM tb_usuarios WHERE estado = 'activo'";
        $query_last_act = $this->pdo->prepare($sql_last_act);
        $query_last_act->execute();
        $stats['ultima_actividad'] = $query_last_act->fetchColumn();
        
        return $stats;
    }

    /**
     * Obtiene el último ID de usuario insertado.
     * Útil después de crear un usuario para obtener su ID.
     * @return int|false
     */
    public function getUltimoIdUsuario() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Verifica si un usuario está activo.
     * @param int $id_usuario
     * @return bool
     */
    public function usuarioEstaActivo(int $id_usuario): bool {
        $sql = "SELECT COUNT(*) FROM tb_usuarios WHERE id_usuario = :id_usuario AND estado = 'activo'";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchColumn() > 0;
    }

    /**
     * Busca usuarios por término de búsqueda.
     * @param string $termino Término a buscar en nombre, usuario o email
     * @param bool $soloActivos Si buscar solo usuarios activos
     * @return array
     */
    public function buscarUsuarios(string $termino, bool $soloActivos = true): array {
        $whereClause = $soloActivos ? "WHERE us.estado = 'activo' AND" : "WHERE";
        
        $sql = "SELECT us.id_usuario, us.nombres, us.usuario, us.email, us.imagen_perfil, us.estado,
                       rol.rol as nombre_rol, us.fyh_creacion, us.fyh_actualizacion
                FROM tb_usuarios as us
                INNER JOIN tb_roles as rol ON us.id_rol = rol.id_rol
                {$whereClause} (us.nombres LIKE :termino OR us.usuario LIKE :termino OR us.email LIKE :termino)
                ORDER BY us.nombres ASC";
        
        $query = $this->pdo->prepare($sql);
        $terminoBusqueda = "%{$termino}%";
        $query->bindParam(':termino', $terminoBusqueda, PDO::PARAM_STR);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene usuarios por rol.
     * @param int $id_rol
     * @param bool $soloActivos
     * @return array
     */
    public function getUsuariosPorRol(int $id_rol, bool $soloActivos = true): array {
        $whereClause = $soloActivos ? "WHERE us.estado = 'activo' AND us.id_rol = :id_rol" : "WHERE us.id_rol = :id_rol";
        
        $sql = "SELECT us.id_usuario, us.nombres, us.usuario, us.email, us.imagen_perfil, us.estado,
                       rol.rol as nombre_rol, us.fyh_creacion, us.fyh_actualizacion
                FROM tb_usuarios as us
                INNER JOIN tb_roles as rol ON us.id_rol = rol.id_rol
                {$whereClause}
                ORDER BY us.nombres ASC";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza el estado de un usuario.
     * @param int $id_usuario
     * @param string $estado 'activo' o 'eliminado'
     * @param string $fyh_actualizacion
     * @return bool
     */
    public function actualizarEstadoUsuario(int $id_usuario, string $estado, string $fyh_actualizacion): bool {
        $sql = "UPDATE tb_usuarios 
                SET estado = :estado, fyh_actualizacion = :fyh_actualizacion
                WHERE id_usuario = :id_usuario";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':estado', $estado, PDO::PARAM_STR);
        $query->bindParam(':fyh_actualizacion', $fyh_actualizacion, PDO::PARAM_STR);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        
        return $query->execute();
    }

    /**
     * Valida credenciales de usuario para cambios sensibles.
     * @param int $id_usuario
     * @param string $password_actual
     * @return bool
     */
    public function validarCredenciales(int $id_usuario, string $password_actual): bool {
        $usuario = $this->getUsuarioById($id_usuario, false);
        if (!$usuario) {
            return false;
        }
        
        return password_verify($password_actual, $usuario['password_user']);
    }

    /**
     * Genera un nombre de usuario único basado en el nombre.
     * @param string $nombres
     * @return string
     */
    public function generarUsuarioUnico(string $nombres): string {
        // Limpiar y formatear el nombre
        $usuario_base = strtolower(trim($nombres));
        $usuario_base = preg_replace('/[^a-zA-Z0-9]/', '', $usuario_base);
        $usuario_base = substr($usuario_base, 0, 20); // Limitar longitud
        
        // Si está vacío, usar un nombre genérico
        if (empty($usuario_base)) {
            $usuario_base = 'usuario';
        }
        
        $usuario_final = $usuario_base;
        $contador = 1;
        
        // Buscar un nombre disponible
        while ($this->usuarioExiste($usuario_final)) {
            $usuario_final = $usuario_base . $contador;
            $contador++;
            
            // Evitar bucle infinito
            if ($contador > 1000) {
                $usuario_final = $usuario_base . time(); // Usar timestamp como último recurso
                break;
            }
        }
        
        return $usuario_final;
    }

    /**
     * Obtiene información básica del usuario para sesión.
     * @param int $id_usuario
     * @return array|null
     */
    public function getUsuarioParaSesion(int $id_usuario): ?array {
        $sql = "SELECT us.id_usuario, us.nombres, us.usuario, us.email, us.imagen_perfil,
                       rol.rol as nombre_rol, rol.id_rol
                FROM tb_usuarios as us
                INNER JOIN tb_roles as rol ON us.id_rol = rol.id_rol
                WHERE us.id_usuario = :id_usuario AND us.estado = 'activo'";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }
}

?>