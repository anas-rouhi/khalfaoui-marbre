<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectTypeResource\Pages;
use App\Models\ProjectType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProjectTypeResource extends Resource
{
    protected static ?string $model = ProjectType::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    public static function getNavigationGroup(): ?string
    {
        return __('Catalogue');
    }

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'types-projet';

    public static function getModelLabel(): string
    {
        return __('type de projet');
    }

    public static function getPluralModelLabel(): string
    {
        return __('types de projet');
    }

    public static function getNavigationLabel(): string
    {
        return __('Types de projet');
    }

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Champs du formulaire, partagés avec la création express depuis une
     * réalisation (ProjectResource).
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
                // création comme fenêtre « + » de la réalisation.
                ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                    if ($operation !== 'edit') {
                        $set('key', Str::slug($state));
                    }
                })
                ->placeholder(__('Villa, Restaurant, Clinique…')),

            Forms\Components\TextInput::make('name_ar')
                ->label(__('Nom en arabe'))
                ->maxLength(190)
                ->extraInputAttributes(['dir' => 'rtl'])
                ->helperText(__('Facultatif : le nom français s\'affiche à défaut.')),

            Forms\Components\TextInput::make('key')
                ->label(__('Identifiant'))
                ->required()
                ->maxLength(60)
                ->alphaDash()
                ->unique('project_types', 'key', ignoreRecord: true)
                // Les réalisations référencent cette clé : la modifier les
                // détacherait du type.
                ->disabledOn('edit')
                ->helperText(__('Généré depuis le nom, non modifiable ensuite.')),

            Forms\Components\TextInput::make('sort_order')
                ->label(__('Ordre d\'affichage'))
                ->numeric()
                // Un nouveau type se range en fin de liste.
                ->default(fn () => (int) ProjectType::max('sort_order') + 1)
                ->helperText(__('Le plus petit nombre apparaît en premier.')),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Type de projet'))
                ->description(__('Un type apparaît comme bouton de filtre de la section Réalisations dès qu\'au moins une réalisation publiée le porte.'))
                ->columns(2)
                ->schema(static::formFields()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Type'))
                    ->searchable()
                    ->weight('medium')
                    ->description(fn (ProjectType $record) => $record->name_ar),

                Tables\Columns\TextColumn::make('projects_count')
                    ->label(__('Réalisations'))
                    ->counts('projects')
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
                // Un type encore porté par des réalisations ne se supprime pas :
                // elles perdraient leur filtre.
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (ProjectType $record) => $record->projects()->exists()),
            ])
            ->emptyStateHeading(__('Aucun type de projet'))
            ->emptyStateDescription(__('Sans type, la section Réalisations du site s\'affiche sans barre de filtres.'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjectTypes::route('/'),
            'create' => Pages\CreateProjectType::route('/create'),
            'edit' => Pages\EditProjectType::route('/{record}/edit'),
        ];
    }
}
