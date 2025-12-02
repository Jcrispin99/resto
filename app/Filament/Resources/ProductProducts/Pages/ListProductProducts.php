<?php

namespace App\Filament\Resources\ProductProducts\Pages;

use App\Filament\Resources\ProductProducts\ProductProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductProducts extends ListRecords
{
    protected static string $resource = ProductProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
