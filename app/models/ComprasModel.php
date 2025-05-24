<?php

require_once __DIR__ . '/AlmacenModel.php'; // Necesario para actualizar stock

class CompraModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene el siguiente número secuencial para una nueva compra de un usuario.
     * Se basa en el valor máximo actual de nro_compra para ese usuario.
     * @param int $id_usuario
     * @return int El siguiente número secuencial.
     */
    public function getSiguienteNumeroCompraSecuencial(int $id_usuario): int {
        $sql = "SELECT MAX(nro_compra) as max_nro FROM tb_compras WHERE id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        $max_nro = $query->fetchColumn();
        return ($max_nro === null) ? 1 : (int)$max_nro + 1;
    }

    /**
     * Formatea el número secuencial de compra al formato C-XXXXX.
     * @param int $numero_secuencial
     * @return string Código formateado.
     */
    public function formatearCodigoCompra(int $numero_secuencial): string {
        return "C-" . str_pad($numero_secuencial, 5, "0", STR_PAD_LEFT);
    }
    
    /**
     * Registra una nueva compra con sus detalles (múltiples productos).
     * Utiliza transacciones para asegurar la integridad de los datos.
     *
     * @param array $datosCabecera Datos para la tabla tb_compras.
     * @param array $datosItems Array de ítems para la tabla tb_detalle_compras.
     * @return mixed El ID de la compra registrada en caso de éxito, o un array con un mensaje de error en caso de fallo.
     */
    public function registrarCompraConDetalles(array $datosCabecera, array $datosItems) {
        $almacenModel = new AlmacenModel($this->pdo);
        $id_usuario_compra = $datosCabecera['id_usuario']; 

        try {
            $this->pdo->beginTransaction();

            $nro_compra_secuencial = $this->getSiguienteNumeroCompraSecuencial($id_usuario_compra);
            $codigo_compra_formateado = $this->formatearCodigoCompra($nro_compra_secuencial);

            $subtotal_general_calculado = 0;
            $monto_iva_general_calculado = 0;
            foreach ($datosItems as $item) {
                $subtotal_general_calculado += $item['subtotal_item'];
                $monto_iva_general_calculado += $item['monto_iva_item'];
            }
            $total_general_calculado = $subtotal_general_calculado + $monto_iva_general_calculado;

            $sql_compra = "INSERT INTO tb_compras 
                                (nro_compra, codigo_compra_referencia, fecha_compra, id_proveedor, comprobante, id_usuario, 
                                 subtotal_general, monto_iva_general, total_general, fyh_creacion, fyh_actualizacion) 
                           VALUES 
                                (:nro_compra, :codigo_compra_referencia, :fecha_compra, :id_proveedor, :comprobante, :id_usuario, 
                                 :subtotal_general, :monto_iva_general, :total_general, :fyh_creacion, :fyh_actualizacion)";
            
            $query_compra = $this->pdo->prepare($sql_compra);
            $query_compra->bindValue(':nro_compra', $nro_compra_secuencial, PDO::PARAM_INT);
            $query_compra->bindValue(':codigo_compra_referencia', $codigo_compra_formateado, PDO::PARAM_STR);
            $query_compra->bindValue(':fecha_compra', $datosCabecera['fecha_compra'], PDO::PARAM_STR);
            $query_compra->bindValue(':id_proveedor', $datosCabecera['id_proveedor'], PDO::PARAM_INT);
            $query_compra->bindValue(':comprobante', $datosCabecera['comprobante'], PDO::PARAM_STR);
            $query_compra->bindValue(':id_usuario', $id_usuario_compra, PDO::PARAM_INT);
            $query_compra->bindValue(':subtotal_general', $subtotal_general_calculado, PDO::PARAM_STR); 
            $query_compra->bindValue(':monto_iva_general', $monto_iva_general_calculado, PDO::PARAM_STR);
            $query_compra->bindValue(':total_general', $total_general_calculado, PDO::PARAM_STR);
            $query_compra->bindValue(':fyh_creacion', $datosCabecera['fyh_creacion'], PDO::PARAM_STR);
            $query_compra->bindValue(':fyh_actualizacion', $datosCabecera['fyh_actualizacion'], PDO::PARAM_STR);
            
            if (!$query_compra->execute()) {
                throw new PDOException("Error al insertar la cabecera de la compra.");
            }
            $id_compra_nueva = $this->pdo->lastInsertId();

            $sql_detalle = "INSERT INTO tb_detalle_compras 
                                (id_compra, id_producto, cantidad, precio_compra_unitario, porcentaje_iva_item, 
                                 subtotal_item, monto_iva_item, total_item, fyh_creacion, fyh_actualizacion)
                            VALUES
                                (:id_compra, :id_producto, :cantidad, :precio_compra_unitario, :porcentaje_iva_item, 
                                 :subtotal_item, :monto_iva_item, :total_item, :fyh_creacion, :fyh_actualizacion)";
            $query_detalle = $this->pdo->prepare($sql_detalle);

            foreach ($datosItems as $item) {
                $query_detalle->bindValue(':id_compra', $id_compra_nueva, PDO::PARAM_INT);
                $query_detalle->bindValue(':id_producto', $item['id_producto'], PDO::PARAM_INT);
                $query_detalle->bindValue(':cantidad', $item['cantidad'], PDO::PARAM_STR); 
                $query_detalle->bindValue(':precio_compra_unitario', $item['precio_compra_unitario'], PDO::PARAM_STR);
                $query_detalle->bindValue(':porcentaje_iva_item', $item['porcentaje_iva_item'], PDO::PARAM_STR);
                $query_detalle->bindValue(':subtotal_item', $item['subtotal_item'], PDO::PARAM_STR);
                $query_detalle->bindValue(':monto_iva_item', $item['monto_iva_item'], PDO::PARAM_STR);
                $query_detalle->bindValue(':total_item', $item['total_item'], PDO::PARAM_STR);
                $query_detalle->bindValue(':fyh_creacion', $item['fyh_creacion'], PDO::PARAM_STR);
                $query_detalle->bindValue(':fyh_actualizacion', $item['fyh_actualizacion'], PDO::PARAM_STR);

                if (!$query_detalle->execute()) {
                    throw new PDOException("Error al insertar el detalle del producto ID: " . $item['id_producto']);
                }

                if (!$almacenModel->ajustarStockProducto($item['id_producto'], (float)$item['cantidad'], $id_usuario_compra)) {
                     throw new Exception("Error al actualizar el stock para el producto ID: " . $item['id_producto']);
                }
            }

            $this->pdo->commit();
            return $id_compra_nueva; 

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error PDO en ComprasModel->registrarCompraConDetalles: " . $e->getMessage());
            return ['error' => "Error de base de datos: " . $e->getMessage()];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error General en ComprasModel->registrarCompraConDetalles: " . $e->getMessage());
            return ['error' => "Error general: " . $e->getMessage()];
        }
    }

    /**
     * Obtiene todas las compras (cabeceras) de un usuario específico.
     * Se une con la tabla de proveedores para obtener el nombre del proveedor.
     * @param int $id_usuario
     * @return array Lista de compras.
     */
    public function getComprasPorUsuarioId(int $id_usuario): array {
        $sql = "SELECT 
                    c.id_compra, 
                    c.nro_compra, 
                    c.codigo_compra_referencia, 
                    c.fecha_compra, 
                    c.comprobante, 
                    c.total_general,
                    c.fyh_creacion,
                    p.nombre_proveedor,
                    p.empresa as empresa_proveedor
                FROM 
                    tb_compras as c
                INNER JOIN 
                    tb_proveedores as p ON c.id_proveedor = p.id_proveedor
                WHERE 
                    c.id_usuario = :id_usuario
                ORDER BY 
                    c.fecha_compra DESC, c.id_compra DESC";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los datos completos de una compra específica, incluyendo sus detalles (ítems)
     * y la información del proveedor.
     * Verifica que la compra pertenezca al usuario especificado.
     * @param int $id_compra
     * @param int $id_usuario
     * @return array|false Los datos de la compra con sus detalles, o false si no se encuentra o no pertenece al usuario.
     */
    public function getCompraConDetallesPorId(int $id_compra, int $id_usuario) {
        // 1. Obtener la cabecera de la compra y datos del proveedor
        $sql_cabecera = "SELECT 
                            c.*, 
                            p.nombre_proveedor, 
                            p.celular as celular_proveedor, 
                            p.telefono as telefono_proveedor, 
                            p.empresa as empresa_proveedor, 
                            p.email as email_proveedor, 
                            p.direccion as direccion_proveedor
                         FROM tb_compras as c
                         INNER JOIN tb_proveedores as p ON c.id_proveedor = p.id_proveedor
                         WHERE c.id_compra = :id_compra AND c.id_usuario = :id_usuario";
        
        $query_cabecera = $this->pdo->prepare($sql_cabecera);
        $query_cabecera->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
        $query_cabecera->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query_cabecera->execute();
        $compra_cabecera = $query_cabecera->fetch(PDO::FETCH_ASSOC);

        if (!$compra_cabecera) {
            return false; // La compra no existe o no pertenece al usuario
        }

        // 2. Obtener los detalles (ítems) de la compra
        $sql_detalles = "SELECT 
                            dc.*,
                            prod.nombre as nombre_producto,
                            prod.codigo as codigo_producto
                         FROM tb_detalle_compras as dc
                         INNER JOIN tb_almacen as prod ON dc.id_producto = prod.id_producto
                         WHERE dc.id_compra = :id_compra
                         ORDER BY dc.id_detalle_compra ASC";
        
        $query_detalles = $this->pdo->prepare($sql_detalles);
        $query_detalles->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
        $query_detalles->execute();
        $compra_detalles = $query_detalles->fetchAll(PDO::FETCH_ASSOC);

        // 3. Combinar cabecera y detalles
        $compra_cabecera['detalles'] = $compra_detalles;

        return $compra_cabecera;
    }
}
?>