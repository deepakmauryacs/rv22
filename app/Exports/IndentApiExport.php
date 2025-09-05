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

class IndentApiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{

    use ExportStylingTrait;

    protected $data;

    /***:- old or new  -:***/
    protected $type;

    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function collection()
    {
        $indentData = $this->data;
        return $indentData;
    }

    public function map($indent): array
    {
        /***:- new data mapping  -:***/
        if ($this->type == 2) {
            return [
                $indent->indent_no,
                $indent->product_id,
                $indent->plant,
                $indent->product_name,
                $indent->quantity,
                optional($indent->getUom)->uom_name ?? '',
                $indent->delivery_date,
                $indent->material_group,
                $indent->purchase_group,
                optional($indent->getProduct)->product_name ?? '',
                htmlspecialchars_decode($indent->product_specs ?? '', ENT_QUOTES),
                $indent->product_size ?? '',
                optional($indent->created_at)->format('d-m-Y'),
                $indent->rfq_quantity_sum ?? 0,
            ];
        } elseif ($this->type == 1) {
            /***:- old data mapping  -:***/
            return [
                $indent->indent_no,
                $indent->product_name,
                $indent->product_id,
                $indent->division_code,
                $indent->dept_code,
                $indent->cost_code,
                optional($indent->getProduct)->product_name ?? '',
                htmlspecialchars_decode($indent->product_specs ?? '', ENT_QUOTES),
                $indent->product_size ?? '',
                optional($indent->getUom)->uom_name ?? '',
                $indent->product_brand,
                optional($indent->created_at)->format('d-m-Y'),
                $indent->quantity,
                $indent->rfq_quantity_sum ?? 0,
            ];
        } else {
            return [];
        }
    }

    public function headings(): array
    {
        /***:- new data mapping  -:***/
        if ($this->type == 2) {
            return [
                'PR No',
                'Material No',
                'Plant',
                'PR Text',
                'QTY',
                'UOM',
                'Delivery date',
                'Material',
                'Purchase',
                'Product(Raprocure)',
                'Specification',
                'Size',
                'Created Date',
                'RFQ Qty'
            ];
        } elseif ($this->type == 1) {
            /***:- old data mapping  -:***/
            return [
                'Indent No',
                'Product',
                'Product ID',
                'Division Code',
                'Dept Code',
                'Cost Code',
                'Product(Raprocure)',
                'Specification',
                'Size',
                'UOM',
                'Brand',
                'Created Date',
                'Indent Qty',
                'RFQ Qty',
            ];
        } else {
            return [];
        }
    }
}