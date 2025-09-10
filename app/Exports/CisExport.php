<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class CisExport implements FromView, WithTitle
{
    protected $data;


    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('buyer.rfq.cis.cis-export', $this->data);
    }


    public function title(): string
    {
        return 'CIS Report ' . $this->data['rfq']['rfq_id'] . ' ' . now()->format('d-m-Y');
    }
}