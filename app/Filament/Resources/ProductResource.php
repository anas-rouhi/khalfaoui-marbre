<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Support\MediaPath;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'référence';

    protected static ?string $pluralModelLabel = 'références';

    protected static ?string $navigationLabel = 'Marbres & granits';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identité de la pierre')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nom commercial')
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
                        ->label('Identifiant URL')
                        ->required()
                        ->maxLength(190)
                        ->unique(ignoreRecord: true)
                        ->helperText('Généré automatiquement depuis le nom.'),

                    Forms\Components\Select::make('category_id')
                        ->label('Famille')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->label('Nom')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
                            Forms\Components\TextInput::make('slug')
                                ->label('Identifiant URL')
                                ->required()
                                ->unique('categories', 'slug'),
                        ])
                        ->helperText('Marbre, Granit, Travertin…'),

                    Forms\Components\TextInput::make('name_ar')
                        ->label('Nom en arabe')
                        ->maxLength(190)
                        ->extraInputAttributes(['dir' => 'rtl'])
                        ->helperText('Facultatif : le nom français s\x27affiche à défaut.'),

                    Forms\Components\TextInput::make('origin')
                        ->label('Origine')
                        ->maxLength(190)
                        ->placeholder('Maroc, Italie, Espagne…'),
                ]),

            Forms\Components\Section::make('Aspect')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('color')
                        ->label('Couleur affichée')
                        ->maxLength(190)
                        ->placeholder('Noir veiné blanc')
                        ->helperText('Texte libre montré sur la fiche.'),

                    Forms\Components\Select::make('color_family')
                        ->label('Teinte (filtre du site)')
                        ->options(Product::COLOR_FAMILIES)
                        ->native(false)
                        ->helperText('Détermine sous quel bouton de teinte la pierre apparaît.'),

                    Forms\Components\TextInput::make('finish')
                        ->label('Finition')
                        ->maxLength(190)
                        ->placeholder('Poli miroir, Adouci, Brossé…'),

                    Forms\Components\TextInput::make('price_per_m2')
                        ->label('Prix indicatif')
                        ->numeric()
                        ->minValue(0)
                        ->step(10)
                        ->suffix('DH / m²')
                        ->helperText('Laisser vide pour afficher « Prix sur demande ». Sert au calcul du devis en ligne.'),

                    Forms\Components\CheckboxList::make('applications')
                        ->label('Usages')
                        ->options(Product::APPLICATIONS)
                        ->columns(2)
                        ->columnSpanFull()
                        ->helperText('Détermine sous quels filtres d\'usage la pierre apparaît.'),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('color_ar')
                        ->label('Couleur en arabe')
                        ->maxLength(190)
                        ->extraInputAttributes(['dir' => 'rtl']),

                    Forms\Components\Textarea::make('description_ar')
                        ->label('Description en arabe')
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull()
                        ->extraInputAttributes(['dir' => 'rtl'])
                        ->helperText('Facultatif : la description française s\x27affiche à défaut.'),

                    Forms\Components\Toggle::make('featured')
                        ->label('Mettre en avant')
                        ->helperText('Les références mises en avant apparaissent en tête du catalogue.')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Photographies')
                ->description('La photo principale est celle affichée sur la carte du catalogue.')
                ->schema([
                    Forms\Components\Repeater::make('images')
                        ->label('')
                        ->relationship()
                        ->addActionLabel('Ajouter une photo')
                        ->schema([
                            Forms\Components\FileUpload::make('image_path')
                                ->label('Fichier')
                                ->image()
                                ->disk('public')
                                ->directory('products')
                                ->visibility('public')
                                ->imageEditor()
                                ->maxSize(5120)
                                ->required()
                                ->helperText('JPG ou WebP, format paysage 4:3, 5 Mo maximum.'),

                            Forms\Components\Toggle::make('is_main')
                                ->label('Photo principale')
                                ->helperText('Une seule par référence : cocher ici décoche les autres.'),
                        ])
                        ->itemLabel(fn (array $state): ?string => ($state['is_main'] ?? false) ? 'Photo principale' : 'Photo')
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
                    ->label('Photo')
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
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Product $record) => $record->color),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Famille')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('finish')
                    ->label('Finition')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price_per_m2')
                    ->label('Prix / m²')
                    ->numeric(decimalPlaces: 0, thousandsSeparator: ' ')
                    ->suffix(' DH')
                    ->placeholder('Sur demande')
                    ->sortable(),

                Tables\Columns\IconColumn::make('featured')
                    ->label('En avant')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifiée')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Famille')
                    ->relationship('category', 'name')
                    ->preload(),

                Tables\Filters\SelectFilter::make('color_family')
                    ->label('Teinte')
                    ->options(Product::COLOR_FAMILIES),

                Tables\Filters\TernaryFilter::make('featured')
                    ->label('Mise en avant'),
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
            ->emptyStateHeading('Aucune référence')
            ->emptyStateDescription('Ajoutez vos marbres et granits pour les faire apparaître sur le site.');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
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
