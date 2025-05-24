<?php

class VentasModel {
    private $pdo;
    private $id_usuario_sesion; // Para registrar quién realiza la acción

    public function __construct(PDO $pdo, $id_usuario_sesion = null) {
        $this->pdo = $pdo;
        $this->id_usuario_sesion = $id_usuario_sesion;
    }

    /**
     * Obtiene el siguiente número secuencial para una venta para el usuario actual.
     * Si no hay ventas previas para este usuario, empieza en 1.
     *
     * @return int El siguiente número secuencial.
     */
    public function getSiguienteNumeroVentaSecuencial() {
        if (!$this->id_usuario_sesion) {
            throw new Exception("ID de usuario no establecido en VentasModel para getSiguienteNumeroVentaSecuencial.");
        }
        $sql = "SELECT MAX(nro_venta_secuencial) as ultimo_nro FROM tb_ventas WHERE id_usuario = :id_usuario";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_usuario', $this->id_usuario_sesion, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['ultimo_nro'] ? $resultado['ultimo_nro'] + 1 : 1;
    }

    /**
     * Formatea el código de referencia de la venta.
     * Ejemplo: V-00001
     *
     * @param int $numeroSecuencial El número secuencial de la venta.
     * @return string El código de venta formateado.
     */
    public function formatearCodigoVenta($numeroSecuencial) {
        return "V-" . str_pad($numeroSecuencial, 5, "0", STR_PAD_LEFT);
    }

    /**
     * Registra una nueva venta con sus detalles en la base de datos.
     * Actualiza el stock de los productos vendidos.
     * Utiliza una transacción para asegurar la atomicidad.
     *
     * @param array $datosVenta Datos de la cabecera de la venta.
     *                          Ej: ['id_cliente', 'nro_venta_secuencial', 'codigo_venta_referencia', 'fecha_venta', 
     *                               'tipo_comprobante', 'nro_comprobante_fisico', 'subtotal_general', 
     *                               'monto_iva_general', 'descuento_general', 'total_general', 'observaciones']
     * @param array $itemsVenta Array de items de la venta.
     *                          Cada item: ['id_producto', 'cantidad', 'precio_venta_unitario', 'porcentaje_iva_item']
     * @return int El ID de la venta registrada.
     * @throws Exception Si ocurre algún error durante el proceso.
     */
    public function registrarVentaConDetalles(array $datosVenta, array $itemsVenta) {
        if (!$this->id_usuario_sesion) {
            throw new Exception("ID de usuario no establecido en VentasModel para registrarVentaConDetalles.");
        }
        if (empty($itemsVenta)) {
            throw new Exception("No se pueden registrar ventas sin productos.");
        }

        $this->pdo->beginTransaction();
        try {
            // 1. Insertar en tb_ventas
            $sqlVenta = "INSERT INTO tb_ventas 
                            (id_usuario, id_cliente, nro_venta_secuencial, codigo_venta_referencia, fecha_venta, 
                             tipo_comprobante, nro_comprobante_fisico, subtotal_general, monto_iva_general, 
                             descuento_general, total_general, estado_venta, observaciones, fyh_creacion, fyh_actualizacion)
                         VALUES 
                            (:id_usuario, :id_cliente, :nro_venta_secuencial, :codigo_venta_referencia, :fecha_venta,
                             :tipo_comprobante, :nro_comprobante_fisico, :subtotal_general, :monto_iva_general,
                             :descuento_general, :total_general, :estado_venta, :observaciones, :fyh_creacion, :fyh_actualizacion)";
            
            $stmtVenta = $this->pdo->prepare($sqlVenta);

            $fechaHoraActual = date('Y-m-d H:i:s');
            $estadoVentaPredeterminado = 'PAGADA'; // O 'PENDIENTE' si manejas estados de pago

            $stmtVenta->bindParam(':id_usuario', $this->id_usuario_sesion, PDO::PARAM_INT);
            $stmtVenta->bindParam(':id_cliente', $datosVenta['id_cliente'], PDO::PARAM_INT);
            $stmtVenta->bindParam(':nro_venta_secuencial', $datosVenta['nro_venta_secuencial'], PDO::PARAM_INT);
            $stmtVenta->bindParam(':codigo_venta_referencia', $datosVenta['codigo_venta_referencia'], PDO::PARAM_STR);
            $stmtVenta->bindParam(':fecha_venta', $datosVenta['fecha_venta'], PDO::PARAM_STR);
            $stmtVenta->bindParam(':tipo_comprobante', $datosVenta['tipo_comprobante'], PDO::PARAM_STR);
            $stmtVenta->bindParam(':nro_comprobante_fisico', $datosVenta['nro_comprobante_fisico'], PDO::PARAM_STR);
            $stmtVenta->bindParam(':subtotal_general', $datosVenta['subtotal_general'], PDO::PARAM_STR); // DECIMAL se pasa como string
            $stmtVenta->bindParam(':monto_iva_general', $datosVenta['monto_iva_general'], PDO::PARAM_STR);
            $stmtVenta->bindParam(':descuento_general', $datosVenta['descuento_general'], PDO::PARAM_STR);
            $stmtVenta->bindParam(':total_general', $datosVenta['total_general'], PDO::PARAM_STR);
            $stmtVenta->bindParam(':estado_venta', $estadoVentaPredeterminado, PDO::PARAM_STR); // Puedes cambiar esto
            $stmtVenta->bindParam(':observaciones', $datosVenta['observaciones'], PDO::PARAM_STR);
            $stmtVenta->bindParam(':fyh_creacion', $fechaHoraActual, PDO::PARAM_STR);
            $stmtVenta->bindParam(':fyh_actualizacion', $fechaHoraActual, PDO::PARAM_STR);
            
            $stmtVenta->execute();
            $idVenta = $this->pdo->lastInsertId();

            // 2. Insertar en tb_detalle_ventas y actualizar stock en tb_almacen
            $sqlDetalle = "INSERT INTO tb_detalle_ventas
                                (id_venta, id_producto, cantidad, precio_venta_unitario, 
                                 porcentaje_iva_item, monto_iva_item, descuento_item, 
                                 subtotal_item, total_item, fyh_creacion, fyh_actualizacion)
                           VALUES
                                (:id_venta, :id_producto, :cantidad, :precio_venta_unitario,
                                 :porcentaje_iva_item, :monto_iva_item, :descuento_item,
                                 :subtotal_item, :total_item, :fyh_creacion, :fyh_actualizacion)";
            $stmtDetalle = $this->pdo->prepare($sqlDetalle);

            $sqlActualizarStock = "UPDATE tb_almacen SET stock = stock - :cantidad_vendida, fyh_actualizacion = :fyh_actualizacion 
                                   WHERE id_producto = :id_producto AND id_usuario = :id_usuario";
            $stmtActualizarStock = $this->pdo->prepare($sqlActualizarStock);

            foreach ($itemsVenta as $item) {
                $cantidadItem = floatval($item['cantidad']);
                $precioVentaUnitarioItem = floatval($item['precio_venta_unitario']);
                $porcentajeIvaItem = floatval($item['porcentaje_iva_item']);
                // Asumimos que no hay descuento por item en la interfaz actual, se podría añadir
                $descuentoItem = isset($item['descuento_item']) ? floatval($item['descuento_item']) : 0.00; 

                $subtotalItem = ($cantidadItem * $precioVentaUnitarioItem) - $descuentoItem;
                $montoIvaItem = $subtotalItem * ($porcentajeIvaItem / 100);
                $totalItem = $subtotalItem + $montoIvaItem;

                $stmtDetalle->bindParam(':id_venta', $idVenta, PDO::PARAM_INT);
                $stmtDetalle->bindParam(':id_producto', $item['id_producto'], PDO::PARAM_INT);
                $stmtDetalle->bindParam(':cantidad', $cantidadItem, PDO::PARAM_STR); // DECIMAL
                $stmtDetalle->bindParam(':precio_venta_unitario', $precioVentaUnitarioItem, PDO::PARAM_STR); // DECIMAL
                $stmtDetalle->bindParam(':porcentaje_iva_item', $porcentajeIvaItem, PDO::PARAM_STR); // DECIMAL
                $stmtDetalle->bindParam(':monto_iva_item', $montoIvaItem, PDO::PARAM_STR); // DECIMAL
                $stmtDetalle->bindParam(':descuento_item', $descuentoItem, PDO::PARAM_STR); // DECIMAL
                $stmtDetalle->bindParam(':subtotal_item', $subtotalItem, PDO::PARAM_STR); // DECIMAL
                $stmtDetalle->bindParam(':total_item', $totalItem, PDO::PARAM_STR); // DECIMAL
                $stmtDetalle->bindParam(':fyh_creacion', $fechaHoraActual, PDO::PARAM_STR);
                $stmtDetalle->bindParam(':fyh_actualizacion', $fechaHoraActual, PDO::PARAM_STR);
                $stmtDetalle->execute();

                // Actualizar stock
                $stmtActualizarStock->bindParam(':cantidad_vendida', $cantidadItem, PDO::PARAM_STR); // DECIMAL
                $stmtActualizarStock->bindParam(':fyh_actualizacion', $fechaHoraActual, PDO::PARAM_STR);
                $stmtActualizarStock->bindParam(':id_producto', $item['id_producto'], PDO::PARAM_INT);
                $stmtActualizarStock->bindParam(':id_usuario', $this->id_usuario_sesion, PDO::PARAM_INT);
                $stmtActualizarStock->execute();

                // Validar que el stock no quede negativo (opcional, pero recomendado si no hay validación previa estricta)
                // Esta validación es post-actualización. Idealmente, se valida antes de permitir la venta.
                $stockActual = $this->verificarStockProducto($item['id_producto']);
                if ($stockActual < 0) {
                    // Esto no debería pasar si la interfaz valida bien el stock disponible antes de añadir
                    throw new Exception("Stock insuficiente para el producto ID {$item['id_producto']} después de la venta. Transacción revertida.");
                }
            }

            $this->pdo->commit();
            return $idVenta;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            // Loggear el error $e->getMessage()
            throw new Exception("Error al registrar la venta: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene el stock actual de un producto específico.
     *
     * @param int $id_producto
     * @return float|null El stock actual o null si el producto no existe.
     */
    public function verificarStockProducto($id_producto) {
        $sql = "SELECT stock FROM tb_almacen WHERE id_producto = :id_producto AND id_usuario = :id_usuario";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        $stmt->bindParam(':id_usuario', $this->id_usuario_sesion, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? floatval($resultado['stock']) : null;
    }

    // --- Métodos Adicionales que podrías necesitar (Listar Ventas, Ver Venta, etc.) ---

    /**
     * Obtiene una venta por su ID.
     *
     * @param int $id_venta
     * @return array|false Datos de la venta o false si no se encuentra.
     */
    public function getVentaById($id_venta) {
        $sql = "SELECT v.*, c.nombre_cliente, c.nit_ci_cliente, u.nombres as nombre_vendedor
                FROM tb_ventas as v
                INNER JOIN tb_clientes as c ON v.id_cliente = c.id_cliente
                INNER JOIN tb_usuarios as u ON v.id_usuario = u.id_usuario
                WHERE v.id_venta = :id_venta AND v.id_usuario = :id_usuario_sesion"; // Asegurar que la venta pertenezca al usuario
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
        $stmt->bindParam(':id_usuario_sesion', $this->id_usuario_sesion, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los detalles (productos) de una venta específica.
     *
     * @param int $id_venta
     * @return array Array de items de la venta.
     */
    public function getDetallesVentaById($id_venta) {
        $sql = "SELECT dv.*, p.codigo as codigo_producto, p.nombre as nombre_producto
                FROM tb_detalle_ventas as dv
                INNER JOIN tb_almacen as p ON dv.id_producto = p.id_producto
                WHERE dv.id_venta = :id_venta";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
        // ... (otros métodos existentes) ...

    /**
     * Obtiene todas las ventas registradas por el usuario actual, con opción de paginación y búsqueda para DataTables.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order).
     * @return array Datos para DataTables: ['draw', 'recordsTotal', 'recordsFiltered', 'data'].
     * @throws Exception Si ocurre algún error.
     */
    public function getVentasListadoDT(array $params) {
        if (!$this->id_usuario_sesion) {
            throw new Exception("ID de usuario no establecido en VentasModel para getVentasListadoDT.");
        }

        $bindings = [];
        $sqlBase = "FROM tb_ventas v
                    INNER JOIN tb_clientes c ON v.id_cliente = c.id_cliente
                    WHERE v.id_usuario = :id_usuario_sesion";
        $bindings[':id_usuario_sesion'] = $this->id_usuario_sesion;

        // Búsqueda (search)
        $sqlSearch = "";
        if (!empty($params['search']['value'])) {
            $searchValue = '%' . $params['search']['value'] . '%';
            $sqlSearch = " AND (
                            v.codigo_venta_referencia LIKE :search_value 
                            OR c.nombre_cliente LIKE :search_value 
                            OR v.fecha_venta LIKE :search_value 
                            OR v.total_general LIKE :search_value
                            OR v.estado_venta LIKE :search_value
                          )";
            $bindings[':search_value'] = $searchValue;
        }

        // Conteo total de registros (sin filtros de búsqueda)
        $stmtTotal = $this->pdo->prepare("SELECT COUNT(v.id_venta) " . $sqlBase);
        $stmtTotal->execute([':id_usuario_sesion' => $this->id_usuario_sesion]); // Solo el binding de usuario aquí
        $recordsTotal = $stmtTotal->fetchColumn();

        // Conteo de registros filtrados (con filtros de búsqueda)
        $stmtFiltered = $this->pdo->prepare("SELECT COUNT(v.id_venta) " . $sqlBase . $sqlSearch);
        $stmtFiltered->execute($bindings);
        $recordsFiltered = $stmtFiltered->fetchColumn();

        // Ordenamiento (order)
        $sqlOrder = "";
        if (isset($params['order']) && count($params['order'])) {
            $columnIdx = intval($params['order'][0]['column']);
            $columnDir = $params['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';
            
            // Mapeo de índices de columna de DataTables a nombres de columna de DB
            // Asegúrate que estos índices coincidan con las columnas en tu JS de DataTables
            $columns = ['v.codigo_venta_referencia', 'c.nombre_cliente', 'v.fecha_venta', 'v.total_general', 'v.estado_venta', 'v.fyh_creacion']; 
            if (isset($columns[$columnIdx])) {
                $sqlOrder = " ORDER BY " . $columns[$columnIdx] . " " . $columnDir;
            } else {
                $sqlOrder = " ORDER BY v.fyh_creacion DESC"; // Orden por defecto
            }
        } else {
            $sqlOrder = " ORDER BY v.fyh_creacion DESC"; // Orden por defecto
        }

        // Paginación (limit y offset)
        $sqlLimit = "";
        if (isset($params['start']) && $params['length'] != -1) {
            $sqlLimit = " LIMIT " . intval($params['start']) . ", " . intval($params['length']);
        }

        // Consulta principal para obtener los datos
        $stmtData = $this->pdo->prepare(
            "SELECT v.id_venta, v.codigo_venta_referencia, c.nombre_cliente, v.fecha_venta, 
                    v.total_general, v.estado_venta, v.fyh_creacion "
            . $sqlBase . $sqlSearch . $sqlOrder . $sqlLimit
        );
        $stmtData->execute($bindings);
        $data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

        return [
            "draw" => isset($params['draw']) ? intval($params['draw']) : 0,
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $data
        ];
    }
}



?>