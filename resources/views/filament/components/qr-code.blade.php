@php
    $qrValue = $value ?? 'https://example.com';
    $qrSize = $size ?? 150;
@endphp

<div class="flex flex-col items-center space-y-2">
    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <div class="qr-code flex justify-center">
            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size($qrSize)->generate($qrValue) !!}
        </div>
    </div>

    <div class="text-center">
        <a href="{{ $qrValue }}"
           target="_blank"
           class="inline-flex items-center px-3 py-1 text-xs font-medium text-white bg-primary-600 rounded-full hover:bg-primary-700 transition-colors">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
            IR
        </a>
    </div>

    <p class="text-xs text-gray-500 text-center max-w-xs break-all">{{ $qrValue }}</p>
</div>
