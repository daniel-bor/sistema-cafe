<?php

namespace App\Filament\PesoCabal\Resources\ParcialidadResource\Pages;

use App\Filament\PesoCabal\Resources\ParcialidadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParcialidads extends ListRecords
{
    protected static string $resource = ParcialidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
