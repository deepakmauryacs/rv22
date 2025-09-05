<table class="product-listing-table w-100">
    <thead>
        <tr>
            <th>RFQ No.</th>
            <th>RFQ Date</th>
            <th>Product Name</th>
            <th>PRN Number</th>
            <th>Username</th>
            <th>Branch/Unit</th>
            <th>Auction Date</th>
            <th>Auction Time</th>
            <th>Auction Status</th>
            <th>RFQ Action</th>
        </tr>
    </thead>
    <tbody>
        @php
            use Carbon\Carbon;

            // Status badges
            $rfq_status_badge = [
                1 => "<span class='badge badge-success'>Active</span>",
                2 => "<span class='badge badge-warning'>Scheduled</span>",
                3 => "<span class='badge badge-danger'>Closed</span>",
            ];

            // Primary action button text
            $rfq_button = [
                1 => "Live",
                2 => "View",
                3 => "View",
            ];
        @endphp

        @forelse ($results as $result)
            @php
                // --- Product names ---
                $products = [];
                foreach ($result->rfqProducts as $variant) {
                    $products[] = $variant->masterProduct?->product_name;
                }
                $products_name = implode(', ', $products);

                // --- Auction fields via relation (if present) ---
                $hasAuction = !empty($result->rfq_auction);

                $auctionDateRaw = $hasAuction ? $result->rfq_auction->auction_date : null;
                $auctionStart   = $hasAuction ? $result->rfq_auction->auction_start_time : null;
                $auctionEnd     = $hasAuction ? $result->rfq_auction->auction_end_time   : null;

                // Display strings
                $auctionDate = ($auctionDateRaw) ? date('d/m/Y', strtotime($auctionDateRaw)) : '—';
                $auctionTime = ($auctionStart && $auctionEnd)
                    ? date('h:i A', strtotime($auctionStart)) . ' To ' . date('h:i A', strtotime($auctionEnd))
                    : '—';

                // Status logic (Active / Scheduled / Closed) based on today's date & current time
                $current_status = null; // 1=Active, 2=Scheduled, 3=Closed
                $close_btn = 'disabled'; // default disabled

                if ($hasAuction && $auctionDateRaw && $auctionStart && $auctionEnd) {
                    $today_date   = date('Y-m-d');
                    $current_time = date('H:i:s');

                    if ($auctionDateRaw == $today_date) {
                        if ($current_time >= $auctionStart && $current_time <= $auctionEnd) {
                            $current_status = 1; // Active
                            $close_btn = "disabled";
                        } elseif ($current_time < $auctionStart) {
                            $current_status = 2; // Scheduled
                            $close_btn = ""; // allow closing only when scheduled
                        } else {
                            $current_status = 3; // Closed
                            $close_btn = "disabled";
                        }
                    } elseif ($auctionDateRaw < $today_date) {
                        $current_status = 3; // Closed
                        $close_btn = "disabled";
                    } else {
                        $current_status = 2; // Scheduled
                        $close_btn = ""; // allow closing only when scheduled
                    }
                } else {
                    // No auction: show dashes and keep Close disabled
                    $current_status = null;
                    $close_btn = "disabled";
                }

                $cis_url = route('buyer.auction.cis-sheet', ['rfq_id' => $result->rfq_id]);
            @endphp

            <tr>
                <td class="clickable-td">
                    <a href="{{ route('buyer.rfq.details', $result->rfq_id) }}?page=active-rfq">
                        {{ $result->rfq_id }}
                    </a>
                </td>

                <td class="clickable-td">
                    <a href="{{ route('buyer.rfq.details', $result->rfq_id) }}?page=active-rfq">
                        {{ date('d/m/Y', strtotime($result->created_at)) }}
                    </a>
                </td>

                <td>
                    <div class="d-flex">
                        <span class="rfq-product-name text-truncate">
                            {{ $products_name }}
                        </span>
                        @if (strlen($products_name) > 50)
                            <button class="btn btn-link text-black border-0 p-0 font-size-12 bi bi-info-circle-fill ms-1"
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                title="{{ $products_name }}"></button>
                        @endif
                    </div>
                </td>

                <td>{{ $result->prn_no }}</td>
                <td>{{ $result->buyerUser->name }}</td>
                <td>{{ $result->buyerBranch->name }}</td>

                {{-- Auction columns --}}
                <td>{{ $auctionDate }}</td>
                <td>{{ $auctionTime }}</td>
                <td>{!! $current_status ? ($rfq_status_badge[$current_status] ?? '—') : '—' !!}</td>

                <td>
                    <div class="rfq-table-btn-group">
                        <a class="ra-btn small-btn ra-btn-primary text-white d-inline-block {{ $result->buyer_rfq_status == 1 ? 'disabled' : ''}}"
                           href="{{ $cis_url }}">
                            {{ $rfq_button[$current_status] ?? 'View' }}
                        </a>

                        <a href="javascript:void(0)"
                           class="ra-btn small-btn ra-btn-outline-danger  close-auction {{ $close_btn }}"
                           data-id="{{ $result->rfq_id }}">
                           Close
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10">No RFQ found.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<x-paginationwithlength :paginator="$results" />
