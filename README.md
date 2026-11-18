# Coworking Management System - API Backend

Backend RESTful desarrollado en **Laravel** para la gestión de reservas de espacios de coworking, cumpliendo con arquitectura limpia, respuestas homogéneas mediante Traits, autenticación stateless con **Laravel Sanctum** y control de acceso granular mediante **Spatie Laravel-Permission**.

---

## Requisitos Previos

* PHP >= 8.2
* Composer
* MySQL / MariaDB (Laragon, XAMPP o Docker)

---

## Instalación y Configuración

1. **Crear proyecto base de Laravel (si aún no tienes la carpeta `vendor`):**
   ```bash
   composer create-project laravel/laravel coworking-api
   cd coworking-api
   ```
   *(Copia todos los archivos de este paquete dentro de la raíz de tu proyecto recién creado reemplazando los existentes).*

2. **Instalar dependencias requeridas:**
   ```bash
   php artisan install:api
   composer require spatie/laravel-permission
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
   ```

3. **Configurar variables de entorno (`.env`):**
   Crea una base de datos vacía llamada `coworking_api` en MySQL y configura tu `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=coworking_api
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Ejecutar migraciones y seeders:**
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Levantar servidor local:**
   ```bash
   php artisan serve
   ```

---

## Credenciales de Prueba por Rol

| Rol | Nombre | Correo Electrónico | Contraseña |
| :--- | :--- | :--- | :--- |
| **Administrador (`admin`)** | Administrador Principal | `admin@coworking.com` | `password123` |
| **Recepción (`staff`)** | Recepcionista Staff | `staff@coworking.com` | `password123` |
| **Cliente (`client`)** | Carlos Mendoza | `cliente@coworking.com` | `password123` |

---

## Colección de Postman

Importa el archivo `postman/Coworking_API.postman_collection.json` incluido en este paquete. Al ejecutar el endpoint `Login`, la colección guarda automáticamente el `token` en las variables de entorno para probar todos los endpoints protegidos.
