# TecnoStock

Sistema web MVP para controlar el inventario de una tienda de tecnología. Resuelve la falta de visibilidad de existencias y de trazabilidad de entradas y salidas: centraliza los productos, alerta sobre stock bajo y conserva quién registró cada movimiento.

## Alcance del MVP

- Inicio de sesión y registro de usuarios con contraseñas protegidas.
- Catálogo de productos con búsqueda por nombre/código y filtro por categoría.
- Alta y edición de productos.
- Alertas cuando el stock actual es igual o inferior al mínimo configurado.
- Registro transaccional de entradas/salidas e historial auditable.
- No incluye roles, ventas, compras, reportes exportables ni gestión de categorías desde la interfaz.

**Actor principal:** encargado/a de inventario (usuario autenticado).

## Requerimientos funcionales

- **RF1:** El sistema permite iniciar sesión y cerrar sesión de forma segura.
- **RF2:** El sistema permite crear una cuenta de usuario.
- **RF3:** El sistema muestra un resumen de productos y alertas de stock bajo.
- **RF4:** El encargado busca productos por nombre o código y filtra por categoría.
- **RF5:** El encargado crea y edita productos, con código único y datos no negativos.
- **RF6:** El encargado registra entradas y salidas; una salida nunca excede las existencias.
- **RF7:** El sistema muestra historial de movimientos con fecha, producto, tipo, cantidad, motivo y usuario.

