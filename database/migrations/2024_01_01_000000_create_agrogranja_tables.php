<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('nombre_finca', 150)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->string('municipio', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('foto_perfil')->nullable();
            $table->boolean('onboarding_completado')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('cultivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('tipo', 100);
            $table->string('nombre', 150);
            $table->date('fecha_siembra');
            $table->decimal('area', 10, 2)->nullable();
            $table->enum('unidad', ['hectareas','metros2','fanegadas','lotes'])->default('hectareas');
            $table->enum('estado', ['activo','cosechado','vendido'])->default('activo');
            $table->text('notas')->nullable();
            $table->string('imagen')->nullable();
            $table->timestamps();
        });

        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('cultivo_id')->nullable()->constrained('cultivos')->onDelete('set null');
            $table->enum('categoria', ['Semillas','Fertilizantes','Plaguicidas','Herramientas','Combustible','Mano de obra','Transporte','Alimento animal','Veterinario','Mantenimiento','Otros']);
            $table->string('descripcion', 255);
            $table->decimal('cantidad', 10, 2)->nullable();
            $table->string('unidad_cantidad', 50)->nullable();
            $table->decimal('valor', 12, 2);
            $table->date('fecha');
            $table->string('proveedor', 150)->nullable();
            $table->string('factura_numero', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('ingresos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('cultivo_id')->nullable()->constrained('cultivos')->onDelete('set null');
            $table->string('descripcion', 255);
            $table->decimal('cantidad', 10, 2)->nullable();
            $table->string('unidad', 50)->nullable();
            $table->decimal('precio_unitario', 12, 2)->nullable();
            $table->decimal('valor_total', 12, 2);
            $table->date('fecha');
            $table->string('comprador', 150)->nullable();
            $table->text('notas')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('cultivo_id')->nullable()->constrained('cultivos')->onDelete('set null');
            $table->string('titulo', 200);
            $table->enum('tipo', ['riego','vacunacion','cosecha','fertilizacion','fumigacion','poda','otro'])->default('otro');
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->boolean('completada')->default(false);
            $table->timestamp('fecha_completada')->nullable();
            $table->text('notas')->nullable();
            $table->enum('prioridad', ['baja','media','alta'])->default('media');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('animales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('especie', 100);
            $table->string('nombre_lote', 150)->nullable();
            $table->integer('cantidad')->default(1);
            $table->date('fecha_ingreso')->nullable();
            $table->enum('estado', ['activo','vendido','muerte'])->default('activo');
            $table->decimal('peso_promedio', 8, 2)->nullable();
            $table->enum('unidad_peso', ['kg','lb'])->default('kg');
            $table->text('notas')->nullable();
            $table->timestamps();
        });

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

         Schema::create('inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('nombre', 150);
            $table->string('categoria', 100);          // Semillas, Fertilizantes...
            $table->decimal('cantidad_actual', 10, 2)->default(0);
            $table->decimal('stock_minimo', 10, 2)->default(0);
            $table->string('unidad', 50);              // kg, litros, bultos...
            $table->decimal('precio_unitario', 12, 2)->nullable();
            $table->string('proveedor', 150)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->text('notas')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
        });

        // Movimientos de inventario (entradas y salidas)
        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->constrained('inventario')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('cultivo_id')->nullable()->constrained('cultivos')->onDelete('set null');
            $table->enum('tipo', ['entrada', 'salida', 'ajuste']);
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_unitario', 12, 2)->nullable(); // precio en entradas
            $table->string('motivo', 200)->nullable();  // compra, uso en cultivo, vencimiento...
            $table->date('fecha');
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animales');
        Schema::dropIfExists('tareas');
        Schema::dropIfExists('ingresos');
        Schema::dropIfExists('gastos');
        Schema::dropIfExists('cultivos');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('cosechas');
        Schema::dropIfExists('inventario_movimientos');
        Schema::dropIfExists('inventario');
    }
};
