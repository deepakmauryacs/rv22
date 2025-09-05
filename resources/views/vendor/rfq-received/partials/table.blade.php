<table class="table ra-table ra-table-stripped ">
    <thead>
        <th scope="col" class="text-nowrap">
            RFQ No
        </th>
        <th scope="col" class="text-nowrap">
            RFQ Date
        </th>
        <th scope="col" class="text-nowrap">
            Product
        </th>
        <th scope="col" class="text-nowrap">
            Buyer Name
        </th>
        <th scope="col" class="text-nowrap">
            Username
        </th>
        <th scope="col" class="text-nowrap">
            RFQ Status
        </th>
        <th scope="col" class="text-nowrap">
            Action
        </th>
    </thead>
    <tbody>
        @php
        $rfq_status=[
                        '1'=>'<span class="badge badge-success text-start">RFQ Received</span>',
                        '4'=>'<span class="badge badge-secondary text-start">Counter Offer Received</span>',
                        '5'=>'<span class="badge badge-primary text-start">Order Confirmed</span>',
                        '6'=>'<span class="badge badge-pink text-start">Counter Offer Sent</span>',
                        '7'=>'<span class="badge badge-pink text-start">Quotation Received</span>',
                        '8'=>'<span class="badge badge-danger text-start">Closed</span>',
                        '9'=>'<span class="badge badge-primary text-start">Order Confirmed</span>',
                        '10'=>'<span class="badge badge-primary text-start">Order Confirmed</span>'
                    ];
        $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp

        @forelse ($results as $result)
        <tr>
            @php
                $productNames = $result->rfqProducts
                    ->pluck('masterProduct.product_name')
                    ->filter()
                    ->values();
                $fullText = $productNames->join(', ');
                $words = str_word_count($fullText, 1);
                $shortText = implode(' ', array_slice($words, 0, 10));
                $isTruncated = count($words) > 10;
            
                $product = $result->rfqProducts->first()?->masterProduct;

                $btn_name = 'Reply';
                $btn_class = 'ra-btn-primary';
                $btn_link = route('vendor.rfq.reply', ['rfq_id' => $result->rfq_id]);
                if(in_array($result->rfqVendors->first()->vendor_status, [5, 10]) && isset($orders_id[$result->rfq_id])) {
                    $btn_link = route('vendor.rfq_order.show', $orders_id[$result->rfq_id])."?page_name=rfq_received";
                }
                switch ($result->rfqVendors->first()->vendor_status) {
                    case '1':
                        $btn_name = 'Reply';
                        $btn_class = 'ra-btn-primary';
                        break;
                    case '4':
                    case '6':
                    case '7':
                    case '9':
                    case '10':
                        $btn_name = 'Edit';
                        $btn_class = 'ra-btn-outline-primary-light';
                        break;
                    case '5':
                    case '8':
                        $btn_name = 'View';
                        $btn_class = 'ra-btn-outline-primary-light';
                        break;
                    
                    default:
                        $btn_name = 'Reply';
                        $btn_class = 'ra-btn-primary';
                        break;
                }
            @endphp

            <td class="align-middle">{{ $result->rfq_id }}</td>
            <td class="align-middle">{{ date('d/m/Y', strtotime($result->created_at)) }}</td>
            <td>
                @if($product)
                    {{ $product->division->division_name ?? '-' }} >
                    {{ $product->category->category_name ?? '-' }}<br>
                    {{ $product->product_name }} 
                @else
                    -
                @endif
            </td>
            <td class="align-middle">{{ $result->buyer->legal_name ?? '-' }}</td>
            <td class="align-middle">{{ $result->buyer->users->name ?? '-' }}</td>
            <td class="align-middle">{!! $rfq_status[($result->rfqVendors->first()->vendor_status ?? null)] ?? '-' !!} </td>
            <td>
               <a class="ra-btn {{$btn_class}} py-2 height-inherit" href="{{$btn_link}}">{{$btn_name}}</a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">No Data Available in Table</td>
        </tr>
        @endforelse
    </tbody>
</table>

<x-paginationwithlength :paginator="$results" />