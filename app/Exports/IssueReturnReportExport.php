<?php

namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\NumberFormatterHelper;
use App\Http\Controllers\Buyer\IssueReturnController;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\{
    FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
};

class IssueReturnReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    protected $currency;
    protected $controller;
    protected $filters;
    use ExportStylingTrait;

    public function __construct(array $filters, $currency = '₹')
    {
        $this->filters = $filters;
        $this->currency = $currency;
        $this->controller = app()->make(IssueReturnController::class);
    }

    public function query()
    {
        $request = new \Illuminate\Http\Request($this->filters);
        return $this->controller->ApplyFilterQuery($request);
    }

    public function map($row): array
    {

        return [
            $row->issue_unique_no,
            optional($row->inventory->branch)->name ?? '',
            $row->inventory->product->product_name ?? '',
            htmlspecialchars_decode($row->inventory->specification ?? '', ENT_QUOTES),
            $row->inventory->size ?? '',
            $row->inventory->inventory_grouping ?? '',
            $row->issuedType->name ?? '',
            $row->updater->name ?? '',
            $row->created_at ? Carbon::parse($row->created_at)->format('d-m-Y') : '',
            " ".NumberFormatterHelper::formatQty($row->qty, $this->currency),
            $row->inventory->uom->uom_name ?? '',
            htmlspecialchars_decode($row->remarks ?? '', ENT_QUOTES),
        ];
    }

    public function headings(): array
    {
        return [
             'Issued Return Number','Branch','Product Name','Specification','Size','Inventory Grouping','Issued Return Type','Added BY','Added Date','Quantity','UOM','Remarks'
        ];
    }
}
