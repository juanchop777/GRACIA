# API REST - Gracia

API para que la app Flutter consuma los datos del backend.

**Base URL:** `http://localhost/gracia/api/` (o la URL de tu servidor + `/api/`)

## Endpoints

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| POST | login.php | No | Login. Body: `{ "correo", "contrasena" }`. Devuelve `token` y `usuario`. |
| POST | registro.php | No | Registro. Body: `{ "nombre", "correo", "contrasena" }`. |
| GET | productos.php | No | Lista de productos. Query: `?categoria_id=uuid&limite=n`. |
| GET | productos.php?id=uuid | No | Un producto por ID. |
| POST | productos.php | Sí (admin) | Crear producto. Body: nombre, descripcion, precio, stock, categoria (nombre), imagen (opc). |
| PUT | productos.php?id=uuid | Sí (admin) | Actualizar producto. Body: nombre, descripcion, precio, stock, categoria, imagen (opc). |
| DELETE | productos.php?id=uuid | Sí (admin) | Eliminar producto. |
| GET | categorias.php | No | Lista de categorías. |
| GET | perfil.php | Sí | Usuario actual (Bearer token). |
| GET | pedidos.php | Sí | Pedidos del usuario. |
| POST | pedidos.php | Sí | Crear pedido. Body: `{ "items": [ { "producto_id", "cantidad", "precio_unitario" } ] }`. |

**Cabecera de autenticación:** `Authorization: Bearer <token>` o `X-Api-Token: <token>`.
