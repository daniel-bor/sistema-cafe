<?php

namespace App\Filament\PesoCabal\Resources\ParcialidadResource\Pages;

use App\Filament\PesoCabal\Resources\ParcialidadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditParcialidad extends EditRecord
{
    protected static string $resource = ParcialidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
