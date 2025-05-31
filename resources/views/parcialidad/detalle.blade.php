<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Parcialidad #{{ $parcialidad->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Detalle de Parcialidad</h1>
                    <p class="text-gray-600 mt-1">Información completa de la parcialidad #{{ $parcialidad->id }}</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Generado el</div>
                    <div class="text-lg font-semibold">{{ now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>

        <!-- Información básica de la parcialidad -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="text-xl font-semibold text-gray-900">Información de la Parcialidad</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <label class="block text-sm font-medium text-gray-500 mb-1">No. de Cuenta</label>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800">
                        {{ $parcialidad->pesaje->cuenta->no_cuenta ?? 'No asignada' }}
                    </span>
                </div>

                <div class="text-center">
                    <label class="block text-sm font-medium text-gray-500 mb-1">ID Parcialidad</label>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        {{ $parcialidad->id }}
                    </span>
                </div>

                <div class="text-center">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Estado</label>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $parcialidad->estado->getColor() == 'success' ? 'bg-green-100 text-green-800' :
                           ($parcialidad->estado->getColor() == 'warning' ? 'bg-yellow-100 text-yellow-800' :
                           ($parcialidad->estado->getColor() == 'danger' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                        {{ $parcialidad->estado->getLabel() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Información del transporte -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                <h2 class="text-xl font-semibold text-gray-900">Información de Transporte</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">No. de Parcialidad</label>
                    <div class="text-lg font-bold text-gray-900">{{ $parcialidad->id }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Placa</label>
                    @if($parcialidad->transporte)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            {{ $parcialidad->transporte->placa }}
                        </span>
                    @else
                        <span class="text-gray-400">Sin asignar</span>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Transporte</label>
                    @if($parcialidad->transporte)
                        <div class="text-gray-900">{{ $parcialidad->transporte->marca }} - {{ $parcialidad->transporte->color }}</div>
                    @else
                        <span class="text-gray-400">Sin asignar</span>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Peso</label>
                    <div class="text-lg font-bold text-green-600">
                        {{ $parcialidad->peso }} {{ $parcialidad->pesaje->medidaPeso->nombre ?? 'kg' }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Peso en Báscula</label>
                    @if($parcialidad->peso_bascula)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            {{ $parcialidad->peso_bascula }} {{ $parcialidad->pesaje->medidaPeso->nombre ?? 'kg' }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            No pesado
                        </span>
                    @endif
                </div>

                @if($parcialidad->pesaje->observaciones)
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Observaciones del Pesaje</label>
                    <div class="bg-gray-50 p-3 rounded-md text-gray-700">{{ $parcialidad->pesaje->observaciones }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Información del transportista -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-purple-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <h2 class="text-xl font-semibold text-gray-900">Información del Transportista</h2>
            </div>

            @if($parcialidad->transportista)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    @if($parcialidad->transportista->foto)
                        <img src="{{ asset('storage/' . $parcialidad->transportista->foto) }}"
                             alt="Foto del transportista"
                             class="w-24 h-24 rounded-full mx-auto object-cover border-4 border-gray-200">
                    @else
                        <div class="w-24 h-24 rounded-full mx-auto bg-gray-200 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    @endif
                </div>

                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">CUI</label>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V4a2 2 0 012-2h2a2 2 0 012 2v2m-4 0a2 2 0 012-2h2a2 2 0 012 2v2m-4 0v6m-4-6v6"></path>
                            </svg>
                            <span class="text-gray-900">{{ $parcialidad->transportista->cui }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nombre Completo</label>
                        <div class="text-lg font-bold text-gray-900">{{ $parcialidad->transportista->nombre_completo }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Estado</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $parcialidad->transportista->estado->getColor() == 'success' ? 'bg-green-100 text-green-800' :
                               ($parcialidad->transportista->estado->getColor() == 'warning' ? 'bg-yellow-100 text-yellow-800' :
                               ($parcialidad->transportista->estado->getColor() == 'danger' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                            {{ $parcialidad->transportista->estado->getLabel() }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Teléfono</label>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span class="text-gray-900">{{ $parcialidad->transportista->telefono ?? 'No disponible' }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Tipo de Licencia</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            {{ $parcialidad->transportista->tipo_licencia_label ?? 'No especificado' }}
                        </span>
                    </div>
                </div>
            </div>
            @else
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <p class="text-gray-500">No hay transportista asignado</p>
            </div>
            @endif
        </div>

        <!-- Información de fechas -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h2 class="text-xl font-semibold text-gray-900">Información de Fechas</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Fecha de Envío</label>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        <span class="text-gray-900">
                            {{ $parcialidad->fecha_envio ? $parcialidad->fecha_envio->format('d/m/Y H:i') : 'No enviado' }}
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Fecha de Recepción</label>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-gray-900">
                            {{ $parcialidad->fecha_recepcion ? $parcialidad->fecha_recepcion->format('d/m/Y H:i') : 'No recibido' }}
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Fecha de Creación</label>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-gray-900">{{ $parcialidad->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center py-6">
            <p class="text-gray-500 text-sm">
                Sistema de Gestión de Café - Generado automáticamente
            </p>
        </div>
    </div>
</body>
</html>
