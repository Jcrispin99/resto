<?php

namespace App\Filament\Resources\ProductProducts;

use App\Filament\Resources\ProductProducts\Pages\CreateProductProduct;
use App\Filament\Resources\ProductProducts\Pages\EditProductProduct;
use App\Filament\Resources\ProductProducts\Pages\ListProductProducts;
use App\Filament\Resources\ProductProducts\Schemas\ProductProductForm;
use App\Filament\Resources\ProductProducts\Tables\ProductProductsTable;
use App\Models\ProductProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductProductResource extends Resource
{
    protected static ?string $model = ProductProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'ProductProduct';

    public static function form(Schema $schema): Schema
    {
        return ProductProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductProducts::route('/'),
            'create' => CreateProductProduct::route('/create'),
            'edit' => EditProductProduct::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
