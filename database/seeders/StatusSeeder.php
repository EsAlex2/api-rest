<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = collect([
            // --- Estatus para Productos e Inventario ---
            [
                'name_status' => 'Disponible',
                'description' => 'El producto está listo y disponible para la venta o distribución.'
            ],
            [
                'name_status' => 'Agotado',
                'description' => 'El producto no cuenta con existencias en almacén.'
            ],
            [
                'name_status' => 'Stock Bajo',
                'description' => 'El producto ha alcanzado o superado el umbral de stock mínimo requerido.'
            ],
            [
                'name_status' => 'Inactivo',
                'description' => 'El producto ha sido deshabilitado del catálogo y no se permite su transacción.'
            ],
            [
                'name_status' => 'Dañado / Defectuoso',
                'description' => 'El producto se encuentra en mal estado o no apto para la venta.'
            ],
            [
                'name_status' => 'En Cuarentena',
                'description' => 'El producto está retenido temporalmente para inspección de calidad o revisión.'
            ],

            // --- Estatus para Órdenes y Movimientos ---
            [
                'name_status' => 'Pendiente',
                'description' => 'La orden o movimiento ha sido registrado pero aún no procesado.'
            ],
            [
                'name_status' => 'En Proceso',
                'description' => 'La solicitud, compra o despacho está en etapa de preparación o empaquetado.'
            ],
            [
                'name_status' => 'Completado',
                'description' => 'El movimiento o la transacción se ha completado exitosamente.'
            ],
            [
                'name_status' => 'Cancelado',
                'description' => 'La orden o el movimiento fue anulado antes de ejecutarse.'
            ],
            [
                'name_status' => 'Devuelto',
                'description' => 'El producto o pedido ha sido retornado al almacén.'
            ]
        ]);

        $statuses->each(function($data){
            Status::create($data);
        });
    }
}
