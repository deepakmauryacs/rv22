@php
    $filename = 'Forward-Auction-CIS-' . $auction->auction_id . ' ' . date('d-m-Y') . ".xls";
    header("Content-Type: application/vnd.ms-excel");
    header("Content-disposition: attachment; filename=$filename");

    $startDateTime = $auction->schedule_date . ' ' . $auction->schedule_start_time;
    $endDateTime = $auction->schedule_date . ' ' . $auction->schedule_end_time;
    $startTimestamp = strtotime($startDateTime);
    $endTimestamp = strtotime($endDateTime);
    $now = time();

    if ($now < $startTimestamp) {
        $status = 'UPCOMING';
    } elseif ($now >= $startTimestamp && $now <= $endTimestamp) {
        $status = 'LIVE';
    } else {
        $status = 'COMPLETED';
    }
@endphp

<style>
    .cis-border { border: 2px solid #222 !important; }
</style>

<table border="0" cellpadding="0" cellspacing="0" id="sheet0" class="sheet0 gridlines" style="border-collapse: collapse;page-break-after: always;">
    <tbody>
        <!-- Header Row -->
        <tr class="row0" style="height: 80.8pt;">
            <td class="cis-border" colspan="{{ count($vendorBids) + 4 }}" style="text-align: center; vertical-align: middle; font-weight: bold; color: #27405E; font-family: 'Calibri'; font-size: 20pt; background-color: white;">
                <img src="{{ asset('assets/images/logo/rfq-logo-sm.png') }}" border="0"><br>
                Forward Auction - Comparative Information Statement
            </td>
        </tr>
        
        <!-- Auction Info Row -->
        <tr class="row1" style="height: 31.2pt;">
            <td class="cis-border" style="text-align: left; font-weight: bold;">
                &nbsp; Auction ID: {{ $auction->auction_id }}
            </td>
            <td class="cis-border" style="text-align: left; font-weight: bold;">
                Date: {{ date('d/m/Y', strtotime($auction->schedule_date)) }}
            </td>
            <td class="cis-border" style="text-align: left; font-weight: bold;">
                Time: {{ date('g:i A', strtotime($auction->schedule_start_time)) }} TO {{ date('g:i A', strtotime($auction->schedule_end_time)) }}
            </td>
            <td class="cis-border" style="text-align: left; font-weight: bold;">
                Branch/Unit: {{ $auction->branch_name }}
            </td>
            <td class="cis-border" colspan="{{ count($vendorBids) }}" style="text-align: left; font-weight: bold;">
                Status: {{ $status }}
            </td>
        </tr>
        
        <!-- Vendor Names Row -->
        <tr class="row2" style="height: 48.6pt;">
            <td class="cis-border" rowspan="2" style="text-align: center; font-weight: bold;">
                Product Details
            </td>
            <td class="cis-border" rowspan="2" style="text-align: center; font-weight: bold;">
                Specs
            </td>
            <td class="cis-border" rowspan="2" style="text-align: center; font-weight: bold;">
                Qty/UOM
            </td>
            <td class="cis-border" rowspan="2" style="text-align: center; font-weight: bold;">
                Start Price
            </td>
            @foreach ($vendorBids as $vendor)
                <td class="cis-border" colspan="1" style="text-align: center; font-weight: bold; color: #1F497D;">
                    {{ $vendor['name'] }}
                    <br>
                    (M: {{ (!empty($vendor['country_code']) ? '+' . $vendor['country_code'] . ' ' : '') . $vendor['mobile'] }})
                </td>
            @endforeach
        </tr>
        <!-- Vendor Currency Symbol Row -->
        <tr class="row3" style="height: 31.2pt;">
            @foreach ($vendorBids as $vendor)
                <td class="cis-border" style="text-align: center; font-weight: bold; color: #1F497D;">
                    @php $currency_symbol = get_currency_symbol($auction->currency); @endphp
                    Rate Per Unit ({{ $currency_symbol }})
                </td>
            @endforeach
        </tr>
        <!-- Product Rows -->
        @foreach ($products as $p)
            @php
                $max_price = 0;
                foreach ($vendorBids as $vendor) {
                    $price = $vendor['prices'][$p->id] ?? null;
                    if (!is_null($price) && $price > $max_price) {
                        $max_price = $price;
                    }
                }
            @endphp
            <tr class="row4" style="height: 19.2pt;">
                <td class="cis-border" style="text-align: center; font-weight: bold; color: #1F497D; background-color: #DBE5F1;">
                    {{ $p->product_name }}
                </td>
                <td class="cis-border" style="text-align: center;">
                    {{ $p->specs }}
                </td>
                <td class="cis-border" style="text-align: center;">
                    {{ $p->quantity }} {{ $p->uom_name }}
                </td>
                <td class="cis-border" style="text-align: center;">
                    {{ number_format($p->start_price, 2) }}
                </td>
                @foreach ($vendorBids as $vendor)
                    @php
                        $price = $vendor['prices'][$p->id] ?? null;
                        $is_max = (!is_null($price) && $price == $max_price);
                        $bg_color = $is_max ? 'background-color: yellow;' : '';
                    @endphp
                    <td class="cis-border" style="text-align: center; {{ $bg_color }}">
                        {{ $price ? number_format($price, 2) : '-' }}
                    </td>
                @endforeach
            </tr>
        @endforeach
        <!-- Total Row -->
        <tr class="row5" style="height: 16.36pt;">
            <td class="cis-border" colspan="4" style="text-align: right; font-weight: bold; background-color: #F2DBDB;">
                Total
            </td>
            @foreach ($vendorBids as $vendor)
                <td class="cis-border" style="text-align: center; background-color: #F2DBDB;">
                    {{ get_currency_symbol($auction->currency) }} {{ number_format($vendor['total'], 2) }}
                </td>
            @endforeach
        </tr>
        <!-- Remarks -->
        <tr class="row6" style="height: 16.36pt;">
            <td class="cis-border" style="text-align: center; font-weight: bold;">
                Remarks
            </td>
            <td class="cis-border" colspan="{{ count($vendorBids) + 3 }}">
                {{ !empty($auction->remarks) ? $auction->remarks : '-' }}
            </td>
        </tr>
        <!-- Price Basis -->
        <tr class="row7" style="height: 16.36pt;">
            <td class="cis-border" style="text-align: center; font-weight: bold;">
                Price Basis
            </td>
            <td class="cis-border" colspan="{{ count($vendorBids) + 3 }}">{{ !empty($auction->price_basis) ? $auction->price_basis : '-' }}
            </td>
        </tr>
        <!-- Payment Terms -->
        <tr class="row8" style="height: 16.36pt;">
            <td class="cis-border" style="text-align: center; font-weight: bold; background-color: #F2F2F2;">
                Payment Terms
            </td>
            <td class="cis-border" colspan="{{ count($vendorBids) + 3 }}" style="background-color: #F2F2F2;">
                {{ !empty($auction->payment_terms) ? $auction->payment_terms : '-' }}
            </td>
        </tr>
        <!-- Delivery Period -->
        <tr class="row9" style="height: 16.36pt;">
            <td class="cis-border" style="text-align: center; font-weight: bold;">
                Delivery Period (Days)
            </td>
            <td class="cis-border" colspan="{{ count($vendorBids) + 3 }}">
                {{ !empty($auction->delivery_period) ? $auction->delivery_period : '-' }}
            </td>
        </tr>
    </tbody>
</table>
