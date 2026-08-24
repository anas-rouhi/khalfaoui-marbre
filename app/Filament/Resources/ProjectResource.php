<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use App\Support\MediaPath;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'réalisation';

    protected static ?string $pluralModelLabel = 'réalisations';

    protected static ?string $navigationLabel = 'Réalisations';

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Le site public construit ses boutons de filtre à partir de ces valeurs :
     * une liste fermée évite qu'une faute de frappe crée un filtre parasite.
     */
    public const CATEGORIES = [
        'Villa' => 'Villa',
        'Appartement' => 'Appartement',
        'Résidence' => 'Résidence',
        'Hôtel' => 'Hôtel',
        'Bureau' => 'Bureau',
        'Showroom' => 'Showroom',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Le chantier')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Titre')
                        ->required()
                        ->maxLength(190)
                        ->columnSpanFull()
                        ->placeholder('Villa contemporaine — plan de travail Emerald Pearl'),

                    Forms\Components\TextInput::make('title_ar')
                        ->label('Titre en arabe')
                        ->maxLength(190)
                        ->columnSpanFull()
                        ->extraInputAttributes(['dir' => 'rtl'])
                        ->helperText('Facultatif : le titre français s\'affiche à défaut.'),

                    Forms\Components\Select::make('category')
                        ->label('Type de bien')
                        ->options(self::CATEGORIES)
                        ->native(false)
                        ->helperText('Sert de bouton de filtre sur le site.'),

                    Forms\Components\TextInput::make('location')
                        ->label('Localisation')
                        ->maxLength(190)
                        ->placeholder('Bouskoura, Casablanca'),

                    Forms\Components\TextInput::make('year')
                        ->label('Année')
                        ->numeric()
                        ->minValue(1990)
                        ->maxValue((int) date('Y') + 1)
                        ->placeholder(date('Y')),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Ordre d\'affichage')
                        ->numeric()
                        ->default(0)
                        ->helperText('Le plus petit nombre apparaît en premier.'),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull()
                        ->helperText('Affichée quand le visiteur agrandit la photo.'),

                    Forms\Components\Textarea::make('description_ar')
                        ->label('Description en arabe')
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull()
                        ->extraInputAttributes(['dir' => 'rtl'])
                        ->helperText('Facultatif : la description française s\'affiche à défaut.'),
                ]),

            Forms\Components\Section::make('Photo de couverture')
                ->description('Vignette affichée dans le portfolio du site.')
                ->schema([
                    Forms\Components\FileUpload::make('cover_image')
                        ->label('')
                        ->image()
                        ->disk('public')
                        ->directory('projects')
                        ->visibility('public')
                        ->imageEditor()
                        ->maxSize(5120)
                        ->helperText('JPG ou WebP, format paysage (1600 × 1200 px conseillé), 5 Mo maximum.'),
                ]),

            Forms\Components\Section::make('Photo « avant travaux »')
                ->description('Facultative. Dès qu\'elle est renseignée, la réalisation apparaît dans le comparateur avant/après du site.')
                ->collapsed(fn (?Project $record) => blank($record?->before_image))
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('before_image')
                        ->label('Le chantier avant notre intervention')
                        ->image()
                        ->disk('public')
                        ->directory('projects')
                        ->visibility('public')
                        ->imageEditor()
                        ->maxSize(5120)
                        ->helperText('Cadrez au même endroit que la photo de couverture : la comparaison n\'en sera que plus parlante.'),

                    Forms\Components\TextInput::make('before_caption')
                        ->label('Légende')
                        ->maxLength(190)
                        ->placeholder('Cloisons déposées, réseaux apparents.'),
                ]),

            Forms\Components\Section::make('Galerie du chantier')
                ->description('Vues supplémentaires, feuilletées par le visiteur quand il agrandit la réalisation.')
                ->collapsed(fn (?Project $record) => $record?->images()->exists() === false)
                ->schema([
                    Forms\Components\Repeater::make('images')
                        ->label('')
                        ->relationship()
                        ->addActionLabel('Ajouter une vue')
                        ->orderColumn('sort_order')
                        ->reorderable()
                        ->collapsible()
                        ->defaultItems(0)
                        ->itemLabel(fn (array $state): ?string => $state['caption'] ?? 'Vue du chantier')
                        ->schema([
                            Forms\Components\FileUpload::make('image_path')
                                ->label('Fichier')
                                ->image()
                                ->disk('public')
                                ->directory('projects')
                                ->visibility('public')
                                ->imageEditor()
                                ->maxSize(5120)
                                ->required(),

                            Forms\Components\TextInput::make('caption')
                                ->label('Légende')
                                ->maxLength(190)
                                ->placeholder("Détail du raccord de veinage à l'angle"),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('Photo')
                    // Gère à la fois les chemins /images/… livrés avec le site
                    // et les fichiers téléversés sur le disque « public ».
                    ->getStateUsing(fn (Project $record) => MediaPath::thumbnail($record->cover_image))
                    ->height(56)
                    ->width(76)
                    ->extraImgAttributes(['class' => 'rounded object-cover'])
                    ->defaultImageUrl(asset(ltrim(config('company.logo_mark'), '/'))),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap()
                    ->description(fn (Project $record) => $record->location),

                Tables\Columns\TextColumn::make('category')
                    ->label('Type')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('year')
                    ->label('Année')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Type de bien')
                    ->options(self::CATEGORIES),
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
            ->emptyStateHeading('Aucune réalisation')
            ->emptyStateDescription('Ajoutez vos plus beaux chantiers pour alimenter le portfolio du site.');
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
