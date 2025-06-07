<?php

namespace App\Http\Controllers;

use App\Models\Parcialidad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Enums\EstadoParcialidad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ParcialidadController extends Controller
{
    /**
     * Mostrar todas las parcialidades.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $query = Parcialidad::with(['pesaje.cuenta', 'pesaje.medidaPeso', 'transporte', 'transportista']);

            // Filtros según el rol del usuario
            if ($user->isAdmin()) {
                // Admin puede ver todas las parcialidades
            } elseif ($user->hasRole('AGRICULTOR')) {
                // Agricultor solo puede ver las parcialidades de sus pesajes
                $query->whereHas('pesaje', function ($q) use ($user) {
                    $q->where('agricultor_id', $user->agricultor->id);
                });
            } elseif ($user->hasRole('PESO_CABAL')) {
                // Peso cabal puede ver parcialidades enviadas, recibidas y pesadas
                $query->whereIn('estado', [
                    EstadoParcialidad::ENVIADO,
                    EstadoParcialidad::RECIBIDO,
                    EstadoParcialidad::PESADO
                ]);
            } elseif ($user->hasRole('BENEFICIO_CAFE')) {
                // Beneficio puede ver parcialidades pesadas y finalizadas
                $query->whereIn('estado', [
                    EstadoParcialidad::PESADO,
                    EstadoParcialidad::FINALIZADO
                ]);
            }

            // Filtros adicionales desde query parameters
            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('pesaje_id')) {
                $query->where('pesaje_id', $request->pesaje_id);
            }

            if ($request->has('transportista_id')) {
                $query->where('transportista_id', $request->transportista_id);
            }

            if ($request->has('transporte_id')) {
                $query->where('transporte_id', $request->transporte_id);
            }

            if ($request->has('fecha_desde')) {
                $query->whereDate('fecha_recepcion', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->whereDate('fecha_recepcion', '<=', $request->fecha_hasta);
            }

            // Paginación opcional
            if ($request->has('per_page')) {
                $parcialidades = $query->paginate($request->per_page);
            } else {
                $parcialidades = $query->get();
            }

            return response()->json([
                'success' => true,
                'data' => $parcialidades,
                'message' => 'Parcialidades obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener parcialidades: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una nueva parcialidad.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'pesaje_id' => 'required|integer|exists:pesajes,id',
                'transporte_id' => 'required|integer|exists:transportes,id',
                'transportista_id' => 'required|integer|exists:transportistas,id',
                'peso' => 'required|numeric|min:0',
                'peso_bascula' => 'nullable|numeric|min:0',
                'fecha_recepcion' => 'nullable|date',
                'fecha_envio' => 'nullable|date',
                'observaciones' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            /** @var User $user */
            $user = Auth::user();

            // Verificar permisos para crear parcialidades
            if (!$user->isAdmin() && !$user->hasRole('AGRICULTOR')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para crear parcialidades'
                ], 403);
            }

            $parcialidad = new Parcialidad($request->only([
                'pesaje_id', 'transporte_id', 'transportista_id',
                'peso', 'peso_bascula', 'fecha_recepcion',
                'fecha_envio', 'observaciones'
            ]));

            $parcialidad->save();

            $parcialidad->load(['pesaje.cuenta', 'pesaje.medidaPeso', 'transporte', 'transportista']);

            return response()->json([
                'success' => true,
                'data' => $parcialidad,
                'message' => 'Parcialidad creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear parcialidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una parcialidad específica.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $parcialidad = Parcialidad::with(['pesaje.cuenta', 'pesaje.medidaPeso', 'transporte', 'transportista'])
                ->findOrFail($id);

            /** @var User $user */
            $user = Auth::user();

            // Verificar permisos según el rol
            if (!$user->isAdmin()) {
                if ($user->hasRole('AGRICULTOR')) {
                    // Agricultor solo puede ver sus propias parcialidades
                    if ($parcialidad->pesaje->agricultor_id !== $user->agricultor->id) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No tienes permisos para ver esta parcialidad'
                        ], 403);
                    }
                } elseif ($user->hasRole('PESO_CABAL')) {
                    // Peso cabal solo puede ver parcialidades enviadas, recibidas y pesadas
                    if (!in_array($parcialidad->estado, [
                        EstadoParcialidad::ENVIADO,
                        EstadoParcialidad::RECIBIDO,
                        EstadoParcialidad::PESADO
                    ])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No tienes permisos para ver esta parcialidad'
                        ], 403);
                    }
                } elseif ($user->hasRole('BENEFICIO_CAFE')) {
                    // Beneficio solo puede ver parcialidades pesadas y finalizadas
                    if (!in_array($parcialidad->estado, [
                        EstadoParcialidad::PESADO,
                        EstadoParcialidad::FINALIZADO
                    ])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No tienes permisos para ver esta parcialidad'
                        ], 403);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $parcialidad,
                'message' => 'Parcialidad obtenida exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parcialidad no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener parcialidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar una parcialidad.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $parcialidad = Parcialidad::findOrFail($id);

            /** @var User $user */
            $user = Auth::user();

            // Verificar permisos según el rol y estado
            if (!$user->isAdmin()) {
                if ($user->hasRole('AGRICULTOR')) {
                    // Agricultor solo puede editar sus parcialidades en estado PENDIENTE o RECHAZADO
                    if ($parcialidad->pesaje->agricultor_id !== $user->agricultor->id ||
                        !in_array($parcialidad->estado, [EstadoParcialidad::PENDIENTE, EstadoParcialidad::RECHAZADO])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No tienes permisos para editar esta parcialidad o ya fue procesada'
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permisos para editar parcialidades'
                    ], 403);
                }
            }

            $validator = Validator::make($request->all(), [
                'transporte_id' => 'integer|exists:transportes,id',
                'transportista_id' => 'integer|exists:transportistas,id',
                'peso' => 'numeric|min:0',
                'peso_bascula' => 'nullable|numeric|min:0',
                'fecha_recepcion' => 'nullable|date',
                'fecha_envio' => 'nullable|date',
                'observaciones' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $parcialidad->update($request->only([
                'transporte_id', 'transportista_id', 'peso',
                'peso_bascula', 'fecha_recepcion', 'fecha_envio', 'observaciones'
            ]));

            $parcialidad->load(['pesaje.cuenta', 'pesaje.medidaPeso', 'transporte', 'transportista']);

            return response()->json([
                'success' => true,
                'data' => $parcialidad,
                'message' => 'Parcialidad actualizada exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parcialidad no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar parcialidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una parcialidad (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $parcialidad = Parcialidad::findOrFail($id);

            /** @var User $user */
            $user = Auth::user();

            // Solo admin y agricultor (dueño) pueden eliminar, y solo si está en estado PENDIENTE
            if (!$user->isAdmin()) {
                if (!$user->hasRole('AGRICULTOR') ||
                    $parcialidad->pesaje->agricultor_id !== $user->agricultor->id ||
                    $parcialidad->estado !== EstadoParcialidad::PENDIENTE) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permisos para eliminar esta parcialidad o ya fue procesada'
                    ], 403);
                }
            }

            $parcialidad->delete();

            return response()->json([
                'success' => true,
                'message' => 'Parcialidad eliminada exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parcialidad no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar parcialidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar parcialidad (cambiar estado a ENVIADO).
     */
    public function enviar(string $id): JsonResponse
    {
        try {
            $parcialidad = Parcialidad::findOrFail($id);

            /** @var User $user */
            $user = Auth::user();

            // Verificar permisos y estado
            if (!$user->isAdmin() &&
                (!$user->hasRole('AGRICULTOR') ||
                 $parcialidad->pesaje->agricultor_id !== $user->agricultor->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para enviar esta parcialidad'
                ], 403);
            }

            if ($parcialidad->estado !== EstadoParcialidad::PENDIENTE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden enviar parcialidades en estado PENDIENTE'
                ], 400);
            }

            // Verificar que el transporte y transportista estén disponibles
            if (!$parcialidad->transporte->disponible || !$parcialidad->transportista->disponible) {
                return response()->json([
                    'success' => false,
                    'message' => 'El transporte o transportista no están disponibles'
                ], 400);
            }

            $parcialidad->estado = EstadoParcialidad::ENVIADO;
            $parcialidad->fecha_envio = now();
            $parcialidad->save();

            // Marcar transporte y transportista como no disponibles
            $parcialidad->transporte->disponible = false;
            $parcialidad->transporte->save();

            $parcialidad->transportista->disponible = false;
            $parcialidad->transportista->save();

            $parcialidad->load(['pesaje.cuenta', 'pesaje.medidaPeso', 'transporte', 'transportista']);

            return response()->json([
                'success' => true,
                'data' => $parcialidad,
                'message' => 'Parcialidad enviada exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parcialidad no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar parcialidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recibir parcialidad (cambiar estado a RECIBIDO).
     */
    public function recibir(string $id): JsonResponse
    {
        try {
            $parcialidad = Parcialidad::findOrFail($id);

            /** @var User $user */
            $user = Auth::user();

            // Solo peso cabal y admin pueden recibir parcialidades
            if (!$user->isAdmin() && !$user->hasRole('PESO_CABAL')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para recibir parcialidades'
                ], 403);
            }

            if ($parcialidad->estado !== EstadoParcialidad::ENVIADO) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden recibir parcialidades en estado ENVIADO'
                ], 400);
            }

            $parcialidad->estado = EstadoParcialidad::RECIBIDO;
            $parcialidad->fecha_recepcion = now();
            $parcialidad->save();

            $parcialidad->load(['pesaje.cuenta', 'pesaje.medidaPeso', 'transporte', 'transportista']);

            return response()->json([
                'success' => true,
                'data' => $parcialidad,
                'message' => 'Parcialidad recibida exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parcialidad no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recibir parcialidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pesar parcialidad (cambiar estado a PESADO y actualizar peso_bascula).
     */
    public function pesar(Request $request, string $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'peso_bascula' => 'required|numeric|min:0',
                'observaciones' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $parcialidad = Parcialidad::findOrFail($id);

            /** @var User $user */
            $user = Auth::user();

            // Solo peso cabal y admin pueden pesar parcialidades
            if (!$user->isAdmin() && !$user->hasRole('PESO_CABAL')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para pesar parcialidades'
                ], 403);
            }

            if ($parcialidad->estado !== EstadoParcialidad::RECIBIDO) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden pesar parcialidades en estado RECIBIDO'
                ], 400);
            }

            $parcialidad->estado = EstadoParcialidad::PESADO;
            $parcialidad->peso_bascula = $request->peso_bascula;
            if ($request->has('observaciones')) {
                $parcialidad->observaciones = $request->observaciones;
            }
            $parcialidad->save();

            // Liberar transporte y transportista
            $parcialidad->transporte->disponible = true;
            $parcialidad->transporte->save();

            $parcialidad->transportista->disponible = true;
            $parcialidad->transportista->save();

            $parcialidad->load(['pesaje.cuenta', 'pesaje.medidaPeso', 'transporte', 'transportista']);

            return response()->json([
                'success' => true,
                'data' => $parcialidad,
                'message' => 'Parcialidad pesada exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parcialidad no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al pesar parcialidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalizar parcialidad.
     */
    public function finalizar(string $id): JsonResponse
    {
        try {
            $parcialidad = Parcialidad::findOrFail($id);

            /** @var User $user */
            $user = Auth::user();

            // Solo beneficio y admin pueden finalizar parcialidades
            if (!$user->isAdmin() && !$user->hasRole('BENEFICIO_CAFE')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para finalizar parcialidades'
                ], 403);
            }

            if ($parcialidad->estado !== EstadoParcialidad::PESADO) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden finalizar parcialidades en estado PESADO'
                ], 400);
            }

            $parcialidad->estado = EstadoParcialidad::FINALIZADO;
            $parcialidad->save();

            $parcialidad->load(['pesaje.cuenta', 'pesaje.medidaPeso', 'transporte', 'transportista']);

            return response()->json([
                'success' => true,
                'data' => $parcialidad,
                'message' => 'Parcialidad finalizada exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parcialidad no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar parcialidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener catálogos relacionados con parcialidades.
     */
    public function catalogos(): JsonResponse
    {
        try {
            $estados = collect(EstadoParcialidad::cases())->map(function ($estado) {
                return [
                    'value' => $estado->value,
                    'label' => $estado->getLabel(),
                    'color' => $estado->getColor()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'estados' => $estados
                ],
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
