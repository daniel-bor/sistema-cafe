<?php

namespace App\Filament\PesoCabal\Resources\ParcialidadResource\Pages;

use App\Filament\PesoCabal\Resources\ParcialidadResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewParcialidad extends ViewRecord
{
    protected static string $resource = ParcialidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }
}
