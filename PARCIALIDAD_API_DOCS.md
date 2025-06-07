# API de Parcialidades - Documentación

## Descripción
Este controlador maneja todas las operaciones relacionadas con las parcialidades del sistema de café, incluyendo la gestión de estados y los flujos de trabajo según los diferentes roles de usuario.

## Rutas Disponibles

### Rutas de Recursos (CRUD)
- `GET /api/parcialidades` - Listar todas las parcialidades
- `POST /api/parcialidades` - Crear una nueva parcialidad
- `GET /api/parcialidades/{id}` - Obtener una parcialidad específica
- `PUT /api/parcialidades/{id}` - Actualizar una parcialidad
- `DELETE /api/parcialidades/{id}` - Eliminar una parcialidad (soft delete)

### Rutas de Acciones Específicas
- `POST /api/parcialidades/{id}/enviar` - Enviar parcialidad (PENDIENTE → ENVIADO)
- `POST /api/parcialidades/{id}/recibir` - Recibir parcialidad (ENVIADO → RECIBIDO)
- `POST /api/parcialidades/{id}/pesar` - Pesar parcialidad (RECIBIDO → PESADO)
- `POST /api/parcialidades/{id}/finalizar` - Finalizar parcialidad (PESADO → FINALIZADO)

### Rutas de Catálogos
- `GET /api/parcialidades/catalogos/general` - Obtener catálogos (estados, etc.)

## Estados de Parcialidad

| Estado | Valor | Descripción | Color |
|--------|-------|-------------|-------|
| PENDIENTE | 0 | Parcialidad creada, esperando envío | warning |
| ENVIADO | 1 | Parcialidad enviada al peso cabal | info |
| RECIBIDO | 2 | Parcialidad recibida en peso cabal | success |
| RECHAZADO | 3 | Parcialidad rechazada | danger |
| PESADO | 4 | Parcialidad pesada | primary |
| FINALIZADO | 5 | Proceso completado | secondary |

## Permisos por Rol

### AGRICULTOR
- **Ver**: Solo sus propias parcialidades
- **Crear**: Nuevas parcialidades para sus pesajes
- **Editar**: Solo parcialidades en estado PENDIENTE o RECHAZADO
- **Eliminar**: Solo parcialidades en estado PENDIENTE
- **Enviar**: Cambiar de PENDIENTE a ENVIADO

### PESO_CABAL
- **Ver**: Parcialidades en estado ENVIADO, RECIBIDO y PESADO
- **Recibir**: Cambiar de ENVIADO a RECIBIDO
- **Pesar**: Cambiar de RECIBIDO a PESADO (incluye peso_bascula)

### BENEFICIO_CAFE
- **Ver**: Parcialidades en estado PESADO y FINALIZADO
- **Finalizar**: Cambiar de PESADO a FINALIZADO

### ADMINISTRADOR
- **Acceso completo**: Todas las operaciones sin restricciones

## Ejemplos de Uso

### 1. Listar Parcialidades con Filtros
```http
GET /api/parcialidades?estado=1&pesaje_id=123&per_page=10
Authorization: Bearer {token}
```

### 2. Crear Nueva Parcialidad
```http
POST /api/parcialidades
Authorization: Bearer {token}
Content-Type: application/json

{
    "pesaje_id": 123,
    "transporte_id": 456,
    "transportista_id": 789,
    "peso": 100.5,
    "observaciones": "Café de primera calidad"
}
```

### 3. Enviar Parcialidad
```http
POST /api/parcialidades/123/enviar
Authorization: Bearer {token}
```

### 4. Pesar Parcialidad
```http
POST /api/parcialidades/123/pesar
Authorization: Bearer {token}
Content-Type: application/json

{
    "peso_bascula": 98.7,
    "observaciones": "Peso verificado en báscula principal"
}
```

## Filtros Disponibles

Al listar parcialidades (`GET /api/parcialidades`), se pueden aplicar los siguientes filtros:

- `estado`: Filtrar por estado específico
- `pesaje_id`: Filtrar por ID de pesaje
- `transportista_id`: Filtrar por ID de transportista
- `transporte_id`: Filtrar por ID de transporte
- `fecha_desde`: Filtrar desde fecha de recepción
- `fecha_hasta`: Filtrar hasta fecha de recepción
- `per_page`: Número de elementos por página (para paginación)

## Respuestas de API

### Respuesta Exitosa
```json
{
    "success": true,
    "data": {
        "id": 123,
        "pesaje_id": 456,
        "transporte_id": 789,
        "transportista_id": 101,
        "peso": 100.5,
        "peso_bascula": 98.7,
        "estado": 1,
        "fecha_recepcion": "2025-06-07T10:30:00Z",
        "fecha_envio": "2025-06-07T08:15:00Z",
        "observaciones": "Café de primera calidad",
        "pesaje": { /* datos del pesaje */ },
        "transporte": { /* datos del transporte */ },
        "transportista": { /* datos del transportista */ }
    },
    "message": "Operación exitosa"
}
```

### Respuesta de Error
```json
{
    "success": false,
    "message": "Descripción del error",
    "errors": { /* errores de validación si aplica */ }
}
```

## Validaciones

### Crear/Actualizar Parcialidad
- `pesaje_id`: Requerido, debe existir en la tabla pesajes
- `transporte_id`: Requerido, debe existir en la tabla transportes
- `transportista_id`: Requerido, debe existir en la tabla transportistas
- `peso`: Requerido, numérico, mayor a 0
- `peso_bascula`: Opcional, numérico, mayor a 0
- `observaciones`: Opcional, máximo 500 caracteres

### Pesar Parcialidad
- `peso_bascula`: Requerido, numérico, mayor a 0
- `observaciones`: Opcional, máximo 500 caracteres

## Flujo de Trabajo

1. **Agricultor** crea parcialidad → Estado: PENDIENTE
2. **Agricultor** envía parcialidad → Estado: ENVIADO (transporte y transportista quedan no disponibles)
3. **Peso Cabal** recibe parcialidad → Estado: RECIBIDO
4. **Peso Cabal** pesa parcialidad → Estado: PESADO (transporte y transportista quedan disponibles)
5. **Beneficio** finaliza parcialidad → Estado: FINALIZADO

## Consideraciones Especiales

- Al enviar una parcialidad, el transporte y transportista asociados se marcan como no disponibles
- Al pesar una parcialidad, el transporte y transportista se liberan (disponibles = true)
- Solo se pueden editar parcialidades en estado PENDIENTE o RECHAZADO
- Solo se pueden eliminar parcialidades en estado PENDIENTE
- Las transiciones de estado son estrictas y siguen el flujo definido
