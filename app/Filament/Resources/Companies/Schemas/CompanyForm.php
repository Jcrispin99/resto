<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('business_name')
                    ->required(),
                TextInput::make('trade_name')
                    ->required(),
                TextInput::make('tax_id')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('website')
                    ->url(),
                TextInput::make('logo'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
