<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\ColorFamily;
use App\Models\InstallationRate;
use App\Models\Product;
use App\Support\MediaPath;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function getNavigationGroup(): ?string
    {
        return __('Catalogue');
    }

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('référence');
    }

    public static function getPluralModelLabel(): string
    {
        return __('références');
    }

    public static function getNavigationLabel(): string
    {
        return __('Marbres & granits');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Identité de la pierre'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('Nom commercial'))
                        ->required()
                        ->maxLength(190)
                        ->live(onBlur: true)
                        // Le slug ne se régénère que tant qu'il n'a pas été
                        // publié : modifier le nom d'une fiche en ligne ne doit
                        // pas casser une URL déjà partagée.
                        ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                            if ($operation === 'create') {
                                $set('slug', Str::slug($state));
                            }
                        }),

                    Forms\Components\TextInput::make('slug')
                        ->label(__('Identifiant URL'))
                        ->required()
                        ->maxLength(190)
                        ->unique(ignoreRecord: true)
                        ->helperText(__('Généré automatiquement depuis le nom.')),

                    Forms\Components\Select::make('category_id')
                        ->label(__('Famille de matière'))
                        ->relationship('category', 'name', fn ($query) => $query->ordered())
                        ->searchable()
                        ->preload()
                        // Nouvelle famille créée sans quitter la fiche : elle
                        // devient aussitôt un bouton du filtre catalogue.
                        ->createOptionForm(CategoryResource::formFields())
                        ->helperText(__('Marbre, Granit, Travertin, Quartz… Détermine sous quel bouton « Famille » la pierre apparaît. Liste gérée dans Catalogue › Familles de matière.')),

                    Forms\Components\TextInput::make('name_ar')
                        ->label(__('Nom en arabe'))
                        ->maxLength(190)
                        ->extraInputAttributes(['dir' => 'rtl'])
                        ->helperText(__('Facultatif : le nom français s\'affiche à défaut.')),

                    Forms\Components\TextInput::make('origin')
                        ->label(__('Origine'))
                        ->maxLength(190)
                        ->placeholder(__('Maroc, Italie, Espagne…')),
                ]),

            Forms\Components\Section::make(__('Aspect'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('color')
                        ->label(__('Couleur affichée'))
                        ->maxLength(190)
                        ->placeholder(__('Noir veiné blanc'))
                        ->helperText(__('Texte libre montré sur la fiche.')),

                    Forms\Components\Select::make('color_family')
                        ->label(__('Teinte (filtre du site)'))
                        ->options(fn () => ColorFamily::options())
                        ->native(false)
                        ->searchable()
                        // Nouvelle teinte créée sans quitter la fiche : elle
                        // devient aussitôt un bouton du filtre catalogue.
                        ->createOptionForm(ColorFamilyResource::formFields())
                        ->createOptionUsing(fn (array $data) => ColorFamily::create($data)->key)
                        ->helperText(__('Détermine sous quel bouton de teinte la pierre apparaît. Liste gérée dans Réglages › Teintes.')),

                    Forms\Components\TextInput::make('finish')
                        ->label(__('Finition'))
                        ->maxLength(190)
                        ->placeholder(__('Poli miroir, Adouci, Brossé…')),

                    Forms\Components\TextInput::make('price_per_m2')
                        ->label(__('Prix indicatif'))
                        ->numeric()
                        ->minValue(0)
                        ->step(10)
                        ->suffix(__('DH / m²'))
                        ->helperText(__('Laisser vide pour afficher « Prix sur demande ». Sert au calcul du devis en ligne.')),

                    Forms\Components\CheckboxList::make('applications')
                        ->label(__('Usages'))
                        ->options(fn () => InstallationRate::options())
                        ->columns(2)
                        ->columnSpanFull()
                        ->helperText(__('Détermine sous quels filtres d\'usage la pierre apparaît. Pour ajouter un usage (Terrasse, Piscine…) : Réglages › Usages & tarifs.')),

                    Forms\Components\Textarea::make('description')
                        ->label(__('Description'))
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('color_ar')
                        ->label(__('Couleur en arabe'))
                        ->maxLength(190)
                        ->extraInputAttributes(['dir' => 'rtl']),

                    Forms\Components\Textarea::make('description_ar')
                        ->label(__('Description en arabe'))
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull()
                        ->extraInputAttributes(['dir' => 'rtl'])
                        ->helperText(__('Facultatif : la description française s\'affiche à défaut.')),

                    Forms\Components\Toggle::make('featured')
                        ->label(__('Mettre en avant'))
                        ->helperText(__('Les références mises en avant apparaissent en tête du catalogue.'))
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make(__('Photographies'))
                ->description(__('La photo principale est celle affichée sur la carte du catalogue.'))
                ->schema([
                    Forms\Components\Repeater::make('images')
                        ->label('')
                        ->relationship()
                        ->addActionLabel(__('Ajouter une photo'))
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
                                ->directory('products')
                                ->visibility('public')
                                ->imageEditor()
                                ->maxSize(5120)
                                ->required()
                                ->helperText(__('JPG, PNG ou WebP, format paysage 4:3, 5 Mo maximum. Réduite automatiquement à 1920 px.')),

                            Forms\Components\Toggle::make('is_main')
                                ->label(__('Photo principale'))
                                ->helperText(__('Une seule par référence : cocher ici décoche les autres.')),
                        ])
                        ->itemLabel(fn (array $state): ?string => ($state['is_main'] ?? false) ? __('Photo principale') : __('Photo'))
                        ->collapsible()
                        ->defaultItems(1),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label(__('Photo'))
                    // Les fiches livrées avec le site pointent vers /images/…
                    // et celles téléversées ici vers le disque « public » :
                    // le même résolveur que le site gère les deux.
                    ->getStateUsing(fn (Product $record) => MediaPath::thumbnail(
                        $record->mainImage?->image_path ?? $record->images->first()?->image_path
                    ))
                    ->height(56)
                    ->width(76)
                    ->extraImgAttributes(['class' => 'rounded object-cover'])
                    ->defaultImageUrl(asset(ltrim(config('company.logo_mark'), '/'))),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('Nom'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Product $record) => $record->color),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('Famille'))
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('finish')
                    ->label(__('Finition'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price_per_m2')
                    ->label(__('Prix / m²'))
                    ->numeric(decimalPlaces: 0, thousandsSeparator: ' ')
                    ->suffix(__(' DH'))
                    ->placeholder(__('Sur demande'))
                    ->sortable(),

                Tables\Columns\IconColumn::make('featured')
                    ->label(__('En avant'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Modifiée'))
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('Famille'))
                    ->relationship('category', 'name')
                    ->preload(),

                Tables\Filters\SelectFilter::make('color_family')
                    ->label(__('Teinte'))
                    ->options(fn () => ColorFamily::options()),

                Tables\Filters\TernaryFilter::make('featured')
                    ->label(__('Mise en avant')),
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
            ->emptyStateHeading(__('Aucune référence'))
            ->emptyStateDescription(__('Ajoutez vos marbres et granits pour les faire apparaître sur le site.'));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['category', 'mainImage', 'images']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
