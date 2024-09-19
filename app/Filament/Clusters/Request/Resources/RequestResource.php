<?php

namespace App\Filament\Clusters\Request\Resources;

use App\Filament\Clusters\Request;
use App\Filament\Clusters\Request\Resources\RequestResource\Pages;
use App\Filament\Clusters\Request\Resources\RequestResource\RelationManagers;
use App\Models\Request as ModelRequest;
use App\TypeRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RequestResource extends Resource
{
    protected static ?string $model = ModelRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Request::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\ToggleButtons::make('type')
                    ->label('Kategori Ajuan')
                    ->options(TypeRequest::class)
                    ->inline()
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Dari Tanggal')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Sampai Tanggal')
                    ->required(),
                Forms\Components\TimePicker::make('start_time')
                    ->label('Jam Mulai')
                    ->required(),
                Forms\Components\TimePicker::make('end_time')
                    ->label('Jam Selesai')
                    ->required()
                    ->after('start_time'),
                Forms\Components\ToggleButtons::make('condition')
                    ->label('Lokasi')
                    ->inline()
                    ->options([
                        true => 'Dalam Kota',
                        false => 'Luar Kota'
                    ])->colors([
                        true => 'primary',
                        false => 'danger'
                    ])->icons([
                        true => 'heroicon-m-home',
                        false => 'heroicon-m-arrow-right-start-on-rectangle'
                    ])
                    ->required()
                    ->live(),
                Forms\Components\Textarea::make('location')
                    ->label('Detail Lokasi')
                    ->required()
                    ->maxLength(256)
                    ->columnSpanFull()
                    ->visible(fn(Get $get): bool => !$get('condition')),
                Forms\Components\Textarea::make('description')
                    ->label('Alasan')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (!auth()->user()->isAdmin() && !auth()->user()->isDirector()) {
                    $query->where('user_id', auth()->id());
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time'),
                Tables\Columns\TextColumn::make('end_time'),
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequests::route('/'),
            'create' => Pages\CreateRequest::route('/create'),
            'view' => Pages\ViewRequest::route('/{record}'),
            'edit' => Pages\EditRequest::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return !auth()->user()->isAdmin() && !auth()->user()->isDirector();
    }
}
