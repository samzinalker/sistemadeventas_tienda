<?php

class ClienteModel {
    private $pdo;
    private $URL; // Puede ser útil para futuras extensiones

    public function __construct(PDO $pdo, string $URL) {
        $this->pdo = $pdo;
        $this->URL = $URL;
    }

    /**
     * Obtiene un cliente específico por su ID, verificando que pertenezca al usuario.
     * @param int $id_cliente
     * @param int $id_usuario
     * @return array|false Datos del cliente o false si no se encuentra o no pertenece al usuario.
     */
    public function getClienteByIdAndUsuarioId(int $id_cliente, int $id_usuario) {
        $sql = "SELECT * FROM tb_clientes 
                WHERE id_cliente = :id_cliente AND id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Valida un documento ecuatoriano (Cédula, RUC, Pasaporte).
     * Implementa validaciones básicas de formato y longitud.
     * @param string $documento Número de documento.
     * @param string $tipo 'cedula', 'ruc', 'pasaporte', 'consumidor_final', 'otro', 'extranjero'.
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public function validarDocumentoEcuatoriano(string $documento, string $tipo): array {
        $documento = trim(preg_replace('/[^a-zA-Z0-9]/', '', $documento)); // Permitir alfanuméricos para pasaporte

        switch ($tipo) {
            case 'cedula':
                if (!ctype_digit($documento)) {
                    return ['valido' => false, 'mensaje' => 'La cédula debe contener solo números.'];
                }
                if (strlen($documento) !== 10) {
                    return ['valido' => false, 'mensaje' => 'La cédula ecuatoriana debe tener 10 dígitos.'];
                }
                // Aquí iría el algoritmo de validación del SRI si se requiere mayor precisión
                return ['valido' => true, 'mensaje' => 'Formato de cédula válido.'];
            case 'ruc':
                if (!ctype_digit($documento)) {
                    return ['valido' => false, 'mensaje' => 'El RUC debe contener solo números.'];
                }
                if (strlen($documento) !== 13) {
                    return ['valido' => false, 'mensaje' => 'El RUC ecuatoriano debe tener 13 dígitos.'];
                }
                // Aquí iría el algoritmo de validación del SRI
                return ['valido' => true, 'mensaje' => 'Formato de RUC válido.'];
            case 'pasaporte':
                if (empty($documento) || strlen($documento) < 5 || strlen($documento) > 20) { // Longitud genérica
                    return ['valido' => false, 'mensaje' => 'Formato o longitud de pasaporte no válido.'];
                }
                return ['valido' => true, 'mensaje' => 'Formato de pasaporte aceptado.'];
            case 'consumidor_final':
                 if ($documento === '9999999999999' || $documento === '9999999999' || empty($documento)) {
                    return ['valido' => true, 'mensaje' => 'Documento de Consumidor Final válido.'];
                }
                return ['valido' => false, 'mensaje' => 'Documento para Consumidor Final no es el genérico.'];
            case 'otro':
            case 'extranjero':
                if (empty($documento) || strlen($documento) < 3 || strlen($documento) > 25) {
                     return ['valido' => false, 'mensaje' => 'Formato o longitud de documento no válido para tipo ' . $tipo];
                }
                return ['valido' => true, 'mensaje' => 'Documento tipo "' . $tipo . '" aceptado.'];
            default:
                return ['valido' => false, 'mensaje' => 'Tipo de documento no reconocido para validación.'];
        }
    }

    /**
     * Verifica si un número de documento ya existe para OTRO cliente del MISMO usuario.
     * @param string $documento Número de documento.
     * @param string $tipo_documento Tipo de documento.
     * @param int $id_usuario ID del usuario actual.
     * @param int|null $excluir_id_cliente ID del cliente a excluir (útil al actualizar).
     * @return bool True si ya existe para otro cliente del mismo usuario, false en caso contrario.
     */
    public function documentoExisteParaOtroCliente(string $documento, string $tipo_documento, int $id_usuario, ?int $excluir_id_cliente = null): bool {
        if (empty($documento) || $tipo_documento === 'consumidor_final' && ($documento === '9999999999999' || $documento === '9999999999')) {
            return false; // No verificar duplicidad para estos casos
        }

        $sql = "SELECT COUNT(*) FROM tb_clientes 
                WHERE nit_ci_cliente = :documento 
                AND tipo_documento = :tipo_documento
                AND id_usuario = :id_usuario";
        if ($excluir_id_cliente !== null) {
            $sql .= " AND id_cliente != :excluir_id_cliente";
        }
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':documento', $documento, PDO::PARAM_STR);
        $query->bindParam(':tipo_documento', $tipo_documento, PDO::PARAM_STR);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        if ($excluir_id_cliente !== null) {
            $query->bindParam(':excluir_id_cliente', $excluir_id_cliente, PDO::PARAM_INT);
        }
        $query->execute();
        return $query->fetchColumn() > 0;
    }

    /**
     * Crea un nuevo cliente.
     * @param array $datos Datos del cliente.
     * @return string|false El ID del cliente creado o false en error.
     */
    public function crearCliente(array $datos): ?string {
        $sql = "INSERT INTO tb_clientes (id_usuario, nombre_cliente, tipo_documento, nit_ci_cliente, 
                                     celular_cliente, telefono_fijo, email_cliente, direccion, 
                                     ciudad, provincia, fecha_nacimiento, observaciones, estado, 
                                     fyh_creacion, fyh_actualizacion) 
                VALUES (:id_usuario, :nombre_cliente, :tipo_documento, :nit_ci_cliente, 
                        :celular_cliente, :telefono_fijo, :email_cliente, :direccion, 
                        :ciudad, :provincia, :fecha_nacimiento, :observaciones, :estado, 
                        :fyh_creacion, :fyh_actualizacion)";
        
        $query = $this->pdo->prepare($sql);
        
        $params_to_bind = [
            ':id_usuario' => $datos['id_usuario'],
            ':nombre_cliente' => $datos['nombre_cliente'],
            ':tipo_documento' => $datos['tipo_documento'],
            ':nit_ci_cliente' => $datos['nit_ci_cliente'],
            ':celular_cliente' => $datos['celular_cliente'],
            ':telefono_fijo' => $datos['telefono_fijo'],
            ':email_cliente' => $datos['email_cliente'],
            ':direccion' => $datos['direccion'],
            ':ciudad' => $datos['ciudad'],
            ':provincia' => $datos['provincia'],
            ':fecha_nacimiento' => $datos['fecha_nacimiento'],
            ':observaciones' => $datos['observaciones'],
            ':estado' => $datos['estado'],
            ':fyh_creacion' => $datos['fyh_creacion'],
            ':fyh_actualizacion' => $datos['fyh_actualizacion']
        ];

        if ($query->execute($params_to_bind)) {
            return $this->pdo->lastInsertId();
        }
        return null;
    }

    /**
     * Actualiza un cliente existente, verificando pertenencia.
     * @param int $id_cliente ID del cliente a actualizar.
     * @param int $id_usuario ID del usuario propietario.
     * @param array $datos Nuevos datos del cliente.
     * @return bool True si se actualizó, false si no o si no pertenece al usuario.
     */
    public function actualizarCliente(int $id_cliente, int $id_usuario, array $datos): bool {
        $cliente_actual = $this->getClienteByIdAndUsuarioId($id_cliente, $id_usuario);
        if (!$cliente_actual) {
            return false; 
        }

        $sql_parts = [];
        $bindings = [];
        $allowed_fields = ['nombre_cliente', 'tipo_documento', 'nit_ci_cliente', 'celular_cliente', 
                           'telefono_fijo', 'email_cliente', 'direccion', 'ciudad', 'provincia', 
                           'fecha_nacimiento', 'observaciones', 'estado', 'fyh_actualizacion'];

        foreach ($allowed_fields as $field) {
            if (array_key_exists($field, $datos)) { // Usar array_key_exists para permitir valores null o vacíos explícitos
                $sql_parts[] = "$field = :$field";
                $bindings[":$field"] = $datos[$field];
            }
        }

        if (empty($sql_parts)) {
            return true; // No hay nada que actualizar, se considera exitoso.
        }

        $sql = "UPDATE tb_clientes SET " . implode(', ', $sql_parts) . 
               " WHERE id_cliente = :id_cliente_cond AND id_usuario = :id_usuario_cond";
        
        $query = $this->pdo->prepare($sql);
        
        $bindings[':id_cliente_cond'] = $id_cliente;
        $bindings[':id_usuario_cond'] = $id_usuario;
        
        return $query->execute($bindings);
    }

    /**
     * Elimina un cliente, verificando pertenencia.
     * @param int $id_cliente
     * @param int $id_usuario
     * @return bool True si se eliminó, false en caso contrario.
     */
    public function eliminarCliente(int $id_cliente, int $id_usuario): bool {
        // Primero verificar si el cliente tiene ventas asociadas
        $sql_check_ventas = "SELECT COUNT(*) FROM tb_ventas WHERE id_cliente = :id_cliente";
        $query_check_ventas = $this->pdo->prepare($sql_check_ventas);
        $query_check_ventas->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $query_check_ventas->execute();
        if ($query_check_ventas->fetchColumn() > 0) {
            // Podrías lanzar una excepción o devolver un código específico
            // throw new Exception("No se puede eliminar el cliente porque tiene ventas asociadas.");
            return false; // No se elimina si tiene ventas
        }

        $sql = "DELETE FROM tb_clientes 
                WHERE id_cliente = :id_cliente AND id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        
        if ($query->execute()) {
            return $query->rowCount() > 0; 
        }
        return false;
    }
    
    /**
     * Crea un cliente "Consumidor Final" para un usuario si no existe uno genérico.
     * @param int $id_usuario
     * @param string $fyh_actual
     * @return string|false El ID del cliente Consumidor Final (existente o nuevo) o false.
     */
    public function obtenerOCrearConsumidorFinal(int $id_usuario, string $fyh_actual): ?string {
        $nombre_cf = 'CONSUMIDOR FINAL';
        $doc_cf = '9999999999'; // Para Cédula/RUC genérico si el tipo es 'consumidor_final' o un RUC genérico
        $tipo_doc_cf = 'consumidor_final';

        $sql_find = "SELECT id_cliente FROM tb_clientes 
                     WHERE id_usuario = :id_usuario 
                     AND tipo_documento = :tipo_documento 
                     AND (nit_ci_cliente = :nit_ci_cliente OR nombre_cliente = :nombre_cliente)
                     LIMIT 1";
        $query_find = $this->pdo->prepare($sql_find);
        $query_find->execute([
            ':id_usuario' => $id_usuario,
            ':tipo_documento' => $tipo_doc_cf,
            ':nit_ci_cliente' => $doc_cf,
            ':nombre_cliente' => $nombre_cf
        ]);
        $existente = $query_find->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            return $existente['id_cliente'];
        }

        $datos_cf = [
            'id_usuario' => $id_usuario,
            'nombre_cliente' => $nombre_cf,
            'tipo_documento' => $tipo_doc_cf,
            'nit_ci_cliente' => $doc_cf,
            'celular_cliente' => null,
            'telefono_fijo' => null,
            'email_cliente' => null,
            'direccion' => null,
            'ciudad' => null,
            'provincia' => null,
            'fecha_nacimiento' => null,
            'observaciones' => 'Cliente genérico para ventas rápidas.',
            'estado' => 'activo',
            'fyh_creacion' => $fyh_actual,
            'fyh_actualizacion' => $fyh_actual
        ];
        return $this->crearCliente($datos_cf);
    }

    /**
     * Busca clientes para DataTables con paginación, búsqueda y ordenamiento del lado del servidor.
     * @param int $id_usuario
     * @param array $params Parámetros de DataTables (draw, start, length, search, order).
     * @return array Formateado para DataTables: ['draw', 'recordsTotal', 'recordsFiltered', 'data'].
     */
    public function buscarClientesDataTables(int $id_usuario, array $params): array {
        $draw = intval($params['draw'] ?? 0);
        $start = intval($params['start'] ?? 0);
        $length = intval($params['length'] ?? 10);
        $searchValue = trim($params['search']['value'] ?? '');

        $columnMapping = [
            0 => 'id_cliente', 
            1 => 'nombre_cliente',
            2 => 'tipo_documento',
            3 => 'nit_ci_cliente',
            4 => 'celular_cliente',
            5 => 'email_cliente',
            6 => 'fyh_creacion'
        ];
        
        $orderByColumnIndex = intval($params['order'][0]['column'] ?? 1);
        $orderByColumnName = $columnMapping[$orderByColumnIndex] ?? 'nombre_cliente'; 
        $orderDir = strtolower($params['order'][0]['dir'] ?? 'asc') === 'asc' ? 'ASC' : 'DESC';

        $bindings = [':id_usuario' => $id_usuario];

        $stmtTotal = $this->pdo->prepare("SELECT COUNT(id_cliente) FROM tb_clientes WHERE id_usuario = :id_usuario");
        $stmtTotal->execute([':id_usuario' => $id_usuario]);
        $recordsTotal = $stmtTotal->fetchColumn();

        $sql = "SELECT id_cliente, nombre_cliente, tipo_documento, nit_ci_cliente, celular_cliente, email_cliente, estado, fyh_creacion 
                FROM tb_clientes
                WHERE id_usuario = :id_usuario";

        $searchSql = "";
        if (!empty($searchValue)) {
            $searchSql = " AND (nombre_cliente LIKE :searchValue 
                             OR nit_ci_cliente LIKE :searchValue 
                             OR celular_cliente LIKE :searchValue 
                             OR email_cliente LIKE :searchValue
                             OR tipo_documento LIKE :searchValue)";
            $bindings[':searchValue'] = '%' . $searchValue . '%';
        }
        $sql .= $searchSql;

        $stmtFiltered = $this->pdo->prepare("SELECT COUNT(id_cliente) FROM tb_clientes WHERE id_usuario = :id_usuario_filtered " . $searchSql);
        $bindingsFiltered = [':id_usuario_filtered' => $id_usuario];
        if (!empty($searchValue)) {
            $bindingsFiltered[':searchValue'] = '%' . $searchValue . '%';
        }
        $stmtFiltered->execute($bindingsFiltered);
        $recordsFiltered = $stmtFiltered->fetchColumn();

        $sql .= " ORDER BY $orderByColumnName $orderDir";
        if ($length != -1) {
            $sql .= " LIMIT :start, :length";
            $bindings[':start'] = $start; 
            $bindings[':length'] = $length;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        if (!empty($searchValue)) {
            $stmt->bindParam(':searchValue', $bindings[':searchValue'], PDO::PARAM_STR);
        }
        if ($length != -1) {
            $stmt->bindParam(':start', $bindings[':start'], PDO::PARAM_INT);
            $stmt->bindParam(':length', $bindings[':length'], PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "draw" => $draw,
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $data,
        ];
    }
}
?>