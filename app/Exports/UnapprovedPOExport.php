<?php

namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Models\IndentApi;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithChunkReading
};

class UnapprovedPOExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    use ExportStylingTrait;

    protected $data;
    protected $type;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $indentData = $this->data;
        return $indentData;
    }

    public function map($indent): array
    {
        return [
            'po_number' => $indent->po_number,
            'buyer_order_number' => $indent->buyer_order_number,
            'rfq_no' => $indent->rfq_id,
            'uo_date' => $indent->created_at->format('d/m/Y'),
            'rfq_date' => $indent->rfq?->created_at?->format('d/m/Y'),
            'branch' => $indent->rfq?->buyerBranch?->name,
            'product' => $indent->order_variants->pluck('product.product_name')->unique()->join(', '),
            'buyer' => $indent->buyer?->legal_name,
            'vendor' => $indent->vendor?->legal_name,
            'order_value' => $indent->vendor_currency . $indent->order_total_amount,
        ];
    }

    public function headings(): array
    {
        return [
            'UNAPPROVED ORDER NO',
            'BUYER ORDER NUMBER',
            'RFQ NO',
            'UNAPPROVED ORDER DATE',
            'RFQ DATE',
            'BRANCH/UNIT',
            'PRODUCT',
            'USER',
            'VENDOR',
            'UNAPPROVED ORDER VALUE'
        ];
    }
}
