<table class="table ra-table ra-table-stripped ">
    <thead>
        <tr>
            <th scope="col" class="text-nowrap">Auction No</th>
            <th scope="col" class="text-nowrap">Product(s)</th>
            <th scope="col" class="text-nowrap">Buyer Name</th>
            <th scope="col" class="text-nowrap">Username</th>
            <th scope="col" class="text-nowrap">Auction Date</th>
            <th scope="col" class="text-nowrap">Auction Time</th>
            <th scope="col" class="text-nowrap">Auction Status</th>
            <th scope="col" class="text-nowrap">Action</th>
        </tr>
    </thead>
    <tbody>
        @php
          date_default_timezone_set('Asia/Kolkata');
          $now = time();
        @endphp
        @forelse($results as $auction)
        <tr>
            <td class="align-middle">{{ $auction->auction_id }}</td>
            <td class="align-middle">
                @php
                    $productText = $auction->products;
                @endphp
                @if(strlen($productText) > 50)
                    <span title="{{ $productText }}">
                        {{ \Illuminate\Support\Str::limit($productText, 50, '...') }}
                        <i class="bi bi-info-circle text-muted"></i>
                    </span>
                @else
                    {{ $productText ?: '-' }}
                @endif
            </td>
            <td class="align-middle">{{ $auction->buyer_name ?? '-' }}</td>
            <td class="align-middle">{{ $auction->buyer_user_name ?? '-' }}</td>
            <td class="align-middle">
                {{ $auction->schedule_date ? \Carbon\Carbon::parse($auction->schedule_date)->format('d/m/Y') : '-' }}
            </td>
            <td class="align-middle">
                {{ $auction->schedule_start_time ? \Carbon\Carbon::parse($auction->schedule_start_time)->format('h:i A') : '-' }}
                to
                {{ $auction->schedule_end_time ? \Carbon\Carbon::parse($auction->schedule_end_time)->format('h:i A') : '-' }}
            </td>
            <td class="align-middle">
                @php
                    

                    $startTimestamp = strtotime($auction->schedule_date . ' ' . $auction->schedule_start_time);
                    $endTimestamp = strtotime($auction->schedule_date . ' ' . $auction->schedule_end_time);

                    if ($now < $startTimestamp) {
                        $status = 'Upcoming'; $badge = 'badge-success';
                    } elseif ($now >= $startTimestamp && $now <= $endTimestamp) {
                        $status = 'Live'; $badge = 'badge-warning';
                    } else {
                        $status = 'Closed'; $badge = 'badge-danger';
                    }

                @endphp
                <span class="badge {{ $badge }}">{{ $status }}</span>
            </td>
            <td class="align-middle">
                <a href="{{ route('vendor.forward-auction.view', $auction->auction_id) }}"
                   class="ra-btn small-btn ra-btn-outline-primary-light width-inherit d-inline-flex">
                    View
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center py-5">
                <img src="{{ asset('images/no-data.svg') }}" alt="No Results" style="max-width:140px;">
                <div class="mt-3 text-muted">No Forward Auctions found.</div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
<x-paginationwithlength :paginator="$results" />
