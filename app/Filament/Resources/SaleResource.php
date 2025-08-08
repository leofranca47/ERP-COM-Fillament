<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Person;
use App\Models\Product;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Vendas';

    protected static ?string $modelLabel = 'Venda';

    protected static ?string $pluralModelLabel = 'Vendas';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Pedido')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('Número do Pedido')
                            ->default(fn () => Sale::generateOrderNumber())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Forms\Components\DatePicker::make('sale_date')
                            ->label('Data da Venda')
                            ->default(now())
                            ->required(),
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('person_id')
                                    ->label('Cliente Cadastrado')
                                    ->relationship('person', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->optionsLimit(50)
                                    ->placeholder('Selecione um cliente (opcional)')
                                    ->helperText('Para cadastrar um novo cliente, utilize a tela de Pessoas')
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($state) {
                                            $set('customer_name', null);
                                        }
                                    }),
                                Forms\Components\TextInput::make('customer_name')
                                    ->label('Cliente Avulso')
                                    ->helperText('Preencha apenas se não selecionar um cliente cadastrado')
                                    ->maxLength(255)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($state) {
                                            $set('person_id', null);
                                        }
                                    }),
                            ])
                            ->columnSpan(2),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'open' => 'Aberto',
                                'finalized' => 'Finalizado',
                                'canceled' => 'Cancelado',
                            ])
                            ->default('open')
                            ->required(),
                        Forms\Components\Textarea::make('observations')
                            ->label('Observações')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Itens da Venda')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produto')
                                    ->options(Product::query()->pluck('description', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($state) {
                                            $product = Product::find($state);
                                            if ($product) {
                                                $set('unit_price', $product->sale_price);
                                            }
                                        }
                                    }),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Quantidade')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn (Set $set, Get $get) =>
                                        $set('total_price', $get('quantity') * $get('unit_price'))),
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Valor Unitário')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn (Set $set, Get $get) =>
                                        $set('total_price', $get('quantity') * $get('unit_price'))),
                                Forms\Components\TextInput::make('total_price')
                                    ->label('Total')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),
                            ])
                            ->columns(4)
                            ->itemLabel(fn (array $state): ?string =>
                                $state['product_id']
                                    ? Product::find($state['product_id'])?->description ?? 'Produto'
                                    : 'Novo Item')
                            ->addActionLabel('Adicionar Item')
                            ->reorderable(false)
                            ->defaultItems(0)
                            ->required(),

                        Forms\Components\Placeholder::make('total_amount_display')
                            ->label('Total do Pedido')
                            ->content(function (Get $get) {
                                $items = $get('items');
                                $total = 0;

                                if (is_array($items)) {
                                    foreach ($items as $item) {
                                        $total += $item['total_price'] ?? 0;
                                    }
                                }

                                return 'R$ ' . number_format($total, 2, ',', '.');
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Número do Pedido')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Data da Venda')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('person.name')
                    ->label('Cliente (Cadastrado)')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente (Avulso)')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'finalized' => 'success',
                        'canceled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Aberto',
                        'finalized' => 'Finalizado',
                        'canceled' => 'Cancelado',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('finalize')
                    ->label('Finalizar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Sale $record) => $record->status === 'open')
                    ->action(function (Sale $record) {
                        $record->update(['status' => 'finalized']);

                        // Optional: Decrement stock
                        foreach ($record->items as $item) {
                            if ($item->product && $item->product->variants->count() === 0) {
                                // If no variants, we could update a stock field directly on the product
                                // This is just a placeholder for future implementation
                            }
                        }
                    }),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Sale $record) => $record->status === 'open')
                    ->requiresConfirmation()
                    ->action(fn (Sale $record) => $record->update(['status' => 'canceled'])),
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
