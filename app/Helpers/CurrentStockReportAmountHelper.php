<?php

namespace App\Helpers;
use App\Models\{Grn, Issued, IssuedReturn, ReturnStock};
use Illuminate\Support\Facades\Auth;
class CurrentStockReportAmountHelper
{

    public static function getGrnAmounts(array $inventoryIds): array
    {
        $companyId = Auth::user()->parent_id ?: Auth::user()->id;

        return Grn::whereIn('inventory_id', $inventoryIds)
            ->where('company_id', $companyId)
            //->where('inv_status', 1)
            ->get()
            ->groupBy('inventory_id')
            ->map(fn($items) => $items->sum(fn($grn) => $grn->grn_qty * $grn->order_rate))
            ->toArray();
    }
    public static function getIssueAmounts(array $inventoryIds): array
    {
        $buyerId = Auth::user()->parent_id ?: Auth::user()->id;

        return Issued::whereIn('inventory_id', $inventoryIds)
            ->where('buyer_id', $buyerId)
            ->get()
            ->groupBy('inventory_id')
            ->map(fn($items) => $items->sum(fn($issue) => $issue->rate * $issue->qty))
            ->toArray();
    }
    public static function getIssueReturnAmounts(array $inventoryIds): array
    {
        $buyerId = Auth::user()->parent_id ?: Auth::user()->id;

        return IssuedReturn::whereIn('inventory_id', $inventoryIds)
            ->where('buyer_id', $buyerId)
            ->get()
            ->groupBy('inventory_id')
            ->map(fn($items) => $items->sum(fn($return) => $return->rate * $return->qty))
            ->toArray();
    }
    public static function getStockReturnAmounts(array $inventoryIds): array
    {
        $buyerId = Auth::user()->parent_id ?: Auth::user()->id;

        return ReturnStock::whereIn('inventory_id', $inventoryIds)
            ->where('buyer_id', $buyerId)
            ->get()
            ->groupBy('inventory_id')
            ->map(fn($items) => $items->sum(fn($return) => $return->rate * $return->qty))
            ->toArray();
    }
    public static function preloadValueMaps(array $inventoryIds): array
    {
        return [
            'grn' => self::getGrnAmounts($inventoryIds),
            'issue' => self::getIssueAmounts($inventoryIds),
            'issue_return' => self::getIssueReturnAmounts($inventoryIds),
            'stock_return' => self::getStockReturnAmounts($inventoryIds),
        ];
    }
    public static function calculateAmountValue(
        int $inventoryId,
        float $openingStock,
        float $openingStockPrice,
        array $valueMaps
    ): float|string {
        $initialValue = $openingStock * $openingStockPrice;

        $total = $initialValue
            + ($valueMaps['grn'][$inventoryId] ?? 0)
            - ($valueMaps['issue'][$inventoryId] ?? 0)
            + ($valueMaps['issue_return'][$inventoryId] ?? 0)
            - ($valueMaps['stock_return'][$inventoryId] ?? 0);

        return $total <= 0 ? '0' : round($total, 2);
    }
}
