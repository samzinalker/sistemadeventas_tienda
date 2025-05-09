# Checklist de Seguridad y Aislamiento de Datos en "sistemadeventas_3"

## Para cada módulo (Carrito, Ventas, Compras, Clientes, Almacén):

- [ ] **Filtro por usuario en consultas SQL**  
      ¿Todas las consultas (SELECT) incluyen `WHERE id_usuario = :id_usuario`?  
      ¿Usan el valor de `$_SESSION['id_usuario']`?

- [ ] **Inserciones asociadas al usuario**  
      ¿Al crear un registro, se guarda el campo `id_usuario` con el valor del usuario autenticado?

- [ ] **Edición/Borrado solo sobre datos propios**  
      ¿Al editar o borrar, se verifica que el registro pertenece al usuario autenticado?

- [ ] **Menús y vistas protegidos**  
      ¿El menú de usuarios y roles solo es visible para el admin?  
      ¿El admin NO ve ventas, carritos, compras, etc. de otros usuarios?

- [ ] **No hay datos cruzados ni para admin**  
      ¿El admin, en los módulos operativos, solo ve sus propios registros?

- [ ] **Validación adicional en controladores**  
      ¿Hay validaciones extra para evitar manipulación por URL o peticiones maliciosas?

---

## Ejemplo de consulta SQL filtrada por usuario

```php
$id_usuario = $_SESSION['id_usuario'];
$sql = "SELECT * FROM tb_ventas WHERE id_usuario = :id_usuario";
$query = $pdo->prepare($sql);
$query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$query->execute();
$ventas = $query->fetchAll(PDO::FETCH_ASSOC);
```

---

## Ejemplo de inserción asociada al usuario

```php
$id_usuario = $_SESSION['id_usuario'];
$sql = "INSERT INTO tb_ventas (id_usuario, ...) VALUES (:id_usuario, ...)";
$query = $pdo->prepare($sql);
// ...otros bindParam
$query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$query->execute();
```

---

## Ejemplo de edición/borrado seguro

```php
$id_usuario = $_SESSION['id_usuario'];
$id_venta = $_POST['id_venta'];
$sql = "UPDATE tb_ventas SET ... WHERE id_venta = :id_venta AND id_usuario = :id_usuario";
$query = $pdo->prepare($sql);
$query->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
$query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$query->execute();
```

---

## Menú solo para admin

```php
<?php if($_SESSION['rol'] === 'administrador'): ?>
    <!-- menú de usuarios/roles -->
<?php endif; ?>
```

---

## Prueba tu sistema

1. Inicia sesión como un usuario regular y revisa cada módulo: solo debes ver tus propios datos.
2. Inicia sesión como administrador: en los módulos de ventas, compras, etc., solo debes ver tus propios datos.  
   Solo en el menú de administración de usuarios/roles tendrás acceso extra.

---