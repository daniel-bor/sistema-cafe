<?php

namespace App\Filament\Resources\AgricultorResource\Pages;

use App\Filament\Resources\AgricultorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAgricultor extends EditRecord
{
    protected static string $resource = AgricultorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
