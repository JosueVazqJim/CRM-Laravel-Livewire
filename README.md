# CRM / Cotizador

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-4.2-4E56A6?logo=livewire&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)
![Tailwind](https://img.shields.io/badge/Tailwind-4.x-06B6D4?logo=tailwindcss&logoColor=white)

CRM y sistema de cotizaciones construido con **Laravel 12, Livewire 4, MaryUI y Flux**. Gestiona leads, catálogo de productos, empresas de un corporativo y usuarios con roles; genera cotizaciones con **versionado inmutable**, conversión **USD/MXN con tipo de cambio de Banxico** y exportación a **PDF**.

> Proyecto de portafolio basado en un sistema desarrollado para un entorno real de ventas. Los datos incluidos son ficticios y se generan con seeders.

---

## Tabla de contenidos

1. [Características destacadas](#1-características-destacadas)
2. [Stack tecnológico](#2-stack-tecnológico)
3. [Instalación local](#3-instalación-local)
4. [Usuarios demo](#4-usuarios-demo)
5. [Variables de entorno](#5-variables-de-entorno)
6. [Módulos](#6-módulos)
7. [Arquitectura y decisiones técnicas](#7-arquitectura-y-decisiones-técnicas)
8. [Tests](#8-tests)
9. [Despliegue](#9-despliegue)
10. [Personalización](#10-personalización)
11. [Deuda técnica conocida](#11-deuda-técnica-conocida)

---

## 1. Características destacadas

- **Versionado de cotizaciones inmutable**: cada cambio genera una nueva versión dentro de un árbol (A, B, C → revisiones D, E, F…); los precios históricos nunca se reescriben.
- **Snapshots de tipo de cambio**: cada versión guarda las tasas vigentes al momento de crearse, así el PDF siempre muestra los mismos importes aunque el dólar cambie después.
- **Integración con la API de Banxico** (serie `SF43718`) con caché y lock para evitar peticiones concurrentes.
- **PDF con DomPDF** y vista previa editable antes de descargar.
- **Roles y permisos** (Spatie): `admin` ve todo; `vendedor` solo sus empresas y los leads propios o compartidos.
- **Leads con probabilidad de cierre automática**, actividades, notas, contactos, facturación y **fusión de leads duplicados**.
- **Inventario por seriales** con movimientos de stock.
- **Notificaciones en tiempo real** (Laravel Reverb + Echo) y en base de datos.
- **Autenticación de dos factores (TOTP)** con Laravel Fortify.
- **Tema claro / oscuro** persistente, disponible desde el login.

---

## 2. Stack tecnológico

| Capa | Tecnología | Versión |
|---|---|---|
| Backend | Laravel | ^12.0 |
| Runtime | PHP | ^8.2 |
| Frontend reactivo | Livewire + Alpine.js | ^4.2 |
| Componentes UI | MaryUI (`robsontenorio/mary`) + daisyUI | ^2.8 |
| Componentes adicionales | Livewire Flux | ^2.9 |
| CSS | Tailwind CSS | ^4.2 |
| Base de datos | MySQL / MariaDB | 8.0+ / 10.4+ |
| PDF | `barryvdh/laravel-dompdf` | ^3.1 |
| Autenticación | Laravel Fortify (2FA) | ^1.30 |
| Roles y permisos | Spatie Laravel Permission | ^6.25 |
| Tiempo real | Laravel Reverb | ^1.0 |
| Tests | Pest | ^3 |
| Build frontend | Vite | ^7.0 |

---

## 3. Instalación local

### Requisitos

- PHP 8.2+ (extensiones: `pdo_mysql`, `mbstring`, `gd`, `zip`)
- Composer 2+
- Node.js 18+
- MySQL 8+ o MariaDB 10.4+ (XAMPP / Laragon funcionan)

### Pasos

```bash
git clone https://github.com/JosueVazqJim/CRM-Laravel-Livewire.git
cd CRM-Laravel-Livewire

# Dependencias
composer install
npm install

# Entorno
cp .env.example .env
php artisan key:generate
```

Crea una base de datos vacía (ej. `crm_portfolio`) y configura `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en `.env`. Después:

```bash
# Tablas + datos demo (estados, municipios, roles, empresas, usuarios, productos y leads)
php artisan migrate --seed

# Enlace public/storage → storage/app/public (avatares, logos e imágenes de productos)
php artisan storage:link

# Servidor + cola + Vite en paralelo
composer run dev
```

Abre `http://localhost:8000` e inicia sesión con alguno de los [usuarios demo](#4-usuarios-demo).

> Si usas Apache (XAMPP) en lugar de `php artisan serve`, ajusta `APP_URL` a la ruta real, por ejemplo `http://localhost/crm/public`, y corre solo `npm run dev`.

**Correo**: por defecto `MAIL_MAILER=log`, así que los correos (recuperación de contraseña, verificación) se escriben en `storage/logs/laravel.log` en lugar de enviarse. Para verlos con formato puedes usar [Mailpit](https://mailpit.axllent.org/) cambiando solo el `.env`.

---

## 4. Usuarios demo

Creados por `DatabaseSeeder`:

| Correo | Contraseña | Rol |
|---|---|---|
| `admin@demo.test` | `password` | admin — acceso total |
| `vendedor@demo.test` | `password` | vendedor — una empresa asignada |

Además se generan 3 vendedores, 3 empresas y 20 leads con datos aleatorios (Faker).

---

## 5. Variables de entorno

Además de las variables estándar de Laravel (`APP_*`, `DB_*`, `MAIL_*`):

| Variable | Descripción | Notas |
|---|---|---|
| `BANXICO_TOKEN` | Token de la API SIE de Banxico | Gratuito en [banxico.org.mx/SieAPIRest](https://www.banxico.org.mx/SieAPIRest/service/v1/token). **Requerido para crear cotizaciones** (ver [deuda técnica](#11-deuda-técnica-conocida)). |
| `QUEUE_CONNECTION` | Driver de colas | `database` |
| `SESSION_DRIVER` | Driver de sesión | `database` |
| `BROADCAST_CONNECTION` | Broadcasting | `log` en local, `reverb` para tiempo real |
| `REVERB_APP_ID` / `_KEY` / `_SECRET` | Credenciales de Reverb | Generar con `php artisan reverb:install` |
| `VITE_REVERB_ENABLED` | Activa Echo en el frontend | `false` si no corres Reverb |

Para notificaciones en tiempo real en local: `BROADCAST_CONNECTION=reverb`, `VITE_REVERB_ENABLED=true` y ejecutar `php artisan reverb:start`.

---

## 6. Módulos

### Empresas (`Companies`)
- CRUD de empresas del corporativo: nombre, slug, logo, sitio web, descripción y tema.
- Teléfonos, correos, dirección, módulos habilitados y usuarios asignados.
- Productos, leads y cotizaciones pertenecen a una empresa.

### Usuarios
- CRUD con rol (`admin` / `vendedor`), teléfonos, dirección y empresas asignadas.
- Perfil, cambio de contraseña, apariencia y 2FA con códigos de recuperación.

### Leads
- Alta multistep: información general, comercial, estado del cliente, diagnóstico, facturación, contactos y accesos.
- **Probabilidad de cierre calculada** a partir del proceso de venta: primer contacto (25 %), diferenciador explicado (15 %), diagnóstico (20 %), demo ofrecida (25 %) y problema detectado (15 %).
- **Acceso compartido** entre vendedores con notificación en tiempo real.
- Actividades (pasadas y programadas), notas y cotizaciones del lead.
- **Fusión de leads duplicados** (`LeadMergeService`) eligiendo campo por campo qué datos conservar.
- Importación desde CSV: `php artisan import:pipeline-csv --file=pipeline.csv --company=1 --dry-run --skip-duplicates`.

### Productos
- Nombre, SKU, imagen, descripciones, tipo, categoría y tags.
- Precios regular, mínimo, máximo y costo de instalación, en **USD o MXN**.
- Especificaciones técnicas clave-valor.
- **Seriales** con estado, datos de compra/venta y stock calculado por Observer; movimientos de stock trazables.

### Cotizaciones
- Ítems del **catálogo** (precio normalizado a USD) y **conceptos manuales**.
- Versionado jerárquico e inmutable (ver [Versionado](#versionado-de-cotizaciones)).
- Estados: `en_proceso` → `en_validacion` → `exitosa` / `sin_exito` / `cancelada` / `papelera`.
- Al marcar como `exitosa` se captura la **orden de compra** (anticipo y forma de pago); el saldo se calcula en MXN.
- Vista previa en modal con datos del cliente editables y descarga en PDF.

### Dashboard
- KPIs por empresa y vendedor: leads activos, pipeline y cotizaciones.
- Gráficas de leads por etapa, pipeline por vendedor e ingresos mensuales.
- Kanban de actividades pendientes.

### Notificaciones
- Persistidas en base de datos y emitidas en tiempo real.
- Campana con contador en el sidebar y listado con marcado de leídas.

---

## 7. Arquitectura y decisiones técnicas

### Organización por dominio

`app/Livewire`, `app/Models`, las vistas y las rutas se agrupan por dominio de negocio (`Companies`, `Leads`, `Products`, `Quotes`, `Users`), no por tipo técnico.

```
app/
├── Http/Controllers/        # Mínimos: PDF de cotizaciones
├── Livewire/
│   ├── Companies/ Leads/ Products/ Quotes/ Users/ Notifications/ Settings/
│   ├── Leads/ComponentsShow/   # Subcomponentes del detalle (GeneralInfo, Activities, ...)
│   └── Forms/                  # Livewire Form Objects (QuoteForm, ProductForm, ...)
├── Models/{Companies,Leads,Products,Quotes,Users,States}/
├── Observers/                  # Stock de productos por seriales
└── Services/
    ├── ExchangeRateService.php         # Banxico + caché
    ├── Leads/LeadMergeService.php      # Fusión de leads
    └── Quotes/CurrencySummaryService.php  # Totales USD/MXN desde el snapshot
routes/{companies,leads,products,quotes,users,notifications,settings}.php
```

### Patrones

- **Livewire Form Objects**: validación y persistencia viven en `app/Livewire/Forms/`; el componente solo orquesta.
- **Componentes Blade anónimos** reutilizables en `resources/views/components/{dominio}/`.
- **Servicios** para lógica que se comparte entre componentes, PDF y tests.
- **Laravel 12 sin `Kernel.php`**: middleware y excepciones en `bootstrap/app.php`.

### Versionado de cotizaciones

Cada `Quote` es un expediente; cada `QuoteVersion` es **inmutable** (solo cambia `status`).

```
A           ← versión raíz
B           ← nueva versión raíz (recotización: tasas del día)
C           ← versión raíz
├── D       ← revisión de C (precios congelados)
├── E       ← revisión de C
│   └── F   ← revisión de E: sigue la secuencia plana del árbol
└── G       ← revisión de C
```

- **Revisiones**: heredan los ítems con precios congelados y comparten una secuencia plana de letras por árbol (máx. 26; `QuoteVersion::nextChildVersionLetter()` lanza `\OverflowException`). `source_version_id` permite reconstruir el árbol.
- **Recotizaciones**: nueva versión raíz que recalcula precios con las tasas actuales.
- **Folio**: `[slug_empresa][DDMMAAAA][letra][id_quote]`, ej. `ACM01062026C42`.

### Tipos de cambio y snapshots

- `ExchangeRate` guarda pares `base_currency → target_currency`. `ExchangeRateService::ensureFreshRates()` solo consulta Banxico si no existe la tasa del día (con lock de caché).
- Al crear una versión se copian las tasas vigentes a `quote_exchange_rates`. El PDF y la vista de la cotización **siempre leen el snapshot**, nunca la tasa actual.
- `CurrencySummaryService::calculate($version)` es la única fuente de verdad para los totales por moneda.

### PDF

Se eligió **DomPDF** porque el hosting original no permitía Chromium (descarta Browsershot/Puppeteer). La plantilla `resources/views/quotes/pdf/quote2.blade.php` es standalone: CSS embebido, sin Tailwind ni Vite, e imágenes con rutas absolutas (`public_path()`).

---

## 8. Tests

```bash
php artisan test --compact
php artisan test --compact --filter=LeadMergeService
```

Los tests usan SQLite en memoria (`phpunit.xml`), no tocan la base de datos local. Hay pruebas de negocio para fusión de leads, filtros y catálogo de cotizaciones y cambio masivo de estado de seriales, además de las pruebas de autenticación y ajustes del starter kit.

---

## 9. Despliegue

El proyecto corrió en producción en **hosting compartido (cPanel)**, lo que condicionó varias decisiones:

| Limitación | Solución |
|---|---|
| Sin Supervisor / workers permanentes | `QUEUE_CONNECTION=database` procesada por el scheduler vía cron |
| Sin Node.js en el servidor | `npm run build` en local y subir `public/build/` |
| Sin Chromium | PDF con DomPDF (solo PHP) |

Pasos generales para producción:

```bash
npm run build
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Cron del scheduler:

```
* * * * * php /ruta/al/proyecto/artisan schedule:run >> /dev/null 2>&1
```

---

## 10. Personalización

El tema claro / oscuro lo controla el toggle de MaryUI, que agrega la clase `dark` al `<html>` y guarda la preferencia en `localStorage`. Los ajustes visuales dependientes del tema se basan en esa clase.

### Color de la barra de carga

La barra que aparece al navegar entre páginas (`wire:navigate`) toma su color de dos variables en `resources/css/app.css`:

```css
:root {
    --progress-bar-color: #171717; /* tema claro */
}

.dark {
    --progress-bar-color: #ffffff; /* tema oscuro */
}
```

Cambia los valores por cualquier color CSS; el brillo de la punta de la barra usa la misma variable.

### Logos

Los logos están en `public/` y se intercambian según el tema con las variantes `dark:` de Tailwind:

| Archivo | Se muestra en |
|---|---|
| `public/logo_negro.svg` | Tema claro |
| `public/logo_blanco.svg` | Tema oscuro |

Para usar tu marca, reemplaza ambos archivos conservando el nombre. Los componentes que los muestran son `resources/views/components/app-logo-icon.blade.php` (login y pantallas de autenticación) y `resources/views/components/brand-custom/brand.blade.php` (sidebar).

> Después de cambiar CSS, los cambios se ven al instante con `npm run dev`; para producción ejecuta `npm run build`.

---

## 11. Deuda técnica conocida

| Descripción | Ubicación |
|---|---|
| El token de Banxico se lee con `env()` dentro del servicio; con `config:cache` devuelve `null`. Debe moverse a `config/services.php`. | `app/Services/ExchangeRateService.php:68` |
| Si Banxico falla o no hay token y no existe la tasa del día, la pantalla de crear cotización lanza una excepción (no hay fallback a la última tasa ni seeder de tipos de cambio). | `app/Livewire/Quotes/Create.php:67` |
| Logs de diagnóstico temporales marcados `// TODO: remove`. | `app/Livewire/Forms/Quotes/QuoteForm.php` |
| `Company::avatar_url` llegó a guardar URLs absolutas; el PDF usa `basename()` como workaround. | `app/Models/Companies/Company.php`, `quote2.blade.php` |
| La creación de actividades al dar de alta un lead está comentada. | `app/Livewire/Leads/Create.php` |
| La columna `creat_at` (en `leads` y `contact_people`) tiene un error de nombre heredado. | Migraciones de leads |
| Componentes de prueba `app/Livewire/Dev/` y la ruta `/dev/livewire-test` sin autenticación. | `routes/web.php:7` |
| Parte de los tests del starter kit fallan: `UserFactory` asigna el rol `vendedor` sin sembrar roles, y el registro público está deshabilitado. | `database/factories/Users/UserFactory.php`, `tests/Feature/Auth` |
