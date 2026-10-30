<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'BLOMWHEELS Automobile';
    protected static ?string $navigationLabel = 'Vehicle Inventory';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) Vehicle::where('status', 'available')->count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identity & classification')->columns(2)->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                        if ($operation === 'create' && filled($state)) $set('slug', Str::slug($state));
                    }),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                Forms\Components\TextInput::make('stock_reference')->label('Internal stock reference')->helperText('Leave blank to generate automatically.')->unique(ignoreRecord: true),
                Forms\Components\Select::make('make')->options(array_combine(Vehicle::MAKES, Vehicle::MAKES))->searchable()->native(false)->required(),
                Forms\Components\TextInput::make('model')->required()->maxLength(100),
                Forms\Components\TextInput::make('trim')->maxLength(100),
                Forms\Components\TextInput::make('year')->required()->integer()->minValue(1950)->maxValue(2100),
                Forms\Components\Select::make('condition')->options(Vehicle::CONDITIONS)->required(),
                Forms\Components\Select::make('body_type')->options(Vehicle::BODY_TYPES),
            ]),
            Forms\Components\Section::make('Technical specifications')->columns(2)->schema([
                Forms\Components\TextInput::make('mileage_km')->label('Mileage (km)')->integer()->minValue(0),
                Forms\Components\Select::make('transmission')->options(Vehicle::TRANSMISSIONS),
                Forms\Components\Select::make('fuel_type')->options(Vehicle::FUELS),
                Forms\Components\TextInput::make('engine')->maxLength(100),
                Forms\Components\Select::make('drivetrain')->options(['FWD' => 'FWD', 'RWD' => 'RWD', 'AWD' => 'AWD', '4WD' => '4WD']),
                Forms\Components\TextInput::make('seats')->integer()->minValue(1)->maxValue(100),
                Forms\Components\TextInput::make('exterior_color')->maxLength(100),
                Forms\Components\TextInput::make('interior_color')->maxLength(100),
            ]),
            Forms\Components\Section::make('Price, location & publication')->columns(2)->schema([
                Forms\Components\TextInput::make('price')->required()->numeric()->minValue(0)->prefix('₦'),
                Forms\Components\Toggle::make('negotiable'),
                Forms\Components\Select::make('status')->options(Vehicle::STATUSES)->required()->default('available'),
                Forms\Components\TextInput::make('state')->maxLength(100),
                Forms\Components\TextInput::make('city')->maxLength(100),
                Forms\Components\TextInput::make('viewing_address')->helperText('Internal reference, not publicly displayed.')->columnSpanFull(),
                Forms\Components\Toggle::make('is_published')->label('Publish listing')->default(false),
                Forms\Components\Toggle::make('is_featured')->label('Feature on homepage')->default(false),
                Forms\Components\TextInput::make('display_order')->integer()->default(0),
            ]),
            Forms\Components\Section::make('Description and disclosures')->schema([
                Forms\Components\Textarea::make('description')->required()->rows(6),
                Forms\Components\TagsInput::make('features')->placeholder('Known feature'),
                Forms\Components\TagsInput::make('known_issues')->placeholder('Known issue'),
                Forms\Components\TextInput::make('accident_history')->placeholder('State only what is known'),
                Forms\Components\TextInput::make('service_history'),
                Forms\Components\TextInput::make('customs_status'),
                Forms\Components\TagsInput::make('documents')->label('Available documentation'),
                Forms\Components\Textarea::make('internal_notes')->label('Private administrative notes')->rows(3),
                Forms\Components\TextInput::make('video_url')->url()->maxLength(255)->helperText('Optional hosted video URL.'),
            ]),
            Forms\Components\Section::make('Media')->schema([
                Forms\Components\SpatieMediaLibraryFileUpload::make('cover')->collection('cover')->image()->maxSize(10240),
                Forms\Components\SpatieMediaLibraryFileUpload::make('gallery')->collection('gallery')->image()
                    ->multiple()->reorderable()->appendFiles()->maxFiles(25)->maxSize(10240),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            Tables\Columns\SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->square(),
            Tables\Columns\TextColumn::make('stock_reference')->label('Ref')->searchable()->copyable(),
            Tables\Columns\TextColumn::make('title')->searchable()->sortable()->weight('bold'),
            Tables\Columns\TextColumn::make('year')->sortable(),
            Tables\Columns\TextColumn::make('condition')->badge()->formatStateUsing(fn ($s) => Vehicle::CONDITIONS[$s] ?? $s),
            Tables\Columns\TextColumn::make('price')->money('NGN')->sortable(),
            Tables\Columns\TextColumn::make('status')->badge()->sortable(),
            Tables\Columns\IconColumn::make('is_published')->boolean()->label('Public'),
            Tables\Columns\IconColumn::make('is_featured')->boolean()->label('Featured'),
        ])->filters([
            Tables\Filters\SelectFilter::make('status')->options(Vehicle::STATUSES),
            Tables\Filters\SelectFilter::make('condition')->options(Vehicle::CONDITIONS),
            Tables\Filters\TernaryFilter::make('is_published')->label('Published'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('public')->label('View public page')
                ->url(fn (Vehicle $record) => $record->is_published ? route('automobiles.show', $record) : null)
                ->visible(fn (Vehicle $record) => $record->is_published)->openUrlInNewTab(),
        ])->bulkActions([
            Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
