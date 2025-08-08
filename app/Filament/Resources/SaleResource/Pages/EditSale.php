<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('back')
                ->label('Voltar')
                ->url(static::getResource()::getUrl())
                ->color('gray'),
            Actions\Action::make('finalize')
                ->label('Finalizar Pedido')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === 'open')
                ->action(function () {
                    $this->record->update(['status' => 'finalized']);

                    // Optional: Decrement stock
                    foreach ($this->record->items as $item) {
                        if ($item->product && $item->product->variants->count() === 0) {
                            // If no variants, we could update a stock field directly on the product
                            // This is just a placeholder for future implementation
                        }
                    }

                    $this->refreshFormData([
                        'status',
                    ]);
                }),
        ];
    }

    protected function afterSave(): void
    {
        // Update the total amount after saving the sale
        $this->record->updateTotal();
    }
}
