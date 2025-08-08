<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonResource\Pages;
use App\Filament\Resources\PersonResource\RelationManagers;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Pessoas';

    protected static ?string $modelLabel = 'Pessoa';

    protected static ?string $pluralModelLabel = 'Pessoas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Cadastro de Pessoa')
                    ->schema([
                        Forms\Components\Select::make('registration_type')
                            ->label('Tipo de Cadastro')
                            ->options([
                                'client' => 'Cliente',
                                'supplier' => 'Fornecedor',
                            ])
                            ->required(),
                        Forms\Components\Select::make('person_type')
                            ->label('Tipo de Pessoa')
                            ->options([
                                'individual' => 'Física',
                                'legal' => 'Jurídica',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Nascimento/Data de Criação'),
                        Forms\Components\TextInput::make('name')
                            ->label('Nome/Nome Fantasia')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_name')
                            ->label('Razão Social')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('state_registration')
                            ->label('Inscrição Estadual')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('document')
                            ->label('CPF/CNPJ')
                            ->required()
                            ->maxLength(20),
                        Forms\Components\Radio::make('status')
                            ->label('Situação')
                            ->options([
                                'active' => 'Ativo',
                                'inactive' => 'Inativo',
                            ])
                            ->default('active')
                            ->inline(),
                        Forms\Components\FileUpload::make('profile_photo')
                            ->label('Foto do Perfil')
                            ->image()
                            ->directory('profile-photos'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Endereços')
                    ->schema([
                        Forms\Components\Repeater::make('addresses')
                            ->relationship()
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
                            ])
                            ->columns(2)
                            ->defaultItems(1),
                    ]),

                Forms\Components\Section::make('Contatos')
                    ->schema([
                        Forms\Components\Repeater::make('contacts')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Tipo')
                                    ->options([
                                        'commercial' => 'Comercial',
                                        'residential' => 'Residencial',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Celular')
                                    ->tel()
                                    ->maxLength(20),
                            ])
                            ->columns(3)
                            ->defaultItems(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Código')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('document')
                    ->label('CPF/CNPJ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('registration_type')
                    ->label('Tipo de Cadastro')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'client' => 'Cliente',
                        'supplier' => 'Fornecedor',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('person_type')
                    ->label('Tipo de Pessoa')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'individual' => 'Física',
                        'legal' => 'Jurídica',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Situação')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                        default => $state,
                    }),
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
            RelationManagers\AddressesRelationManager::class,
            RelationManagers\ContactsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }
}
