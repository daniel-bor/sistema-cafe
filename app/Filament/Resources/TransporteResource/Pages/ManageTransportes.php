<?php

namespace App\Filament\Resources\TransporteResource\Pages;

use App\Filament\Resources\TransporteResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTransportes extends ManageRecords
{
    protected static string $resource = TransporteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
