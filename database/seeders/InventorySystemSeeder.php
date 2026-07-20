<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Status;
use App\Models\InventoryMovement;

class InventorySystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear Categorías
        $catElectronica = Category::create([
            'name' => 'Electrónica',
            'description' => 'Dispositivos electrónicos, computación, telefonía y accesorios.'
        ]);

        $catHogar = Category::create([
            'name' => 'Hogar y Cocina',
            'description' => 'Muebles, utensilios de cocina y artículos para el hogar.'
        ]);

        $catRopa = Category::create([
            'name' => 'Ropa y Calzado',
            'description' => 'Prendas de vestir para adultos y niños, calzado y accesorios de moda.'
        ]);

        $catAlimentos = Category::create([
            'name' => 'Alimentos y Bebidas',
            'description' => 'Productos de consumo básico, snacks y bebidas.'
        ]);

        // 2. Crear Proveedores
        $supTech = Supplier::create([
            'name' => 'TechDistribuidora',
            'contact_name' => 'Carlos Pérez',
            'email' => 'ventas@techdist.com',
            'phone' => '+584121234567',
            'address' => 'Av. Principal Sabana Grande, Caracas'
        ]);

        $supMuebles = Supplier::create([
            'name' => 'Muebles y Más',
            'contact_name' => 'María Gómez',
            'email' => 'contacto@mueblesymas.com',
            'phone' => '+584249876543',
            'address' => 'Zona Industrial La Yaguara, Caracas'
        ]);

        $supTextil = Supplier::create([
            'name' => 'Importaciones Textiles S.A.',
            'contact_name' => 'Julio Rodríguez',
            'email' => 'julio@textilesa.com',
            'phone' => '+584161112233',
            'address' => 'El Cementerio, Caracas'
        ]);

        $supSol = Supplier::create([
            'name' => 'Distribuidora El Sol',
            'contact_name' => 'Ana Martínez',
            'email' => 'info@distelsol.com',
            'phone' => '+582125556677',
            'address' => 'Av. Sucre Catia, Caracas'
        ]);

        // Obtener IDs de estado para asignación manual directa en seeder
        $statusDisponible = Status::where('name_status', 'Disponible')->first()?->id ?? 1;
        $statusAgotado = Status::where('name_status', 'Agotado')->first()?->id ?? 2;
        $statusBajo = Status::where('name_status', 'Stock Bajo')->first()?->id ?? 3;

        // 3. Crear Productos y sus movimientos iniciales
        
        // Producto 1: Laptop HP
        $prod1 = Product::create([
            'name' => 'Laptop HP Pavillion',
            'description' => 'Laptop HP 15.6 pulgadas con procesador Ryzen 5, 16GB RAM y 512GB SSD.',
            'sku' => 'LPT-HP-001',
            'price' => 650.00,
            'stock' => 15,
            'min_stock' => 5,
            'category_id' => $catElectronica->id,
            'supplier_id' => $supTech->id,
            'status_id' => $statusDisponible
        ]);

        // Movimientos de Laptop HP
        InventoryMovement::create([
            'product_id' => $prod1->id,
            'type' => 'in',
            'quantity' => 20,
            'reason' => 'Compra inicial a proveedor TechDistribuidora'
        ]);

        InventoryMovement::create([
            'product_id' => $prod1->id,
            'type' => 'out',
            'quantity' => 5,
            'reason' => 'Venta registrada - Factura #001'
        ]);

        // Producto 2: Teléfono Samsung
        $prod2 = Product::create([
            'name' => 'Teléfono Samsung Galaxy A35',
            'description' => 'Smartphone con 128GB de almacenamiento, 8GB RAM, pantalla Super AMOLED.',
            'sku' => 'CEL-SAM-002',
            'price' => 280.00,
            'stock' => 4,
            'min_stock' => 8,
            'category_id' => $catElectronica->id,
            'supplier_id' => $supTech->id,
            'status_id' => $statusBajo
        ]);

        InventoryMovement::create([
            'product_id' => $prod2->id,
            'type' => 'in',
            'quantity' => 10,
            'reason' => 'Compra inicial a proveedor'
        ]);

        InventoryMovement::create([
            'product_id' => $prod2->id,
            'type' => 'out',
            'quantity' => 6,
            'reason' => 'Venta por mostrador'
        ]);

        // Producto 3: Televisor LG (Agotado)
        $prod3 = Product::create([
            'name' => 'Televisor LG 55 pulgadas OLED',
            'description' => 'Smart TV LG 4K OLED con Procesador Inteligente y Dolby Vision.',
            'sku' => 'TV-LG-003',
            'price' => 1200.00,
            'stock' => 0,
            'min_stock' => 2,
            'category_id' => $catElectronica->id,
            'supplier_id' => $supTech->id,
            'status_id' => $statusAgotado
        ]);

        InventoryMovement::create([
            'product_id' => $prod3->id,
            'type' => 'in',
            'quantity' => 5,
            'reason' => 'Stock inicial importado'
        ]);

        InventoryMovement::create([
            'product_id' => $prod3->id,
            'type' => 'out',
            'quantity' => 5,
            'reason' => 'Ventas web y tienda física completadas'
        ]);

        // Producto 4: Juego de Ollas
        $prod4 = Product::create([
            'name' => 'Juego de Ollas de Acero Inoxidable',
            'description' => 'Juego de 7 piezas de ollas y sartenes apto para cocinas de inducción.',
            'sku' => 'HGR-OLL-004',
            'price' => 85.00,
            'stock' => 20,
            'min_stock' => 5,
            'category_id' => $catHogar->id,
            'supplier_id' => $supMuebles->id,
            'status_id' => $statusDisponible
        ]);

        InventoryMovement::create([
            'product_id' => $prod4->id,
            'type' => 'in',
            'quantity' => 20,
            'reason' => 'Carga inicial de mercancía de cocina'
        ]);

        // Producto 5: Franela de Algodón
        $prod5 = Product::create([
            'name' => 'Franela de Algodón Negra M',
            'description' => 'Camiseta básica unisex color negro, 100% algodón premium.',
            'sku' => 'ROP-FRA-005',
            'price' => 15.00,
            'stock' => 50,
            'min_stock' => 15,
            'category_id' => $catRopa->id,
            'supplier_id' => $supTextil->id,
            'status_id' => $statusDisponible
        ]);

        InventoryMovement::create([
            'product_id' => $prod5->id,
            'type' => 'in',
            'quantity' => 50,
            'reason' => 'Entrada de lote manufacturado'
        ]);

        // Producto 6: Café Molido
        $prod6 = Product::create([
            'name' => 'Café Molido Gourmet 1Kg',
            'description' => 'Café premium tostado y molido origen andino, empaque sellado al vacío.',
            'sku' => 'ALI-CAF-006',
            'price' => 6.50,
            'stock' => 8,
            'min_stock' => 10,
            'category_id' => $catAlimentos->id,
            'supplier_id' => $supSol->id,
            'status_id' => $statusBajo
        ]);

        InventoryMovement::create([
            'product_id' => $prod6->id,
            'type' => 'in',
            'quantity' => 15,
            'reason' => 'Recepción de pedido'
        ]);

        InventoryMovement::create([
            'product_id' => $prod6->id,
            'type' => 'out',
            'quantity' => 7,
            'reason' => 'Ventas semanales'
        ]);
    }
}
