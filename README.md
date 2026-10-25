# AgroGranja

Aplicación web para administrar una finca: cultivos, animales, inventario, trabajadores y finanzas en un solo lugar. Además de los módulos generales, tiene módulos por línea productiva (bovina, avícola, porcícola y piscícola), cada uno con sus propios registros y reportes.

Pensada para pequeños y medianos productores de San Pelayo, Córdoba.

## Stack

- PHP 8.2 + Laravel 10
- MySQL (tests con SQLite en memoria)
- Vistas Blade, sin framework de frontend
- barryvdh/laravel-dompdf para reportes en PDF y PhpSpreadsheet para Excel
- PHPUnit
- Despliegue en Railway con Nixpacks

## Módulos

| Módulo | Qué permite |
|---|---|
| Cultivos | Registro de lotes, fotos, eventos, fases fenológicas y rendimiento |
| Cosechas, gastos e ingresos | Registro de movimientos; proveedores, clientes y gastos recurrentes |
| Rentabilidad | Costos contra ingresos por cultivo |
| Inventario | Insumos, movimientos de entrada/salida y alertas de stock bajo |
| Animales | Inventario de animales, pesajes, eventos y producción |
| Bovino | Ordeños, lactancias, servicios, preñez, partos, plan sanitario y ventas |
| Avícola | Postura, engorde, mortalidad, vacunación y conversión alimenticia |
| Porcícola | Ciclo reproductivo, ceba, sanidad |
| Piscícola | Estanques, siembra, muestreos, alimentación, calidad del agua y cosecha |
| Personas | Trabajadores, labores asignadas y pagos (nómina) |
| Tareas | Calendario de tareas de la finca |
| Reportes | PDF de cultivos, gastos, cosechas, animales, nómina, inventario, rentabilidad y producción; Excel de cultivos, gastos y cosechas |

Al registrarse, el usuario elige qué líneas productivas tiene su finca y la aplicación solo le muestra esos módulos.

## Correr el proyecto en local

Requisitos: PHP 8.2+, Composer y MySQL.

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Crea una base de datos `agrogranja` en MySQL, ajusta las variables `DB_*` del `.env` y luego:

```bash
php artisan migrate
php artisan serve
```

La aplicación queda en http://localhost:8000. Crea una cuenta desde `/register`; el onboarding te pide el nombre de la finca y las líneas productivas.

La encuesta de satisfacción envía las respuestas a Supabase; si `SUPABASE_URL` y `SUPABASE_KEY` no están configuradas, el resto de la aplicación funciona igual.

## Tests

```bash
vendor/bin/phpunit
```

Los tests usan SQLite en memoria (configurado en `phpunit.xml`), así que no necesitan MySQL. Hay tests de feature para autenticación, animales, cultivos, gastos y tareas, y tests unitarios del modelo de usuario.

## Despliegue

`nixpacks.toml` instala PHP 8.2 con las extensiones necesarias y las dependencias de Composer. Al arrancar, `start.sh` genera el `.env` a partir de las variables de entorno de Railway, corre las migraciones y levanta el servidor en el puerto que asigna Railway.

## Proyectos relacionados

- [agrogranja-mobile](https://github.com/Juanchiz1/agrogranja-mobile): app móvil en React Native con base de datos local para trabajar sin conexión.
- [agrogranja-bovinos](https://github.com/Juanchiz1/agrogranja-bovinos): versión enfocada en la línea bovina.

## Autor

**Juan Diego Negrete Portillo**
Estudiante de Ingeniería de Sistemas — Universidad de Córdoba
[Portafolio](https://juanchiz1.github.io/Portafolio-Juan_Diego_Negrete/) · [LinkedIn](https://www.linkedin.com/in/juan-diego-negrete-portillo-352111195/) · [GitHub](https://github.com/Juanchiz1)
