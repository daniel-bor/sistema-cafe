# Sistema de Restricciones de Acceso a Paneles Filament

## Descripción General

Este sistema implementa restricciones de acceso basadas en roles para los diferentes paneles de Filament v3 en el sistema de café.

## Estructura de Roles

El sistema maneja los siguientes roles:

1. **AGRICULTOR** (ID: 1) - Acceso al panel agricultor (`/`)
2. **BENEFICIO_CAFE** (ID: 2) - Acceso al panel beneficio (`/beneficio`)
3. **PESO_CABAL** (ID: 3) - Acceso al panel peso cabal (`/peso-cabal`)
4. **ADMINISTRADOR** (ID: 4) - Acceso a todos los paneles

## Paneles Configurados

### Panel Agricultor
- **Ruta**: `/`
- **Acceso**: Usuarios con rol AGRICULTOR y ADMINISTRADOR
- **Recursos**: PesajeResource, TransporteResource, TransportistaResource

### Panel Beneficio
- **Ruta**: `/beneficio`
- **Acceso**: Usuarios con rol BENEFICIO_CAFE y ADMINISTRADOR
- **Recursos**: AgricultorResource, PesajeResource, TransporteResource, TransportistaResource

### Panel Peso Cabal
- **Ruta**: `/peso-cabal`
- **Acceso**: Usuarios con rol PESO_CABAL y ADMINISTRADOR
- **Recursos**: ParcialidadResource

## Componentes del Sistema

### 1. Middleware FilamentPanelAccess
- **Ubicación**: `app/Http/Middleware/FilamentPanelAccess.php`
- **Función**: Controla el acceso a cada panel según el rol del usuario
- **Comportamiento**: Redirige automáticamente al panel correcto si el usuario intenta acceder a un panel no autorizado

### 2. Métodos Auxiliares en User Model
- **Ubicación**: `app/Models/User.php`
- **Métodos**:
  - `hasRole(string $roleName)`: Verifica si el usuario tiene un rol específico
  - `isAdmin()`: Verifica si el usuario es administrador
  - `canAccessAgricultorPanel()`: Verifica acceso al panel agricultor
  - `canAccessBeneficioPanel()`: Verifica acceso al panel beneficio
  - `canAccessPesoCabalPanel()`: Verifica acceso al panel peso cabal
  - `getDefaultPanel()`: Obtiene el panel por defecto según el rol
  - `getDefaultPanelUrl()`: Obtiene la URL del panel por defecto

### 3. Política de Recursos
- **Ubicación**: `app/Policies/FilamentResourcePolicy.php`
- **Función**: Controla acceso a operaciones específicas (crear, editar, eliminar) en cada panel

### 4. Gates Registrados
- **Ubicación**: `app/Providers/AppServiceProvider.php`
- **Gates disponibles**:
  - `viewAny-agricultor-resource`
  - `create-agricultor-resource`
  - `update-agricultor-resource`
  - `delete-agricultor-resource`
  - `viewAny-beneficio-resource`
  - `create-beneficio-resource`
  - `update-beneficio-resource`
  - `delete-beneficio-resource`
  - `viewAny-pesocabal-resource`
  - `create-pesocabal-resource`
  - `update-pesocabal-resource`
  - `delete-pesocabal-resource`

## Configuración de Panel Providers

Cada panel provider tiene configurado el middleware de acceso:

```php
->authMiddleware([
    Authenticate::class,
    'filament.panel.access:nombrePanel',
])
```

## Flujo de Autenticación y Autorización

1. **Login**: Usuario se autentica en cualquier panel
2. **Verificación de Acceso**: El middleware `FilamentPanelAccess` verifica si el usuario puede acceder al panel solicitado
3. **Redirección Automática**: Si no tiene acceso, se redirige al panel correcto según su rol
4. **Acceso a Recursos**: Los Gates y políticas controlan el acceso a recursos específicos dentro del panel

## Comportamiento Especial para Administradores

Los usuarios con rol **ADMINISTRADOR** tienen acceso completo a todos los paneles y pueden navegar libremente entre ellos.

## Usuarios de Prueba (Configurados en DatabaseSeeder)

- **Administrador**: admin@cafe.com / 12345678
- **Agricultor**: agricultor@cafe.com / 12345678
- **Beneficio**: beneficio@cafe.com / 12345678
- **Peso Cabal**: peso@cafe.com / 12345678

## Mantenimiento

Para agregar nuevos roles o paneles:

1. Agregar el nuevo rol en `DatabaseSeeder.php`
2. Crear nuevo panel provider si es necesario
3. Actualizar los métodos en `User.php`
4. Actualizar el middleware `FilamentPanelAccess.php`
5. Agregar nuevos Gates en `AppServiceProvider.php`

## Seguridad

- Todas las rutas están protegidas por autenticación
- El acceso se verifica tanto a nivel de panel como de recurso
- Las redirecciones automáticas previenen accesos no autorizados
- Los administradores mantienen acceso completo para gestión del sistema
