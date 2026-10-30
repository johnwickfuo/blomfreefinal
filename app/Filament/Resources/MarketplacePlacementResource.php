<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketplacePlacementResource\Pages;
use App\Models\Animal;
use App\Models\Land;
use App\Models\MarketplacePlacement;
use App\Models\Product;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MarketplacePlacementResource extends Resource
{
    protected static ?string $model = MarketplacePlacement::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Marketplace Homepage';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Homepage placement')
                ->description('Choose a real listing and where it should appear. Deals require a genuine product markdown; no checkout prices are changed here.')
                ->columns(2)->schema([
                    Forms\Components\Select::make('section')->options(MarketplacePlacement::SECTIONS)
                        ->required()->live()->native(false)
                        ->afterStateUpdated(function (?string $state, Forms\Set $set): void {
                            $set('item_id', null);
                            if ($state === 'deals') $set('item_type', Product::class);
                        }),
                    Forms\Components\Select::make('item_type')->label('Listing division')
                        ->options(MarketplacePlacement::ITEM_TYPES)
                        ->required()->native(false)->live()->afterStateUpdated(fn (Forms\Set $set) => $set('item_id', null))
                        ->disabled(fn (Get $get) => $get('section') === 'deals')
                        ->dehydrated()
                        ->helperText("Today's Deals is only available for products. Select Fashion or Gadget."),
                    Forms\Components\Select::make('item_id')->label('Listing')
                        ->options(function (Get $get): array {
                            $model = $get('section') === 'deals' ? Product::class : $get('item_type');
                            if (! array_key_exists($model ?? '', MarketplacePlacement::ITEM_TYPES)) return [];
                            $query = $model::query()->orderByDesc('id')->limit(200);
                            if ($model === Product::class) {
                                $query->where('is_published', true);
                                return $query->pluck('name', 'id')->all();
                            }
                            if ($model === Vehicle::class) {
                                $query->where('is_published', true);
                                return $query->pluck('title', 'id')->all();
                            }
                            if ($model === Land::class) return $query->pluck('title', 'id')->all();
                            return $query->pluck('name', 'id')->all();
                        })->searchable()->required()->native(false)
                        ->helperText('Choose a published/eligible listing; only the newest 200 per division are shown.'),
                    Forms\Components\TextInput::make('display_order')->numeric()->minValue(0)->default(0),
                    Forms\Components\Toggle::make('is_active')->default(true),
                    Forms\Components\DateTimePicker::make('starts_at')->label('Show from (optional)'),
                    Forms\Components\DateTimePicker::make('ends_at')->label('Hide after (optional)')
                        ->after('starts_at')
                        ->helperText('A timer is shown only for an active deal with an end time.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('display_order')->columns([
            Tables\Columns\TextColumn::make('section')->badge()->formatStateUsing(fn ($state) => MarketplacePlacement::SECTIONS[$state] ?? $state),
            Tables\Columns\TextColumn::make('item_type')->label('Division')
                ->formatStateUsing(fn ($state) => MarketplacePlacement::ITEM_TYPES[$state] ?? $state),
            Tables\Columns\TextColumn::make('item.title')->label('Listing')->getStateUsing(fn (MarketplacePlacement $record) =>
                $record->item?->title ?? $record->item?->name ?? '(Listing removed)')->limit(55),
            Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
            Tables\Columns\TextColumn::make('display_order')->sortable(),
            Tables\Columns\TextColumn::make('ends_at')->dateTime()->placeholder('No expiry')->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('section')->options(MarketplacePlacement::SECTIONS),
            Tables\Filters\TernaryFilter::make('is_active'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketplacePlacements::route('/'),
            'create' => Pages\CreateMarketplacePlacement::route('/create'),
            'edit' => Pages\EditMarketplacePlacement::route('/{record}/edit'),
        ];
    }
}
