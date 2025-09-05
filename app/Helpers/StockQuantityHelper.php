<?php
namespace App\Helpers;

use App\Models\{Grn, Issued, IssuedReturn, ReturnStock};
use Illuminate\Support\Facades\Auth;

class StockQuantityHelper
{
    public static function getGrnQuantities(array $inventoryIds): array
    {
        $companyId = Auth::user()->parent_id ?: Auth::user()->id;

        return Grn::whereIn('inventory_id', $inventoryIds)
            ->where('company_id', $companyId)
            ->groupBy('inventory_id')
            ->selectRaw('inventory_id, SUM(grn_qty) as total')
            ->pluck('total', 'inventory_id')
            ->toArray();
    }

    public static function getIssueQuantities(array $inventoryIds): array
    {
        $buyerId = Auth::user()->parent_id ?: Auth::user()->id;

        return Issued::whereIn('inventory_id', $inventoryIds)
            ->where('buyer_id', $buyerId)
            ->where('is_deleted', 2)
            ->groupBy('inventory_id')
            ->selectRaw('inventory_id, SUM(qty) as total')
            ->pluck('total', 'inventory_id')
            ->toArray();
    }

    public static function getIssueReturnQuantities(array $inventoryIds): array
    {
        $buyerId = Auth::user()->parent_id ?: Auth::user()->id;

        return IssuedReturn::whereIn('inventory_id', $inventoryIds)
            ->where('buyer_id', $buyerId)
            ->where('is_deleted', 2)
            ->groupBy('inventory_id')
            ->selectRaw('inventory_id, SUM(qty) as total')
            ->pluck('total', 'inventory_id')
            ->toArray();
    }

    public static function getStockReturnQuantities(array $inventoryIds): array
    {
        $buyerId = Auth::user()->parent_id ?: Auth::user()->id;

        return ReturnStock::whereIn('inventory_id', $inventoryIds)
            ->where('buyer_id', $buyerId)
            ->where('is_deleted', 2)
            ->groupBy('inventory_id')
            ->selectRaw('inventory_id, SUM(qty) as total')
            ->pluck('total', 'inventory_id')
            ->toArray();
    }
    public static function preloadStockQuantityMaps(array $inventoryIds): array
    {
        return [
            'grn' => self::getGrnQuantities($inventoryIds),
            'issue' => self::getIssueQuantities($inventoryIds),
            'issue_return' => self::getIssueReturnQuantities($inventoryIds),
            'stock_return' => self::getStockReturnQuantities($inventoryIds),
        ];
    }
    public static function calculateCurrentStockValue(int $inventoryId,float $openingStock,array $quantityMaps): float {
        return $openingStock
            + ($quantityMaps['grn'][$inventoryId] ?? 0)
            - ($quantityMaps['issue'][$inventoryId] ?? 0)
            + ($quantityMaps['issue_return'][$inventoryId] ?? 0)
            - ($quantityMaps['stock_return'][$inventoryId] ?? 0);
    }
}
