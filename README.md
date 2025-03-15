# Sistema de Café

## Prerrequisitos
- PHP 8.2 o superior
- Composer

## Instalación

### 1. Clonar el repositorio
```bash
git clone https://github.com/daniel-bor/sistema-cafe.git
cd sistema-cafe
```

### 2. Instalar dependencias
```bash
composer install
```

### 3. Configurar entorno
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurar base de datos
Edita el archivo `.env` con tus credenciales de base de datos.

### 5. Ejecutar migraciones
```bash
php artisan migrate --seed
```

## Iniciar servidor
```bash
php artisan serve
```

El servidor estará disponible en http://localhost:8000

## Rutas principales
- `/login` - Iniciar sesión
- `/register` - Registrar usuario
