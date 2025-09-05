@extends('buyer.layouts.app', ['title' => 'Forward Auction - CIS'])

@section('content')
<div class="bg-white">
    @include('buyer.layouts.sidebar-menu')
</div>
@php
    $now = time();
    $auctionStart = strtotime($auction->schedule_date . ' ' . $auction->schedule_start_time);
    $auctionEnd = strtotime($auction->schedule_date . ' ' . $auction->schedule_end_time);
    $isLive = ($now >= $auctionStart && $now <= $auctionEnd);
@endphp
<style>
.live-badge {
    display: inline-flex;
    align-items: center;
    background: #ff2626;
    color: #fff;
    font-weight: 600;
    border-radius: 20px;
    padding: 2px 16px 2px 10px;
    font-size: 1rem;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.live-dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    background: #fff;
    border-radius: 50%;
    margin-right: 8px;
    box-shadow: 0 0 6px rgba(255,255,255,0.5);
    border: 2px solid #ff2626;
}

.live-text {
    position: relative;
    top: 1px;
}
</style>
<main class="main flex-grow-1">
    <div class="container-fluid">
        <!---CIS Statement section-->
        <section class="card rounded">
            <div class="card-header bg-white border-0">
                <div class="row gy-3 justify-content-between align-items-center py-3 px-0 px-md-3 mb-30">
                    <div class="col-12 col-lg-auto flex-grow-1 order-2 order-lg-1">
                        <h1 class="text-primary-blue font-size-18 fw-bold">
                            Forward Auction - Comparative Information Statement
                        </h1>
                    </div>
                    <div class="col-12 col-lg-auto order-1 order-lg-2">
                        <div class="row gx-3 gy-2 align-items-center justify-content-center justify-content-lg-end">
                            <div class="col-auto">
                                <a href="{{ route('buyer.forward-auction.export-cis', $auction->auction_id) }}" class="ra-btn ra-btn-success px-2 font-size-11">
                                    <span class="bi bi-download font-size-12" aria-hidden="true"></span>
                                    Export
                                </a>

                            </div>
                            @if($isLive)
                            <div class="col-auto">
                                <a href="javascript:void(0);" class="ra-btn ra-btn-danger px-2 font-size-11"
                                   onclick="forceStopAuction('{{ $auction->auction_id }}')">
                                    <span class="bi bi-stop-circle font-size-12" aria-hidden="true"></span>
                                    Stop Auction
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="forward-auction-cis cis-info pb-2 px-0 px-md-3 d-sm-flex">
                    <div class="d-flex cis-info-left align-items-center">
                        <ul class="mb-0 d-flex flex-wrap align-items-center" style="gap: 12px;">
                            @if($isLive)
                                <li>
                                    <span class="live-badge">
                                        <span class="live-dot"></span>
                                        <span class="live-text">Live</span>
                                    </span>
                                </li>
                                <li>
                                    <span id="countdown-timer" class="text-success ms-2 fw-bold"></span>
                                </li>
                            @endif

                           
                            <li>
                                <strong>Auction ID.: </strong>{{ $auction->auction_id }}
                            </li>
                            <li>
                                <strong> Date: </strong> {{ \Carbon\Carbon::parse($auction->schedule_date)->format('d/m/Y') }}
                            </li>
                            <li>
                                <strong> Time: </strong> {{ \Carbon\Carbon::parse($auction->schedule_start_time)->format('g:i A') }}
                                TO {{ \Carbon\Carbon::parse($auction->schedule_end_time)->format('g:i A') }}
                                
                            </li>
                            <li>
                                <strong> Branch/Unit: </strong> {{ $auction->branch_name }}
                            </li>
                            <li>
                                <a href="javascript:void(0);"
                                   class="ra-btn btn-outline-primary ra-btn-outline-primary d-inline-flex text-uppercase text-nowrap font-size-11"
                                   onclick="location.reload();">
                                    <span class="bi bi-arrow-clockwise font-size-11" aria-hidden="true"></span>
                                    Refresh
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </div>


                <div class="cis-details py-3 px-0 px-md-3">
                    <div class="row g-0 gy-5">
                        <!-- Product Table (Left) -->
                        <div class="col-7 px-1">
                            <div class="table-responsive">
                                <table class="table table-bordered border-dark forward-action-cis-table">
                                    <thead>
                                        <tr class="h-140">
                                            <th>Product</th>
                                            <th>Specs</th>
                                            <th>Quantity/UOM</th>
                                            <th>Start Price</th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($products as $p)
                                        <tr>
                                            <td>
                                                <div style="height: 25px;">{{ \Illuminate\Support\Str::limit($p->product_name, 20, '...') }}
                                                @if(strlen($p->product_name) > 40)
                                                <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip" title="{{ $p->product_name }}"></i>
                                                @endif</div>
                                            </td>
                                            <td>
                                                <div style="height: 25px;">{{ \Illuminate\Support\Str::limit($p->specs, 20, '...') }}
                                                @if(strlen($p->specs) > 20)
                                                <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip" title="{{ $p->specs }}"></i>
                                                @endif</div>
                                            </td>
                                            <td><div style="height: 25px;">{{ $p->quantity }} {{ $p->uom_name }}</div></td>
                                            <td><div style="height: 25px;">{{ number_format($p->start_price, 2) }}</div></td>
                                        </tr>
                                        @endforeach
                                        <tr>
                                            <td colspan="4" style="text-align: right;"><div style="height: 25px;">Total</div></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Vendor Table (Right) -->
                        <div class="col-5 px-1">
                            <div class="table-responsive" style="overflow-x: auto; min-height: 1px;">
                                <table class="table table-bordered border-dark" style="white-space: nowrap;">
                                    <thead>
                                        <tr class="h-140">
                                            @foreach($vendorBids as $vendor)
                                            <th style="vertical-align: middle; padding:0; border-color: #2c2a29 !important; min-width: 220px;">
                                                <div style="height:140px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
                                                    <span style="font-weight: 600;">{{ $vendor['name'] }}</span><br>
                                                    <span style="font-weight: 500;">(M: {{ (!empty($vendor['country_code']) ? '+' . $vendor['country_code'] . ' ' : '') . $vendor['mobile'] }})</span>
                                                </div>
                                            </th>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach($vendorBids as $vendor)
                                            <th><div style="height: 25px;display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">Rate Per Unit({{ $auction->currency }})</div></th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($products as $p)
                                        <tr>
                                            @foreach($vendorBids as $vendor)
                                            @php $price = $vendor['prices'][$p->id] ?? null; @endphp
                                            <td style="text-align: center;">
                                                <div style="height: 25px;">{{ $price ? number_format($price, 2) : '-' }}</div>
                                            </td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            @foreach($vendorBids as $vendor)
                                            <td style="text-align: center;"><div style="height: 25px;">{{ $auction->currency }}{{ number_format($vendor['total'], 2) }}</div></td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <!-- Auction Remarks and Info -->
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered border-dark">
                                    <tbody>
                                        <tr>
                                            <th class="w-200">Remarks</th>
                                            <td>{{ !empty($auction->remarks) ? $auction->remarks : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Price Basis</th>
                                            <td>{{ !empty($auction->price_basis) ? $auction->price_basis : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="w-200">Payment Terms</th>
                                            <td>{{ !empty($auction->payment_terms) ? $auction->payment_terms : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="w-200">Delivery Period (In Days)</th>
                                            <td>{{ !empty($auction->delivery_period) ? $auction->delivery_period : '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

@if($isLive)
<script>
    const auctionEndTime = new Date("{{ $auction->schedule_date . ' ' . $auction->schedule_end_time }}").getTime();
    function updateCountdown() {
        const now = new Date().getTime();
        const distance = auctionEndTime - now;
        if (distance <= 0) {
            document.getElementById("countdown-timer").innerHTML = "Auction Ended";
            clearInterval(countdownInterval);
            return;
        }
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        document.getElementById("countdown-timer").innerHTML =
            "Time Left: " +
            (hours < 10 ? "0" + hours : hours) + "h " +
            (minutes < 10 ? "0" + minutes : minutes) + "m " +
            (seconds < 10 ? "0" + seconds : seconds) + "s";
    }
    updateCountdown();
    const countdownInterval = setInterval(updateCountdown, 1000);
</script>
@endif

<script>
function forceStopAuction(auction_id) {
    if (confirm("Are you sure you want to force stop this auction?")) {
        fetch("{{ url('buyer/forward-auction/force_stop') }}", {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
            body: JSON.stringify({ auction_id: auction_id })
        })
        .then(response => response.json())
        .then(response => {
            if (response.status === 'success') {
                toastr.success(response.message);
                setTimeout(function() { window.location.reload(); }, 1000);
            } else {
                toastr.error(response.message);
            }
        });
    }
}
</script>
@endsection
