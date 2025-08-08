<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductSubgroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Produtos';

    protected static ?string $modelLabel = 'Produto';

    protected static ?string $pluralModelLabel = 'Produtos';

    protected static ?string $navigationGroup = 'Produtos';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Basic Information Section
                Forms\Components\Section::make('Informações Básicas')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Código')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('description')
                            ->label('Descrição')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('gross_weight')
                            ->label('Peso bruto')
                            ->numeric()
                            ->step(0.001),
                        Forms\Components\TextInput::make('net_weight')
                            ->label('Peso líquido')
                            ->numeric()
                            ->step(0.001),
                        Forms\Components\TextInput::make('brand')
                            ->label('Marca')
                            ->maxLength(255),
                        Forms\Components\Select::make('unit_of_measure')
                            ->label('Unidade de medida')
                            ->options([
                                'UN' => 'Unidade',
                                'KG' => 'Quilograma',
                                'M' => 'Metro',
                                'L' => 'Litro',
                                'CX' => 'Caixa',
                                'PC' => 'Peça',
                                'PAR' => 'Par',
                                'CJ' => 'Conjunto',
                            ])
                            ->required(),
                        Forms\Components\Select::make('product_group_id')
                            ->label('Grupo')
                            ->options(ProductGroup::all()->pluck('description', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('product_subgroup_id', null)),
                        Forms\Components\Select::make('product_subgroup_id')
                            ->label('Subgrupo')
                            ->options(function (Get $get) {
                                $groupId = $get('product_group_id');

                                if (!$groupId) {
                                    return [];
                                }

                                return ProductSubgroup::query()
                                    ->where('product_group_id', $groupId)
                                    ->pluck('description', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->live(),
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Imagem do produto')
                            ->image()
                            ->directory('products'),
                        Forms\Components\Checkbox::make('active')
                            ->label('Ativo')
                            ->default(true),
                    ])
                    ->columns(2),

                // Stock and Prices Section
                Forms\Components\Section::make('Estoque e Preços')
                    ->schema([
                        Forms\Components\TextInput::make('sale_price')
                            ->label('Valor de Venda')
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01),
                        Forms\Components\TextInput::make('average_cost')
                            ->label('Custo médio')
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01),
                        Forms\Components\TextInput::make('current_cost')
                            ->label('Custo atual')
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01),
                        Forms\Components\TextInput::make('minimum_stock')
                            ->label('Estoque mínimo')
                            ->numeric()
                            ->step(0.01),
                        Forms\Components\TextInput::make('maximum_stock')
                            ->label('Estoque máximo')
                            ->numeric()
                            ->step(0.01),
                    ])
                    ->columns(2),

                // Taxation Section (Placeholder)
                Forms\Components\Section::make('Tributação')
                    ->schema([
                        Forms\Components\Placeholder::make('taxation_info')
                            ->label('Informação sobre Tributação')
                            ->content('Os campos referentes à tributação serão acrescentados no módulo fiscal posteriormente.')
                    ]),

                // Grading Section
                Forms\Components\Section::make('Gradeamento')
                    ->schema([
                        Forms\Components\Repeater::make('variants')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('barcode')
                                    ->label('Código de Barras')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('grade_x')
                                    ->label('Grade X')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('grade_y')
                                    ->label('Grade Y')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('stock_location')
                                    ->label('Localização no estoque')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Quantidade disponível')
                                    ->numeric()
                                    ->step(0.01)
                                    ->default(0),
                            ])
                            ->columns(3)
                            ->itemLabel(function (array $state): ?string {
                                $barcode = $state['barcode'] ?? null;
                                $gradeX = $state['grade_x'] ?? null;
                                $gradeY = $state['grade_y'] ?? null;

                                if ($barcode) {
                                    return "Código: {$barcode}" . ($gradeX ? " - {$gradeX}" : '') . ($gradeY ? " - {$gradeY}" : '');
                                }

                                return ($gradeX && $gradeY) ? "{$gradeX} - {$gradeY}" : 'Nova variação';
                            })
                            ->reorderable(false)
                            ->addActionLabel('Adicionar variação')
                            ->deleteAction(
                                fn (Forms\Components\Actions\Action $action) => $action->label('Excluir')
                            ),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('group.description')
                    ->label('Grupo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subgroup.description')
                    ->label('Subgrupo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Preço de Venda')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
