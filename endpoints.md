# Catálogo y Especificación de Endpoints - API REST

Esta guía detalla el uso, parámetros y payloads para interactuar con todos los endpoints expuestos por la API de Gestión de Inventario, incluyendo autenticación y control de usuarios.

---

## ⚙️ Cabeceras Obligatorias (Headers)
Para garantizar la correcta respuesta de la API, incluye siempre las siguientes cabeceras en tus peticiones:
```http
Accept: application/json
Content-Type: application/json
```

---

## 🔐 0. Autenticación (`/api/login`, `/api/logout`, `/api/me`)

Todas las rutas protegidas requieren pasar un token Bearer en las cabeceras:
```http
Authorization: Bearer <tu_token_sanctum>
```

### Iniciar Sesión (Login)
* **Método**: `POST`
* **Ruta**: `/api/login` (Pública, no requiere token)
* **Cuerpo de Petición (JSON)**:
  ```json
  {
      "username": "admin",
      "password": "admin123"
  }
  ```
* **Respuesta exitosa (`200 OK`)**:
  ```json
  {
      "message": "Inicio de sesión exitoso",
      "access_token": "1|b78a9c8f0...",
      "token_type": "Bearer",
      "user": {
          "id": 1,
          "first_name": "Administrador",
          "last_name": "Sistema",
          "username": "admin",
          "role": "admin",
          "email": "admin@inventario.com"
      }
  }
  ```

### Obtener Perfil Actual (Me)
* **Método**: `GET`
* **Ruta**: `/api/me` (Protegida)
* **Respuesta exitosa (`200 OK`)**: Devuelve el perfil completo del usuario autenticado.

### Cerrar Sesión (Logout)
* **Método**: `POST`
* **Ruta**: `/api/logout` (Protegida)
* **Respuesta exitosa (`200 OK`)**:
  ```json
  {
      "message": "Sesión cerrada exitosamente y token revocado."
  }
  ```

---

## 👥 1. Administración de Usuarios (`/api/users`) - [Solo Administradores]

### Registrar Nuevo Usuario
* **Método**: `POST`
* **Ruta**: `/api/users` (Protegida)
* **Cuerpo de Petición (JSON)**:
  ```json
  {
      "first_name": "Carlos Alberto",
      "last_name": "Pérez Gómez",
      "cedula": "22334455",
      "gender": "M",
      "mobile_phone": "04123334455",
      "email": "carlos.perez@inventario.com",
      "role": "user"
  }
  ```
* **Lógica automática**:
  * **Nombre de usuario**: Generado automáticamente como `nombre.apellido` (`carlos.perez`). Si ya existe, añade un correlativo (`carlos.perez1`).
  * **Contraseña por defecto**: Se le asigna automáticamente el número de su `cedula` (`22334455`) cifrado mediante bcrypt.
* **Respuesta exitosa (`210 Created` / `201 Created`)**:
  ```json
  {
      "message": "Usuario registrado exitosamente",
      "data": {
          "id": 3,
          "first_name": "Carlos Alberto",
          "last_name": "Pérez Gómez",
          "cedula": "22334455",
          "gender": "M",
          "email": "carlos.perez@inventario.com",
          "username": "carlos.perez",
          "role": "user"
      },
      "credenciales_generadas": {
          "username": "carlos.perez",
          "password_temporal": "22334455"
      }
  }
  ```

### Listar Usuarios
* **Método**: `GET`
* **Ruta**: `/api/users` (Protegida)
* **Respuesta exitosa (`200 OK`)**: Devuelve un listado completo con todos los usuarios registrados.

### Ver Detalle de Usuario
* **Método**: `GET`
* **Ruta**: `/api/users/{id}` (Protegida)
* **Respuesta exitosa (`200 OK`)**: Devuelve la información del usuario consultado.

### Eliminar Usuario
* **Método**: `DELETE`
* **Ruta**: `/api/users/{id}` (Protegida)
* **Respuesta exitosa (`200 OK`)**:
  ```json
  {
      "message": "Usuario eliminado exitosamente."
  }
  ```
  *(Nota: Un usuario administrador no puede eliminarse a sí mismo para evitar el bloqueo del sistema).*

---

## 🔑 1.1. Roles y Permisos (`/api/roles`, `/api/permissions`) - [Solo Administradores / users.manage]

### Listar Roles
* **Método**: `GET`
* **Ruta**: `/api/roles` (Protegida)
* **Respuesta exitosa (`200 OK`)**: Devuelve un listado completo con todos los roles y sus respectivos permisos vinculados.

### Ver Detalle de Rol
* **Método**: `GET`
* **Ruta**: `/api/roles/{id}` (Protegida)
* **Respuesta exitosa (`200 OK`)**: Devuelve la información detallada del rol consultado junto con sus permisos.

### Registrar Nuevo Rol
* **Método**: `POST`
* **Ruta**: `/api/roles` (Protegida)
* **Cuerpo de Petición (JSON)**:
  ```json
  {
      "name_role": "supervisor",
      "description": "Supervisor operativo del almacén",
      "permissions": [3, 5, 6]
  }
  ```
* **Respuesta exitosa (`201 Created`)**:
  ```json
  {
      "message": "Rol creado exitosamente.",
      "data": {
          "id": 4,
          "name_role": "supervisor",
          "description": "Supervisor operativo del almacén",
          "permissions": [
              {
                  "id": 3,
                  "name_permission": "products.view",
                  "description": "Ver catálogo de productos y stock"
              }
          ]
      }
  }
  ```

### Actualizar Rol
* **Método**: `PUT`
* **Ruta**: `/api/roles/{id}` (Protegida)
* **Cuerpo de Petición (JSON)**:
  ```json
  {
      "name_role": "supervisor_senior",
      "description": "Nueva descripción",
      "permissions": [3, 5, 6, 7]
  }
  ```
* **Respuesta exitosa (`200 OK`)**: Devuelve el rol modificado y sincroniza sus permisos.

### Eliminar Rol
* **Método**: `DELETE`
* **Ruta**: `/api/roles/{id}` (Protegida)
* **Respuesta exitosa (`200 OK`)**:
  ```json
  {
      "message": "Rol eliminado exitosamente."
  }
  ```
  *(Nota: El sistema no permite eliminar los roles base `admin` y `user` para evitar inconsistencias).*

### Listar Catálogo de Permisos
* **Método**: `GET`
* **Ruta**: `/api/permissions` (Protegida)
* **Respuesta exitosa (`200 OK`)**: Devuelve el listado completo con todos los permisos del sistema.

---

## 📦 2. Productos (`/api/products`) - [Protegido]

### Listar Productos
* **Método**: `GET`
* **Ruta**: `/api/products` (Protegida)
* **Parámetros de consulta (Query params - Opcionales)**:
  * `category_id`: Filtrar por categoría (ej: `?category_id=1`).
  * `supplier_id`: Filtrar por proveedor (ej: `?supplier_id=2`).
  * `status_id`: Filtrar por estado (ej: `?status_id=3`).
  * `search`: Filtrar por nombre o SKU (ej: `?search=Laptop`).
  * `low_stock`: Si se envía `true` o `1`, sólo devuelve productos con bajo stock.
  * `per_page`: Paginación (por defecto `15`).

### Listar Productos con Stock Bajo
* **Método**: `GET`
* **Ruta**: `/api/products/low-stock` (Protegida)

### Crear Producto
* **Método**: `POST`
* **Ruta**: `/api/products` (Protegida)
* **Cuerpo de Petición (JSON)**:
  ```json
  {
      "name": "Audífonos Inalámbricos Sony",
      "description": "Cancelación de ruido activa WH-1000XM5.",
      "sku": "AUD-SONY-007",
      "price": 350.00,
      "stock": 10,
      "min_stock": 3,
      "category_id": 1,
      "supplier_id": 1
  }
  ```

---

## 🔄 3. Movimientos de Inventario (`/api/movements`) - [Protegido]

### Listar Historial de Movimientos
* **Método**: `GET`
* **Ruta**: `/api/movements` (Protegida)

### Registrar Movimiento (Lógica Automatizada de Stock)
* **Método**: `POST`
* **Ruta**: `/api/movements` (Protegida)
* **Cuerpo de Petición (JSON)**:
  * **Entrada (`in`)**: `{"product_id": 1, "type": "in", "quantity": 10, "reason": "Compra de lote"}`
  * **Salida (`out`)**: `{"product_id": 1, "type": "out", "quantity": 5, "reason": "Ventas web"}`
  * **Ajuste (`adjustment`)**: `{"product_id": 1, "type": "adjustment", "quantity": 12, "reason": "Auditoría de almacén"}`

---

## 📂 4. Categorías (`/api/categories`) - [Protegido]
* `GET /api/categories` - Obtener todas las categorías con cantidad de productos.
* `POST /api/categories` - Crear categoría. `{"name": "Nombre", "description": "Opcional"}`.
* `DELETE /api/categories/{id}` - Eliminar categoría (bloqueado si tiene productos asignados).

---

## 🤝 5. Proveedores (`/api/suppliers`) - [Protegido]
* `GET /api/suppliers` - Lista proveedores.
* `POST /api/suppliers` - Crear proveedor.
* `DELETE /api/suppliers/{id}` - Eliminar proveedor (desvincula productos asociados).

---

## 🚥 6. Estados (`/api/statuses`) - [Protegido]
* `GET /api/statuses` - Lista estados (e.g. Disponible, Stock Bajo, Agotado).
* `POST /api/statuses` - Crear estado.
* `DELETE /api/statuses/{id}` - Eliminar estado (bloqueado si tiene productos asignados).
