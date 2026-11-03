<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DevisResource\Pages;
use App\Models\Devis;
use App\Models\InstallationRate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DevisResource extends Resource
{
    protected static ?string $model = Devis::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    public static function getNavigationGroup(): ?string
    {
        return __('Clients');
    }

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'devis';

    public static function getModelLabel(): string
    {
        return __('demande de devis');
    }

    public static function getPluralModelLabel(): string
    {
        return __('demandes de devis');
    }

    public static function getNavigationLabel(): string
    {
        return __('Demandes de devis');
    }

    protected static ?string $recordTitleAttribute = 'client_name';

    /** Filament met le libellé en capitales de titre : « Demandes De Devis ». */
    public static function getBreadcrumb(): string
    {
        return __('Demandes de devis');
    }

    public const STATUSES = [
        'pending' => 'À rappeler',
        'contacted' => 'Contacté',
        'completed' => 'Terminé',
    ];

    /** Statuts dans la langue du back-office (le français sert de clé). */
    public static function statusOptions(): array
    {
        return array_map(fn (string $label) => __($label), self::STATUSES);
    }

    /** Couleur du badge de statut, partagée par le tableau et le formulaire. */
    public static function statusColor(?string $status): string
    {
        return match ($status) {
            'pending' => 'warning',   // ambre
            'contacted' => 'info',    // bleu
            'completed' => 'success', // vert
            default => 'gray',
        };
    }

    /** Pastille du menu : nombre de demandes encore à traiter. */
    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('status', 'pending')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /** Les demandes proviennent du formulaire public, jamais du back-office. */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Suivi'))
                ->description(__('Seul le statut et les notes internes sont modifiables.'))
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label(__('Statut'))
                        ->options(fn () => self::statusOptions())
                        ->default('pending')
                        ->required()
                        ->native(false),
                ]),

            Forms\Components\Section::make(__('Le client'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('client_name')
                        ->label(__('Nom'))
                        ->disabled(),

                    Forms\Components\TextInput::make('phone')
                        ->label(__('Téléphone'))
                        ->tel()
                        ->disabled(),

                    Forms\Components\TextInput::make('email')
                        ->label(__('E-mail'))
                        ->disabled()
                        ->placeholder(__('Non renseigné')),

                    Forms\Components\TextInput::make('location')
                        ->label(__('Ville / Quartier'))
                        ->disabled()
                        ->placeholder(__('Non renseignée')),
                ]),

            Forms\Components\Section::make(__('Le projet'))
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('product.name')
                        ->label(__('Matériau demandé'))
                        ->formatStateUsing(fn ($record) => $record?->product?->name ?? __('À définir'))
                        ->disabled(),

                    Forms\Components\TextInput::make('application')
                        ->label(__('Application'))
                        ->formatStateUsing(fn (?string $state) => InstallationRate::labelFor($state, 'fr') ?? '—')
                        ->disabled(),

                    Forms\Components\TextInput::make('surface_m2')
                        ->label(__('Surface'))
                        ->suffix(__('m²'))
                        ->disabled(),

                    Forms\Components\TextInput::make('estimated_total')
                        ->label(__('Estimation en ligne'))
                        ->suffix(__('DH'))
                        ->disabled()
                        ->placeholder(__('Non calculée'))
                        ->helperText(__('Fourchette indicative générée par le site, à confirmer après métré.')),

                    Forms\Components\Textarea::make('message')
                        ->label(__('Message du client'))
                        ->rows(4)
                        ->disabled()
                        ->columnSpanFull()
                        ->placeholder(__('Aucun message')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Reçue'))
                    ->dateTime('d/m/Y H:i')
                    ->description(fn (Devis $record) => $record->created_at?->diffForHumans())
                    ->sortable(),

                Tables\Columns\TextColumn::make('client_name')
                    ->label(__('Client'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Devis $record) => $record->location),

                Tables\Columns\TextColumn::make('phone')
                    ->label(__('Téléphone'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('Numéro copié')),

                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('Matériau'))
                    ->placeholder(__('À définir'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('surface_m2')
                    ->label(__('Surface'))
                    ->numeric(decimalPlaces: 1, thousandsSeparator: ' ')
                    ->suffix(__(' m²'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('estimated_total')
                    ->label(__('Estimation'))
                    ->numeric(decimalPlaces: 0, thousandsSeparator: ' ')
                    ->suffix(__(' DH'))
                    ->placeholder(__('—'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Statut'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => self::statusOptions()[$state] ?? $state)
                    ->color(fn (?string $state) => self::statusColor($state))
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Statut'))
                    ->options(fn () => self::statusOptions()),

                Tables\Filters\Filter::make('recentes')
                    ->label(__('Reçues cette semaine'))
                    ->query(fn ($query) => $query->where('created_at', '>=', now()->subWeek())),
            ])
            ->actions([
                // Contact direct depuis le tableau, sans ouvrir la fiche.
                Tables\Actions\Action::make('whatsapp')
                    ->label(__('WhatsApp'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn (Devis $record) => self::whatsappUrl($record), shouldOpenInNewTab: true),

                Tables\Actions\Action::make('appeler')
                    ->label(__('Appeler'))
                    ->icon('heroicon-o-phone')
                    ->color('gray')
                    ->url(fn (Devis $record) => 'tel:'.self::normalisePhone($record->phone)),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->label(__('Ouvrir la fiche')),

                    Tables\Actions\Action::make('marquer_contacte')
                        ->label(__('Marquer « Contacté »'))
                        ->icon('heroicon-o-check')
                        ->color('info')
                        ->visible(fn (Devis $record) => $record->status === 'pending')
                        ->action(fn (Devis $record) => $record->markAs('contacted')),

                    Tables\Actions\Action::make('marquer_termine')
                        ->label(__('Marquer « Terminé »'))
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn (Devis $record) => $record->status !== 'completed')
                        ->action(fn (Devis $record) => $record->markAs('completed')),

                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('Aucune demande pour le moment'))
            ->emptyStateDescription(__('Les devis envoyés depuis le site apparaîtront ici.'))
            ->poll('60s');
    }

    /**
     * Met un numéro saisi par le client au format international marocain.
     * « 06 61 21 94 09 » et « +212661219409 » aboutissent au même résultat.
     */
    public static function normalisePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return '';
        }

        // 00212… -> 212…  (à tester avant le cas national : « 00 » commence
        // aussi par un zéro et serait sinon mal découpé)
        if (str_starts_with($digits, '00')) {
            return substr($digits, 2);
        }

        // 0XXXXXXXXX -> 212XXXXXXXXX
        if (str_starts_with($digits, '0')) {
            return '212'.substr($digits, 1);
        }

        return $digits;
    }

    /** Lien wa.me avec un message de rappel déjà rédigé. */
    public static function whatsappUrl(Devis $record): string
    {
        $lines = [
            "Bonjour {$record->client_name},",
            '',
            'Nous faisons suite à votre demande de devis sur le site '.config('company.legal_name').'.',
        ];

        if ($record->product?->name) {
            $lines[] = "Matériau : {$record->product->name}";
        }

        if ($record->surface_m2) {
            $lines[] = 'Surface : '.rtrim(rtrim((string) $record->surface_m2, '0'), '.').' m²';
        }

        $lines[] = '';
        $lines[] = 'Quand seriez-vous disponible pour un métré sur place ?';

        return 'https://wa.me/'.self::normalisePhone($record->phone)
            .'?text='.rawurlencode(implode("\n", $lines));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('product');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevis::route('/'),
            'edit' => Pages\EditDevis::route('/{record}/edit'),
        ];
    }
}
