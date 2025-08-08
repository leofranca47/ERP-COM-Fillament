<?php

namespace App\Filament\Resources\ProductGroupResource\Pages;

use App\Filament\Resources\ProductGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductGroup extends EditRecord
{
    protected static string $resource = ProductGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('back')
                ->label('Voltar')
                ->url(static::getResource()::getUrl())
                ->color('gray'),
        ];
    }
}
