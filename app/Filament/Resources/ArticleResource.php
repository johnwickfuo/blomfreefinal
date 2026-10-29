<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Forms\Components\{DateTimePicker, FileUpload, RichEditor, Select, Textarea, TextInput, Toggle};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')->required()->maxLength(255),
            TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255)->alphaDash(),
            Select::make('category')->required()->options(['real-estate'=>'Real estate', 'automobile'=>'Automobile', 'animals'=>'Kennel & Farm', 'technology'=>'Technology', 'fashion'=>'Fashion', 'company'=>'Company updates']),
            Textarea::make('excerpt')->required()->maxLength(500),
            RichEditor::make('body')->required()->columnSpanFull(),
            FileUpload::make('image_path')->image()->disk('public')->directory('articles'),
            TextInput::make('seo_title')->maxLength(255),
            Textarea::make('seo_description')->maxLength(500),
            Toggle::make('is_published')->default(false),
            DateTimePicker::make('published_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->sortable()->searchable(),Tables\Columns\TextColumn::make('category')->sortable()->searchable(),Tables\Columns\TextColumn::make('is_published')->sortable()->searchable(),Tables\Columns\TextColumn::make('published_at')->sortable()->searchable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
