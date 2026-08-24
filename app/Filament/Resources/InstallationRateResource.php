<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstallationRateResource\Pages;
use App\Models\InstallationRate;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InstallationRateResource extends Resource
{
    protected static ?string $model = InstallationRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Réglages';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'tarifs-pose';

    protected static ?string $modelLabel = 'tarif de pose';

    protected static ?string $pluralModelLabel = 'tarifs de pose';

    protected static ?string $navigationLabel = 'Tarifs de pose';

    protected static ?string $recordTitleAttribute = 'label';

    public static function getBreadcrumb(): string
    {
        return 'Tarifs de pose';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Tarif de main d\'œuvre')
                ->description("Montant facturé au m² pour la pose, hors fourniture de la pierre. C'est la valeur utilisée par l'estimateur de devis du site.")
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('application')
                        ->label('Type de pose')
                        ->options(Product::APPLICATIONS)
                        ->required()
                        ->native(false)
                        ->unique(ignoreRecord: true)
                        ->live()
                        // Le libellé suit le type choisi tant qu'il n'a pas été
                        // personnalisé à la main.
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            if (blank($get('label')) && filled($state)) {
                                $set('label', Product::APPLICATIONS[$state] ?? null);
                            }
                        })
                        ->helperText('Doit correspondre à un usage du catalogue.'),

                    Forms\Components\TextInput::make('label')
                        ->label('Nom affiché sur le site')
                        ->required()
                        ->maxLength(190)
                        ->helperText('Texte du bouton dans l\'estimateur de devis.'),

                    Forms\Components\TextInput::make('rate_per_m2')
                        ->label('Tarif de pose')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->maxValue(100000)
                        ->step(10)
                        ->suffix('DH / m²')
                        ->helperText('Exemple : 220 DH/m² pour un plan de travail de cuisine.'),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Ordre d\'affichage')
                        ->numeric()
                        ->default(0)
                        ->helperText('Le plus petit nombre apparaît en premier.'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Proposé aux visiteurs')
                        ->default(true)
                        ->columnSpanFull()
                        ->helperText('Désactivé, ce type de pose disparaît de l\'estimateur du site.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Type de pose')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (InstallationRate $record) => $record->application),

                // Modifiable directement dans le tableau : c'est le geste le
                // plus fréquent, il ne doit pas demander d'ouvrir une fiche.
                Tables\Columns\TextInputColumn::make('rate_per_m2')
                    ->label('Tarif (DH / m²)')
                    ->type('number')
                    ->rules(['required', 'numeric', 'min:0', 'max:100000'])
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Actif'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifié')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated(false)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('Aucun tarif de pose')
            ->emptyStateDescription("Sans tarif, l'estimateur applique une valeur de repli de "
                .number_format(InstallationRate::FALLBACK_RATE, 0, ',', ' ').' DH/m².');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstallationRates::route('/'),
            'create' => Pages\CreateInstallationRate::route('/create'),
            'edit' => Pages\EditInstallationRate::route('/{record}/edit'),
        ];
    }
}
