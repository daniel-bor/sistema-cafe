<?php

namespace App\Http\Controllers;

use App\Models\Cuenta;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Enums\EstadoCuentaEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CuentaController extends Controller
{
    /**
     * Mostrar todas las cuentas.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $query = Cuenta::with([
                'agricultor',
                'pesajes' => function ($q) {
                    $q->with(['medidaPeso', 'parcialidades']);
                }
            ]);

            // Si es administrador, puede ver todas las cuentas
            if (!$user->isAdmin()) {
                // Si es agricultor, solo puede ver sus propias cuentas
                if ($user->agricultor) {
                    $query->where('agricultor_id', $user->agricultor->id);
                } else {
                    return response()->json([
                        'error' => 'Usuario sin permisos para ver cuentas'
                    ], 403);
                }
            }

            // Filtros opcionales
            if ($request->has('estado')) {
                $estadoValue = EstadoCuentaEnum::tryFrom((int) $request->estado);
                if ($estadoValue) {
                    $query->where('estado', $estadoValue);
                }
            }

            if ($request->has('agricultor_id') && $user->isAdmin()) {
                $query->where('agricultor_id', $request->agricultor_id);
            }

            if ($request->has('no_cuenta')) {
                $query->where('no_cuenta', 'LIKE', '%' . $request->no_cuenta . '%');
            }

            // Paginación
            $perPage = $request->get('per_page', 15);
            $cuentas = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $cuentas->items(),
                'meta' => [
                    'current_page' => $cuentas->currentPage(),
                    'per_page' => $cuentas->perPage(),
                    'total' => $cuentas->total(),
                    'last_page' => $cuentas->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener las cuentas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una nueva cuenta.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'agricultor_id' => 'required|exists:agricultors,id',
                'no_cuenta' => 'nullable|string|max:255|unique:cuentas,no_cuenta',
                'estado' => 'nullable|integer|in:0,1,2,3,4,5'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            /** @var User $user */
            $user = Auth::user();

            // Verificar permisos - solo admin puede crear cuentas manualmente
            if (!$user->isAdmin()) {
                return response()->json([
                    'error' => 'No tiene permisos para crear cuentas'
                ], 403);
            }

            $data = $validator->validated();

            // Si no se proporciona estado, se asigna el estado por defecto
            if (!isset($data['estado'])) {
                $data['estado'] = EstadoCuentaEnum::CUENTA_CREADA;
            } else {
                $data['estado'] = EstadoCuentaEnum::from($data['estado']);
            }

            // Si no se proporciona número de cuenta, se genera automáticamente
            if (!isset($data['no_cuenta'])) {
                $data['no_cuenta'] = $this->generarNumeroCuenta($data['agricultor_id']);
            }

            $cuenta = Cuenta::create($data);
            $cuenta->load(['agricultor', 'pesajes']);

            return response()->json([
                'success' => true,
                'message' => 'Cuenta creada exitosamente',
                'data' => $cuenta
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear la cuenta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una cuenta específica.
     */
    public function show(string $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $query = Cuenta::with([
                'agricultor',
                'pesajes' => function ($q) {
                    $q->with(['medidaPeso', 'parcialidades' => function ($pq) {
                        $pq->with(['transportista', 'transporte']);
                    }]);
                }
            ]);

            $cuenta = $query->findOrFail($id);

            // Verificar permisos
            if (!$user->isAdmin() && $user->agricultor && $cuenta->agricultor_id !== $user->agricultor->id) {
                return response()->json([
                    'error' => 'No tiene permisos para ver esta cuenta'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $cuenta
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Cuenta no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener la cuenta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar una cuenta.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // Solo admin puede actualizar cuentas manualmente
            if (!$user->isAdmin()) {
                return response()->json([
                    'error' => 'No tiene permisos para actualizar cuentas'
                ], 403);
            }

            $cuenta = Cuenta::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'agricultor_id' => 'sometimes|exists:agricultors,id',
                'no_cuenta' => 'sometimes|string|max:255|unique:cuentas,no_cuenta,' . $id,
                'estado' => 'sometimes|integer|in:0,1,2,3,4,5'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Convertir estado si viene como entero
            if (isset($data['estado'])) {
                $data['estado'] = EstadoCuentaEnum::from($data['estado']);
            }

            $cuenta->update($data);
            $cuenta->load(['agricultor', 'pesajes']);

            return response()->json([
                'success' => true,
                'message' => 'Cuenta actualizada exitosamente',
                'data' => $cuenta
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Cuenta no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar la cuenta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una cuenta (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // Solo admin puede eliminar cuentas
            if (!$user->isAdmin()) {
                return response()->json([
                    'error' => 'No tiene permisos para eliminar cuentas'
                ], 403);
            }

            $cuenta = Cuenta::findOrFail($id);

            // Verificar que la cuenta no tenga pesajes activos
            $pesajesActivos = $cuenta->pesajes()
                ->whereNotIn('estado', [
                    \App\Enums\EstadoPesaje::RECHAZADO,
                    \App\Enums\EstadoPesaje::PESAJE_FINALIZADO
                ])
                ->exists();

            if ($pesajesActivos) {
                return response()->json([
                    'error' => 'No se puede eliminar una cuenta con pesajes activos'
                ], 422);
            }

            $cuenta->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cuenta eliminada exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Cuenta no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al eliminar la cuenta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar el estado de una cuenta.
     */
    public function cambiarEstado(Request $request, string $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'estado' => 'required|integer|in:0,1,2,3,4,5',
                'observaciones' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $cuenta = Cuenta::findOrFail($id);

            // Verificar permisos según el panel del usuario
            $nuevoEstado = EstadoCuentaEnum::from($request->estado);

            if (!$this->puedeActualizarEstado($user, $cuenta, $nuevoEstado)) {
                return response()->json([
                    'error' => 'No tiene permisos para cambiar el estado de esta cuenta'
                ], 403);
            }

            $cuenta->update([
                'estado' => $nuevoEstado
            ]);

            $cuenta->load(['agricultor', 'pesajes']);

            return response()->json([
                'success' => true,
                'message' => 'Estado de la cuenta actualizado exitosamente',
                'data' => $cuenta
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Cuenta no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cambiar el estado de la cuenta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Abrir una cuenta (cambiar estado a CUENTA_ABIERTA).
     */
    public function abrir(string $id): JsonResponse
    {
        return $this->cambiarEstadoInterno($id, EstadoCuentaEnum::CUENTA_ABIERTA, 'Cuenta abierta exitosamente');
    }

    /**
     * Cerrar una cuenta (cambiar estado a CUENTA_CERRADA).
     */
    public function cerrar(string $id): JsonResponse
    {
        return $this->cambiarEstadoInterno($id, EstadoCuentaEnum::CUENTA_CERRADA, 'Cuenta cerrada exitosamente');
    }

    /**
     * Confirmar una cuenta (cambiar estado a CUENTA_CONFIRMADA).
     */
    public function confirmar(string $id): JsonResponse
    {
        return $this->cambiarEstadoInterno($id, EstadoCuentaEnum::CUENTA_CONFIRMADA, 'Cuenta confirmada exitosamente');
    }

    /**
     * Obtener estadísticas de las cuentas.
     */
    public function estadisticas(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $query = Cuenta::query();

            // Filtrar por agricultor si no es admin
            if (!$user->isAdmin() && $user->agricultor) {
                $query->where('agricultor_id', $user->agricultor->id);
            }

            $estadisticas = [
                'total_cuentas' => $query->count(),
                'por_estado' => [],
                'cuentas_activas' => $query->whereIn('estado', [
                    EstadoCuentaEnum::CUENTA_ABIERTA,
                    EstadoCuentaEnum::PESAJE_INICIADO,
                    EstadoCuentaEnum::PESAJE_FINALIZADO
                ])->count(),
                'cuentas_finalizadas' => $query->whereIn('estado', [
                    EstadoCuentaEnum::CUENTA_CERRADA,
                    EstadoCuentaEnum::CUENTA_CONFIRMADA
                ])->count()
            ];

            // Estadísticas por estado
            foreach (EstadoCuentaEnum::cases() as $estado) {
                $count = (clone $query)->where('estado', $estado)->count();
                $estadisticas['por_estado'][] = [
                    'estado' => $estado->value,
                    'label' => $estado->getLabel(),
                    'count' => $count
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $estadisticas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener estadísticas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener catálogos relacionados con cuentas.
     */
    public function catalogos(): JsonResponse
    {
        try {
            $catalogos = [
                'estados' => collect(EstadoCuentaEnum::cases())->map(function ($estado) {
                    return [
                        'value' => $estado->value,
                        'label' => $estado->getLabel(),
                        'color' => $estado->getColor()
                    ];
                })->toArray()
            ];

            return response()->json([
                'success' => true,
                'data' => $catalogos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener catálogos',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método privado para cambiar estado interno.
     */
    private function cambiarEstadoInterno(string $id, EstadoCuentaEnum $nuevoEstado, string $mensaje): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $cuenta = Cuenta::findOrFail($id);

            // Verificar permisos
            if (!$this->puedeActualizarEstado($user, $cuenta, $nuevoEstado)) {
                return response()->json([
                    'error' => 'No tiene permisos para realizar esta acción'
                ], 403);
            }

            $cuenta->update(['estado' => $nuevoEstado]);
            $cuenta->load(['agricultor', 'pesajes']);

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'data' => $cuenta
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Cuenta no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al procesar la solicitud',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si el usuario puede actualizar el estado de la cuenta.
     */
    private function puedeActualizarEstado(User $user, Cuenta $cuenta, EstadoCuentaEnum $nuevoEstado): bool
    {
        // Admin puede hacer cualquier cambio
        if ($user->isAdmin()) {
            return true;
        }

        // Agricultor solo puede crear cuentas para sí mismo
        if ($user->hasRole('AGRICULTOR') && $user->agricultor) {
            return $cuenta->agricultor_id === $user->agricultor->id &&
                   $nuevoEstado === EstadoCuentaEnum::CUENTA_CREADA;
        }

        // Usuarios del panel beneficio pueden gestionar estados de proceso
        if ($user->canAccessBeneficioPanel()) {
            return in_array($nuevoEstado, [
                EstadoCuentaEnum::CUENTA_ABIERTA,
                EstadoCuentaEnum::PESAJE_INICIADO,
                EstadoCuentaEnum::PESAJE_FINALIZADO
            ]);
        }

        // Usuarios del panel peso cabal pueden finalizar y confirmar
        if ($user->canAccessPesoCabalPanel()) {
            return in_array($nuevoEstado, [
                EstadoCuentaEnum::CUENTA_CERRADA,
                EstadoCuentaEnum::CUENTA_CONFIRMADA
            ]);
        }

        return false;
    }

    /**
     * Generar número de cuenta único.
     */
    private function generarNumeroCuenta(int $agricultorId): string
    {
        $prefijo = 'CTA';
        $anio = date('Y');
        $secuencial = Cuenta::count() + 1;
        $secuencial = str_pad($secuencial, 4, '0', STR_PAD_LEFT);

        return "{$prefijo}-{$anio}-{$agricultorId}-{$secuencial}";
    }
}
