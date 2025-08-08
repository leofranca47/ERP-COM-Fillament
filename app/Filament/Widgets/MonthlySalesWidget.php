<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlySalesWidget extends ChartWidget
{
    protected static ?string $heading = 'Total de Vendas por Mês';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = $this->getMonthlySalesData();

        return [
            'datasets' => [
                [
                    'label' => 'Total de Vendas (R$)',
                    'data' => $data['totals'],
                    'backgroundColor' => 'rgba(54, 162, 235, 0.7)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'borderWidth' => 1
                ],
            ],
            'labels' => $data['months'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    private function getMonthlySalesData(): array
    {
        // Get data for the last 6 months
        $startDate = Carbon::now()->subMonths(5)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Get monthly sales data
        $sales = Sale::where('status', 'finalized')
            ->where('sale_date', '>=', $startDate)
            ->where('sale_date', '<=', $endDate)
            ->select(
                DB::raw("strftime('%Y', sale_date) as year"),
                DB::raw("strftime('%m', sale_date) as month"),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Create an array of the last 6 months
        $months = [];
        $totals = [];

        // Initialize with zeros
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $yearMonth = $date->format('Y-m');
            $monthName = $date->format('M/Y');

            $months[$yearMonth] = $monthName;
            $totals[$yearMonth] = 0;
        }

        // Fill with actual data
        foreach ($sales as $sale) {
            $yearMonth = sprintf('%04d-%02d', $sale->year, $sale->month);
            if (isset($totals[$yearMonth])) {
                $totals[$yearMonth] = $sale->total;
            }
        }

        return [
            'months' => array_values($months),
            'totals' => array_values($totals),
        ];
    }
}
