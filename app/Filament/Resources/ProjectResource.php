<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use App\Models\ProjectType;
use App\Support\MediaPath;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    public static function getNavigationGroup(): ?string
    {
        return __('Catalogue');
    }

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('réalisation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('réalisations');
    }

    public static function getNavigationLabel(): string
    {
        return __('Réalisations');
    }

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Le chantier'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label(__('Titre'))
                        ->required()
                        ->maxLength(190)
                        ->columnSpanFull()
                        ->placeholder(__('Villa contemporaine — plan de travail Emerald Pearl')),

                    Forms\Components\TextInput::make('title_ar')
                        ->label(__('Titre en arabe'))
                        ->maxLength(190)
                        ->columnSpanFull()
                        ->extraInputAttributes(['dir' => 'rtl'])
                        ->helperText(__('Facultatif : le titre français s\'affiche à défaut.')),

                    // Liste administrée (Catalogue › Types de projet) plutôt que
                    // saisie libre : une faute de frappe créerait un filtre
                    // parasite sur le site.
                    Forms\Components\Select::make('category')
                        ->label(__('Type de projet'))
                        ->options(fn () => ProjectType::options())
                        ->native(false)
                        ->searchable()
                        // Nouveau type créé sans quitter la réalisation : il
                        // devient aussitôt un bouton de filtre du site.
                        ->createOptionForm(ProjectTypeResource::formFields())
                        ->createOptionUsing(fn (array $data) => ProjectType::create($data)->key)
                        ->helperText(__('Sert de bouton de filtre sur le site. Bouton « + » pour ajouter un type (Restaurant, Clinique…).')),

                    Forms\Components\TextInput::make('location')
                        ->label(__('Localisation'))
                        ->maxLength(190)
                        ->placeholder(__('Bouskoura, Casablanca')),

                    Forms\Components\TextInput::make('year')
                        ->label(__('Année'))
                        ->numeric()
                        ->minValue(1990)
                        ->maxValue((int) date('Y') + 1)
                        ->placeholder(date('Y')),

                    Forms\Components\TextInput::make('sort_order')
                        ->label(__('Ordre d\'affichage'))
                        ->numeric()
                        ->default(0)
                        ->helperText(__('Le plus petit nombre apparaît en premier.')),

                    Forms\Components\Toggle::make('is_published')
                        ->label(__('Publiée sur le site'))
                        ->default(true)
                        ->helperText(__('Décochée, la réalisation est conservée ici mais masquée du site et de ses filtres.')),

                    Forms\Components\Textarea::make('description')
                        ->label(__('Description'))
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull()
                        ->helperText(__('Affichée quand le visiteur agrandit la photo.')),

                    Forms\Components\Textarea::make('description_ar')
                        ->label(__('Description en arabe'))
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull()
                        ->extraInputAttributes(['dir' => 'rtl'])
                        ->helperText(__('Facultatif : la description française s\'affiche à défaut.')),
                ]),

            Forms\Components\Section::make(__('Photo de couverture'))
                ->description(__('Vignette affichée dans le portfolio du site.'))
                ->schema([
                    Forms\Components\FileUpload::make('cover_image')
                        ->label('')
                        ->image()
                        ->acceptedFileTypes(MediaPath::UPLOAD_MIME_TYPES)
                        ->imageResizeMode('contain')
                        ->imageResizeTargetWidth('1920')
                        ->imageResizeTargetHeight('1920')
                        ->imageResizeUpscale(false)
                        ->disk('public')
                        ->directory('projects')
                        ->visibility('public')
                        ->imageEditor()
                        ->maxSize(5120)
                        ->helperText(__('JPG, PNG ou WebP, format paysage (1600 × 1200 px conseillé), 5 Mo maximum. Réduite automatiquement à 1920 px.')),
                ]),

            Forms\Components\Section::make(__('Photo « avant travaux »'))
                ->description(__('Facultative. Dès qu\'elle est renseignée, la réalisation apparaît dans le comparateur avant/après du site.'))
                ->collapsed(fn (?Project $record) => blank($record?->before_image))
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('before_image')
                        ->label(__('Le chantier avant notre intervention'))
                        ->image()
                        ->acceptedFileTypes(MediaPath::UPLOAD_MIME_TYPES)
                        ->imageResizeMode('contain')
                        ->imageResizeTargetWidth('1920')
                        ->imageResizeTargetHeight('1920')
                        ->imageResizeUpscale(false)
                        ->disk('public')
                        ->directory('projects')
                        ->visibility('public')
                        ->imageEditor()
                        ->maxSize(5120)
                        ->helperText(__('Cadrez au même endroit que la photo de couverture : la comparaison n\'en sera que plus parlante.')),

                    Forms\Components\TextInput::make('before_caption')
                        ->label(__('Légende'))
                        ->maxLength(190)
                        ->placeholder(__('Cloisons déposées, réseaux apparents.')),
                ]),

            Forms\Components\Section::make(__('Galerie du chantier'))
                ->description(__('Vues supplémentaires, feuilletées par le visiteur quand il agrandit la réalisation.'))
                ->collapsed(fn (?Project $record) => $record?->images()->exists() === false)
                ->schema([
                    Forms\Components\Repeater::make('images')
                        ->label('')
                        ->relationship()
                        ->addActionLabel(__('Ajouter une vue'))
                        ->orderColumn('sort_order')
                        ->reorderable()
                        ->collapsible()
                        ->defaultItems(0)
                        ->itemLabel(fn (array $state): ?string => $state['caption'] ?? __('Vue du chantier'))
                        ->schema([
                            Forms\Components\FileUpload::make('image_path')
                                ->label(__('Fichier'))
                                ->image()
                                ->acceptedFileTypes(MediaPath::UPLOAD_MIME_TYPES)
                                ->imageResizeMode('contain')
                                ->imageResizeTargetWidth('1920')
                                ->imageResizeTargetHeight('1920')
                                ->imageResizeUpscale(false)
                                ->disk('public')
                                ->directory('projects')
                                ->visibility('public')
                                ->imageEditor()
                                ->maxSize(5120)
                                ->required(),

                            Forms\Components\TextInput::make('caption')
                                ->label(__('Légende'))
                                ->maxLength(190)
                                ->placeholder(__("Détail du raccord de veinage à l'angle")),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label(__('Photo'))
                    // Gère à la fois les chemins /images/… livrés avec le site
                    // et les fichiers téléversés sur le disque « public ».
                    ->getStateUsing(fn (Project $record) => MediaPath::thumbnail($record->cover_image))
                    ->height(56)
                    ->width(76)
                    ->extraImgAttributes(['class' => 'rounded object-cover'])
                    ->defaultImageUrl(asset(ltrim(config('company.logo_mark'), '/'))),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('Titre'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap()
                    ->description(fn (Project $record) => $record->location),

                Tables\Columns\TextColumn::make('category')
                    ->label(__('Type'))
                    ->formatStateUsing(fn (Project $record) => $record->projectType?->name ?? $record->category)
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_published')
                    ->label(__('Publiée')),

                Tables\Columns\TextColumn::make('year')
                    ->label(__('Année'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('Ordre'))
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label(__('Type de projet'))
                    ->options(fn () => ProjectType::options()),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label(__('Publication'))
                    ->trueLabel(__('Publiées'))
                    ->falseLabel(__('Masquées')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('Aucune réalisation'))
            ->emptyStateDescription(__('Ajoutez vos plus beaux chantiers pour alimenter le portfolio du site.'));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('projectType');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
