# API de Cuentas - Documentación

## Descripción
Este controlador maneja todas las operaciones relacionadas con las cuentas del sistema de café, incluyendo la gestión de estados y los flujos de trabajo según los diferentes roles de usuario. Las cuentas actúan como contenedores para los pesajes de los agricultores.

## Rutas Disponibles

### Rutas de Recursos (CRUD)
- `GET /api/cuentas` - Listar todas las cuentas
- `POST /api/cuentas` - Crear una nueva cuenta
- `GET /api/cuentas/{id}` - Obtener una cuenta específica
- `PUT /api/cuentas/{id}` - Actualizar una cuenta
- `DELETE /api/cuentas/{id}` - Eliminar una cuenta (soft delete)

### Rutas de Acciones Específicas
- `POST /api/cuentas/{id}/cambiar-estado` - Cambiar estado de cuenta
- `POST /api/cuentas/{id}/abrir` - Abrir cuenta (CREADA → ABIERTA)
- `POST /api/cuentas/{id}/cerrar` - Cerrar cuenta (PESAJE_FINALIZADO → CERRADA)
- `POST /api/cuentas/{id}/confirmar` - Confirmar cuenta (CERRADA → CONFIRMADA)

### Rutas de Información
- `GET /api/cuentas-estadisticas` - Obtener estadísticas de cuentas
- `GET /api/cuentas/catalogos/general` - Obtener catálogos (estados, etc.)

## Estados de Cuenta

| Estado | Valor | Descripción | Color |
|--------|-------|-------------|-------|
| CUENTA_CREADA | 0 | Cuenta creada, lista para abrir | gray |
| CUENTA_ABIERTA | 1 | Cuenta abierta, puede recibir pesajes | blue |
| PESAJE_INICIADO | 2 | Pesajes en proceso | yellow |
| PESAJE_FINALIZADO | 3 | Pesajes completados | green |
| CUENTA_CERRADA | 4 | Cuenta cerrada para revisión | red |
| CUENTA_CONFIRMADA | 5 | Proceso completado y confirmado | purple |

## Permisos por Rol

### AGRICULTOR
- **Ver**: Solo sus propias cuentas
- **Crear**: No puede crear cuentas manualmente (se crean automáticamente)
- **Editar**: No puede editar cuentas directamente
- **Eliminar**: No puede eliminar cuentas
- **Estados**: Solo puede crear cuentas (estado CUENTA_CREADA)

### BENEFICIO_CAFE
- **Ver**: Cuentas asignadas a su procesamiento
- **Estados permitidos**: 
  - Abrir cuenta (CREADA → ABIERTA)
  - Iniciar pesaje (ABIERTA → PESAJE_INICIADO)
  - Finalizar pesaje (PESAJE_INICIADO → PESAJE_FINALIZADO)

### PESO_CABAL
- **Ver**: Cuentas en proceso de finalización
- **Estados permitidos**:
  - Cerrar cuenta (PESAJE_FINALIZADO → CERRADA)
  - Confirmar cuenta (CERRADA → CONFIRMADA)

### ADMINISTRADOR
- **Acceso completo**: Todas las operaciones sin restricciones
- **Puede**: Crear, editar, eliminar y cambiar cualquier estado

## Ejemplos de Uso

### 1. Listar Cuentas con Filtros
```http
GET /api/cuentas?estado=1&agricultor_id=123&per_page=10
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "no_cuenta": "CTA-2025-123-0001",
            "estado": 1,
            "agricultor_id": 123,
            "created_at": "2025-06-07T10:30:00Z",
            "agricultor": {
                "id": 123,
                "nombre": "Juan Pérez"
            },
            "pesajes": []
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 10,
        "total": 1,
        "last_page": 1
    }
}
```

### 2. Crear Nueva Cuenta (Solo Administrador)
```http
POST /api/cuentas
Authorization: Bearer {token}
Content-Type: application/json

{
    "agricultor_id": 123,
    "no_cuenta": "CTA-2025-123-0002",
    "estado": 0
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Cuenta creada exitosamente",
    "data": {
        "id": 2,
        "no_cuenta": "CTA-2025-123-0002",
        "estado": 0,
        "agricultor_id": 123,
        "agricultor": {
            "id": 123,
            "nombre": "Juan Pérez"
        },
        "pesajes": []
    }
}
```

### 3. Abrir Cuenta
```http
POST /api/cuentas/1/abrir
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Cuenta abierta exitosamente",
    "data": {
        "id": 1,
        "no_cuenta": "CTA-2025-123-0001",
        "estado": 1,
        "agricultor_id": 123
    }
}
```

### 4. Cambiar Estado Manualmente
```http
POST /api/cuentas/1/cambiar-estado
Authorization: Bearer {token}
Content-Type: application/json

{
    "estado": 2,
    "observaciones": "Iniciando proceso de pesaje"
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Estado de la cuenta actualizado exitosamente",
    "data": {
        "id": 1,
        "no_cuenta": "CTA-2025-123-0001",
        "estado": 2,
        "agricultor_id": 123
    }
}
```

### 5. Obtener Estadísticas
```http
GET /api/cuentas-estadisticas
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "total_cuentas": 150,
        "cuentas_activas": 85,
        "cuentas_finalizadas": 45,
        "por_estado": [
            {
                "estado": 0,
                "label": "Creada",
                "count": 20
            },
            {
                "estado": 1,
                "label": "Abierta",
                "count": 35
            },
            {
                "estado": 2,
                "label": "Pesaje Iniciado",
                "count": 25
            },
            {
                "estado": 3,
                "label": "Pesaje Finalizado",
                "count": 30
            },
            {
                "estado": 4,
                "label": "Cerrada",
                "count": 25
            },
            {
                "estado": 5,
                "label": "Confirmada",
                "count": 15
            }
        ]
    }
}
```

## Filtros Disponibles

Al listar cuentas (`GET /api/cuentas`), se pueden aplicar los siguientes filtros:

- `estado`: Filtrar por estado específico (0-5)
- `agricultor_id`: Filtrar por ID de agricultor (solo administradores)
- `no_cuenta`: Búsqueda parcial por número de cuenta
- `per_page`: Número de elementos por página (para paginación, default: 15)

## Validaciones

### Crear/Actualizar Cuenta
- `agricultor_id`: Requerido al crear, debe existir en la tabla agricultors
- `no_cuenta`: Opcional, string máximo 255 caracteres, único en la tabla
- `estado`: Opcional, entero entre 0-5

### Cambiar Estado
- `estado`: Requerido, entero entre 0-5
- `observaciones`: Opcional, máximo 500 caracteres

## Respuestas de API

### Respuesta Exitosa
```json
{
    "success": true,
    "data": {
        "id": 123,
        "no_cuenta": "CTA-2025-123-0001",
        "estado": 1,
        "agricultor_id": 456,
        "created_at": "2025-06-07T10:30:00Z",
        "updated_at": "2025-06-07T10:35:00Z",
        "agricultor": {
            "id": 456,
            "nombre": "Juan Pérez",
            "apellido": "García"
        },
        "pesajes": [
            {
                "id": 789,
                "peso": 100.5,
                "estado": 1
            }
        ]
    },
    "message": "Operación exitosa"
}
```

### Respuesta de Error
```json
{
    "success": false,
    "error": "Descripción del error",
    "message": "Mensaje detallado del error",
    "errors": {
        "campo": ["Error de validación específico"]
    }
}
```

## Flujo de Trabajo

1. **Sistema/Admin** crea cuenta → Estado: CUENTA_CREADA
2. **Beneficio** abre cuenta → Estado: CUENTA_ABIERTA
3. **Beneficio** inicia pesajes → Estado: PESAJE_INICIADO
4. **Beneficio** finaliza pesajes → Estado: PESAJE_FINALIZADO
5. **Peso Cabal** cierra cuenta → Estado: CUENTA_CERRADA
6. **Peso Cabal** confirma cuenta → Estado: CUENTA_CONFIRMADA

## Generación Automática de Número de Cuenta

Si no se proporciona un `no_cuenta` al crear una cuenta, se genera automáticamente con el formato:
```
CTA-{AÑO}-{AGRICULTOR_ID}-{SECUENCIAL}
```

**Ejemplo:** `CTA-2025-123-0001`

## Consideraciones Especiales

- Las cuentas solo pueden eliminarse si no tienen pesajes activos
- Solo administradores pueden crear y eliminar cuentas manualmente
- Los agricultores solo pueden ver sus propias cuentas
- Las transiciones de estado están controladas por permisos de rol
- Se utiliza soft delete para mantener histórico
- Las cuentas actúan como contenedores para agrupar pesajes del mismo agricultor

## Relaciones del Modelo

### Cuenta pertenece a:
- **Agricultor** (belongsTo): Un agricultor puede tener múltiples cuentas

### Cuenta tiene muchos:
- **Pesajes** (hasMany): Una cuenta puede contener múltiples pesajes

## Códigos de Estado HTTP

- `200` - Operación exitosa
- `201` - Recurso creado exitosamente
- `403` - Sin permisos para realizar la acción
- `404` - Cuenta no encontrada
- `422` - Errores de validación
- `500` - Error interno del servidor
