<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketplaceSectionResource\Pages;
use App\Models\MarketplaceSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MarketplaceSectionResource extends Resource
{
    protected static ?string $model = MarketplaceSection::class;
    protected static ?string $navigationIcon = 'heroicon-o-bars-3-bottom-left';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Homepage Sections';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('section_key')->options(
                collect(MarketplaceSection::DEFAULTS)->mapWithKeys(
                    fn (array $value, string $key) => [$key => $value['title']]
                )->all()
            )->required()->unique(ignoreRecord: true)->disabledOn('edit'),
            Forms\Components\TextInput::make('title')->required()->maxLength(120),
            Forms\Components\Toggle::make('is_enabled')->default(true),
            Forms\Components\TextInput::make('display_order')->required()->numeric()->minValue(0)->maxValue(999)->default(50),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('display_order')->columns([
            Tables\Columns\TextColumn::make('section_key')->label('Section')->sortable(),
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\IconColumn::make('is_enabled')->boolean(),
            Tables\Columns\TextColumn::make('display_order')->sortable(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketplaceSections::route('/'),
            'create' => Pages\CreateMarketplaceSection::route('/create'),
            'edit' => Pages\EditMarketplaceSection::route('/{record}/edit'),
        ];
    }
}
