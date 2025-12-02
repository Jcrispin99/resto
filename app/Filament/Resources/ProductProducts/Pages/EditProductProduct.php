<?php

namespace App\Filament\Resources\ProductProducts\Pages;

use App\Filament\Resources\ProductProducts\ProductProductResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProductProduct extends EditRecord
{
    protected static string $resource = ProductProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
