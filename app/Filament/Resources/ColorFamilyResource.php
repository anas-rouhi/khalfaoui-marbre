<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ColorFamilyResource\Pages;
use App\Models\ColorFamily;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ColorFamilyResource extends Resource
{
    protected static ?string $model = ColorFamily::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    public static function getNavigationGroup(): ?string
    {
        return __('Réglages');
    }

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'teintes';

    public static function getModelLabel(): string
    {
        return __('teinte');
    }

    public static function getPluralModelLabel(): string
    {
        return __('teintes');
    }

    public static function getNavigationLabel(): string
    {
        return __('Teintes');
    }

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Champs du formulaire, partagés avec la création express depuis une
     * fiche marbre (ProductResource).
     *
     * @return array<int, Forms\Components\Component>
     */
    public static function formFields(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label(__('Nom affiché sur le site'))
                ->required()
                ->maxLength(190)
                ->live(onBlur: true)
                // La clé suit le nom à la création seulement — page de
                // création comme fenêtre « + » de la fiche marbre.
                ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                    if ($operation !== 'edit') {
                        $set('key', Str::slug($state));
                    }
                })
                ->placeholder(__('Rouge, Rose, Doré…')),

            Forms\Components\TextInput::make('name_ar')
                ->label(__('Nom en arabe'))
                ->maxLength(190)
                ->extraInputAttributes(['dir' => 'rtl'])
                ->helperText(__('Facultatif : le nom français s\'affiche à défaut.')),

            Forms\Components\ColorPicker::make('swatch')
                ->label(__('Pastille'))
                ->required()
                ->default('#8b8e94')
                ->helperText(__('Couleur du rond affiché sur le bouton du filtre.')),

            Forms\Components\TextInput::make('key')
                ->label(__('Identifiant'))
                ->required()
                ->maxLength(60)
                ->alphaDash()
                ->unique('color_families', 'key', ignoreRecord: true)
                // Les fiches référencent cette clé : la modifier les
                // détacherait de la teinte.
                ->disabledOn('edit')
                ->helperText(__('Généré depuis le nom, non modifiable ensuite.')),

            Forms\Components\TextInput::make('sort_order')
                ->label(__('Ordre d\'affichage'))
                ->numeric()
                // Une nouvelle teinte se range en fin de liste.
                ->default(fn () => (int) ColorFamily::max('sort_order') + 1)
                ->helperText(__('Le plus petit nombre apparaît en premier.')),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Teinte du filtre catalogue'))
                ->description(__('Une teinte apparaît comme bouton du filtre « Teinte » dès qu\'au moins une fiche marbre la porte.'))
                ->columns(2)
                ->schema(static::formFields()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('swatch')
                    ->label(''),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('Teinte'))
                    ->searchable()
                    ->weight('medium')
                    ->description(fn (ColorFamily $record) => $record->name_ar),

                Tables\Columns\TextColumn::make('products_count')
                    ->label(__('Fiches'))
                    ->counts('products')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('Ordre'))
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated(false)
            ->actions([
                Tables\Actions\EditAction::make(),
                // Une teinte encore portée par des fiches ne se supprime pas :
                // elles disparaîtraient de tous les boutons de teinte.
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (ColorFamily $record) => $record->products()->exists()),
            ])
            ->emptyStateHeading(__('Aucune teinte'))
            ->emptyStateDescription(__('Sans teinte, le filtre « Teinte » du catalogue n\'est pas affiché.'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListColorFamilies::route('/'),
            'create' => Pages\CreateColorFamily::route('/create'),
            'edit' => Pages\EditColorFamily::route('/{record}/edit'),
        ];
    }
}
