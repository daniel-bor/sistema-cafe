<?php

namespace App\Filament\Agricultor\Resources\PesajeResource\Pages;

use App\Filament\Agricultor\Resources\PesajeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPesaje extends EditRecord
{
    protected static string $resource = PesajeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            // Actions\ForceDeleteAction::make(),
            // Actions\RestoreAction::make(),
        ];
    }
}
