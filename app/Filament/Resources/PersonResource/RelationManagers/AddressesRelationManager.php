<?php

namespace App\Filament\Resources\PersonResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $recordTitleAttribute = 'street';

    protected static ?string $title = 'Endereços';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'residential' => 'Residencial',
                        'commercial' => 'Comercial',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('zip_code')
                    ->label('CEP')
                    ->required()
                    ->maxLength(10)
                    ->mask('99999-999')
                    ->live(debounce: 500)
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if (strlen($state) !== 9) {
                            return;
                        }

                        $cep = str_replace('-', '', $state);
                        $url = "https://viacep.com.br/ws/{$cep}/json/";

                        try {
                            $response = file_get_contents($url);
                            $data = json_decode($response, true);

                            if (!isset($data['erro'])) {
                                $set('street', $data['logradouro'] ?? '');
                                $set('neighborhood', $data['bairro'] ?? '');
                                $set('state', $data['uf'] ?? '');

                                // Find or create city
                                if (isset($data['localidade']) && isset($data['uf'])) {
                                    $city = \App\Models\City::firstOrCreate(
                                        ['name' => $data['localidade'], 'state' => $data['uf']],
                                        ['ibge_code' => $data['ibge'] ?? null]
                                    );

                                    $set('city_id', $city->id);
                                }
                            }
                        } catch (\Exception $e) {
                            // Handle exception silently
                        }
                    }),
                Forms\Components\Select::make('state')
                    ->label('Estado')
                    ->options([
                        'AC' => 'Acre',
                        'AL' => 'Alagoas',
                        'AP' => 'Amapá',
                        'AM' => 'Amazonas',
                        'BA' => 'Bahia',
                        'CE' => 'Ceará',
                        'DF' => 'Distrito Federal',
                        'ES' => 'Espírito Santo',
                        'GO' => 'Goiás',
                        'MA' => 'Maranhão',
                        'MT' => 'Mato Grosso',
                        'MS' => 'Mato Grosso do Sul',
                        'MG' => 'Minas Gerais',
                        'PA' => 'Pará',
                        'PB' => 'Paraíba',
                        'PR' => 'Paraná',
                        'PE' => 'Pernambuco',
                        'PI' => 'Piauí',
                        'RJ' => 'Rio de Janeiro',
                        'RN' => 'Rio Grande do Norte',
                        'RS' => 'Rio Grande do Sul',
                        'RO' => 'Rondônia',
                        'RR' => 'Roraima',
                        'SC' => 'Santa Catarina',
                        'SP' => 'São Paulo',
                        'SE' => 'Sergipe',
                        'TO' => 'Tocantins',
                    ])
                    ->required(),
                Forms\Components\Select::make('city_id')
                    ->label('Cidade')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('street')
                    ->label('Logradouro')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('number')
                    ->label('Número')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('neighborhood')
                    ->label('Bairro')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('complement')
                    ->label('Complemento')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'residential' => 'Residencial',
                        'commercial' => 'Comercial',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('zip_code')
                    ->label('CEP'),
                Tables\Columns\TextColumn::make('city.name')
                    ->label('Cidade'),
                Tables\Columns\TextColumn::make('state')
                    ->label('Estado'),
                Tables\Columns\TextColumn::make('street')
                    ->label('Logradouro'),
                Tables\Columns\TextColumn::make('number')
                    ->label('Número'),
                Tables\Columns\TextColumn::make('neighborhood')
                    ->label('Bairro'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
