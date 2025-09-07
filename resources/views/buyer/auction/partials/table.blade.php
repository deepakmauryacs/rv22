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

            // Helpers for flexible parsing
            $tz = 'Asia/Kolkata';
            $parseDate = function ($v) use ($tz) {
                if (!$v) return null;
                $v = trim($v);
                foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'] as $fmt) {
                    try { return Carbon::createFromFormat($fmt, $v, $tz)->startOfDay(); } catch (\Throwable $e) {}
                }
                try { return Carbon::parse($v, $tz)->startOfDay(); } catch (\Throwable $e) { return null; }
            };
            $parseTime = function ($v) use ($tz) {
                if (!$v) return null;
                $v = strtoupper(trim($v));
                foreach (['H:i:s', 'H:i', 'h:i A', 'g:i A', 'h:iA', 'g:iA'] as $fmt) {
                    try { return Carbon::createFromFormat($fmt, $v, $tz); } catch (\Throwable $e) {}
                }
                try { return Carbon::parse($v, $tz); } catch (\Throwable $e) { return null; }
            };
        @endphp

        @forelse ($results as $result)
            @php
                // --- Product names ---
                $products = [];
                foreach ($result->rfqProducts as $variant) {
                    $products[] = $variant->masterProduct?->product_name;
                }
                $products_name = implode(', ', array_filter($products));

                // --- Auction fields via relation (if present) ---
                $hasAuction = !empty($result->rfq_auction);
                $auctionDateRaw = $hasAuction ? $result->rfq_auction->auction_date : null;
                $auctionStartRaw = $hasAuction ? $result->rfq_auction->auction_start_time : null;
                $auctionEndRaw   = $hasAuction ? $result->rfq_auction->auction_end_time   : null;

                // Parse date/time robustly
                $d  = $parseDate($auctionDateRaw);
                $t1 = $parseTime($auctionStartRaw);
                $t2 = $parseTime($auctionEndRaw);

                // Build display strings from parsed values
                $auctionDate = $d ? $d->format('d/m/Y') : '—';
                $auctionTime = ($t1 && $t2)
                    ? $t1->format('h:i A').' To '.$t2->format('h:i A')
                    : '—';

                // Status logic (Active / Scheduled / Closed)
                $current_status = null;  // 1=Active, 2=Scheduled, 3=Closed
                $show_close = false;     // only show Close when scheduled
                if ($hasAuction && $d && $t1 && $t2) {
                    $start = $d->copy()->setTime($t1->hour, $t1->minute, $t1->second);
                    $end   = $d->copy()->setTime($t2->hour, $t2->minute, $t2->second);
                    // Cross-midnight window support
                    if ($end->lessThanOrEqualTo($start)) {
                        $end->addDay();
                    }

                    $now = Carbon::now($tz);
                    if ($now->betweenIncluded($start, $end)) {
                        $current_status = 1; // Active
                    } elseif ($now->lt($start)) {
                        $current_status = 2; // Scheduled
                        $show_close = true; // allow closing only when scheduled
                    } else {
                        $current_status = 3; // Closed
                    }
                } else {
                    // Not enough data to determine — show dashes
                    $current_status = null;
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
                        {{ \Carbon\Carbon::parse($result->created_at, $tz)->format('d/m/Y') }}
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
                           class="ra-btn small-btn ra-btn-outline-danger close-auction disabled"
                           aria-disabled="true"
                           data-rfq="{{ $result->rfq_id }}">
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
