<?php

namespace App\Http\Controllers;

use App\Models\Transportista;
use App\Enums\TipoLicencia;
use App\Enums\EstadoGenericoEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TransportistaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transportista::with('agricultor');

        // Filtros opcionales
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('disponible')) {
            $query->where('disponible', $request->boolean('disponible'));
        }

        if ($request->has('tipo_licencia')) {
            $query->where('tipo_licencia', $request->tipo_licencia);
        }

        if ($request->has('agricultor_id')) {
            $query->where('agricultor_id', $request->agricultor_id);
        }

        // Búsqueda por nombre
        if ($request->has('search')) {
            $query->where('nombre_completo', 'like', '%' . $request->search . '%');
        }

        $transportistas = $query->paginate($request->get('per_page', 15));

        return response()->json($transportistas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cui' => 'required|string|max:13|unique:transportistas,cui',
            'nombre_completo' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date|before:today',
            'tipo_licencia' => ['required', Rule::enum(TipoLicencia::class)],
            'fecha_vencimiento_licencia' => 'required|date|after:today',
            'telefono' => 'nullable|string|max:20',
            'disponible' => 'boolean',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Manejar la subida de foto
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('transportistas', 'public');
        }

        $transportista = Transportista::create($data);
        $transportista->load('agricultor');

        return response()->json([
            'message' => 'Transportista creado exitosamente',
            'data' => $transportista
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $transportista = Transportista::with('agricultor')->find($id);

        if (!$transportista) {
            return response()->json([
                'message' => 'Transportista no encontrado'
            ], 404);
        }

        return response()->json([
            'data' => $transportista
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $transportista = Transportista::find($id);

        if (!$transportista) {
            return response()->json([
                'message' => 'Transportista no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'cui' => 'string|max:13|unique:transportistas,cui,' . $id,
            'nombre_completo' => 'string|max:255',
            'fecha_nacimiento' => 'date|before:today',
            'tipo_licencia' => [Rule::enum(TipoLicencia::class)],
            'fecha_vencimiento_licencia' => 'date|after:today',
            'telefono' => 'nullable|string|max:20',
            'disponible' => 'boolean',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Manejar la subida de foto
        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existe
            if ($transportista->foto) {
                Storage::disk('public')->delete($transportista->foto);
            }
            $data['foto'] = $request->file('foto')->store('transportistas', 'public');
        }

        $transportista->update($data);
        $transportista->load('agricultor');

        return response()->json([
            'message' => 'Transportista actualizado exitosamente',
            'data' => $transportista
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $transportista = Transportista::find($id);

        if (!$transportista) {
            return response()->json([
                'message' => 'Transportista no encontrado'
            ], 404);
        }

        // Eliminar foto si existe
        if ($transportista->foto) {
            Storage::disk('public')->delete($transportista->foto);
        }

        $transportista->delete();

        return response()->json([
            'message' => 'Transportista eliminado exitosamente'
        ]);
    }

    /**
     * Aprobar un transportista
     */
    public function aprobar(string $id): JsonResponse
    {
        $transportista = Transportista::find($id);

        if (!$transportista) {
            return response()->json([
                'message' => 'Transportista no encontrado'
            ], 404);
        }

        $transportista->aprobar();
        $transportista->load('agricultor');

        return response()->json([
            'message' => 'Transportista aprobado exitosamente',
            'data' => $transportista
        ]);
    }

    /**
     * Rechazar un transportista
     */
    public function rechazar(string $id): JsonResponse
    {
        $transportista = Transportista::find($id);

        if (!$transportista) {
            return response()->json([
                'message' => 'Transportista no encontrado'
            ], 404);
        }

        $transportista->rechazar();
        $transportista->load('agricultor');

        return response()->json([
            'message' => 'Transportista rechazado',
            'data' => $transportista
        ]);
    }

    /**
     * Obtener transportistas disponibles
     */
    public function disponibles(): JsonResponse
    {
        $transportistas = Transportista::with('agricultor')
            ->where('disponible', true)
            ->where('estado', EstadoGenericoEnum::APROBADO)
            ->get();

        return response()->json([
            'data' => $transportistas
        ]);
    }

    /**
     * Cambiar disponibilidad de un transportista
     */
    public function cambiarDisponibilidad(string $id): JsonResponse
    {
        $transportista = Transportista::find($id);

        if (!$transportista) {
            return response()->json([
                'message' => 'Transportista no encontrado'
            ], 404);
        }

        $transportista->disponible = !$transportista->disponible;
        $transportista->save();
        $transportista->load('agricultor');

        return response()->json([
            'message' => 'Disponibilidad cambiada exitosamente',
            'data' => $transportista
        ]);
    }

    /**
     * Obtener tipos de licencia disponibles
     */
    public function tiposLicencia(): JsonResponse
    {
        return response()->json([
            'data' => TipoLicencia::labels()
        ]);
    }

    /**
     * Obtener estados disponibles
     */
    public function estados(): JsonResponse
    {
        $estados = [];
        foreach (EstadoGenericoEnum::cases() as $estado) {
            $estados[$estado->value] = $estado->getLabel();
        }

        return response()->json([
            'data' => $estados
        ]);
    }
}
