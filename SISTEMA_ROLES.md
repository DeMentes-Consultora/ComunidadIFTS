# Sistema de Roles por ID

Este proyecto utiliza un **sistema de roles basado en IDs** en lugar de nombres para mayor seguridad y eficiencia en las consultas SQL.

## Roles Definidos

| ID | Nombre del Rol | Permisos |
|----|----------------|----------|
| 1 | AdministradorComunidad | Control total del sistema, puede crear/editar/eliminar IFTS y gestionar usuarios |
| 2 | Alumno | Solo lectura, puede navegar y ver información de IFTS |
| 3 | AdministradorIFTS | Puede crear/editar IFTS de su institución |

## Permisos por Funcionalidad

### IFTS (Instituciones)

**Roles que pueden CREAR y EDITAR:** `[1, 3]`
- AdministradorComunidad (ID 1)
- AdministradorIFTS (ID 3)

**Roles de SOLO LECTURA:** Cualquier otro ID

### Implementación

#### Frontend (TypeScript/Angular)

```typescript
// En mapa.ts
private verificarPermisos(): void {
  const usuarioActual = this.authService.getCurrentUser();
  if (usuarioActual) {
    // Solo los roles 1 y 3 pueden editar IFTS
    this.canEdit = [1, 3].includes(usuarioActual.id_rol);
  }
}
```

#### Backend (PHP)

```php
// En guardar-institucion.php y actualizar-institucion.php
$rolesPermitidos = [1, 3];
if (!in_array($_SESSION['id_rol'], $rolesPermitidos)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para modificar instituciones']);
    exit;
}
```

```php
// En register.php - Rol predeterminado para nuevos usuarios
function obtenerRolAlumno($pdo) {
    $idRolAlumno = 2; // ID del rol "Alumno regular"
    
    $sql = "SELECT id_rol FROM rol WHERE id_rol = ? AND habilitado = 1 AND cancelado = 0 LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idRolAlumno]);
    $row = $stmt->fetch();
    return $row ? (int)$row['id_rol'] : null;
}
```

## Ventajas del Sistema por ID

1. **Performance:** Las consultas por ID son más rápidas que por string
2. **Seguridad:** Evita inyección SQL y typos en nombres
3. **Consistencia:** El ID nunca cambia aunque se modifique el nombre del rol
4. **Simplicidad:** Comparaciones numéricas más eficientes que comparaciones de texto
5. **Internacionalización:** El nombre del rol puede traducirse sin afectar la lógica

## Tabla de Base de Datos

```sql
CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(250) NOT NULL,
  `habilitado` int(11) NOT NULL DEFAULT 1,
  `cancelado` int(11) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos de roles
INSERT INTO `rol` VALUES
(1, 'AdministradorComunidad', 1, 0),
(2, 'Alumno', 1, 0),
(3, 'AdministradorIFTS', 1, 0);
```

## Buenas Prácticas

### ✅ HACER
- Usar IDs en consultas SQL: `WHERE id_rol = 1`
- Usar arrays de IDs para permisos: `[1, 3].includes(id_rol)`
- Documentar qué permisos tiene cada ID

### ❌ NO HACER
- Buscar por nombre: `WHERE nombre_rol = 'Administrador'`
- Hardcodear nombres de roles en código
- Comparar strings de nombres de roles

## Mantenimiento

Si necesitas agregar un nuevo rol:
1. Insértalo en la tabla `rol` con un ID único
2. Actualiza esta documentación
3. Actualiza los arrays de permisos donde corresponda
4. Prueba la funcionalidad en frontend y backend
