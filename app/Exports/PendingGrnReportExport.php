<?php

namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\NumberFormatterHelper;
use App\Helpers\PendingGrnUpdateBYrHelper;
use App\Http\Controllers\Buyer\GrnController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\{
    FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
};

class PendingGrnReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    use ExportStylingTrait;

    protected $currency;
    protected $controller;
    protected $filters;
    protected $updatedByMap;
    protected $collection;
    protected $rowIndex = 0;

    public function __construct(array $filters, $currency = '₹')
    {
        $this->filters = $filters;
        $this->currency = $currency;
        $this->controller = app()->make(GrnController::class);
    }

    public function collection()
    {
        $request = new \Illuminate\Http\Request($this->filters);

        // Forcefully convert to Collection
        $result = $this->controller->getFilteredPendingGrnData($request);
        $grns = $result instanceof \Illuminate\Database\Eloquent\Builder || $result instanceof \Illuminate\Database\Eloquent\Relations\Relation
            ? $result->get()
            : collect($result);

        $this->updatedByMap = PendingGrnUpdateBYrHelper::getUpdatedByMap($grns);

        // Process and return clean filtered collection
        $this->collection = $grns
            ->unique(fn($item) => $item->inventory_id . '-' . $item->order_id)
            ->filter(function ($item) {
                $totalGrnQty = round($item->totalGrnQty, 2);
                $orderQty = round($item->order_qty, 2);
                $pendingGrnQty = round($orderQty - $totalGrnQty, 2);
                return $totalGrnQty < $orderQty && $totalGrnQty > 0 && $pendingGrnQty > 0;
            })
            ->values();

        return $this->collection;
    }


    public function map($item): array
    {
        $this->rowIndex++;
        $totalGrnQty = round($item->totalGrnQty, 2);
        $orderQty = round($item->order_qty, 2);
        $pendingGrnQty = round($orderQty - $totalGrnQty, 2);

        $key = $item->inventory_id . '-' . $item->order_id . '-' . $item->grn_type . '-' . $item->last_updated_at;
        $updatedById = $this->updatedByMap[$key] ?? null;
        $addedByName = $updatedById ? optional(User::find($updatedById))->name : '';

        return [
            $this->rowIndex,
            optional($item->inventory->branch)->name ?? '',
            $item->po_number ?? '',
            optional($item->created_at)->format('d-m-Y'),
            optional($item->inventory->product)->product_name ?? '',
            $item->vendor_name ?? '',
            $item->inventory->specification ?? '',
            $item->inventory->size ?? '',
            $item->inventory->inventory_grouping ?? '',
            $addedByName,
            Carbon::parse($item->last_updated_at)->format('d-m-Y'),
            optional($item->inventory->uom)->uom_name ?? '',
            " ".NumberFormatterHelper::formatQty($orderQty, $this->currency),
            " ".NumberFormatterHelper::formatQty($totalGrnQty, $this->currency),
            " ".NumberFormatterHelper::formatQty($pendingGrnQty, $this->currency),
        ];
    }

    public function headings(): array
    {
        return [
            'Serial Number',
            'Branch',
            'Order Number',
            'Order Date',
            'Product Name',
            'Vendor Name',
            'Specification',
            'Size',
            'Inventory Grouping',
            'Added By',
            'Added Date',
            'UOM',
            'Order Quantity',
            'Total GRN Quantity',
            'Pending GRN Quantity'
        ];
    }


}
