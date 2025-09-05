<table class="product-listing-table w-100">
    <thead>
        <tr>
            <th>Auction No.</th>
            <th>Product Name</th>
            <th>Username</th>
            <th>Branch/Unit</th>
            <th>Auction Date</th>
            <th>Auction Time</th>
            <th>Auction Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    @forelse($results as $auction)
        <tr id="auction-row-{{ $auction->id }}">
            <td>{{ $auction->auction_id }}</td>
            <td>{{ $auction->product_names }}</td>
            <td>{{ $auction->buyer_user_name ?? '-' }}</td>
            <td>{{ $auction->branch_name ?? $auction->buyer_branch }}</td>
            <td>{{ \Carbon\Carbon::parse($auction->schedule_date)->format('d-m-Y') }}</td>

            @php
                $startTime = \Carbon\Carbon::createFromFormat('H:i:s', $auction->schedule_start_time)->format('h:i A');
                $endTime = \Carbon\Carbon::createFromFormat('H:i:s', $auction->schedule_end_time)->format('h:i A');

                $startTimestamp = strtotime($auction->schedule_date . ' ' . $auction->schedule_start_time);
                $endTimestamp = strtotime($auction->schedule_date . ' ' . $auction->schedule_end_time);
                $now = time();

                if ($now < $startTimestamp) {
                    $status = 'Upcoming';
                    $badge = 'badge-upcoming';
                } elseif ($now >= $startTimestamp && $now <= $endTimestamp) {
                    $status = 'Live';
                    $badge = 'badge-live';
                } else {
                    $status = 'Closed';
                    $badge = 'badge-closed';
                }
            @endphp

            <td>{{ $startTime }} To {{ $endTime }}</td>
            <td>
                <span class="status-badge {{ $badge }}">
                    <span class="status-dot"></span>{{ $status }}
                </span>
            </td>
            <td>
                <div class="rfq-table-btn-group">
                <a href="{{ route('buyer.forward-auction.show', $auction->auction_id) }}"
                   class="ra-btn small-btn ra-btn-outline-primary-light width-inherit d-inline-flex">
                   View
                </a>

                @if ($status === 'Upcoming')

                    <a href="{{ route('buyer.forward-auction.edit', $auction->id ) }}" class="ra-btn small-btn ra-btn-outline-primary-light width-inherit d-inline-flex">Edit</a>

                    <button type="button"
                            class="ra-btn small-btn ra-btn-outline-danger width-inherit d-inline-flex delete-auction-btn"
                            data-auction-id="{{ $auction->auction_id }}"
                            data-row-id="auction-row-{{ $auction->id }}">
                        Delete
                    </button>


                @elseif ($status === 'Closed')
                    <button class="ra-btn small-btn ra-btn-outline-danger disabled" disabled>Closed</button>
                @endif
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="text-center">No auctions found</td>
        </tr>
    @endforelse
    </tbody>
</table>

<x-paginationwithlength :paginator="$results" />