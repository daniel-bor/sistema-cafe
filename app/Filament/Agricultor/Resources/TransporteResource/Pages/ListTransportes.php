<?php

namespace App\Filament\Agricultor\Resources\TransporteResource\Pages;

use App\Filament\Agricultor\Resources\TransporteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransportes extends ListRecords
{
    protected static string $resource = TransporteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->createAnother(false),
        ];
    }
}
