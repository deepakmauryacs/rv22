<?php

namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\CurrentStockReportAmountHelper;
use App\Helpers\NumberFormatterHelper;
use App\Helpers\StockQuantityHelper;
use App\Http\Controllers\Buyer\InventoryController;
use Maatwebsite\Excel\Concerns\{
    FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
};
class CurrentStockExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    protected $currency;
    protected $controller;
    protected $filters;
    protected $quantityMaps;
    protected $amountMaps;
    protected $rowIndex = 0;
    use ExportStylingTrait;

    public function __construct(array $filters, $currency = '₹')
    {
        $this->filters = $filters;
        $this->currency = $currency;
        $this->controller = app()->make(InventoryController::class);
    }
    public function query()
    {
        $request = new \Illuminate\Http\Request($this->filters);
        $originalQuery = $this->controller->currentStockApplyFilters($request);

        $inventoryIds =  (clone $originalQuery)->pluck('id')->toArray();
        $this->quantityMaps = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds);
        $this->amountMaps = CurrentStockReportAmountHelper::preloadValueMaps($inventoryIds);
        return $originalQuery ;
    }
    public function map($inv): array
    {
        $this->rowIndex++;
        $currentStockValue = StockQuantityHelper::calculateCurrentStockValue($inv->id,$inv->opening_stock,$this->quantityMaps);
        $currentStockAmountValue = CurrentStockReportAmountHelper::calculateAmountValue($inv->id,$inv->opening_stock,$inv->stock_price,$this->amountMaps);
        $issueValue = $this->quantityMaps['issue'][$inv->id] ?? 0;
        $issueAmountValue =$this->amountMaps['issue'][$inv->id] ?? 0;
        $grnValue = $this->quantityMaps['grn'][$inv->id] ?? 0;
        $grnAmountValue = $this->amountMaps['grn'][$inv->id] ?? 0;
        return [
            $this->rowIndex,
            $inv->branch->name ?? '',
            $inv->product->product_name ?? '',
            $inv->buyer_product_name,
            htmlspecialchars_decode($inv->specification, ENT_QUOTES),
            $inv->size,
            $inv->inventory_grouping,
            $inv->uom->uom_name ?? '',
            " ".NumberFormatterHelper::formatQty($currentStockValue, $this->currency),
            " ".NumberFormatterHelper::formatCurrency($currentStockAmountValue, $this->currency),
            " ".NumberFormatterHelper::formatQty($issueValue, $this->currency),
            " ".NumberFormatterHelper::formatCurrency($issueAmountValue, $this->currency),
            " ".NumberFormatterHelper::formatQty($grnValue, $this->currency),
            " ".NumberFormatterHelper::formatCurrency($grnAmountValue, $this->currency),
        ];
    }

    public function headings(): array
    {
        return [
            'Serial No','Branch', 'Product Name', 'Our Product Name', 'Specification',
            'Size','Inventory Grouping','UOM', 'Current Stock Quantity', 'Total Amount ('.$this->currency.')',
            'Issued Quantity', 'Issued Amount ('.$this->currency.')', 'GRN Quantity', 'GRN Amount ('.$this->currency.')',
        ];
    }
}
