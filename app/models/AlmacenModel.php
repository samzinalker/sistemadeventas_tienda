<?php

class AlmacenModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ... (métodos existentes: generarCodigoProducto, crearProducto, getProductosByUsuarioId, etc.) ...
    // COPIAR Y PEGAR LOS MÉTODOS EXISTENTES AQUÍ PARA MANTENER EL ARCHIVO COMPLETO

    /**
     * Genera un nuevo código de producto único para un usuario.
     * Formato: P-XXXXX (donde XXXXX es un número secuencial para ese usuario)
     * @param int $id_usuario
     * @return string
     */
    public function generarCodigoProducto(int $id_usuario): string {
        // Esta es una forma simple. Para alta concurrencia, se podría necesitar un enfoque más robusto.
        $sql = "SELECT COUNT(*) FROM tb_almacen WHERE id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        $total_productos_usuario = $query->fetchColumn();
        $siguiente_numero = $total_productos_usuario + 1;
        
        // Busca el último código numérico para evitar colisiones si se borran productos intermedios
        $sql_last = "SELECT MAX(CAST(SUBSTRING_INDEX(codigo, '-', -1) AS UNSIGNED)) as max_codigo 
                     FROM tb_almacen WHERE id_usuario = :id_usuario AND codigo LIKE 'P-%'";
        $query_last = $this->pdo->prepare($sql_last);
        $query_last->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query_last->execute();
        $max_codigo_actual = $query_last->fetchColumn();
        
        if ($max_codigo_actual >= $siguiente_numero) {
            $siguiente_numero = $max_codigo_actual + 1;
        }

        return "P-" . str_pad($siguiente_numero, 5, "0", STR_PAD_LEFT);
    }


    /**
     * Crea un nuevo producto para un usuario.
     * @param array $datos Datos del producto.
     * @return string|false El ID del producto creado o false en error.
     */
    public function crearProducto(array $datos): ?string {
        $sql = "INSERT INTO tb_almacen (codigo, nombre, descripcion, stock, stock_minimo, stock_maximo, 
                                      precio_compra, precio_venta, iva_predeterminado, fecha_ingreso, imagen, 
                                      id_usuario, id_categoria, fyh_creacion, fyh_actualizacion)
                VALUES (:codigo, :nombre, :descripcion, :stock, :stock_minimo, :stock_maximo, 
                        :precio_compra, :precio_venta, :iva_predeterminado, :fecha_ingreso, :imagen, 
                        :id_usuario, :id_categoria, :fyh_creacion, :fyh_actualizacion)";
        
        $query = $this->pdo->prepare($sql);
        // Bind todos los parámetros desde el array $datos
        foreach ($datos as $key => $value) {
            $paramType = PDO::PARAM_STR;
            if (is_int($value) || $key === 'id_usuario' || $key === 'id_categoria' || $key === 'stock' || $key === 'stock_minimo' || $key === 'stock_maximo') {
                $paramType = PDO::PARAM_INT;
            } elseif (is_float($value) || $key === 'precio_compra' || $key === 'precio_venta' || $key === 'iva_predeterminado') {
                $paramType = PDO::PARAM_STR; 
            }
            $query->bindValue(":$key", $value, $paramType);
        }

        if ($query->execute()) {
            return $this->pdo->lastInsertId();
        }
        return null;
    }

    /**
     * Obtiene todos los productos de un usuario específico, uniéndose con categorías.
     * @param int $id_usuario
     * @return array
     */
    public function getProductosByUsuarioId(int $id_usuario): array {
        $sql = "SELECT p.*, c.nombre_categoria as categoria 
                FROM tb_almacen as p
                INNER JOIN tb_categorias as c ON p.id_categoria = c.id_categoria
                WHERE p.id_usuario = :id_usuario
                ORDER BY p.nombre ASC";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getProductoByIdAndUsuarioId(int $id_producto, int $id_usuario) {
        $sql = "SELECT p.*, c.nombre_categoria as categoria
                FROM tb_almacen as p
                INNER JOIN tb_categorias as c ON p.id_categoria = c.id_categoria
                WHERE p.id_producto = :id_producto AND p.id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC); 
    }
    
    public function actualizarProducto(int $id_producto, int $id_usuario, array $datos): bool {
        $producto_actual = $this->getProductoByIdAndUsuarioId($id_producto, $id_usuario);
        if (!$producto_actual) {
            return false; 
        }
        $set_parts = [];
        $campos_permitidos_actualizar = array_keys($producto_actual); 
        foreach (array_keys($datos) as $key) {
            if ($key !== 'fyh_actualizacion' && $key !== 'imagen' && in_array($key, $campos_permitidos_actualizar)) {
                 $set_parts[] = "$key = :$key";
            }
        }
        if (!empty($datos['imagen'])) {
            $set_parts[] = "imagen = :imagen";
        }
        $set_parts[] = "fyh_actualizacion = :fyh_actualizacion";
        if (empty($set_parts)) return false; 
        $sql = "UPDATE tb_almacen SET " . implode(', ', $set_parts) . 
               " WHERE id_producto = :id_producto_cond AND id_usuario = :id_usuario_cond";
        $query = $this->pdo->prepare($sql);
        foreach ($datos as $key => $value) {
            if ($key !== 'fyh_actualizacion' && $key !== 'imagen' && in_array($key, $campos_permitidos_actualizar)) {
                 $paramType = PDO::PARAM_STR;
                 if (is_int($value) || $key === 'id_categoria' || $key === 'stock' || $key === 'stock_minimo' || $key === 'stock_maximo') {
                     $paramType = PDO::PARAM_INT;
                 } elseif (is_float($value) || $key === 'precio_compra' || $key === 'precio_venta' || $key === 'iva_predeterminado') {
                     $paramType = PDO::PARAM_STR;
                 }
                 $query->bindValue(":$key", $value, $paramType);
            }
        }
        if (!empty($datos['imagen'])) {
            $query->bindValue(':imagen', $datos['imagen'], PDO::PARAM_STR);
        }
        $query->bindValue(':fyh_actualizacion', $datos['fyh_actualizacion'], PDO::PARAM_STR);
        $query->bindValue(':id_producto_cond', $id_producto, PDO::PARAM_INT);
        $query->bindValue(':id_usuario_cond', $id_usuario, PDO::PARAM_INT);
        return $query->execute();
    }

    /**
     * Ajusta el stock de un producto específico.
     * Si la cantidad es positiva, incrementa el stock. Si es negativa, lo decrementa.
     * @param int $id_producto ID del producto.
     * @param float $cantidad_ajuste La cantidad a sumar/restar (puede ser decimal si el stock fuera decimal).
     * @param int $id_usuario_producto ID del usuario propietario del producto (para seguridad).
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     * @throws Exception Si el producto no se encuentra o no pertenece al usuario.
     */
    public function ajustarStockProducto(int $id_producto, float $cantidad_ajuste, int $id_usuario_producto): bool {
        // Primero, verificar que el producto exista y pertenezca al usuario.
        $producto = $this->getProductoByIdAndUsuarioId($id_producto, $id_usuario_producto);
        if (!$producto) {
            // Lanzar una excepción es una buena práctica aquí para que la transacción en ComprasModel haga rollback.
            throw new Exception("Producto con ID $id_producto no encontrado o no pertenece al usuario $id_usuario_producto.");
        }

        // Dado que tb_almacen.stock es INT, convertimos la cantidad de ajuste a entero.
        // Decide tu política de redondeo aquí si $cantidad_ajuste es decimal.
        // Por simplicidad, usaremos intval(), que trunca.
        $cantidad_ajuste_entero = intval($cantidad_ajuste);

        // Si $cantidad_ajuste_entero es 0 después de intval() y $cantidad_ajuste original no era 0,
        // podrías querer manejarlo de forma diferente (ej. no hacer nada o registrar un aviso).
        // Por ahora, si resulta en 0, no se hará cambio de stock si el original no era 0.
        // if ($cantidad_ajuste_entero == 0 && $cantidad_ajuste != 0) {
        //     // Opcional: log o manejo especial
        // }


        $sql = "UPDATE tb_almacen SET stock = stock + :cantidad_ajuste, fyh_actualizacion = :fyh_actualizacion
                WHERE id_producto = :id_producto AND id_usuario = :id_usuario_producto";
        
        $query = $this->pdo->prepare($sql);
        $query->bindValue(':cantidad_ajuste', $cantidad_ajuste_entero, PDO::PARAM_INT); // Sumar la cantidad entera
        $query->bindValue(':fyh_actualizacion', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $query->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
        $query->bindValue(':id_usuario_producto', $id_usuario_producto, PDO::PARAM_INT);
        
        return $query->execute();
    }


    public function productoEnUso(int $id_producto): bool {
        $sql_carrito = "SELECT COUNT(*) FROM tb_carrito WHERE id_producto = :id_producto";
        $query_carrito = $this->pdo->prepare($sql_carrito);
        $query_carrito->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        $query_carrito->execute();
        if ($query_carrito->fetchColumn() > 0) return true;

        // Asumiendo que la tabla tb_compras ya NO tiene id_producto directamente,
        // sino que se relaciona a través de tb_detalle_compras.
        // Si tb_compras aún tiene id_producto, la siguiente línea es correcta.
        // Si ya se migró a tb_detalle_compras, esta verificación debe cambiar.
        // BASADO EN EL SQL DEL PROMPT, tb_compras AÚN TIENE id_producto.
        $sql_compras_directa = "SELECT COUNT(*) FROM tb_compras WHERE id_producto = :id_producto_directo";
        $query_compras_directa = $this->pdo->prepare($sql_compras_directa);
        $query_compras_directa->bindParam(':id_producto_directo', $id_producto, PDO::PARAM_INT);
        $query_compras_directa->execute();
        if ($query_compras_directa->fetchColumn() > 0) return true;
        
        // Si ya tienes tb_detalle_compras, la verificación sería:
        
        $sql_detalle_compras = "SELECT COUNT(*) FROM tb_detalle_compras WHERE id_producto = :id_producto_detalle";
        $query_detalle_compras = $this->pdo->prepare($sql_detalle_compras);
        $query_detalle_compras->bindParam(':id_producto_detalle', $id_producto, PDO::PARAM_INT);
        $query_detalle_compras->execute();
        if ($query_detalle_compras->fetchColumn() > 0) return true;
        
        
        return false;
    }

    public function eliminarProducto(int $id_producto, int $id_usuario): ?string {
        $producto_actual = $this->getProductoByIdAndUsuarioId($id_producto, $id_usuario);
        if (!$producto_actual) return null; 
        if ($this->productoEnUso($id_producto)) return null; 
        $sql = "DELETE FROM tb_almacen WHERE id_producto = :id_producto AND id_usuario = :id_usuario";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        if ($query->execute() && $query->rowCount() > 0) {
            return $producto_actual['imagen']; 
        }
        return null;
    }
}
?>