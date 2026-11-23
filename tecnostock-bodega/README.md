# TecnoStock

Sistema web MVP para controlar el inventario de una tienda de tecnología. Resuelve la falta de visibilidad de existencias y de trazabilidad de entradas y salidas: centraliza los productos, alerta sobre stock bajo y conserva quién registró cada movimiento.

## Alcance del MVP

- Inicio de sesión y registro de usuarios con contraseñas protegidas.
- Catálogo de productos activos con búsqueda por nombre/código y filtro por categoría.
- Alta, edición y desactivación lógica de productos.
- Alertas cuando el stock actual es igual o inferior al mínimo configurado.
- Registro transaccional de entradas/salidas e historial auditable.
- No incluye roles, ventas, compras, reportes exportables ni gestión de categorías desde la interfaz.

**Actor principal:** encargado/a de inventario (usuario autenticado).

## Requerimientos funcionales

- **RF1:** El sistema permite iniciar sesión y cerrar sesión de forma segura.
- **RF2:** El sistema permite crear una cuenta de usuario.
- **RF3:** El sistema muestra un resumen de productos activos y alertas de stock bajo.
- **RF4:** El encargado busca productos por nombre o código y filtra por categoría.
- **RF5:** El encargado crea y edita productos, con código único y datos no negativos.
- **RF6:** El encargado desactiva productos sin borrar su información histórica.
- **RF7:** El encargado registra entradas y salidas; una salida nunca excede las existencias.
- **RF8:** El sistema muestra historial de movimientos con fecha, producto, tipo, cantidad, motivo y usuario.

## Reglas de negocio

1. Solo los productos activos aparecen en el inventario y se pueden mover.
2. `codigo` identifica de forma única cada producto, incluso si fue desactivado.
3. Precio, stock actual y stock mínimo no pueden ser negativos; los stocks son enteros.
4. La alerta se activa si `stock_actual <= stock_minimo`.
5. Una entrada suma existencias; una salida las resta y debe ser menor o igual al stock bloqueado en la transacción.
6. Cada movimiento necesita cantidad positiva, motivo, usuario y producto existente.
7. El borrado físico está restringido para conservar la trazabilidad; se desactiva usando `estado = 0`.
8. Las escrituras del movimiento y el ajuste de stock se confirman o revierten juntas.

## Requisitos

- XAMPP con PHP 8.x, Apache, MySQL/MariaDB y extensión `pdo_mysql` habilitada.
- Navegador moderno y conexión a internet para cargar Bootstrap 5.3.3 desde CDN.

## Instalación en XAMPP / phpMyAdmin

1. Copia la carpeta `tecnostock-bodega` en `C:\xampp\htdocs\`.
2. En el panel de XAMPP, inicia **Apache** y **MySQL**.
3. Abre `http://localhost/phpmyadmin`, selecciona **Importar**, elige `tecnostock.sql` y ejecuta el script. Este crea `tecnostock`, elimina las tablas previas de ese esquema y carga datos de demostración.
4. Verifica `config/conexion.php`. La configuración inicial usa `localhost`, usuario `root` y contraseña vacía, como en la instalación estándar de XAMPP.
5. Abre `http://localhost/tecnostock-bodega/`.
6. Acceso inicial: `admin@tecnostock.cl` / `tecno123`. Cambia la clave desde un flujo de administración antes de usarlo con datos reales; el MVP no incluye cambio de contraseña.

El usuario de demostración se almacena usando `password_hash` (compatible con `password_verify`). La conexión usa PDO, excepciones y consultas preparadas para los valores variables.

## Casos de prueba

| Caso | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| CP-01 | Iniciar sesión con la cuenta de demostración | Se abre el panel con los productos de prueba | Verificado por revisión de flujo; requiere MySQL/XAMPP para ejecución integrada |
| CP-02 | Buscar `SSD` o el código `COM-SSD1T` | Se muestra el SSD correspondiente | Verificado por revisión de consulta preparada; requiere MySQL/XAMPP |
| CP-03 | Abrir el panel con `RED-SW08` y `COM-SSD1T` | Ambos muestran alerta porque su stock está bajo el mínimo | Verificado por comparación `<=`; requiere MySQL/XAMPP |
| CP-04 | Crear/editar producto con precio o stock negativo | El servidor rechaza los datos; el navegador también valida | Verificado por revisión de validaciones; requiere MySQL/XAMPP |
| CP-05 | Crear producto con código ya usado | Se informa duplicidad y no se guarda | Verificado por revisión de consulta y restricción `UNIQUE`; requiere MySQL/XAMPP |
| CP-06 | Registrar salida mayor que stock disponible | Se revierte la transacción y no cambia el stock | Verificado por revisión de bloqueo `FOR UPDATE` y rollback; requiere MySQL/XAMPP |
| CP-07 | Registrar entrada válida y revisar historial | Aumenta el stock y queda un movimiento con usuario/fecha | Verificado por revisión de transacción; requiere MySQL/XAMPP |
| CP-08 | Desactivar producto | Desaparece del panel sin borrar historial ni registro | Verificado por revisión de actualización lógica; requiere MySQL/XAMPP |

La ejecución integrada de estos casos debe realizarse tras importar el SQL y arrancar XAMPP.
