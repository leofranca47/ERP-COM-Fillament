<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\SaleItem;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends ChartWidget
{
    protected static ?string $heading = 'Produtos Mais Vendidos (Mês Atual)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = $this->getTopProductsData();

        return [
            'datasets' => [
                [
                    'label' => 'Quantidade Vendida',
                    'data' => $data['quantities'],
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                    ],
                ],
            ],
            'labels' => $data['products'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    private function getTopProductsData(): array
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Get top 5 products for the current month
        $topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'finalized')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'products.id',
                'products.description',
                DB::raw('SUM(sale_items.quantity) as total_quantity')
            )
            ->groupBy('products.id', 'products.description')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        $products = [];
        $quantities = [];

        foreach ($topProducts as $product) {
            $products[] = mb_substr($product->description, 0, 20) . (mb_strlen($product->description) > 20 ? '...' : '');
            $quantities[] = $product->total_quantity;
        }

        // If we have less than 5 products, fill with empty data
        if (count($products) < 5) {
            $missing = 5 - count($products);
            for ($i = 0; $i < $missing; $i++) {
                $products[] = 'Sem dados';
                $quantities[] = 0;
            }
        }

        return [
            'products' => $products,
            'quantities' => $quantities,
        ];
    }
}
