<?php

namespace App\Filament\Agricultor\Resources\TransportistaResource\Pages;

use App\Filament\Agricultor\Resources\TransportistaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransportistas extends ListRecords
{
    protected static string $resource = TransportistaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->createAnother(false),
        ];
    }
}
