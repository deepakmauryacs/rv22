<?php

namespace App\Exports;

use App\Models\VendorProduct;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VerifiedProductsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        $data = collect(); // Initialize an empty collection

        VendorProduct::with([
                'vendor',
                'product.division',
                'product.category',
                'addedBy',
                'verifiedBy'
            ])
            ->where('edit_status', 0)
            ->where('approval_status', 1)
            ->chunk(2000, function ($chunk) use (&$data) { // Add & before $data
                $chunkData = $chunk->map(function ($item) {
                    return [
                        'Vendor Name'     => optional($item->vendor)->name,
                        'Division'        => optional($item->product->division)->division_name,
                        'Category'        => optional($item->product->category)->category_name,
                        'Product Name'    => optional($item->product)->product_name,
                        'Added By Vendor' => optional($item->addedBy)->name,
                        'Verified By'     => optional($item->verifiedBy)->name,
                    ];
                });

                $data = $data->merge($chunkData); // This now modifies $data outside the closure
            });

        return $data;
    }


    public function headings(): array
    {
        return [
            'Vendor Name',
            'Division',
            'Category',
            'Product Name',
            'Added By Vendor',
            'Verified By',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // row 1 = headings
        ];
    }
}
