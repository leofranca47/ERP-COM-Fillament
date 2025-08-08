<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class MonthlySalesSummaryWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $currentMonthSales = $this->getCurrentMonthSales();
        $previousMonthSales = $this->getPreviousMonthSales();

        $percentageChange = 0;
        $changeDescription = 'Sem variação';
        $changeColor = 'gray';

        if ($previousMonthSales > 0) {
            $percentageChange = (($currentMonthSales - $previousMonthSales) / $previousMonthSales) * 100;
            $changeDescription = number_format(abs($percentageChange), 2) . '% ' .
                ($percentageChange >= 0 ? 'de aumento' : 'de queda');
            $changeColor = $percentageChange >= 0 ? 'success' : 'danger';
        }

        return [
            Stat::make('Total de Vendas (Mês Atual)', 'R$ ' . number_format($currentMonthSales, 2, ',', '.'))
                ->description($changeDescription)
                ->descriptionIcon($percentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($changeColor)
                ->chart($this->getChartData()),
        ];
    }

    private function getCurrentMonthSales(): float
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        return $this->getSalesTotal($startDate, $endDate);
    }

    private function getPreviousMonthSales(): float
    {
        $startDate = Carbon::now()->subMonth()->startOfMonth();
        $endDate = Carbon::now()->subMonth()->endOfMonth();

        return $this->getSalesTotal($startDate, $endDate);
    }

    private function getSalesTotal(Carbon $startDate, Carbon $endDate): float
    {
        return Sale::where('status', 'finalized')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->sum('total_amount') ?? 0;
    }

    private function getChartData(): array
    {
        // Get daily sales for the current month for the mini chart
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $daysInMonth = $endDate->day;

        $dailySales = Sale::where('status', 'finalized')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->select(
                DB::raw("strftime('%d', sale_date) as day"),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $chartData = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $chartData[] = $dailySales[$day] ?? 0;
        }

        return $chartData;
    }
}
