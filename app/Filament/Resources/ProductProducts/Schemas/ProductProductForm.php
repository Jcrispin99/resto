<?php

namespace App\Filament\Resources\ProductProducts\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_template_id')
                    ->required()
                    ->numeric(),
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                TextInput::make('barcode'),
                TextInput::make('variant_name'),
                TextInput::make('cost_price')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('sale_price')
                    ->numeric()
                    ->prefix('$'),
                FileUpload::make('image')
                    ->image(),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_default_variant')
                    ->required(),
            ]);
    }
}
