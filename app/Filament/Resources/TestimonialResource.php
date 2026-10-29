<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Forms\Components\{DateTimePicker, FileUpload, RichEditor, Select, Textarea, TextInput, Toggle};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('customer_name')->required()->maxLength(255),
            Textarea::make('quote')->required()->maxLength(2000),
            FileUpload::make('image_path')->image()->disk('public')->directory('testimonials'),
            Select::make('division')->options(['estates'=>'Estates & Properties', 'kennel'=>'Kennel & Farm', 'collections'=>'Collections', 'gadgets'=>'Gadgets & Accessories', 'automobile'=>'BLOMWHEELS']),
            Toggle::make('is_approved')->default(false),
            TextInput::make('display_order')->numeric()->default(0)->minValue(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('customer_name')->sortable()->searchable(),Tables\Columns\TextColumn::make('division')->sortable()->searchable(),Tables\Columns\TextColumn::make('is_approved')->sortable()->searchable(),Tables\Columns\TextColumn::make('display_order')->sortable()->searchable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
