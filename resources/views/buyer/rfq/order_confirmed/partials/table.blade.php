<table class="product-listing-table w-100">
    <thead>
        <tr> 
            <th class="w-300 align-bottom">ORDER NO</th>
            <th class="w-300 align-bottom">BUYER ORDER NUMBER</th>
            <th class="w-300 align-bottom">RFQ No</th>
            <th class="w-300 align-bottom">ORDER DATE</th>
            <th class="w-300 align-bottom">RFQ DATE</th>
            <th class="w-300 align-bottom">BRANCH/UNIT</th>
            <th class="w-300 align-bottom">PRODUCT</th>
            <th class="w-300 align-bottom">USER</th>
            <th class="w-300 align-bottom">VENDOR</th>
            <th class="w-300 align-bottom">ORDER VALUE</th>
            <th class="w-300 align-bottom">STATUS</th>
        </tr>
    </thead>
    <tbody>
        @php
            $order_status=['1'=>'','2'=>'<span class="rfq-status Auction-Completed">Cancelled</span>','3'=>''];
            $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp

        @forelse ($results as $result)
        <tr>
            <td>
                <a href="{{ route('buyer.rfq.order-confirmed.view', $result->id) }}">
                {{ $result->po_number ?? '-' }}
                </a>
            </td>
            <td>
                <a href="{{ route('buyer.rfq.order-confirmed.view', $result->id) }}">
                {{ $result->buyer_order_number }}
                </a>
            </td>
            <td>
                <a href="{{ route('buyer.rfq.order-confirmed.view', $result->id) }}">
                {{ $result->rfq_id ?? '-' }}
                </a>
            </td>
            <td>
                <a href="{{ route('buyer.rfq.order-confirmed.view', $result->id) }}">
                {{ date('d/m/Y', strtotime($result->created_at)) }}
                </a>
            </td>
            <td>
                <a href="{{ route('buyer.rfq.order-confirmed.view', $result->id) }}">
                {{ date('d/m/Y', strtotime($result->rfq->created_at)) }}
                </a>
            </td>
            <td>
                <a href="{{ route('buyer.rfq.order-confirmed.view', $result->id) }}">
                {{ !empty($result->rfq->buyer_branch)?getbuyerBranchById($result->rfq->buyer_branch)->name:'-' }}
                </a>
            </td>
            <td>
                <a href="{{ route('buyer.rfq.order-confirmed.view', $result->id) }}">
                {{ $result->order_variants->pluck('product.product_name')->filter()->unique()->join(', ') ?? '-' }}
                </a>
            </td>
            <td>
                <a href="{{ route('buyer.rfq.order-confirmed.view', $result->id) }}">
                {{ $result->order_confirmed_by->name ?? '-' }}
                </a>
            </td>
            <td>
                <a href="{{ route('buyer.rfq.order-confirmed.view', $result->id) }}">
                {{ $result->vendor->legal_name ?? '-' }}
                </a>
            </td>
            <td>
                <a href="{{ route('buyer.rfq.order-confirmed.view', $result->id) }}">
                {{$result->vendor_currency}}{{ $result->order_total_amount ? IND_money_format($result->order_total_amount) : '0' }}
                </a>
            </td>
            <td>
                <a href="{{ route('buyer.rfq.order-confirmed.view', $result->id) }}">
                {!! $order_status[$result->order_status] !!}
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center">No Data Available in Table</td>
        </tr>
        @endforelse
    </tbody>
</table>

<x-paginationwithlength :paginator="$results" />