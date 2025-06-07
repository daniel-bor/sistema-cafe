<?php

namespace App\Http\Controllers;

use App\Models\Transporte;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Enums\EstadoGenericoEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TransporteController extends Controller
{
    /**
     * Mostrar todos los transportes.
     */
    public function index(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // Si es administrador, puede ver todos los transportes
            if ($user->isAdmin()) {
                $transportes = Transporte::with('agricultor')->get();
            } else {
                // Si es agricultor, solo puede ver sus propios transportes
                $transportes = Transporte::with('agricultor')
                    ->where('agricultor_id', $user->agricultor->id)
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => $transportes,
                'message' => 'Transportes obtenidos exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener transportes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo transporte.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'placa' => 'required|string|max:10|unique:transportes,placa',
                'marca' => 'required|string|max:50',
                'color' => 'required|string|max:20',
                'disponible' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $transporte = new Transporte($request->only([
                'placa', 'marca', 'color', 'disponible'
            ]));

            // El agricultor_id se asigna automáticamente en el boot del modelo
            $transporte->save();

            $transporte->load('agricultor');

            return response()->json([
                'success' => true,
                'data' => $transporte,
                'message' => 'Transporte creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear transporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un transporte específico.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $transporte = Transporte::with('agricultor')->findOrFail($id);

            /** @var User $user */
            $user = Auth::user();

            // Verificar permisos: admin puede ver todos, agricultor solo los suyos
            if (!$user->isAdmin() && $transporte->agricultor_id !== $user->agricultor->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver este transporte'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $transporte,
                'message' => 'Transporte obtenido exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transporte no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener transporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un transporte.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $transporte = Transporte::findOrFail($id);

            $user = Auth::user();

            // Verificar permisos: admin puede editar todos, agricultor solo los suyos
            if (!$user->isAdmin() && $transporte->agricultor_id !== $user->agricultor->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para editar este transporte'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'placa' => 'string|max:10|unique:transportes,placa,' . $id,
                'marca' => 'string|max:50',
                'color' => 'string|max:20',
                'disponible' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $transporte->update($request->only([
                'placa', 'marca', 'color', 'disponible'
            ]));

            $transporte->load('agricultor');

            return response()->json([
                'success' => true,
                'data' => $transporte,
                'message' => 'Transporte actualizado exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transporte no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar transporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un transporte (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $transporte = Transporte::findOrFail($id);

            $user = Auth::user();

            // Verificar permisos: admin puede eliminar todos, agricultor solo los suyos
            if (!$user->isAdmin() && $transporte->agricultor_id !== $user->agricultor->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar este transporte'
                ], 403);
            }

            $transporte->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transporte eliminado exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transporte no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar transporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprobar un transporte.
     */
    public function aprobar(string $id): JsonResponse
    {
        try {
            $transporte = Transporte::findOrFail($id);
            $transporte->aprobar();

            return response()->json([
                'success' => true,
                'data' => $transporte,
                'message' => 'Transporte aprobado exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transporte no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar transporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechazar un transporte.
     */
    public function rechazar(string $id): JsonResponse
    {
        try {
            $transporte = Transporte::findOrFail($id);
            $transporte->rechazar();

            return response()->json([
                'success' => true,
                'data' => $transporte,
                'message' => 'Transporte rechazado exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transporte no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar transporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener transportes disponibles.
     */
    public function disponibles(): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = Transporte::with('agricultor')
                ->where('disponible', true)
                ->where('estado', EstadoGenericoEnum::APROBADO);

            // Si no es admin, solo mostrar sus transportes
            if (!$user->isAdmin()) {
                $query->where('agricultor_id', $user->agricultor->id);
            }

            $transportes = $query->get();

            return response()->json([
                'success' => true,
                'data' => $transportes,
                'message' => 'Transportes disponibles obtenidos exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener transportes disponibles: ' . $e->getMessage()
            ], 500);
        }
    }
}
