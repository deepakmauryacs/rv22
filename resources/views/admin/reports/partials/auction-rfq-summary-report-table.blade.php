<div class="table-responsive">
    <table class="product_listing_table">
        <thead>
            <tr>
                <th>RFQ No</th>
                <th>Auction Date</th>
                <th>Auction Time</th>
                <th>Buyer Name</th>
                <th>Products</th>
                <th>Vendor Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Status</th>
                <th>Is Participated</th>
                <th>Order Confirmed</th>
            </tr>
        </thead>
        <tbody>
        @php
            $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp
        @forelse ($results as $result)
            <tr>
                <td>{{ $result->rfq_no ?? '' }}</td>
                <td>{{ date('d/m/Y', strtotime($result->auction_date)) ?? '' }}</td>
                <td>{{ date('h:i A', strtotime($result->auction_start_time)) }} To {{ date('h:i A', strtotime($result->auction_end_time)) }}</td>
                <td>{{ $result->buyer_legal_name ?? '' }}</td>
                <td>{{ $result->product_name ?? '' }}</td>
                <td>{{ $result->vendor_legal_name ?? '' }}</td>
                <td>{{ $result->email ?? '' }}</td>
                <td>{{ $result->mobile ?? '' }}</td>
                <td>{{ $result->vendor_user_status == 1 ? 'Active' : ($result->vendor_user_status == 2 ? 'Inactive' : '') }}</td>
                <td>{{ $result->is_participated ?? '-' }}</td>
                <td>{{ $result->order_confirmed ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="12" class="text-center">No data available in table</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<x-paginationwithlength :paginator="$results" />
