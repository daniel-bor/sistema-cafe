<?php

namespace App\Filament\Agricultor\Resources\PesajeResource\Pages;

use App\Filament\Agricultor\Resources\PesajeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPesajes extends ListRecords
{
    protected static string $resource = PesajeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
