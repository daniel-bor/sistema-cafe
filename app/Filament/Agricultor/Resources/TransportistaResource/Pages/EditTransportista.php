<?php

namespace App\Filament\Agricultor\Resources\TransportistaResource\Pages;

use App\Filament\Agricultor\Resources\TransportistaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransportista extends EditRecord
{
    protected static string $resource = TransportistaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
