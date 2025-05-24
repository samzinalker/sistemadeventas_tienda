<?php

class ClienteModel {
    private $pdo;
    private $URL;

    public function __construct(PDO $pdo, string $URL) {
        $this->pdo = $pdo;
        $this->URL = $URL;
    }

    /**
     * Obtiene todos los clientes de un usuario específico
     */
    public function getClientesByUsuarioId(int $id_usuario): array {
        $sql = "SELECT * FROM tb_clientes 
                WHERE id_usuario = :id_usuario 
                ORDER BY nombre_cliente ASC";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un cliente específico por ID y usuario
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
     * Valida documento ecuatoriano (cédula, RUC, pasaporte)
     */
    public function validarDocumentoEcuatoriano(string $documento, string $tipo): array {
        $documento = preg_replace('/[^0-9]/', '', $documento); // Solo números
        
        switch ($tipo) {
            case 'cedula':
                return $this->validarCedulaEcuatoriana($documento);
            case 'ruc':
                return $this->validarRucEcuatoriano($documento);
            case 'pasaporte':
                return ['valido' => true, 'mensaje' => 'Formato de pasaporte aceptado'];
            case 'extranjero':
                return ['valido' => true, 'mensaje' => 'Documento extranjero aceptado'];
            default:
                return ['valido' => false, 'mensaje' => 'Tipo de documento no válido'];
        }
    }

    /**
     * Validación de cédula ecuatoriana (algoritmo oficial)
     */
    private function validarCedulaEcuatoriana(string $cedula): array {
        if (strlen($cedula) !== 10) {
            return ['valido' => false, 'mensaje' => 'La cédula debe tener 10 dígitos'];
        }

        $provincia = (int)substr($cedula, 0, 2);
        if ($provincia < 1 || $provincia > 24) {
            return ['valido' => false, 'mensaje' => 'Código de provincia inválido'];
        }

        $tercerDigito = (int)$cedula[2];
        if ($tercerDigito > 5) {
            return ['valido' => false, 'mensaje' => 'Tercer dígito de cédula inválido'];
        }

        // Algoritmo de validación del dígito verificador
        $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $suma = 0;
        
        for ($i = 0; $i < 9; $i++) {
            $producto = (int)$cedula[$i] * $coeficientes[$i];
            if ($producto >= 10) {
                $producto = $producto - 9;
            }
            $suma += $producto;
        }

        $digitoVerificador = 10 - ($suma % 10);
        if ($digitoVerificador === 10) $digitoVerificador = 0;

        if ($digitoVerificador !== (int)$cedula[9]) {
            return ['valido' => false, 'mensaje' => 'Dígito verificador de cédula incorrecto'];
        }

        return ['valido' => true, 'mensaje' => 'Cédula válida'];
    }

    /**
     * Validación básica de RUC ecuatoriano
     */
    private function validarRucEcuatoriano(string $ruc): array {
        if (strlen($ruc) !== 13) {
            return ['valido' => false, 'mensaje' => 'El RUC debe tener 13 dígitos'];
        }

        $provincia = (int)substr($ruc, 0, 2);
        if ($provincia < 1 || $provincia > 24) {
            return ['valido' => false, 'mensaje' => 'Código de provincia en RUC inválido'];
        }

        $tercerDigito = (int)$ruc[2];
        
        // RUC Persona Natural (tercer dígito 0-5)
        if ($tercerDigito <= 5) {
            $cedula = substr($ruc, 0, 10);
            $validacionCedula = $this->validarCedulaEcuatoriana($cedula);
            if (!$validacionCedula['valido']) {
                return ['valido' => false, 'mensaje' => 'RUC con cédula base inválida'];
            }
            
            $establecimiento = substr($ruc, 10, 3);
            if ($establecimiento !== '001') {
                return ['valido' => false, 'mensaje' => 'Código de establecimiento RUC inválido'];
            }
            
            return ['valido' => true, 'mensaje' => 'RUC de persona natural válido'];
        }
        
        // RUC Jurídica (tercer dígito 6) o Pública (tercer dígito 9)
        if ($tercerDigito === 6 || $tercerDigito === 9) {
            return ['valido' => true, 'mensaje' => 'RUC de persona jurídica/pública válido'];
        }

        return ['valido' => false, 'mensaje' => 'Tipo de RUC no reconocido'];
    }

    /**
     * Verifica si ya existe un documento para otro cliente del mismo usuario
     */
    public function documentoExisteParaOtroCliente(string $documento, int $id_usuario, ?int $excluir_id_cliente = null): bool {
        $sql = "SELECT COUNT(*) FROM tb_clientes 
                WHERE nit_ci_cliente = :documento AND id_usuario = :id_usuario";
        
        if ($excluir_id_cliente !== null) {
            $sql .= " AND id_cliente != :excluir_id";
        }

        $query = $this->pdo->prepare($sql);
        $query->bindParam(':documento', $documento, PDO::PARAM_STR);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        
        if ($excluir_id_cliente !== null) {
            $query->bindParam(':excluir_id', $excluir_id_cliente, PDO::PARAM_INT);
        }

        $query->execute();
        return $query->fetchColumn() > 0;
    }

    /**
     * Crear cliente genérico
     */
    public function crearCliente(array $datos): ?string {
        $sql = "INSERT INTO tb_clientes 
                (nombre_cliente, nit_ci_cliente, tipo_documento, celular_cliente, 
                 telefono_fijo, email_cliente, direccion, ciudad, provincia, 
                 fecha_nacimiento, observaciones, estado, id_usuario, fyh_creacion, fyh_actualizacion) 
                VALUES 
                (:nombre_cliente, :nit_ci_cliente, :tipo_documento, :celular_cliente,
                 :telefono_fijo, :email_cliente, :direccion, :ciudad, :provincia,
                 :fecha_nacimiento, :observaciones, :estado, :id_usuario, :fyh_creacion, :fyh_actualizacion)";

        $query = $this->pdo->prepare($sql);
        
        foreach ($datos as $key => $value) {
            $paramType = ($key === 'id_usuario') ? PDO::PARAM_INT : PDO::PARAM_STR;
            $query->bindValue(":$key", $value, $paramType);
        }

        if ($query->execute()) {
            return $this->pdo->lastInsertId();
        }
        return null;
    }

    /**
     * Actualizar cliente
     */
    public function actualizarCliente(int $id_cliente, int $id_usuario, array $datos): bool {
        $cliente_actual = $this->getClienteByIdAndUsuarioId($id_cliente, $id_usuario);
        if (!$cliente_actual) {
            return false;
        }

        $set_parts = [];
        foreach (array_keys($datos) as $key) {
            if ($key !== 'id_cliente' && $key !== 'id_usuario' && $key !== 'fyh_creacion') {
                $set_parts[] = "$key = :$key";
            }
        }

        if (empty($set_parts)) return false;

        $sql = "UPDATE tb_clientes SET " . implode(', ', $set_parts) . 
               " WHERE id_cliente = :id_cliente_cond AND id_usuario = :id_usuario_cond";

        $query = $this->pdo->prepare($sql);
        
        foreach ($datos as $key => $value) {
            if ($key !== 'id_cliente' && $key !== 'id_usuario' && $key !== 'fyh_creacion') {
                $query->bindValue(":$key", $value, PDO::PARAM_STR);
            }
        }
        
        $query->bindValue(':id_cliente_cond', $id_cliente, PDO::PARAM_INT);
        $query->bindValue(':id_usuario_cond', $id_usuario, PDO::PARAM_INT);
        
        return $query->execute();
    }

    /**
     * Eliminar cliente
     */
    public function eliminarCliente(int $id_cliente, int $id_usuario): bool {
        // Verificar si está en uso en ventas
        $sql_check = "SELECT COUNT(*) FROM tb_ventas WHERE id_cliente = :id_cliente";
        $query_check = $this->pdo->prepare($sql_check);
        $query_check->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $query_check->execute();
        
        if ($query_check->fetchColumn() > 0) {
            return false; // Cliente en uso, no se puede eliminar
        }

        $sql = "DELETE FROM tb_clientes 
                WHERE id_cliente = :id_cliente AND id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        
        return $query->execute() && $query->rowCount() > 0;
    }

    /**
     * Crear cliente genérico "Consumidor Final" automáticamente
     */
    public function crearClienteConsumidorFinal(int $id_usuario): ?string {
        // Verificar si ya existe
        $sql = "SELECT id_cliente FROM tb_clientes 
                WHERE nombre_cliente = 'CONSUMIDOR FINAL' AND id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        
        $existente = $query->fetch(PDO::FETCH_ASSOC);
        if ($existente) {
            return $existente['id_cliente'];
        }

        // Crear cliente genérico
        $datos_genericos = [
            'nombre_cliente' => 'CONSUMIDOR FINAL',
            'nit_ci_cliente' => '9999999999999', // Documento genérico
            'tipo_documento' => 'consumidor_final',
            'celular_cliente' => 'N/A',
            'telefono_fijo' => null,
            'email_cliente' => 'consumidor@final.ec',
            'direccion' => 'N/A',
            'ciudad' => 'N/A',
            'provincia' => 'N/A',
            'fecha_nacimiento' => null,
            'observaciones' => 'Cliente genérico para ventas al consumidor final',
            'estado' => 'activo',
            'id_usuario' => $id_usuario,
            'fyh_creacion' => date('Y-m-d H:i:s'),
            'fyh_actualizacion' => date('Y-m-d H:i:s')
        ];

        return $this->crearCliente($datos_genericos);
    }

    /**
     * Búsqueda para DataTables
     */
    public function buscarClientesDataTables(int $id_usuario, array $params): array {
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $search = $params['search'] ?? '';
        $orderColumn = $params['orderColumn'] ?? 'nombre_cliente';
        $orderDir = $params['orderDir'] ?? 'ASC';

        // Conteo total
        $sqlTotal = "SELECT COUNT(*) FROM tb_clientes WHERE id_usuario = :id_usuario";
        $queryTotal = $this->pdo->prepare($sqlTotal);
        $queryTotal->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $queryTotal->execute();
        $recordsTotal = $queryTotal->fetchColumn();

        // Query principal con búsqueda
        $whereClause = "WHERE id_usuario = :id_usuario";
        $bindings = [':id_usuario' => $id_usuario];

        if (!empty($search)) {
            $whereClause .= " AND (nombre_cliente LIKE :search OR nit_ci_cliente LIKE :search 
                             OR celular_cliente LIKE :search OR email_cliente LIKE :search)";
            $bindings[':search'] = "%$search%";
        }

        // Conteo filtrado
        $sqlFiltered = "SELECT COUNT(*) FROM tb_clientes $whereClause";
        $queryFiltered = $this->pdo->prepare($sqlFiltered);
        $queryFiltered->execute($bindings);
        $recordsFiltered = $queryFiltered->fetchColumn();

        // Query de datos
        $sql = "SELECT * FROM tb_clientes $whereClause 
                ORDER BY $orderColumn $orderDir 
                LIMIT :start, :length";

        $query = $this->pdo->prepare($sql);
        foreach ($bindings as $key => $value) {
            $query->bindValue($key, $value);
        }
        $query->bindValue(':start', $start, PDO::PARAM_INT);
        $query->bindValue(':length', $length, PDO::PARAM_INT);
        $query->execute();

        return [
            'data' => $query->fetchAll(PDO::FETCH_ASSOC),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered
        ];
    }
}
?>