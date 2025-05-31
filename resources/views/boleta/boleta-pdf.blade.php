<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta de Pesaje - {{ $parcialidad->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2c5530;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #2c5530;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header h2 {
            color: #666;
            font-size: 16px;
            font-weight: normal;
        }

        .info-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section-title {
            background-color: #2c5530;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ddd;
        }

        .info-table td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .info-table td:first-child {
            background-color: #f8f9fa;
            font-weight: bold;
            width: 35%;
        }

        .badge {
            background-color: #e9ecef;
            color: #495057;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-primary {
            background-color: #cce5ff;
            color: #004085;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            text-align: center;
        }

        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 50%;
            padding: 20px 10px;
            text-align: center;
            vertical-align: bottom;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            height: 40px;
            margin-bottom: 5px;
        }

        .highlight {
            background-color: #fff3cd;
            padding: 10px;
            border-left: 4px solid #ffc107;
            margin: 15px 0;
        }

        .peso-destacado {
            background-color: #e8f5e8;
            border: 2px solid #28a745;
            padding: 10px;
            text-align: center;
            margin: 15px 0;
            border-radius: 5px;
        }

        .peso-destacado .valor {
            font-size: 18px;
            font-weight: bold;
            color: #155724;
        }

        .diferencia-positiva {
            color: #28a745;
            font-weight: bold;
        }

        .diferencia-negativa {
            color: #dc3545;
            font-weight: bold;
        }

        @media print {
            body {
                margin: 10px;
            }

            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>BOLETA DE PESAJE</h1>
        <h2>Sistema de Gestión de Café</h2>
    </div>

    <div class="info-section">
        <div class="section-title">Información General de la Boleta</div>
        <table class="info-table">
            <tr>
                <td>Fecha de Boleta:</td>
                <td><strong>{{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</strong></td>
            </tr>
            <tr>
                <td>Usuario que generó:</td>
                <td><strong>{{ auth()->user()->name }}</strong></td>
            </tr>
            <tr>
                <td>No. Cuenta:</td>
                <td><span class="badge badge-info">{{ $parcialidad->pesaje->cuenta->no_cuenta }}</span></td>
            </tr>
            <tr>
                <td>ID Pesaje:</td>
                <td><span class="badge badge-primary">{{ $parcialidad->pesaje->id }}</span></td>
            </tr>
            <tr>
                <td>ID de la Parcialidad:</td>
                <td><span class="badge badge-primary">{{ $parcialidad->id }}</span></td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <div class="section-title">Información del Transporte y Transportista</div>
        <table class="info-table">
            <tr>
                <td>Placa del Transporte:</td>
                <td><span class="badge badge-success">{{ $parcialidad->transporte->placa }}</span></td>
            </tr>
            <tr>
                <td>Marca del Vehículo:</td>
                <td>{{ $parcialidad->transporte->marca ?? 'No especificada' }}</td>
            </tr>
            <tr>
                <td>CUI del Transportista:</td>
                <td><span class="badge">{{ $parcialidad->transportista->cui }}</span></td>
            </tr>
            <tr>
                <td>Nombre del Transportista:</td>
                <td><strong>{{ $parcialidad->transportista->nombre_completo }}</strong></td>
            </tr>
            <tr>
                <td>Teléfono:</td>
                <td>{{ $parcialidad->transportista->telefono ?? 'No disponible' }}</td>
            </tr>
            <tr>
                <td>Tipo de Licencia:</td>
                <td><span class="badge badge-warning">{{ $parcialidad->transportista->tipo_licencia_label ?? 'No especificada' }}</span></td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <div class="section-title">Información del Pesaje Realizado</div>
        <table class="info-table">
            <tr>
                <td>Tipo Medida:</td>
                <td><span class="badge badge-warning">{{ $parcialidad->pesaje->medidaPeso->nombre }}</span></td>
            </tr>
            <tr>
                <td>Peso Esperado:</td>
                <td>{{ number_format($parcialidad->peso, 2) }} {{ $parcialidad->pesaje->medidaPeso->nombre }}</td>
            </tr>
            <tr>
                <td>Fecha Pesaje:</td>
                <td><strong>{{ $parcialidad->updated_at->format('d/m/Y H:i:s') }}</strong></td>
            </tr>
        </table>

        <div class="peso-destacado">
            <div>PESO OBTENIDO EN BÁSCULA</div>
            <div class="valor">{{ number_format($parcialidad->peso_bascula, 2) }} {{ $parcialidad->pesaje->medidaPeso->nombre }}</div>
        </div>

        <table class="info-table">
            <tr>
                <td>Diferencia (Obtenido - Esperado):</td>
                <td>
                    @php
                        $diferencia = $parcialidad->peso_bascula - $parcialidad->peso;
                        $claseDiferencia = $diferencia >= 0 ? 'diferencia-positiva' : 'diferencia-negativa';
                    @endphp
                    <span class="{{ $claseDiferencia }}">
                        {{ $diferencia >= 0 ? '+' : '' }}{{ number_format($diferencia, 2) }} {{ $parcialidad->pesaje->medidaPeso->nombre }}
                    </span>
                </td>
            </tr>
            <tr>
                <td>Porcentaje de Diferencia:</td>
                <td>
                    @php
                        $porcentajeDiferencia = ($diferencia / $parcialidad->peso) * 100;
                    @endphp
                    <span class="{{ $claseDiferencia }}">
                        {{ $diferencia >= 0 ? '+' : '' }}{{ number_format($porcentajeDiferencia, 2) }}%
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <div class="section-title">Fechas del Proceso</div>
        <table class="info-table">
            <tr>
                <td>Fecha de Envío:</td>
                <td>{{ $parcialidad->fecha_envio ? \Carbon\Carbon::parse($parcialidad->fecha_envio)->format('d/m/Y H:i:s') : 'No disponible' }}</td>
            </tr>
            <tr>
                <td>Fecha de Recepción:</td>
                <td>{{ $parcialidad->fecha_recepcion ? \Carbon\Carbon::parse($parcialidad->fecha_recepcion)->format('d/m/Y H:i:s') : 'No disponible' }}</td>
            </tr>
            <tr>
                <td>Fecha de Pesaje:</td>
                <td><strong>{{ $parcialidad->updated_at->format('d/m/Y H:i:s') }}</strong></td>
            </tr>
        </table>
    </div>

    @if($parcialidad->observaciones)
    <div class="highlight">
        <strong>Observaciones del Pesaje:</strong><br>
        {{ $parcialidad->observaciones }}
    </div>
    @endif

    <div class="info-section">
        <div class="section-title">Resumen del Pesaje Completo</div>
        <table class="info-table">
            <tr>
                <td>Cantidad Total del Pesaje:</td>
                <td><strong>{{ number_format($parcialidad->pesaje->cantidad_total, 2) }} {{ $parcialidad->pesaje->medidaPeso->nombre }}</strong></td>
            </tr>
            <tr>
                <td>Estado del Pesaje:</td>
                <td><span class="badge">{{ $parcialidad->pesaje->estado->value }}</span></td>
            </tr>
            <tr>
                <td>Estado de la Parcialidad:</td>
                <td><span class="badge badge-success">{{ $parcialidad->estado->value }}</span></td>
            </tr>
            @if($parcialidad->pesaje->observaciones)
            <tr>
                <td>Observaciones del Pesaje General:</td>
                <td>{{ $parcialidad->pesaje->observaciones }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-line"></div>
                    <strong>Firma del Operador</strong><br>
                    {{ auth()->user()->name }}<br>
                    <small>Responsable del Pesaje</small>
                </td>
                <td>
                    <div class="signature-line"></div>
                    <strong>Firma del Transportista</strong><br>
                    {{ $parcialidad->transportista->nombre_completo }}<br>
                    <small>CUI: {{ $parcialidad->transportista->cui }}</small>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p><strong>Sistema de Gestión de Café</strong></p>
        <p>Boleta generada automáticamente el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        <p>Este documento es válido sin firma y sello | Boleta ID: {{ $parcialidad->id }}</p>
    </div>
</body>
</html>
