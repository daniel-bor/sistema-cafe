<?php

namespace App\Http\Controllers;

use App\Models\Agricultor;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AgricultorController extends Controller
{
    /**
     * Mostrar todos los agricultores.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $query = Agricultor::with(['user']);

            // Si no es administrador, solo puede ver su propio perfil
            if (!$user->isAdmin()) {
                $query->where('user_id', $user->id);
            }

            // Filtros opcionales
            if ($request->has('nit')) {
                $query->where('nit', 'like', '%' . $request->nit . '%');
            }

            if ($request->has('nombre')) {
                $query->where(function($q) use ($request) {
                    $q->where('nombre', 'like', '%' . $request->nombre . '%')
                      ->orWhere('apellido', 'like', '%' . $request->nombre . '%');
                });
            }

            if ($request->has('telefono')) {
                $query->where('telefono', 'like', '%' . $request->telefono . '%');
            }

            // Paginación
            $perPage = $request->get('per_page', 15);
            $agricultores = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $agricultores,
                'message' => 'Agricultores obtenidos exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener agricultores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo agricultor (solo admin).
     * Al crear un agricultor, también se crea el usuario asociado con rol AGRICULTOR.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // Solo los administradores pueden crear agricultores
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para crear agricultores'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'nit' => 'required|string|max:20|unique:agricultores,nit',
                'nombre' => 'required|string|max:100',
                'apellido' => 'required|string|max:100',
                'telefono' => 'nullable|string|max:20',
                'direccion' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Obtener el rol de AGRICULTOR
            $rolAgricultor = Rol::where('nombre', 'AGRICULTOR')->first();
            if (!$rolAgricultor) {
                throw new \Exception('Rol AGRICULTOR no encontrado');
            }

            // Crear el usuario primero
            $usuario = User::create([
                'name' => $request->nombre . ' ' . $request->apellido,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'rol_id' => $rolAgricultor->id,
                'activo' => true
            ]);

            // Crear el agricultor asociado al usuario
            $agricultor = Agricultor::create([
                'nit' => $request->nit,
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'observaciones' => $request->observaciones,
                'user_id' => $usuario->id
            ]);

            DB::commit();

            $agricultor->load('user');

            return response()->json([
                'success' => true,
                'data' => $agricultor,
                'message' => 'Agricultor creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear agricultor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un agricultor específico.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $agricultor = Agricultor::with(['user'])->findOrFail($id);

            /** @var User $user */
            $user = Auth::user();

            // Si no es administrador, solo puede ver su propio perfil
            if (!$user->isAdmin() && $agricultor->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver este agricultor'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $agricultor,
                'message' => 'Agricultor obtenido exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Agricultor no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener agricultor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un agricultor específico.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $agricultor = Agricultor::findOrFail($id);

            /** @var User $user */
            $user = Auth::user();

            // Si no es administrador, solo puede editar su propio perfil
            if (!$user->isAdmin() && $agricultor->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para editar este agricultor'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'nit' => 'string|max:20|unique:agricultores,nit,' . $id,
                'nombre' => 'string|max:100',
                'apellido' => 'string|max:100',
                'telefono' => 'nullable|string|max:20',
                'direccion' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string',
                'email' => 'email|unique:users,email,' . $agricultor->user_id,
                'password' => 'nullable|string|min:8'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Actualizar datos del agricultor
            $agricultorData = $request->only([
                'nit', 'nombre', 'apellido', 'telefono', 'direccion', 'observaciones'
            ]);

            $agricultor->update($agricultorData);

            // Actualizar datos del usuario si se proporcionan
            $userData = [];
            if ($request->has('email')) {
                $userData['email'] = $request->email;
            }
            if ($request->has('nombre') && $request->has('apellido')) {
                $userData['name'] = $request->nombre . ' ' . $request->apellido;
            }
            if ($request->has('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            if (!empty($userData)) {
                $agricultor->user->update($userData);
            }

            DB::commit();

            $agricultor->load('user');

            return response()->json([
                'success' => true,
                'data' => $agricultor,
                'message' => 'Agricultor actualizado exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Agricultor no encontrado'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar agricultor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un agricultor específico (soft delete).
     * Solo los administradores pueden eliminar agricultores.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // Solo los administradores pueden eliminar agricultores
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar agricultores'
                ], 403);
            }

            $agricultor = Agricultor::findOrFail($id);

            DB::beginTransaction();

            // Soft delete del agricultor
            $agricultor->delete();

            // Desactivar el usuario asociado
            $agricultor->user->update(['activo' => false]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Agricultor eliminado exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Agricultor no encontrado'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar agricultor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar un agricultor eliminado (solo admin).
     */
    public function restore(string $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // Solo los administradores pueden restaurar agricultores
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para restaurar agricultores'
                ], 403);
            }

            $agricultor = Agricultor::withTrashed()->findOrFail($id);

            if (!$agricultor->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El agricultor no está eliminado'
                ], 400);
            }

            DB::beginTransaction();

            // Restaurar el agricultor
            $agricultor->restore();

            // Reactivar el usuario asociado
            $agricultor->user->update(['activo' => true]);

            DB::commit();

            $agricultor->load('user');

            return response()->json([
                'success' => true,
                'data' => $agricultor,
                'message' => 'Agricultor restaurado exitosamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Agricultor no encontrado'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar agricultor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el perfil del agricultor autenticado.
     */
    public function profile(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if (!$user->agricultor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no tiene perfil de agricultor'
                ], 404);
            }

            $agricultor = $user->agricultor->load('user');

            return response()->json([
                'success' => true,
                'data' => $agricultor,
                'message' => 'Perfil de agricultor obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar el perfil del agricultor autenticado.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if (!$user->agricultor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no tiene perfil de agricultor'
                ], 404);
            }

            $agricultor = $user->agricultor;

            $validator = Validator::make($request->all(), [
                'telefono' => 'nullable|string|max:20',
                'direccion' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string',
                'email' => 'email|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:8'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Actualizar datos del agricultor (solo campos permitidos para auto-edición)
            $agricultorData = $request->only(['telefono', 'direccion', 'observaciones']);
            if (!empty($agricultorData)) {
                $agricultor->update($agricultorData);
            }

            // Actualizar datos del usuario
            $userData = [];
            if ($request->has('email')) {
                $userData['email'] = $request->email;
            }
            if ($request->has('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            if (!empty($userData)) {
                $user->update($userData);
            }

            DB::commit();

            $agricultor->load('user');

            return response()->json([
                'success' => true,
                'data' => $agricultor,
                'message' => 'Perfil actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas del agricultor.
     */
    public function estadisticas(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $agricultorId = null;

            if ($user->isAdmin()) {
                // Si es admin y se especifica un agricultor, usar ese
                if ($request->has('agricultor_id')) {
                    $agricultorId = $request->agricultor_id;
                }
            } else {
                // Si no es admin, solo puede ver sus propias estadísticas
                if (!$user->agricultor) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Usuario no tiene perfil de agricultor'
                    ], 404);
                }
                $agricultorId = $user->agricultor->id;
            }

            $estadisticas = [];

            if ($agricultorId) {
                $agricultor = Agricultor::findOrFail($agricultorId);

                // Estadísticas básicas
                $estadisticas = [
                    'agricultor' => $agricultor->only(['id', 'nit', 'nombre', 'apellido']),
                    'totales' => [
                        'transportes' => $agricultor->transportes()->count(),
                        'transportistas' => $agricultor->transportistas()->count(),
                        'pesajes' => $agricultor->pesajes()->count(),
                        'cuentas' => $agricultor->cuentas()->count(),
                    ],
                    'transportes_estado' => $agricultor->transportes()
                        ->selectRaw('estado, COUNT(*) as total')
                        ->groupBy('estado')
                        ->pluck('total', 'estado'),
                    'transportistas_estado' => $agricultor->transportistas()
                        ->selectRaw('estado, COUNT(*) as total')
                        ->groupBy('estado')
                        ->pluck('total', 'estado'),
                    'pesajes_estado' => $agricultor->pesajes()
                        ->selectRaw('estado, COUNT(*) as total')
                        ->groupBy('estado')
                        ->pluck('total', 'estado'),
                ];
            } else {
                // Estadísticas generales para admin
                $estadisticas = [
                    'resumen_general' => [
                        'total_agricultores' => Agricultor::count(),
                        'agricultores_activos' => Agricultor::whereHas('user', function($q) {
                            $q->where('activo', true);
                        })->count(),
                    ]
                ];
            }

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
}
