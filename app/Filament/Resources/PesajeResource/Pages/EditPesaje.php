<?php

namespace App\Filament\Resources\PesajeResource\Pages;

use App\Filament\Resources\PesajeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPesaje extends EditRecord
{
    protected static string $resource = PesajeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
