<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReviewResource\Pages;
use App\Models\ProductReview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Store & Customers';
    protected static ?string $navigationLabel = 'Product Reviews';

    public static function getNavigationBadge(): ?string
    {
        $count = ProductReview::query()->where('status', 'pending')->count();
        return $count ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Customer review')->schema([
                Forms\Components\Placeholder::make('product_name')->label('Product')
                    ->content(fn (?ProductReview $record): string => $record?->product?->name ?? '—'),
                Forms\Components\TextInput::make('display_name')->disabled()->dehydrated(false),
                Forms\Components\TextInput::make('rating')->numeric()->disabled()->dehydrated(false),
                Forms\Components\TextInput::make('title')->disabled()->dehydrated(false),
                Forms\Components\Textarea::make('body')->rows(6)->disabled()->dehydrated(false),
            ]),
            Forms\Components\Select::make('status')->required()->options(ProductReview::STATUSES),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            Tables\Columns\TextColumn::make('product.name')->label('Product')->searchable()->limit(40),
            Tables\Columns\TextColumn::make('display_name')->label('Reviewer')->searchable(),
            Tables\Columns\TextColumn::make('rating')->label('Stars')->sortable(),
            Tables\Columns\TextColumn::make('title')->searchable()->limit(55),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                'approved' => 'success', 'rejected' => 'danger', default => 'warning',
            })->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('status')->options(ProductReview::STATUSES)->default('pending'),
        ])->actions([
            Tables\Actions\Action::make('approve')->label('Approve')->color('success')->icon('heroicon-o-check')
                ->visible(fn (ProductReview $record) => $record->status !== 'approved')
                ->requiresConfirmation()->action(fn (ProductReview $record) => $record->update(['status' => 'approved'])),
            Tables\Actions\Action::make('reject')->label('Reject')->color('danger')->icon('heroicon-o-x-mark')
                ->visible(fn (ProductReview $record) => $record->status !== 'rejected')
                ->requiresConfirmation()->action(fn (ProductReview $record) => $record->update(['status' => 'rejected'])),
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListProductReviews::route('/'), 'edit' => Pages\EditProductReview::route('/{record}/edit')];
    }
}
