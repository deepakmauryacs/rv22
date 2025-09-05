<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VerifiedProductsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        $start = microtime(true);
        Log::info('VerifiedProductsExport started at: ' . now());

        $finalCollection = new Collection();

        DB::table('vendor_products as vp')
            ->leftJoin('users as v', 'v.id', '=', 'vp.vendor_id')
            ->leftJoin('products as p', 'p.id', '=', 'vp.product_id')
            ->leftJoin('divisions as d', 'd.id', '=', 'p.division_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('users as added_by', 'added_by.id', '=', 'vp.added_by_user_id')
            ->leftJoin('users as verified_by', 'verified_by.id', '=', 'vp.verified_by')
            ->where('vp.edit_status', 0)
            ->where('vp.approval_status', 1)
            ->orderBy('v.name')
            ->select([
                'vp.product_id as product_id',
                'vp.vendor_id as vendor_id',
                'v.name as Vendor Name',
                'd.division_name as Division',
                'c.category_name as Category',
                'p.product_name as Product Name',
                'added_by.name as Added By Vendor',
                'verified_by.name as Verified By',
            ])
            ->chunk(2000, function ($rows) use (&$finalCollection) {
                foreach ($rows as $row) {
                    $finalCollection->push([
                        'VENDOR NAME'     => $row->{"Vendor Name"},
                        'DIVISION'        => $row->Division,
                        'CATEGORY'        => $row->Category,
                        'PRODUCT NAME'    => $row->{"Product Name"},
                        'PRODUCT ALIAS'   => $this->getAliasesByProduct($row->product_id, $row->vendor_id),
                        'ADDED BY VENDOR' => $row->{"Added By Vendor"},
                        'VERIFIED BY'     => $row->{"Verified By"},
                    ]);
                }
            });

        $end = microtime(true);
        $duration = round($end - $start, 2);

        Log::info('VerifiedProductsExport completed at: ' . now());
        Log::info('Total time taken: ' . $duration . ' seconds');

        return $finalCollection;
    }

    public function headings(): array
    {
        return [
            'VENDOR NAME',
            'DIVISION',
            'CATEGORY',
            'PRODUCT NAME',
            'PRODUCT ALIAS',
            'ADDED BY VENDOR',
            'VERIFIED BY',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }


    public function getAliasesByProduct($productId, $vendorId = null)
    {
        $query = DB::table('product_alias')->where('product_id', $productId);

        if ($vendorId !== null) {
            $query->where('vendor_id', $vendorId);
        }

        return $query->pluck('alias')->implode(', ');
    }


}
