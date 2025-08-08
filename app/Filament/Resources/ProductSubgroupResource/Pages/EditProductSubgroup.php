<?php

namespace App\Filament\Resources\ProductSubgroupResource\Pages;

use App\Filament\Resources\ProductSubgroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductSubgroup extends EditRecord
{
    protected static string $resource = ProductSubgroupResource::class;

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
