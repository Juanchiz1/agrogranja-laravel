<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cosechas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('cultivo_id')->nullable()->constrained('cultivos')->onDelete('set null');
            $table->string('producto', 150);          // Ej: "Maíz amarillo"
            $table->decimal('cantidad', 10, 2);        // Cantidad cosechada
            $table->string('unidad', 50);              // kg, toneladas, bultos, litros...
            $table->decimal('precio_unitario', 12, 2)->nullable(); // precio por unidad
            $table->decimal('valor_estimado', 12, 2)->nullable();  // calculado
            $table->date('fecha_cosecha');
            $table->enum('calidad', ['excelente','buena','regular','baja'])->default('buena');
            $table->string('destino', 100)->nullable(); // autoconsumo, venta, almacenaje
            $table->string('comprador', 150)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cosechas');
    }
};