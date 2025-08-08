<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalesLastSevenDaysWidget extends ChartWidget
{
    protected static ?string $heading = 'Vendas nos Últimos 7 Dias';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $data = $this->getSalesData();

        return [
            'datasets' => [
                [
                    'label' => 'Total de Vendas (R$)',
                    'data' => $data['totals'],
                    'fill' => false,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'tension' => 0.1,
                ],
            ],
            'labels' => $data['dates'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getSalesData(): array
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Get sales data for the last 7 days
        $sales = Sale::where('status', 'finalized')
            ->where('sale_date', '>=', $startDate)
            ->where('sale_date', '<=', $endDate)
            ->select(
                DB::raw("strftime('%Y-%m-%d', sale_date) as date"),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Create an array of the last 7 days
        $dates = [];
        $totals = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $formattedDate = Carbon::now()->subDays($i)->format('d/m');

            $dates[] = $formattedDate;
            $totals[] = $sales[$date]['total'] ?? 0;
        }

        return [
            'dates' => $dates,
            'totals' => $totals,
        ];
    }
}
