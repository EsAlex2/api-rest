# Catálogo y Especificación de Endpoints - API REST

Esta guía detalla el uso, parámetros y payloads para interactuar con todos los endpoints expuestos por la API de Gestión de Inventario.

---

## ⚙️ Cabeceras Obligatorias (Headers)
Para garantizar la correcta respuesta de la API, incluye siempre las siguientes cabeceras en tus peticiones:
```http
Accept: application/json
Content-Type: application/json
```

---

## 📦 1. Productos (`/api/products`)

### Listar Productos
* **Método**: `GET`
* **Ruta**: `/api/products`
* **Parámetros de consulta (Query params - Opcionales)**:
  * `category_id`: Filtrar por ID de categoría (ej: `?category_id=1`).
  * `supplier_id`: Filtrar por ID de proveedor (ej: `?supplier_id=2`).
  * `status_id`: Filtrar por ID de estado (ej: `?status_id=3`).
  * `search`: Filtrar por nombre o SKU (ej: `?search=Laptop` o `?search=LPT-HP-001`).
  * `low_stock`: Si se envía `true` o `1`, sólo devuelve productos con bajo stock.
  * `per_page`: Número de productos por página (por defecto `15`).
* **Respuesta exitosa (`200 OK`)**:
  ```json
  {
      "current_page": 1,
      "data": [
          {
              "id": 1,
              "name": "Laptop HP Pavillion",
              "description": "Ryzen 5, 16GB RAM, 512GB SSD.",
              "sku": "LPT-HP-001",
              "price": "650.00",
              "stock": 15,
              "min_stock": 5,
              "category_id": 1,
              "supplier_id": 1,
              "status_id": 1,
              "created_at": "2026-07-20T20:23:55.000000Z",
              "updated_at": "2026-07-20T20:23:55.000000Z",
              "category": { "id": 1, "name": "Electrónica" },
              "supplier": { "id": 1, "name": "TechDistribuidora" },
              "status": { "id": 1, "name_status": "Disponible" }
          }
      ],
      "total": 1
  }
  ```

### Listar Productos con Stock Bajo
* **Método**: `GET`
* **Ruta**: `/api/products/low-stock`
* **Respuesta exitosa (`200 OK`)**:
  ```json
  {
      "total_low_stock": 1,
      "data": [
          {
              "id": 2,
              "name": "Teléfono Samsung Galaxy A35",
              "sku": "CEL-SAM-002",
              "price": "280.00",
              "stock": 4,
              "min_stock": 8,
              "category_id": 1,
              "status_id": 3,
              "status": { "id": 3, "name_status": "Stock Bajo" }
          }
      ]
  }
  ```

### Crear Producto
* **Método**: `POST`
* **Ruta**: `/api/products`
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
  *(Nota: Al crear el producto, se le asigna el estado correspondiente basado en su `stock` inicial de forma automática).*
* **Respuesta exitosa (`201 Created`)**:
  ```json
  {
      "message": "Producto creado exitosamente",
      "data": {
          "id": 7,
          "name": "Audífonos Inalámbricos Sony",
          "sku": "AUD-SONY-007",
          "price": "350.00",
          "stock": 10,
          "min_stock": 3,
          "category_id": 1,
          "supplier_id": 1,
          "status_id": 1,
          "status": { "id": 1, "name_status": "Disponible" }
      }
  }
  ```

### Ver Detalle de un Producto
* **Método**: `GET`
* **Ruta**: `/api/products/{id}`
* **Respuesta exitosa (`200 OK`)**:
  Devuelve la información del producto, incluyendo sus relaciones cargadas y todo su historial de movimientos ordenados cronológicamente:
  ```json
  {
      "id": 1,
      "name": "Laptop HP Pavillion",
      "stock": 15,
      "category": { "name": "Electrónica" },
      "movements": [
          { "id": 2, "type": "out", "quantity": 5, "reason": "Venta" },
          { "id": 1, "type": "in", "quantity": 20, "reason": "Compra inicial" }
      ]
  }
  ```

### Actualizar Producto
* **Método**: `PUT` / `PATCH`
* **Ruta**: `/api/products/{id}`
* **Cuerpo de Petición (JSON - Envía sólo lo que desees actualizar)**:
  ```json
  {
      "name": "Audífonos Sony WH-1000XM5",
      "price": 370.00,
      "min_stock": 5
  }
  ```
* **Respuesta exitosa (`200 OK`)**: Devuelve el producto actualizado y con el estado recalculado si cambiaron el stock o stock mínimo.

### Eliminar Producto
* **Método**: `DELETE`
* **Ruta**: `/api/products/{id}`
* **Respuesta exitosa (`200 OK`)**:
  ```json
  {
      "message": "Producto y sus movimientos de inventario asociados eliminados exitosamente"
  }
  ```

---

## 🔄 2. Movimientos de Inventario (`/api/movements`)

### Listar Historial de Movimientos
* **Método**: `GET`
* **Ruta**: `/api/movements`
* **Parámetros de consulta (Query params - Opcionales)**:
  * `product_id`: Filtrar por producto.
  * `type`: Filtrar por tipo de movimiento (`in`, `out`, `adjustment`).
* **Respuesta exitosa (`200 OK`)**: Lista paginada del historial de movimientos.

### Registrar Movimiento (Lógica Automatizada de Stock)
* **Método**: `POST`
* **Ruta**: `/api/movements`
* **Cuerpo de Petición (JSON)**:
  * **Registrar Entrada (`in`)**:
    ```json
    {
        "product_id": 1,
        "type": "in",
        "quantity": 10,
        "reason": "Compra de lote nuevo"
    }
    ```
  * **Registrar Salida (`out`)**:
    ```json
    {
        "product_id": 1,
        "type": "out",
        "quantity": 5,
        "reason": "Venta al cliente"
    }
    ```
  * **Ajuste Directo (`adjustment`)**:
    ```json
    {
        "product_id": 1,
        "type": "adjustment",
        "quantity": 12,
        "reason": "Ajuste físico por auditoría"
    }
    ```
* **Respuesta exitosa (`201 Created`)**:
  ```json
  {
      "message": "Movimiento de inventario registrado con éxito",
      "data": {
          "id": 5,
          "product_id": 1,
          "type": "in",
          "quantity": 10,
          "reason": "Compra de lote nuevo"
      },
      "product": {
          "id": 1,
          "name": "Laptop HP Pavillion",
          "stock": 25,
          "status_id": 1,
          "status": { "id": 1, "name_status": "Disponible" }
      }
  }
  ```
* **Respuesta de Error - Stock Insuficiente (`422 Unprocessable Entity`)**:
  Ocurre cuando intentas hacer una salida (`out`) mayor a la cantidad en almacén:
  ```json
  {
      "message": "Stock insuficiente para realizar esta salida. Stock actual: 15, cantidad solicitada: 20."
  }
  ```

### Ver Historial por Producto
* **Método**: `GET`
* **Ruta**: `/api/products/{id}/movements`
* **Respuesta exitosa (`200 OK`)**: Devuelve un JSON detallando el producto y su historial exclusivo de movimientos.

---

## 📂 3. Categorías (`/api/categories`)

### Endpoints Básicos de Recurso
* `GET /api/categories` - Obtiene todas las categorías con el conteo de sus productos en la clave `products_count`.
* `POST /api/categories` - Crea una categoría. Body: `{"name": "Nombre", "description": "Opcional"}`.
* `GET /api/categories/{id}` - Obtiene detalle.
* `PUT /api/categories/{id}` - Actualiza datos de la categoría.
* `DELETE /api/categories/{id}` - Elimina la categoría.
  * **Restricción**: Si tiene productos asignados, devolverá error HTTP `400` para evitar dejar productos huérfanos.

---

## 🤝 4. Proveedores (`/api/suppliers`)

### Endpoints Básicos de Recurso
* `GET /api/suppliers` - Lista los proveedores registrados.
* `POST /api/suppliers` - Registra un proveedor. Body: `{"name": "TechDist", "contact_name": "Luis", "email": "luis@mail.com", "phone": "0412...", "address": "Dirección"}`.
* `GET /api/suppliers/{id}` - Obtiene detalle.
* `PUT /api/suppliers/{id}` - Modifica datos del proveedor.
* `DELETE /api/suppliers/{id}` - Elimina el proveedor.
  * **Nota**: Configurado con `nullOnDelete()`, los productos asociados a este proveedor no se eliminarán, sino que su valor `supplier_id` pasará a ser `null` automáticamente.

---

## 🚥 5. Estados (`/api/statuses`)

### Endpoints Básicos de Recurso
* `GET /api/statuses` - Lista los estados.
* `POST /api/statuses` - Registra un nuevo estado. Body: `{"name_status": "Dañado", "description": "Defectuoso"}`.
* `GET /api/statuses/{id}` - Detalle del estado.
* `PUT /api/statuses/{id}` - Actualiza el estado.
* `DELETE /api/statuses/{id}` - Elimina el estado.
  * **Restricción**: Retornará un error HTTP `400` si hay productos que dependen de él.
