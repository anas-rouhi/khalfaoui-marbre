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
use Illuminate\Support\Str;

class InstallationRateResource extends Resource
{
    protected static ?string $model = InstallationRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function getNavigationGroup(): ?string
    {
        return __('Réglages');
    }

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'tarifs-pose';

    public static function getModelLabel(): string
    {
        return __('usage');
    }

    public static function getPluralModelLabel(): string
    {
        return __('usages & tarifs de pose');
    }

    public static function getNavigationLabel(): string
    {
        return __('Usages & tarifs');
    }

    protected static ?string $recordTitleAttribute = 'label';

    public static function getBreadcrumb(): string
    {
        return __('Usages & tarifs');
    }

    /** Nombre de fiches produit cochées pour cet usage. */
    public static function productCount(InstallationRate $record): int
    {
        return Product::whereJsonContains('applications', $record->application)->count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Usage'))
                ->description(__('Un usage apparaît à la fois dans les cases « Usages » des fiches marbre, dans le filtre du catalogue (dès qu\'une fiche le porte) et dans l\'estimateur de devis.'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('label')
                        ->label(__('Nom affiché sur le site'))
                        ->required()
                        ->maxLength(190)
                        ->live(onBlur: true)
                        // La clé technique suit le nom à la création seulement.
                        ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                            if ($operation === 'create') {
                                $set('application', Str::slug($state));
                            }
                        })
                        ->placeholder(__('Terrasse, Piscine, Plan vasque…')),

                    Forms\Components\TextInput::make('label_ar')
                        ->label(__('Nom en arabe'))
                        ->maxLength(190)
                        ->extraInputAttributes(['dir' => 'rtl'])
                        ->helperText(__('Facultatif : le nom français s\'affiche à défaut.')),

                    Forms\Components\TextInput::make('application')
                        ->label(__('Identifiant'))
                        ->required()
                        ->maxLength(60)
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        // Les fiches et les demandes de devis référencent cette
                        // clé : la modifier les détacherait de l'usage.
                        ->disabledOn('edit')
                        ->helperText(__('Généré depuis le nom, non modifiable ensuite.')),
                ]),

            Forms\Components\Section::make(__('Tarif de main d\'œuvre'))
                ->description(__("Montant facturé au m² pour la pose, hors fourniture de la pierre. C'est la valeur utilisée par l'estimateur de devis du site."))
                ->columns(2)
                ->schema([

                    Forms\Components\TextInput::make('rate_per_m2')
                        ->label(__('Tarif de pose'))
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->maxValue(100000)
                        ->step(10)
                        ->suffix(__('DH / m²'))
                        ->helperText(__('Exemple : 220 DH/m² pour un plan de travail de cuisine.')),

                    Forms\Components\TextInput::make('sort_order')
                        ->label(__('Ordre d\'affichage'))
                        ->numeric()
                        ->default(0)
                        ->helperText(__('Le plus petit nombre apparaît en premier.')),

                    Forms\Components\Toggle::make('is_active')
                        ->label(__('Proposé dans l\'estimateur de devis'))
                        ->default(true)
                        ->columnSpanFull()
                        ->helperText(__('Désactivé, l\'usage disparaît de l\'estimateur mais reste un filtre du catalogue.')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label(__('Usage'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (InstallationRate $record) => $record->application),

                // Modifiable directement dans le tableau : c'est le geste le
                // plus fréquent, il ne doit pas demander d'ouvrir une fiche.
                Tables\Columns\TextInputColumn::make('rate_per_m2')
                    ->label(__('Tarif (DH / m²)'))
                    ->type('number')
                    ->rules(['required', 'numeric', 'min:0', 'max:100000'])
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label(__('Estimateur')),

                Tables\Columns\TextColumn::make('products')
                    ->label(__('Fiches'))
                    ->state(fn (InstallationRate $record) => static::productCount($record))
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('Ordre'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Modifié'))
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated(false)
            ->actions([
                Tables\Actions\EditAction::make(),
                // Un usage encore coché sur des fiches ne se supprime pas : ces
                // fiches perdraient leur filtre. Le désactiver suffit à le
                // retirer de l'estimateur.
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (InstallationRate $record) => static::productCount($record) > 0),
            ])
            ->emptyStateHeading(__('Aucun usage'))
            ->emptyStateDescription(__("Sans tarif, l'estimateur applique une valeur de repli de :rate DH/m².", [
                'rate' => number_format(InstallationRate::FALLBACK_RATE, 0, ',', ' '),
            ]));
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
