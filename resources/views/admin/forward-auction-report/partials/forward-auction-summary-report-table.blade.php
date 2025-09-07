<div class="table-responsive">
    <table class="product_listing_table">
        <thead>
            <tr>
                <th>S.No</th>
                <th>Auction ID</th>
                <th>Product Details</th>
                <th>Buyer Name</th>
                <th>Vendor Name</th>
                <th>Start Date & Time</th>
                <th>Participated</th>
            </tr>
        </thead>
        <tbody>
            @php
                $i = ($results->currentPage() - 1) * $results->perPage() + 1;
            @endphp
            @forelse ($results as $row)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>{{ $row->auction_id }}</td>
                    <td>{{ $row->products }}</td>
                    <td>{{ $row->buyer_name }}</td>
                    <td>{{ $row->vendor_name }}</td>
                    <td>{{ date('d/m/Y', strtotime($row->schedule_date)) }} {{ date('h:i A', strtotime($row->schedule_start_time)) }} To {{ date('h:i A', strtotime($row->schedule_end_time)) }}</td>
                    <td>{{ $row->participated }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No data available in table</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<x-paginationwithlength :paginator="$results" />
