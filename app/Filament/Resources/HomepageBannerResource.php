<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomepageBannerResource\Pages;
use App\Models\HomepageBanner;
use Filament\Forms\Components\{DateTimePicker, FileUpload, RichEditor, Select, Textarea, TextInput, Toggle};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HomepageBannerResource extends Resource
{
    protected static ?string $model = HomepageBanner::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('eyebrow')->maxLength(255),
            TextInput::make('headline')->required()->maxLength(255),
            Textarea::make('description')->maxLength(2000),
            FileUpload::make('image_path')->image()->disk('public')->directory('homepage-banners'),
            TextInput::make('button_label')->maxLength(255),
            TextInput::make('button_url')->maxLength(255)->helperText('Use a local path such as /contact or an https URL.'),
            Toggle::make('is_active')->default(false),
            TextInput::make('display_order')->numeric()->default(0)->minValue(0),
            DateTimePicker::make('starts_at'),
            DateTimePicker::make('ends_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('headline')->sortable()->searchable(),Tables\Columns\TextColumn::make('is_active')->sortable()->searchable(),Tables\Columns\TextColumn::make('display_order')->sortable()->searchable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomepageBanners::route('/'),
            'create' => Pages\CreateHomepageBanner::route('/create'),
            'edit' => Pages\EditHomepageBanner::route('/{record}/edit'),
        ];
    }
}
