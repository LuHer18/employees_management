# 🚀 Employee Management API

## Descripción

Esta es una API de gestión de empleados creada con Laravel. Permite realizar operaciones CRUD para gestionar empleados, departamentos y roles. También incluye características adicionales como la validación de salarios y filtros de búsqueda por departamento y nombre de empleado.

## Requisitos Previos

- [PHP ^8.0](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- [MySQL](https://www.mysql.com/) o cualquier otra base de datos compatible
- [Laravel ^9.x](https://laravel.com/)

## Instalación

### 1. Clonar el repositorio
```
git clone https://github.com/LuHer18/employees_management.git
```
### 2. Instalar dependencias

Dentro del proyecto ejecutar: 
```
composer install
```
### 3. Configurar el archivo .ev
Copia el archivo de entorno de ejemplo:
```
cp .env.example .env
```
Luego, actualiza el archivo .env con los detalles de tu base de datos, por ejemplo:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_base_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

```
### 4. Genera la clave de la aplicacion
```
php artisan key:generate
```

### 5. Ejecuta la migración
```
php artisan migrate
```
### 6. Ejecuta el servidor en local
La API estará disponible en http://localhost:8000.
```
php artisan serve
```

## Endoints

Listado de endpoints disponibles de la API:

![Rutas_documentacion](https://github.com/user-attachments/assets/5be25fbf-b977-46f7-b747-0397a1ca634d)
Una vez este online el server local puede encontrar la documentación http://localhost:8000/api/documentation

