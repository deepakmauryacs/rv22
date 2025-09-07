<table class="table ra-table ra-table-stripped ">
    <thead>
        <tr>
            <th scope="col" class="text-nowrap">RFQ No</th>
            <th scope="col" class="text-nowrap">RFQ Date</th>
            <th scope="col" class="text-nowrap">Product</th>
            <th scope="col" class="text-nowrap">Buyer Name</th>
            <th scope="col" class="text-nowrap">Username</th>
            <th scope="col" class="text-nowrap">Auction Date</th>
            <th scope="col" class="text-nowrap">Auction Time</th>
            <th scope="col" class="text-nowrap">Auction Status</th>
            <th scope="col" class="text-nowrap">Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($results as $result)
        @php
            // --- Derive auction fields safely ---
            $auction = $result->rfq_auction ?? null;
            $rfqCreatedAt = $result->rfq_auction?->rfq?->created_at;
            $rfqDate = $rfqCreatedAt ? date('d/m/Y', strtotime($rfqCreatedAt)) : '-';
            $auction_date = $auction->auction_date ?? null;            // 'Y-m-d'
            $auction_start = $auction->auction_start_time ?? null;     // 'H:i:s'
            $auction_end   = $auction->auction_end_time ?? null;       // 'H:i:s'

            // --- Current status (1=Active, 2=Scheduled, 3=Closed) ---
            $tz = 'Asia/Kolkata';
            $today = \Carbon\Carbon::today($tz)->toDateString();
            $now   = \Carbon\Carbon::now($tz)->format('H:i:s');

            $current_status = null; // default when missing data
            if ($auction_date) {
                if ($auction_date == $today) {
                    if ($now >= $auction_start && $now <= $auction_end) {
                        $current_status = 1; // Active
                    } elseif ($now < $auction_start) {
                        $current_status = 2; // Scheduled
                    } else {
                        $current_status = 3; // Closed
                    }
                } elseif ($auction_date < $today) {
                    $current_status = 3; // Closed
                } else {
                    $current_status = 2; // Scheduled
                }
            }

            // --- Badge + Button maps (mirror CI) ---
            $rfq_status_badges = [
                1 => ['class' => 'badge badge-success', 'label' => 'Active'],
                2 => ['class' => 'badge badge-warning', 'label' => 'Scheduled'],
                3 => ['class' => 'badge badge-danger',  'label' => 'Closed'],
            ];
            $rfq_button_text = [ 1 => 'Live', 2 => 'View', 3 => 'View' ];

            // --- Fallbacks if we couldn't compute status ---
            $statusHtml = '<span class="text-muted">-</span>';
            $btnText    = 'View';

            if (!is_null($current_status)) {
                $statusMeta = $rfq_status_badges[$current_status];
                $statusHtml = "<span class=\"{$statusMeta['class']}\">{$statusMeta['label']}</span>";
                $btnText    = $rfq_button_text[$current_status];
            }

            // --- Target URL for action ---
            // Adjust route name if different in your app.
            $actionUrl = route('vendor.live-auction.offer', $result->rfq_no);

        @endphp

        <tr>
            <td class="align-middle">{{ $result->rfq_no }}</td>
            <td class="align-middle">{{ $rfqDate }}</td>
            <td>
                @php
                    $variant = $result->rfq_auction->rfq_auction_variant->first();
                    $product = $variant?->product;
                @endphp
                @if($product)
                    {{ $product->division->division_name ?? '-' }} >
                    {{ $product->category->category_name ?? '-' }}<br>
                    {{ $product->product_name }}
                @else
                    -
                @endif
            </td>
            <td class="align-middle">{{ $result->rfq_auction->buyer->legal_name ?? '-' }}</td>
            <td class="align-middle">{{ $result->rfq_auction->buyer->users->name ?? '-' }}</td>
            <td class="align-middle">{{ $auction_date ? date('d/m/Y', strtotime($auction_date)) : '-' }}</td>
            <td class="align-middle">
                @if($auction_start && $auction_end)
                    {{ date('h:i A', strtotime($auction_start)) }} To {{ date('h:i A', strtotime($auction_end)) }}
                @else
                    -
                @endif
            </td>

            {{-- === STATUS (CI style) === --}}
            <td class="align-middle">{!! $statusHtml !!}</td>

            {{-- === ACTION BUTTON (CI style: Live/View) === --}}
            <td class="align-middle">
                <a class="ra-btn ra-btn-primary py-2 height-inherit"
                   href="{{ $actionUrl }}"
                   @if(is_null($current_status)) aria-disabled="true" tabindex="-1" @endif>
                    <span>{{ $btnText }}</span>
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center">No Data Available in Table</td>
        </tr>
        @endforelse

    </tbody>
</table>

<x-paginationwithlength :paginator="$results" />