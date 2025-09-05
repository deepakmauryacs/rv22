<?php

namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\NumberFormatterHelper;
use App\Http\Controllers\Buyer\IssuedController;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\{
    FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
};

class IssueReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    protected $currency;
    protected $controller;
    protected $filters;
    use ExportStylingTrait;

    public function __construct(array $filters, $currency = '₹')
    {
        $this->filters = $filters;
        $this->currency = $currency;
        $this->controller = app()->make(IssuedController::class);
    }

    public function query()
    {
        $request = new \Illuminate\Http\Request($this->filters);
        return $this->controller->applyFilters($request);
    }

    public function map($row): array
    {

        return [
            $row->issued_no,
            optional($row->inventory->branch)->name ?? '',
            $row->inventory->product->product_name ?? '',
            $row->inventory->product->division->division_name ?? '',
            $row->inventory->product->category->category_name ?? '',
            htmlspecialchars_decode($row->inventory->specification ?? '', ENT_QUOTES),
            $row->inventory->size ?? '',
            $row->inventory->inventory_grouping ?? '',
            " " . NumberFormatterHelper::formatQty($row->qty, $this->currency),
            $row->inventory->uom->uom_name ?? '',
            NumberFormatterHelper::formatCurrency($row->amount, $this->currency),
            $row->updater->name ?? '',
            $row->updated_at ? Carbon::parse($row->updated_at)->format('d-m-Y') : '',
            htmlspecialchars_decode($row->remarks ?? '', ENT_QUOTES),
            $row->issuedTo->name ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'Issue Number',
            'Branch',
            'Product Name',
            'Division',
            'Category',
            'Specification',
            'Size',
            'Inventory Grouping',
            'Issued Quantity',
            'UOM',
            'Amount (' . $this->currency . ')',
            'Added BY',
            'Added Date',
            'Remarks',
            'Issued To'
        ];
    }
}
