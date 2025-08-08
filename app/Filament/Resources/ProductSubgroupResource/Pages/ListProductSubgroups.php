<?php

namespace App\Filament\Resources\ProductSubgroupResource\Pages;

use App\Filament\Resources\ProductSubgroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductSubgroups extends ListRecords
{
    protected static string $resource = ProductSubgroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
