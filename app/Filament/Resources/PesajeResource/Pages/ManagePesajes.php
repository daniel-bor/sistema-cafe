<?php

namespace App\Filament\Resources\PesajeResource\Pages;

use App\Filament\Resources\PesajeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePesajes extends ManageRecords
{
    protected static string $resource = PesajeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
