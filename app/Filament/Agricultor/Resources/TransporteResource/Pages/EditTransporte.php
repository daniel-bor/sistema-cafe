<?php

namespace App\Filament\Agricultor\Resources\TransporteResource\Pages;

use App\Filament\Agricultor\Resources\TransporteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransporte extends EditRecord
{
    protected static string $resource = TransporteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
