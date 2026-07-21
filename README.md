# Sistema de Gestión de Inventario - API REST

Esta es una API RESTful desarrollada con **Laravel 13** diseñada para gestionar el inventario de un almacén o negocio. Permite controlar productos, categorías, proveedores y el historial de movimientos de inventario (entradas, salidas y ajustes de stock) de forma totalmente automatizada y transaccional.

---

## 🛠️ Tecnologías y Requerimientos

- **PHP**: `^8.3`
- **Laravel Framework**: `^13.20`
- **Base de Datos**: PostgreSQL (o cualquier motor compatible con Laravel)
- **Gestión de Dependencias**: Composer
- **Pruebas**: PHPUnit

---

## 🚀 Instalación y Configuración Local

Sigue estos pasos para levantar la API en tu máquina de desarrollo:

### 1. Clonar el repositorio y acceder a la carpeta
```bash
git clone <url-del-repositorio>
cd api-rest
```

### 2. Instalar las dependencias de PHP
```bash
composer install
```

### 3. Configurar las variables de entorno
Crea una copia del archivo `.env.example` y nómbralo `.env`:
```bash
cp .env.example .env
```
Abre el archivo `.env` y configura los datos de acceso a tu base de datos PostgreSQL:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña
```

### 4. Generar la clave de la aplicación
```bash
php artisan key:generate
```

### 5. Ejecutar migraciones y sembrar datos de prueba (Seeders)
Este comando creará la estructura de tablas y cargará datos listos para probar (categorías, proveedores, estados iniciales, productos y su historial de movimientos):
```bash
php artisan migrate:fresh --seed
```

### 6. Levantar el servidor de desarrollo
```bash
php artisan serve
```
La aplicación estará disponible por defecto en: `http://localhost:8000` (o el puerto especificado en consola).

---

## 📂 Estructura Principal del Proyecto

- **Modelos (`app/Models`)**:
  - `Status`: Representa el estado del producto (Disponible, Stock Bajo, Agotado, Dañado).
  - `Category`: Categorías de clasificación de los productos.
  - `Supplier`: Datos de contacto de los proveedores.
  - `Product`: Datos del producto (SKU, stock, stock mínimo, precio).
  - `InventoryMovement`: Historial de entradas, salidas y ajustes de inventario.
- **Controladores (`app/Http/Controllers`)**:
  - Manejo de recursos REST para cada entidad con validaciones de datos y respuestas JSON estandarizadas.
- **Pruebas (`tests/Feature`)**:
  - `InventoryMovementTest`: Contiene las pruebas que aseguran el correcto funcionamiento de las transacciones de inventario y el ajuste automático de stock.

---

## 📊 Reglas de Negocio Automatizadas

- **Entradas (`in`)**: Suma la cantidad directamente al stock del producto.
- **Salidas (`out`)**: Valida si el stock es suficiente antes de realizar el despacho. Si no lo es, cancela la operación y devuelve un error de validación `422`.
- **Ajustes (`adjustment`)**: Sobrescribe el stock actual con el nuevo valor (ideal para inventarios físicos anuales).
- **Actualización de Estados Automática**: Después de cada movimiento, el estado de inventario del producto se recalcula automáticamente:
  - Stock igual a `0` ➔ Estado cambia a **`Agotado`**
  - Stock menor o igual a `min_stock` (pero mayor a 0) ➔ Estado cambia a **`Stock Bajo`**
  - Stock mayor que `min_stock` ➔ Estado cambia a **`Disponible`**

---

## 🧪 Pruebas Unitarias y de Integración

Para ejecutar la suite de pruebas automatizadas y validar el sistema:
```bash
php artisan test
```

---

## 📋 Pruebas en Insomnia o Postman

Puedes importar la colección preconfigurada para probar de inmediato todos los endpoints de la API:
- Ubicación del archivo de importación: **`[inventory_api_collection.json](inventory_api_collection.json)`** en la raíz del proyecto.
- La colección incluye una variable global `{{base_url}}` (por defecto `http://localhost:8000`) para que puedas apuntar a tu entorno local al instante.

*Para una descripción técnica detallada de cada ruta, parámetros y formatos JSON, consulta el archivo **`[endpoints.md](endpoints.md)`**.*
