<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la Empresa')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('business_name')
                                    ->label('Razón Social')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                TextInput::make('trade_name')
                                    ->label('Nombre Comercial')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                TextInput::make('tax_id')
                                    ->label('RUC')
                                    ->required()
                                    ->maxLength(11)
                                    ->columnSpan(1),

                                TextInput::make('logo')
                                    ->label('Logo URL')
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true)
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Sucursales')
                    ->schema([
                        Repeater::make('branches')
                            ->relationship('branches')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        // Branch Basic Information
                                        TextInput::make('code')
                                            ->label('Código')
                                            ->required()
                                            ->maxLength(10)
                                            ->placeholder('M001')
                                            ->columnSpan(1),

                                        TextInput::make('name')
                                            ->label('Nombre de Sucursal')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(2),

                                        // Branch Legal Information
                                        TextInput::make('business_name')
                                            ->label('Razón Social de Sucursal')
                                            ->maxLength(255)
                                            ->columnSpan(2),

                                        TextInput::make('tax_id')
                                            ->label('RUC de Sucursal')
                                            ->maxLength(11)
                                            ->columnSpan(1),

                                        // Address Information
                                        Textarea::make('address')
                                            ->label('Dirección')
                                            ->required()
                                            ->maxLength(500)
                                            ->rows(2)
                                            ->columnSpan(3),

                                        TextInput::make('ubigeo_code')
                                            ->label('Código Ubigeo')
                                            ->maxLength(6)
                                            ->placeholder('150122')
                                            ->helperText('Código de 6 dígitos (Departamento + Provincia + Distrito)')
                                            ->columnSpan(1),

                                        Select::make('country')
                                            ->label('País')
                                            ->options([
                                                'PE' => 'Perú',
                                                'CL' => 'Chile',
                                                'CO' => 'Colombia',
                                                'EC' => 'Ecuador',
                                                'BO' => 'Bolivia',
                                            ])
                                            ->default('PE')
                                            ->required()
                                            ->columnSpan(1),

                                        // Contact Information
                                        TextInput::make('phone')
                                            ->label('Teléfono')
                                            ->tel()
                                            ->maxLength(20)
                                            ->columnSpan(1),

                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->maxLength(255)
                                            ->columnSpan(1),

                                        TextInput::make('website')
                                            ->label('Sitio Web')
                                            ->url()
                                            ->maxLength(255)
                                            ->columnSpan(1),

                                        // Geographic Coordinates
                                        TextInput::make('latitude')
                                            ->label('Latitud')
                                            ->numeric()
                                            ->step(0.00000001)
                                            ->placeholder('-12.119820')
                                            ->columnSpan(1),

                                        TextInput::make('longitude')
                                            ->label('Longitud')
                                            ->numeric()
                                            ->step(0.00000001)
                                            ->placeholder('-77.031440')
                                            ->columnSpan(1),

                                        // Operational Information
                                        TimePicker::make('opening_time')
                                            ->label('Hora de Apertura')
                                            ->seconds(false)
                                            ->columnSpan(1),

                                        TimePicker::make('closing_time')
                                            ->label('Hora de Cierre')
                                            ->seconds(false)
                                            ->columnSpan(1),

                                        TextInput::make('max_tables')
                                            ->label('Máximo de Mesas')
                                            ->numeric()
                                            ->minValue(0)
                                            ->columnSpan(1),

                                        TextInput::make('max_capacity')
                                            ->label('Capacidad Máxima')
                                            ->numeric()
                                            ->minValue(0)
                                            ->columnSpan(1),

                                        Toggle::make('is_active')
                                            ->label('Sucursal Activa')
                                            ->default(true)
                                            ->columnSpan(3),
                                    ]),

                                Section::make('Configuración de Sucursal')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Select::make('currency')
                                                    ->label('Moneda')
                                                    ->options([
                                                        'PEN' => 'Soles (PEN)',
                                                        'USD' => 'Dólares (USD)',
                                                        'EUR' => 'Euros (EUR)',
                                                    ])
                                                    ->default('PEN')
                                                    ->required()
                                                    ->columnSpan(1),

                                                Select::make('timezone')
                                                    ->label('Zona Horaria')
                                                    ->options([
                                                        'America/Lima' => 'Lima (UTC-5)',
                                                        'America/Bogota' => 'Bogotá (UTC-5)',
                                                        'America/Santiago' => 'Santiago (UTC-3)',
                                                    ])
                                                    ->default('America/Lima')
                                                    ->required()
                                                    ->columnSpan(1),

                                                TextInput::make('tax_percentage')
                                                    ->label('% IGV/IVA')
                                                    ->numeric()
                                                    ->suffix('%')
                                                    ->minValue(0)
                                                    ->maxValue(100)
                                                    ->step(0.01)
                                                    ->default(18.00)
                                                    ->required()
                                                    ->columnSpan(1),

                                                Toggle::make('print_kitchen_ticket')
                                                    ->label('Imprimir Ticket de Cocina')
                                                    ->default(true)
                                                    ->columnSpan(1),

                                                Toggle::make('print_customer_receipt')
                                                    ->label('Imprimir Recibo de Cliente')
                                                    ->default(true)
                                                    ->columnSpan(1),

                                                Toggle::make('accept_reservations')
                                                    ->label('Aceptar Reservas')
                                                    ->default(true)
                                                    ->columnSpan(1),

                                                Toggle::make('accept_delivery')
                                                    ->label('Aceptar Delivery')
                                                    ->default(true)
                                                    ->columnSpan(1),

                                                Toggle::make('accept_takeout')
                                                    ->label('Aceptar Para Llevar')
                                                    ->default(true)
                                                    ->columnSpan(1),
                                            ]),
                                    ])
                                    ->collapsed(),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? $state['code'] ?? null)
                            ->collapsible()
                            ->collapsed()
                            ->addActionLabel('Agregar Sucursal')
                            ->reorderable(false)
                            ->cloneable()
                            ->deleteAction(
                                fn ($action) => $action->requiresConfirmation()
                            )
                            ->defaultItems(0),
                    ])
                    ->collapsible(),
            ]);
    }
}
