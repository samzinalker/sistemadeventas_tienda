<?php

class BackupUsuarioModel {
    private $pdo;
    private $URL;
    private $backupBasePath;

    public function __construct(PDO $pdo, string $URL) {
        $this->pdo = $pdo;
        $this->URL = $URL;
        $this->backupBasePath = __DIR__ . '/../../backups/usuarios_eliminados/';
        
        // Crear directorio de backups si no existe
        if (!is_dir($this->backupBasePath)) {
            mkdir($this->backupBasePath, 0755, true);
        }
    }

    /**
     * Crea un respaldo completo de toda la información del usuario antes de eliminarlo.
     * @param int $id_usuario
     * @param int $id_usuario_elimina
     * @param string $fecha_eliminacion
     * @return array Resultado del respaldo
     */
    public function crearRespaldoCompletoUsuario(int $id_usuario, int $id_usuario_elimina, string $fecha_eliminacion): array {
        try {
            // 1. Obtener información básica del usuario
            $usuario = $this->obtenerUsuarioCompleto($id_usuario);
            if (!$usuario) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado para respaldo.',
                    'backup_path' => null
                ];
            }

            // 2. Crear directorio único para el respaldo
            $timestamp = date('Y-m-d_H-i-s', strtotime($fecha_eliminacion));
            $nombreLimpio = $this->limpiarNombreArchivo($usuario['nombres']);
            $carpetaRespaldo = "{$timestamp}_{$nombreLimpio}_{$id_usuario}";
            $rutaCompleta = $this->backupBasePath . $carpetaRespaldo . '/';
            
            if (!mkdir($rutaCompleta, 0755, true)) {
                return [
                    'success' => false,
                    'message' => 'No se pudo crear el directorio de respaldo.',
                    'backup_path' => null
                ];
            }

            // 3. Crear subdirectorio para archivos
            $rutaArchivos = $rutaCompleta . 'archivos/';
            mkdir($rutaArchivos, 0755, true);

            // 4. Recopilar toda la información del usuario
            $datosCompletos = [
                'informacion_eliminacion' => [
                    'fecha_eliminacion' => $fecha_eliminacion,
                    'eliminado_por_id' => $id_usuario_elimina,
                    'eliminado_por_info' => $this->obtenerUsuarioCompleto($id_usuario_elimina),
                    'backup_creado' => date('Y-m-d H:i:s'),
                    'version_sistema' => '1.0', // Versión de tu sistema
                ],
                'usuario' => $usuario,
                'categorias' => $this->obtenerCategorias($id_usuario),
                'productos' => $this->obtenerProductos($id_usuario),
                'ventas' => $this->obtenerVentas($id_usuario),
                'clientes' => $this->obtenerClientes($id_usuario),
                'compras' => $this->obtenerCompras($id_usuario),
                'estadisticas' => $this->obtenerEstadisticas($id_usuario)
            ];

            // 5. Guardar archivos JSON
            $this->guardarArchivoJSON($rutaCompleta . 'usuario_info.json', $datosCompletos['usuario']);
            $this->guardarArchivoJSON($rutaCompleta . 'categorias.json', $datosCompletos['categorias']);
            $this->guardarArchivoJSON($rutaCompleta . 'productos.json', $datosCompletos['productos']);
            $this->guardarArchivoJSON($rutaCompleta . 'ventas.json', $datosCompletos['ventas']);
            $this->guardarArchivoJSON($rutaCompleta . 'clientes.json', $datosCompletos['clientes']);
            $this->guardarArchivoJSON($rutaCompleta . 'compras.json', $datosCompletos['compras']);
            $this->guardarArchivoJSON($rutaCompleta . 'estadisticas.json', $datosCompletos['estadisticas']);
            $this->guardarArchivoJSON($rutaCompleta . 'respaldo_completo.json', $datosCompletos);

            // 6. Copiar archivos físicos (imágenes, etc.)
            $this->copiarArchivosUsuario($id_usuario, $rutaArchivos);

            // 7. Crear archivo README con información de la eliminación
            $this->crearArchivoREADME($rutaCompleta, $datosCompletos);

            // 8. Crear ZIP del respaldo (opcional)
            $archivoZip = $this->crearZipRespaldo($rutaCompleta, $carpetaRespaldo);

            return [
                'success' => true,
                'message' => "Respaldo completo creado exitosamente para usuario '{$usuario['nombres']}'.",
                'backup_path' => $rutaCompleta,
                'backup_folder' => $carpetaRespaldo,
                'zip_file' => $archivoZip,
                'datos_respaldados' => [
                    'categorias' => count($datosCompletos['categorias']),
                    'productos' => count($datosCompletos['productos']),
                    'ventas' => count($datosCompletos['ventas']),
                    'clientes' => count($datosCompletos['clientes']),
                    'compras' => count($datosCompletos['compras'])
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear respaldo: ' . $e->getMessage(),
                'backup_path' => null
            ];
        }
    }

    /**
     * Obtiene información completa del usuario incluyendo datos sensibles.
     */
    private function obtenerUsuarioCompleto(int $id_usuario): ?array {
        $sql = "SELECT us.*, rol.rol as nombre_rol 
                FROM tb_usuarios us
                INNER JOIN tb_roles rol ON us.id_rol = rol.id_rol
                WHERE us.id_usuario = :id_usuario";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        
        $usuario = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            // IMPORTANTE: En el respaldo incluimos el hash de la contraseña para auditoría
            // pero esto es solo para casos excepcionales de recuperación
            $usuario['password_hash_backup'] = $usuario['password_user'];
            $usuario['backup_note'] = 'Hash de contraseña respaldado solo para auditoría - NO reutilizar';
        }
        
        return $usuario ?: null;
    }

    /**
     * Obtiene todas las categorías del usuario.
     */
    private function obtenerCategorias(int $id_usuario): array {
        $sql = "SELECT * FROM tb_categorias WHERE id_usuario = :id_usuario ORDER BY fyh_creacion DESC";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los productos del usuario con información detallada.
     */
    private function obtenerProductos(int $id_usuario): array {
        $sql = "SELECT p.*, c.nombre_categoria 
                FROM tb_almacen p
                LEFT JOIN tb_categorias c ON p.id_categoria = c.id_categoria
                WHERE p.id_usuario = :id_usuario 
                ORDER BY p.fyh_creacion DESC";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todas las ventas del usuario con detalles.
     */
    private function obtenerVentas(int $id_usuario): array {
        $sql = "SELECT v.*, c.nombre_cliente, c.nit_ci_cliente,
                       dv.id_detalle_venta, dv.id_producto, dv.cantidad as cantidad_vendida, 
                       dv.precio_venta_unitario, dv.porcentaje_iva_item,
                       p.codigo as codigo_producto, p.nombre as nombre_producto
                FROM tb_ventas v
                LEFT JOIN tb_clientes c ON v.id_cliente = c.id_cliente
                LEFT JOIN tb_detalle_ventas dv ON v.id_venta = dv.id_venta
                LEFT JOIN tb_almacen p ON dv.id_producto = p.id_producto
                WHERE v.id_usuario = :id_usuario
                ORDER BY v.fyh_creacion DESC";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        
        $resultados = $query->fetchAll(PDO::FETCH_ASSOC);
        
        // Agrupar ventas con sus detalles
        $ventas = [];
        foreach ($resultados as $row) {
            $id_venta = $row['id_venta'];
            
            if (!isset($ventas[$id_venta])) {
                $ventas[$id_venta] = [
                    'id_venta' => $row['id_venta'],
                    'nro_venta_secuencial' => $row['nro_venta_secuencial'],
                    'fecha_venta' => $row['fecha_venta'],
                    'tipo_comprobante' => $row['tipo_comprobante'],
                    'nro_comprobante_fisico' => $row['nro_comprobante_fisico'],
                    'subtotal_general' => $row['subtotal_general'],
                    'monto_iva_general' => $row['monto_iva_general'],
                    'descuento_general' => $row['descuento_general'],
                    'total_general' => $row['total_general'],
                    'observaciones' => $row['observaciones'],
                    'cliente_info' => [
                        'nombre' => $row['nombre_cliente'],
                        'documento' => $row['nit_ci_cliente']
                    ],
                    'fyh_creacion' => $row['fyh_creacion'],
                    'fyh_actualizacion' => $row['fyh_actualizacion'],
                    'detalles' => []
                ];
            }
            
            if ($row['id_detalle_venta']) {
                $ventas[$id_venta]['detalles'][] = [
                    'id_detalle' => $row['id_detalle_venta'],
                    'id_producto' => $row['id_producto'],
                    'codigo_producto' => $row['codigo_producto'],
                    'nombre_producto' => $row['nombre_producto'],
                    'cantidad' => $row['cantidad_vendida'],
                    'precio_unitario' => $row['precio_venta_unitario'],
                    'porcentaje_iva' => $row['porcentaje_iva_item']
                ];
            }
        }
        
        return array_values($ventas);
    }

    /**
     * Obtiene todos los clientes del usuario.
     */
    private function obtenerClientes(int $id_usuario): array {
        $sql = "SELECT * FROM tb_clientes WHERE id_usuario = :id_usuario ORDER BY fyh_creacion DESC";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todas las compras del usuario (si existe la tabla).
     */
    private function obtenerCompras(int $id_usuario): array {
        try {
            $sql = "SELECT * FROM tb_compras WHERE id_usuario = :id_usuario ORDER BY fyh_creacion DESC";
            $query = $this->pdo->prepare($sql);
            $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Tabla no existe
            return [];
        }
    }

    /**
     * Obtiene estadísticas del usuario.
     */
    private function obtenerEstadisticas(int $id_usuario): array {
        $stats = [];
        
        try {
            // Total ventas
            $sql = "SELECT COUNT(*) as total_ventas, SUM(total) as monto_total_ventas FROM tb_ventas WHERE id_usuario = :id_usuario";
            $query = $this->pdo->prepare($sql);
            $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $query->execute();
            $stats['ventas'] = $query->fetch(PDO::FETCH_ASSOC);
            
            // Total productos
            $sql = "SELECT COUNT(*) as total_productos, SUM(stock) as stock_total FROM tb_almacen WHERE id_usuario = :id_usuario";
            $query = $this->pdo->prepare($sql);
            $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $query->execute();
            $stats['productos'] = $query->fetch(PDO::FETCH_ASSOC);
            
            // Total clientes
            $sql = "SELECT COUNT(*) as total_clientes FROM tb_clientes WHERE id_usuario = :id_usuario";
            $query = $this->pdo->prepare($sql);
            $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $query->execute();
            $stats['clientes'] = $query->fetch(PDO::FETCH_ASSOC);
            
            // Período de actividad
            $sql = "SELECT MIN(fyh_creacion) as primera_actividad, MAX(fyh_actualizacion) as ultima_actividad FROM tb_usuarios WHERE id_usuario = :id_usuario";
            $query = $this->pdo->prepare($sql);
            $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $query->execute();
            $stats['actividad'] = $query->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $stats['error'] = 'Error al generar estadísticas: ' . $e->getMessage();
        }
        
        return $stats;
    }

    /**
     * Copia archivos físicos del usuario (imágenes de perfil, productos, etc.).
     */
    private function copiarArchivosUsuario(int $id_usuario, string $rutaDestino): void {
        // 1. Imagen de perfil
        $usuario = $this->obtenerUsuarioCompleto($id_usuario);
        if ($usuario && !empty($usuario['imagen_perfil']) && $usuario['imagen_perfil'] !== 'user_default.png') {
            $rutaImagenPerfil = __DIR__ . '/../../public/images/perfiles/' . $usuario['imagen_perfil'];
            if (file_exists($rutaImagenPerfil)) {
                copy($rutaImagenPerfil, $rutaDestino . 'imagen_perfil_' . $usuario['imagen_perfil']);
            }
        }

        // 2. Imágenes de productos
        $productos = $this->obtenerProductos($id_usuario);
        if (!empty($productos)) {
            $rutaImagenesProductos = $rutaDestino . 'imagenes_productos/';
            mkdir($rutaImagenesProductos, 0755, true);
            
            foreach ($productos as $producto) {
                if (!empty($producto['imagen'])) {
                    $rutaImagenProducto = __DIR__ . '/../../public/images/productos/' . $producto['imagen'];
                    if (file_exists($rutaImagenProducto)) {
                        copy($rutaImagenProducto, $rutaImagenesProductos . $producto['codigo'] . '_' . $producto['imagen']);
                    }
                }
            }
        }
    }

    /**
     * Guarda un array como archivo JSON formateado.
     */
    private function guardarArchivoJSON(string $ruta, array $datos): void {
        $json = json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($ruta, $json);
    }

    /**
     * Crea un archivo README con información sobre el respaldo.
     */
    private function crearArchivoREADME(string $rutaCompleta, array $datosCompletos): void {
        $usuario = $datosCompletos['usuario'];
        $info = $datosCompletos['informacion_eliminacion'];
        
        $readme = "# RESPALDO DE USUARIO ELIMINADO\n\n";
        $readme .= "## Información del Usuario Eliminado\n";
        $readme .= "- **ID:** {$usuario['id_usuario']}\n";
        $readme .= "- **Nombre:** {$usuario['nombres']}\n";
        $readme .= "- **Email:** {$usuario['email']}\n";
        $readme .= "- **Rol:** {$usuario['nombre_rol']}\n";
        $readme .= "- **Fecha de Registro:** {$usuario['fyh_creacion']}\n";
        $readme .= "- **Última Actividad:** {$usuario['fyh_actualizacion']}\n\n";
        
        $readme .= "## Información de la Eliminación\n";
        $readme .= "- **Fecha de Eliminación:** {$info['fecha_eliminacion']}\n";
        $readme .= "- **Eliminado por:** {$info['eliminado_por_info']['nombres']} (ID: {$info['eliminado_por_id']})\n";
        $readme .= "- **Respaldo Creado:** {$info['backup_creado']}\n\n";
        
        $readme .= "## Datos Respaldados\n";
        $readme .= "- **Categorías:** " . count($datosCompletos['categorias']) . "\n";
        $readme .= "- **Productos:** " . count($datosCompletos['productos']) . "\n";
        $readme .= "- **Ventas:** " . count($datosCompletos['ventas']) . "\n";
        $readme .= "- **Clientes:** " . count($datosCompletos['clientes']) . "\n";
        $readme .= "- **Compras:** " . count($datosCompletos['compras']) . "\n\n";
        
        $readme .= "## Estructura de Archivos\n";
        $readme .= "- `usuario_info.json`: Información completa del usuario\n";
        $readme .= "- `categorias.json`: Todas las categorías creadas\n";
        $readme .= "- `productos.json`: Todos los productos en almacén\n";
        $readme .= "- `ventas.json`: Historial completo de ventas\n";
        $readme .= "- `clientes.json`: Base de datos de clientes\n";
        $readme .= "- `compras.json`: Historial de compras (si aplica)\n";
        $readme .= "- `estadisticas.json`: Estadísticas del usuario\n";
        $readme .= "- `respaldo_completo.json`: Archivo consolidado con toda la información\n";
        $readme .= "- `archivos/`: Carpeta con imágenes y otros archivos\n\n";
        
        $readme .= "## Notas Importantes\n";
        $readme .= "- Este respaldo contiene información sensible\n";
        $readme .= "- El hash de contraseña se incluye solo para auditoría\n";
        $readme .= "- NO reutilizar credenciales respaldadas\n";
        $readme .= "- Conservar por tiempo reglamentario según políticas de la empresa\n\n";
        
        $readme .= "---\n";
        $readme .= "Sistema de Ventas - Respaldo automatizado v1.0\n";
        $readme .= "Generado el: " . date('Y-m-d H:i:s') . "\n";
        
        file_put_contents($rutaCompleta . 'README.md', $readme);
    }

    /**
     * Crea un archivo ZIP del respaldo completo.
     */
    private function crearZipRespaldo(string $rutaCarpeta, string $nombreCarpeta): ?string {
        if (!class_exists('ZipArchive')) {
            return null; // ZIP no disponible
        }

        $zip = new ZipArchive();
        $archivoZip = $this->backupBasePath . $nombreCarpeta . '.zip';
        
        if ($zip->open($archivoZip, ZipArchive::CREATE) !== TRUE) {
            return null;
        }

        $archivos = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rutaCarpeta),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($archivos as $archivo) {
            if (!$archivo->isDir()) {
                $rutaRelativa = substr($archivo->getRealPath(), strlen($rutaCarpeta));
                $zip->addFile($archivo->getRealPath(), $rutaRelativa);
            }
        }

        $zip->close();
        return $archivoZip;
    }

    /**
     * Limpia el nombre para usar en carpetas.
     */
    private function limpiarNombreArchivo(string $nombre): string {
        // Remover acentos y caracteres especiales
        $nombre = iconv('UTF-8', 'ASCII//TRANSLIT', $nombre);
        // Remover caracteres no alfanuméricos excepto guiones y espacios
        $nombre = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $nombre);
        // Reemplazar espacios con guiones bajos
        $nombre = preg_replace('/\s+/', '_', trim($nombre));
        // Limitar longitud
        return substr($nombre, 0, 50);
    }

    /**
     * Lista todos los respaldos existentes.
     */
    public function listarRespaldos(): array {
        $respaldos = [];
        
        if (is_dir($this->backupBasePath)) {
            $carpetas = scandir($this->backupBasePath);
            foreach ($carpetas as $carpeta) {
                if ($carpeta != '.' && $carpeta != '..' && is_dir($this->backupBasePath . $carpeta)) {
                    $infoFile = $this->backupBasePath . $carpeta . '/respaldo_completo.json';
                    if (file_exists($infoFile)) {
                        $info = json_decode(file_get_contents($infoFile), true);
                        $respaldos[] = [
                            'carpeta' => $carpeta,
                            'ruta_completa' => $this->backupBasePath . $carpeta,
                            'usuario_info' => $info['usuario'] ?? null,
                            'fecha_eliminacion' => $info['informacion_eliminacion']['fecha_eliminacion'] ?? null,
                            'tamaño' => $this->obtenerTamañoCarpeta($this->backupBasePath . $carpeta)
                        ];
                    }
                }
            }
        }
        
        // Ordenar por fecha de eliminación más reciente
        usort($respaldos, function($a, $b) {
            return strtotime($b['fecha_eliminacion']) - strtotime($a['fecha_eliminacion']);
        });
        
        return $respaldos;
    }

    /**
     * Obtiene el tamaño de una carpeta en bytes.
     */
    private function obtenerTamañoCarpeta(string $ruta): int {
        $tamaño = 0;
        $archivos = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($ruta));
        foreach ($archivos as $archivo) {
            if ($archivo->isFile()) {
                $tamaño += $archivo->getSize();
            }
        }
        return $tamaño;
    }
}

?>