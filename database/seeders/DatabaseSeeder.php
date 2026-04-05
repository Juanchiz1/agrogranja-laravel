<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo user
        $userId = DB::table('usuarios')->insertGetId([
            'nombre'                => 'Usuario Demo',
            'email'                 => 'demo@demo.com',
            'password'              => Hash::make('demo123'),
            'nombre_finca'          => 'Finca San Pelayo',
            'departamento'          => 'Córdoba',
            'municipio'             => 'Montería',
            'onboarding_completado' => true,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // Cultivos
        $c1 = DB::table('cultivos')->insertGetId(['usuario_id'=>$userId,'tipo'=>'Maíz','nombre'=>'Maíz Amarillo Lote 1','fecha_siembra'=>now()->toDateString(),'area'=>2.00,'unidad'=>'hectareas','estado'=>'activo','notas'=>'Sembrado en temporada de lluvias','created_at'=>now(),'updated_at'=>now()]);
        $c2 = DB::table('cultivos')->insertGetId(['usuario_id'=>$userId,'tipo'=>'Yuca','nombre'=>'Yuca Lote 2','fecha_siembra'=>now()->subDays(30)->toDateString(),'area'=>1.50,'unidad'=>'hectareas','estado'=>'activo','notas'=>'Variedad criolla','created_at'=>now(),'updated_at'=>now()]);
        $c3 = DB::table('cultivos')->insertGetId(['usuario_id'=>$userId,'tipo'=>'Plátano','nombre'=>'Plátano Dominico Lote 3','fecha_siembra'=>now()->subDays(60)->toDateString(),'area'=>1.00,'unidad'=>'hectareas','estado'=>'cosechado','notas'=>'Primera cosecha exitosa','created_at'=>now(),'updated_at'=>now()]);

        // Gastos
        DB::table('gastos')->insert([
            ['usuario_id'=>$userId,'categoria'=>'Semillas','descripcion'=>'Semillas de maíz híbrido','cantidad'=>10,'valor'=>85000,'fecha'=>now()->toDateString(),'created_at'=>now()],
            ['usuario_id'=>$userId,'categoria'=>'Fertilizantes','descripcion'=>'Abono orgánico compuesto','cantidad'=>5,'valor'=>120000,'fecha'=>now()->subDays(7)->toDateString(),'created_at'=>now()],
            ['usuario_id'=>$userId,'categoria'=>'Mano de obra','descripcion'=>'Jornaleros siembra lote 1','valor'=>200000,'fecha'=>now()->subDays(15)->toDateString(),'created_at'=>now()],
            ['usuario_id'=>$userId,'categoria'=>'Plaguicidas','descripcion'=>'Herbicida lote yuca','cantidad'=>2,'valor'=>65000,'fecha'=>now()->subDays(20)->toDateString(),'created_at'=>now()],
        ]);

        // Tareas
        DB::table('tareas')->insert([
            ['usuario_id'=>$userId,'titulo'=>'Riego lote maíz','tipo'=>'riego','fecha'=>now()->toDateString(),'prioridad'=>'alta','created_at'=>now()],
            ['usuario_id'=>$userId,'titulo'=>'Fertilización lote yuca','tipo'=>'fertilizacion','fecha'=>now()->addDays(2)->toDateString(),'prioridad'=>'media','created_at'=>now()],
            ['usuario_id'=>$userId,'titulo'=>'Vacunación ganado','tipo'=>'vacunacion','fecha'=>now()->addDays(5)->toDateString(),'prioridad'=>'alta','created_at'=>now()],
            ['usuario_id'=>$userId,'titulo'=>'Cosecha plátano lote 3','tipo'=>'cosecha','fecha'=>now()->addDays(10)->toDateString(),'prioridad'=>'alta','created_at'=>now()],
        ]);

        // Ingresos
        DB::table('ingresos')->insert([
            ['usuario_id'=>$userId,'cultivo_id'=>$c3,'descripcion'=>'Venta plátano dominico','cantidad'=>500,'unidad'=>'kg','precio_unitario'=>1200,'valor_total'=>600000,'fecha'=>now()->subDays(5)->toDateString(),'comprador'=>'Comerciante local','created_at'=>now()],
        ]);

        // Animales
        DB::table('animales')->insert([
            ['usuario_id'=>$userId,'especie'=>'Ganado bovino','nombre_lote'=>'Lote bovino 1','cantidad'=>8,'estado'=>'activo','peso_promedio'=>350,'unidad_peso'=>'kg','created_at'=>now(),'updated_at'=>now()],
            ['usuario_id'=>$userId,'especie'=>'Gallinas','nombre_lote'=>'Galpón principal','cantidad'=>45,'estado'=>'activo','created_at'=>now(),'updated_at'=>now()],
        ]);
    }
}
