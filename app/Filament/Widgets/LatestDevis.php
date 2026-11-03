<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\DevisResource;
use App\Models\Devis;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Les cinq dernières demandes, avec rappel WhatsApp ou téléphone en un clic.
 */
class LatestDevis extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public const LIMIT = 5;

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('Dernières demandes de devis');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Devis::query()->with('product')->latest()->limit(self::LIMIT))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime('d/m/Y H:i')
                    ->description(fn (Devis $record) => $record->created_at?->diffForHumans()),

                Tables\Columns\TextColumn::make('client_name')
                    ->label(__('Client'))
                    ->weight('medium')
                    ->description(fn (Devis $record) => $record->location),

                Tables\Columns\TextColumn::make('phone')
                    ->label(__('Téléphone'))
                    ->copyable()
                    ->copyMessage(__('Numéro copié')),

                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('Matériau souhaité'))
                    ->formatStateUsing(fn (Devis $record) => $record->product?->translated('name'))
                    ->placeholder(__('À définir')),

                Tables\Columns\TextColumn::make('surface_m2')
                    ->label(__('Surface'))
                    ->numeric(decimalPlaces: 1, thousandsSeparator: DevisResource::THOUSANDS)
                    ->suffix(__(' m²'))
                    ->placeholder(__('—')),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Statut'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => DevisResource::statusOptions()[$state] ?? $state)
                    ->color(fn (?string $state) => DevisResource::statusColor($state)),
            ])
            ->recordUrl(fn (Devis $record) => DevisResource::getUrl('edit', ['record' => $record]))
            ->actions([
                Tables\Actions\Action::make('whatsapp')
                    ->label(__('WhatsApp'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->button()
                    ->size('sm')
                    ->url(fn (Devis $record) => DevisResource::whatsappUrl($record), shouldOpenInNewTab: true),

                Tables\Actions\Action::make('appeler')
                    ->label(__('Appeler'))
                    ->icon('heroicon-o-phone')
                    ->color('gray')
                    ->button()
                    ->outlined()
                    ->size('sm')
                    ->url(fn (Devis $record) => DevisResource::telUrl($record)),
            ])
            ->headerActions([
                Tables\Actions\Action::make('toutes')
                    ->label(__('Voir toutes les demandes'))
                    ->icon(fn () => __('filament-panels::layout.direction') === 'rtl' ? 'heroicon-m-arrow-left' : 'heroicon-m-arrow-right')
                    ->iconPosition('after')
                    ->link()
                    ->url(DevisResource::getUrl('index')),
            ])
            ->emptyStateIcon('heroicon-o-inbox')
            ->emptyStateHeading(__('Aucune demande pour le moment'))
            ->emptyStateDescription(__('Les devis envoyés depuis le site apparaîtront ici.'))
            ->poll('60s');
    }
}
