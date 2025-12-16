<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Sequences: Controla el número correlativo (1, 2, 3...)
        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->integer('sequence_size')->default(8)->comment('Cantidad de dígitos, ej: 8 para 00000001');
            $table->integer('step')->default(1)->comment('Incremento');
            $table->integer('next_number')->default(1)->comment('Siguiente número a utilizar');
            $table->timestamps();
        });

        // 2. Journals: Controla la Serie y Tipo (F001, B001, Nota de Venta)
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('companies')->onDelete('cascade')->comment('Si es null, es una serie global de la empresa');

            $table->string('name')->comment('Ej: Factura Electrónica F001');
            $table->string('code')->comment('La Serie: F001, B001, NV01');
            $table->string('type')->comment('sale, purchase, quote, credit_note, debit_note, dispatch');
            $table->boolean('is_fiscal')->default(false)->comment('Si es comprobante fiscal SUNAT');
            $table->string('document_type_code', 2)->nullable()->comment('SUNAT: 01=Factura, 03=Boleta, 07=NC, 08=ND, 09=Guia');

            $table->foreignId('sequence_id')->constrained('sequences')->onDelete('restrict');

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Índices y restricciones
            $table->unique(['company_id', 'code']); // No repetir serie F001 en la misma empresa
            $table->index(['branch_id', 'type']);   // Para buscar series disponibles por sucursal y tipo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
        Schema::dropIfExists('sequences');
    }
};
