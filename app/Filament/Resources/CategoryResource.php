<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return __('Catalogue');
    }

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'familles';

    public static function getModelLabel(): string
    {
        return __('famille');
    }

    public static function getPluralModelLabel(): string
    {
        return __('familles de matière');
    }

    public static function getNavigationLabel(): string
    {
        return __('Familles de matière');
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
                // Le slug suit le nom à la création seulement — page de
                // création comme fenêtre « + » de la fiche marbre.
                ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                    if ($operation !== 'edit') {
                        $set('slug', Str::slug($state));
                    }
                })
                ->placeholder(__('Marbre, Granit, Quartz…')),

            Forms\Components\TextInput::make('name_ar')
                ->label(__('Nom en arabe'))
                ->maxLength(190)
                ->extraInputAttributes(['dir' => 'rtl'])
                ->helperText(__('Facultatif : le nom français s\'affiche à défaut.')),

            Forms\Components\TextInput::make('slug')
                ->label(__('Identifiant'))
                ->required()
                ->maxLength(190)
                ->alphaDash()
                ->unique('categories', 'slug', ignoreRecord: true)
                // Le filtre du site identifie la famille par cette clé.
                ->disabledOn('edit')
                ->helperText(__('Généré depuis le nom, non modifiable ensuite.')),

            Forms\Components\TextInput::make('sort_order')
                ->label(__('Ordre d\'affichage'))
                ->numeric()
                // Une nouvelle famille se range en fin de liste.
                ->default(fn () => (int) Category::max('sort_order') + 1)
                ->helperText(__('Le plus petit nombre apparaît en premier.')),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Famille de matière'))
                ->description(__('Une famille apparaît comme bouton du filtre « Famille » du catalogue dès qu\'au moins une fiche marbre lui est rattachée.'))
                ->columns(2)
                ->schema(static::formFields()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Famille'))
                    ->searchable()
                    ->weight('medium')
                    ->description(fn (Category $record) => $record->name_ar),

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
                // Une famille encore portée par des fiches ne se supprime pas :
                // elles perdraient leur filtre.
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (Category $record) => $record->products()->exists()),
            ])
            ->emptyStateHeading(__('Aucune famille'))
            ->emptyStateDescription(__('Sans famille, le filtre « Famille » du catalogue n\'est pas affiché.'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
