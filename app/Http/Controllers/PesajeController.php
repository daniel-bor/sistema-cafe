<?php

namespace App\Http\Controllers;

use App\Models\Pesaje;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Enums\EstadoPesaje;
use App\Services\CuentaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PesajeController extends Controller
{
    protected $cuentaService;

    public function __construct(CuentaService $cuentaService)
    {
        $this->cuentaService = $cuentaService;
    }

    /**
     * Mostrar todos los pesajes.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $query = Pesaje::with([
                'agricultor',
                'cuenta',
                'medidaPeso',
                'parcialidades' => function ($q) {
                    $q->whereNotIn('estado', [\App\Enums\EstadoParcialidad::RECHAZADO]);
                }
            ]);

            // Si es administrador, puede ver todos los pesajes
            if ($user->isAdmin()) {
                // Para administrador, excluir solo los pesajes con estado NUEVO
                $query->where('estado', '!=', EstadoPesaje::NUEVO);
            } else {
                // Si es agricultor, solo puede ver sus propios pesajes
                $query->where('agricultor_id', $user->agricultor->id);
            }

            // Filtros opcionales
            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('fecha_desde')) {
                $query->whereDate('created_at', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->whereDate('created_at', '<=', $request->fecha_hasta);
            }

            // Paginación
            $perPage = $request->get('per_page', 15);
            $pesajes = $query->paginate($perPage);

            // Agregar información calculada a cada pesaje
            $pesajes->getCollection()->transform(function ($pesaje) {
                $pesaje->cantidad_parcialidades = $pesaje->cantidad_parcialidades;
                $pesaje->cantidad_entregas = $pesaje->cantidad_entregas;
                $pesaje->total_parcialidades = $pesaje->total_parcialidades;
                $pesaje->fecha_ultimo_envio = $pesaje->fecha_ultimo_envio;
                $pesaje->no_cuenta = $pesaje->no_cuenta;
                $pesaje->porcentaje_diferencia = $pesaje->porcentaje_diferencia;
                return $pesaje;
            });

            return response()->json([
                'success' => true,
                'data' => $pesajes,
                'message' => 'Pesajes obtenidos exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener pesajes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo pesaje.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'cantidad_total' => 'required|numeric|min:1|max:100000',
                'medida_peso_id' => 'required|exists:medidas_peso,id',
                'tolerancia' => 'nullable|numeric|min:0|max:100',
                'precio_unitario' => 'nullable|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pesaje = new Pesaje($request->only([
                'cantidad_total',
                'medida_peso_id',
                'tolerancia',
                'precio_unitario'
            ]));

            // El agricultor_id se asigna automáticamente en el observer del modelo
            $pesaje->save();

            $pesaje->load(['agricultor', 'medidaPeso']);

            return response()->json([
                'success' => true,
                'data' => $pesaje,
                'message' => 'Pesaje creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear pesaje: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un pesaje específico.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $pesaje = Pesaje::with([
                'agricultor',
                'cuenta',
                'medidaPeso',
                'parcialidades.transporte',
                'parcialidades.transportista'
            ])->findOrFail($id);

            /** @var User $user */
            $user = Auth::user();

            // Verificar permisos: admin puede ver todos, agricultor solo los suyos
            if (!$user->isAdmin() && $pesaje->agricultor_id !== $user->agricultor->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver este pesaje'
                ], 403);
            }

            // Agregar información calculada
            $pesaje->cantidad_parcialidades = $pesaje->cantidad_parcialidades;
            $pesaje->cantidad_entregas = $pesaje->cantidad_entregas;
            $pesaje->total_parcialidades = $pesaje->total_parcialidades;
            $pesaje->fecha_ultimo_envio = $pesaje->fecha_ultimo_envio;
            $pesaje->no_cuenta = $pesaje->no_cuenta;
            $pesaje->porcentaje_diferencia = $pesaje->porcentaje_diferencia;

            return response()->json([
                'success' => true,
                'data' => $pesaje,
                'message' => 'Pesaje obtenido exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pesaje no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener pesaje: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un pesaje.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $pesaje = Pesaje::findOrFail($id);

            $user = Auth::user();

            // Verificar permisos: admin puede editar todos, agricultor solo los suyos
            if (!$user->isAdmin() && $pesaje->agricultor_id !== $user->agricultor->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para editar este pesaje'
                ], 403);
            }

            // Solo permitir edición si el pesaje está en estado NUEVO o RECHAZADO
            if (!in_array($pesaje->estado, [EstadoPesaje::NUEVO, EstadoPesaje::RECHAZADO])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede editar un pesaje en estado: ' . $pesaje->estado->getLabel()
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'cantidad_total' => 'numeric|min:1|max:100000',
                'medida_peso_id' => 'exists:medidas_peso,id',
                'tolerancia' => 'nullable|numeric|min:0|max:100',
                'precio_unitario' => 'nullable|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pesaje->update($request->only([
                'cantidad_total',
                'medida_peso_id',
                'tolerancia',
                'precio_unitario'
            ]));

            $pesaje->load(['agricultor', 'medidaPeso', 'cuenta']);

            return response()->json([
                'success' => true,
                'data' => $pesaje,
                'message' => 'Pesaje actualizado exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pesaje no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar pesaje: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un pesaje.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $pesaje = Pesaje::findOrFail($id);

            $user = Auth::user();

            // Verificar permisos: admin puede eliminar todos, agricultor solo los suyos
            if (!$user->isAdmin() && $pesaje->agricultor_id !== $user->agricultor->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar este pesaje'
                ], 403);
            }

            // Solo permitir eliminación si el pesaje está en estado NUEVO o RECHAZADO
            if (!in_array($pesaje->estado, [EstadoPesaje::NUEVO, EstadoPesaje::RECHAZADO])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un pesaje en estado: ' . $pesaje->estado->getLabel()
                ], 422);
            }

            $pesaje->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pesaje eliminado exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pesaje no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar pesaje: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar solicitud de pesaje (cambiar estado de NUEVO a PENDIENTE).
     */
    public function enviarSolicitud(string $id): JsonResponse
    {
        try {
            $pesaje = Pesaje::findOrFail($id);

            $user = Auth::user();

            // Solo el agricultor propietario puede enviar la solicitud
            if ($pesaje->agricultor_id !== $user->agricultor->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para enviar esta solicitud'
                ], 403);
            }

            // Verificar que el pesaje esté en estado NUEVO o RECHAZADO
            if (!in_array($pesaje->estado, [EstadoPesaje::NUEVO, EstadoPesaje::RECHAZADO])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden enviar pesajes en estado NUEVO o RECHAZADO'
                ], 422);
            }

            // Verificar que la suma de parcialidades sea igual al total
            if ($pesaje->cantidad_total != $pesaje->total_parcialidades) {
                return response()->json([
                    'success' => false,
                    'message' => 'La suma de las parcialidades debe ser igual al peso total del pesaje'
                ], 422);
            }

            $pesaje->update(['estado' => EstadoPesaje::PENDIENTE]);

            return response()->json([
                'success' => true,
                'data' => $pesaje,
                'message' => 'Solicitud de pesaje enviada exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pesaje no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aceptar solicitud de pesaje (solo administradores).
     */
    public function aceptarSolicitud(string $id): JsonResponse
    {
        try {
            $user = Auth::user();

            // Solo administradores pueden aceptar solicitudes
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para realizar esta acción'
                ], 403);
            }

            $pesaje = Pesaje::findOrFail($id);

            // Verificar que el pesaje esté en estado PENDIENTE
            if ($pesaje->estado !== EstadoPesaje::PENDIENTE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden aceptar pesajes en estado PENDIENTE'
                ], 422);
            }

            DB::transaction(function () use ($pesaje) {
                // Asignar una cuenta al pesaje usando el servicio
                $this->cuentaService->asignarCuentaAPesaje($pesaje);

                // Cambiar estado a ACEPTADO
                $pesaje->update(['estado' => EstadoPesaje::ACEPTADO]);
            });

            $pesaje->load(['cuenta', 'agricultor']);

            return response()->json([
                'success' => true,
                'data' => $pesaje,
                'message' => 'Solicitud de pesaje aceptada exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pesaje no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aceptar solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechazar solicitud de pesaje (solo administradores).
     */
    public function rechazarSolicitud(Request $request, string $id): JsonResponse
    {
        try {
            $user = Auth::user();

            // Solo administradores pueden rechazar solicitudes
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para realizar esta acción'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'observaciones' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pesaje = Pesaje::findOrFail($id);

            // Verificar que el pesaje esté en estado PENDIENTE
            if ($pesaje->estado !== EstadoPesaje::PENDIENTE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden rechazar pesajes en estado PENDIENTE'
                ], 422);
            }

            $pesaje->update([
                'estado' => EstadoPesaje::RECHAZADO,
                'observaciones' => $request->observaciones
            ]);

            return response()->json([
                'success' => true,
                'data' => $pesaje,
                'message' => 'Solicitud de pesaje rechazada exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pesaje no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cerrar cuenta de pesaje (solo administradores).
     */
    public function cerrarCuenta(string $id): JsonResponse
    {
        try {
            $user = Auth::user();

            // Solo administradores pueden cerrar cuentas
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para realizar esta acción'
                ], 403);
            }

            $pesaje = Pesaje::with(['parcialidades', 'cuenta'])->findOrFail($id);

            // Verificar que el pesaje esté en estado PESAJE_INICIADO
            if ($pesaje->estado !== EstadoPesaje::PESAJE_INICIADO) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cerrar cuentas de pesajes iniciados'
                ], 422);
            }

            // Verificar que todas las parcialidades estén finalizadas
            $totalParcialidades = $pesaje->parcialidades()
                ->where('estado', '!=', \App\Enums\EstadoParcialidad::RECHAZADO)
                ->count();

            $parcialidadesFinalizadas = $pesaje->parcialidades()
                ->where('estado', \App\Enums\EstadoParcialidad::FINALIZADO)
                ->count();

            if ($totalParcialidades !== $parcialidadesFinalizadas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Todas las parcialidades deben estar finalizadas antes de cerrar la cuenta'
                ], 422);
            }

            DB::transaction(function () use ($pesaje) {
                // Calcular el porcentaje de diferencia
                $porcentajeDiferencia = abs($pesaje->porcentaje_diferencia);
                $tolerancia = $pesaje->tolerancia ?? 0;

                if ($porcentajeDiferencia <= $tolerancia) {
                    // Si está dentro de la tolerancia, cerrar exitosamente
                    $pesaje->update([
                        'estado' => EstadoPesaje::PESAJE_FINALIZADO,
                        'fecha_cierre' => now()
                    ]);

                    if ($pesaje->cuenta) {
                        $pesaje->cuenta->update([
                            'estado' => \App\Enums\EstadoCuentaEnum::CUENTA_CONFIRMADA
                        ]);
                    }
                } else {
                    // Si excede la tolerancia, requiere revisión
                    $pesaje->update([
                        'estado' => EstadoPesaje::CUENTA_CERRADA,
                        'fecha_cierre' => now(),
                        'observaciones' => "Diferencia de peso ({$porcentajeDiferencia}%) excede la tolerancia permitida ({$tolerancia}%)"
                    ]);

                    if ($pesaje->cuenta) {
                        $pesaje->cuenta->update([
                            'estado' => \App\Enums\EstadoCuentaEnum::CUENTA_CERRADA
                        ]);
                    }
                }
            });

            $pesaje->load(['cuenta', 'agricultor']);

            return response()->json([
                'success' => true,
                'data' => $pesaje,
                'message' => 'Cuenta de pesaje cerrada exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pesaje no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar cuenta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de pesajes.
     */
    public function estadisticas(): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = Pesaje::query();

            // Si no es admin, filtrar por agricultor
            if (!$user->isAdmin()) {
                $query->where('agricultor_id', $user->agricultor->id);
            }

            $estadisticas = [
                'total_pesajes' => $query->count(),
                'pesajes_por_estado' => $query->groupBy('estado')
                    ->selectRaw('estado, count(*) as total')
                    ->pluck('total', 'estado'),
                'peso_total_procesado' => $query->where('estado', EstadoPesaje::PESAJE_FINALIZADO)
                    ->sum('cantidad_total'),
                'pesajes_activos' => $query->whereIn('estado', [
                    EstadoPesaje::ACEPTADO,
                    EstadoPesaje::PESAJE_INICIADO
                ])->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $estadisticas,
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener catálogos relacionados con pesajes.
     */
    public function catalogos(): JsonResponse
    {
        try {
            $catalogos = [
                'estados' => collect(EstadoPesaje::cases())->map(function ($estado) {
                    return [
                        'value' => $estado->value,
                        'label' => $estado->getLabel(),
                        'color' => $estado->getColor()
                    ];
                }),
                'medidas_peso' => \App\Models\MedidaPeso::select('id', 'nombre', 'simbolo')->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $catalogos,
                'message' => 'Catálogos obtenidos exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener catálogos: ' . $e->getMessage()
            ], 500);
        }
    }
}
