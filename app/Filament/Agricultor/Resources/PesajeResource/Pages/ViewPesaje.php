<?php

namespace App\Filament\Agricultor\Resources\PesajeResource\Pages;

use App\Filament\Agricultor\Resources\PesajeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPesaje extends ViewRecord
{
    protected static string $resource = PesajeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
