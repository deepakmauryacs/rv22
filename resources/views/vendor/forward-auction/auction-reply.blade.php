@extends('vendor.layouts.app_second', [
    'title' => 'Forward Auction',
    'sub_title' => ''
])

@section('content')

@php
    $auction = (array) $auction;
    $auction_start = strtotime($auction['schedule_date'] . ' ' . $auction['schedule_start_time']);
    $auction_end = strtotime($auction['schedule_date'] . ' ' . $auction['schedule_end_time']);
    $current_time = time();
    $isAuctionLive = ($current_time >= $auction_start && $current_time <= $auction_end);

    $products = DB::table('forward_auction_products as fap')
    ->select(
        'fap.*',
        'far.price as submitted_price',
        'uom.uom_name'
    )
    ->leftJoin('uoms as uom', 'uom.id', '=', 'fap.uom')
    ->join('forward_auction_vendors as fav', 'fav.auction_product_id', '=', 'fap.id')
    ->leftJoin('forward_auction_replies as far', function($join) use ($vendor_id) {
        $join->on('far.auction_product_id', '=', 'fap.id')
             ->where('far.vendor_id', '=', $vendor_id);
    })
    ->where('fap.auction_id', $auction_id)
    ->where('fav.vendor_id', $vendor_id)
    ->get()
    ->map(function($item) { return$item; });

    $showDocumentColumn = collect($products)->contains(function($product) {
        return !empty($product->file_attachment ?? null);
    });
@endphp
<style>
.live-badge {
    display: inline-flex;
    align-items: center;
    background: #ff1e00; /* bright red */
    border-radius: 22px;
    padding: 0 12px 0 10px;
    height: 20px;
    font-weight: bold;
    color: #fff;
    font-size: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    letter-spacing: 0.5px;
}

.live-badge .dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    background: #fff;
    border-radius: 50%;
    margin-right: 9px;
}
.form-control:disabled, .form-control[readonly] {
    background: #eae7e7;
    opacity: 1;
}
</style>
<!---Section Main-->
<main class="main main-inner-page flex-grow-1 py-2 px-md-3 px-1">
    <section class="container-fluid">
        <div class="d-flex align-items-center flex-wrap justify-content-between mr-auto flex py-2">
            <!-- Start Breadcrumb Here -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('vendor.forward-auction.index') }}">Forward Auction</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Forward Auction Details</li>
                </ol>
            </nav>
        </div>

        <!-- Auction Info -->
        <div class="rfq-vendor-listing">
            <div class="card shadow-none mb-3">
                <div class="card-body">
                    <ul>
                        @if($isAuctionLive)
                            <li>
                                <span class="live-badge">
                                    <span class="dot"></span>
                                    Live
                                </span>
                            </li>
                            <li>
                                <span class="fw-bold">Time Left:</span>
                                <span id="auction-timer" class="text-danger fw-bold"></span>
                            </li>
                        @endif

                        <li>
                            <span class="fw-bold">Auction No:</span>
                            <span>{{ $auction['auction_id'] }}</span>
                        </li>
                        <li>
                            <span class="fw-bold">Schedule Date:</span>
                            <span>{{ \Carbon\Carbon::parse($auction['schedule_date'])->format('d/m/Y') }}</span>
                        </li>
                        <li>
                            <span class="fw-bold">Schedule Time:</span>
                            <span>{{ date('g:i A', strtotime($auction['schedule_start_time'])) }} TO {{ date('g:i A', strtotime($auction['schedule_end_time'])) }}</span>
                        </li>
                        <li>
                            <span class="fw-bold">Buyer Name:</span>
                            <span>{{ $auction['buyer_name'] }}</span>
                        </li>
                        <li>
                            <span class="fw-bold">User Name:</span>
                            <span>{{ $auction['username'] }}</span>
                        </li>
                        <li>
                            <span class="fw-bold">Buyer Branch/Unit:</span>
                            <span>{{ $auction['branch_name'] }}</span>
                        </li>
                        <li>
                            <span class="fw-bold">Branch/Unit Address:</span>
                            <span>
                                {{ $auction['branch_address'] }}
                                @if($auction['branch_address'])
                                <button type="button" class="ra-btn ra-btn-link height-inherit text-black font-size-14"
                                    data-bs-toggle="tooltip" data-placement="top"
                                    data-bs-original-title="{{ $auction['branch_address'] }}">
                                    <span class="bi bi-info-circle-fill font-size-14"></span>
                                </button>
                                @endif
                            </span>
                        </li>
                        <li>
                            <a href="javascript:void(0)" onclick="location.reload()"
                                class="ra-btn ra-btn-outline-primary height-inherit py-1 px-2 font-size-10">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Products Form -->
        <form id="auctionReplyForm">
            @csrf
            <input type="hidden" name="auction_id" value="{{ $auction['auction_id'] }}">
            <input type="hidden" name="vendor_id" value="{{ $vendor_id }}">
            <input type="hidden" name="buyer_id" value="{{ $auction['buyer_id'] }}">
            <input type="hidden" name="buyer_user_id" value="{{ $auction['buyer_user_id'] }}">

            <div class="rfq-vendor-listing-product-form">
                @foreach($products as $index => $product)
                    <div class="card shadow-sm mb-4">
                        <div class="card-body font-size-12">
                            <div class="row mb-2">
                                <div class="col-12 mb-3">
                                    <label class="form-label mb-1 font-size-18">{{ $index + 1 }}. <span class="text-primary text-uppercase">{{ $product->product_name }}</span></label>
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold mb-1">Specs</label>
                                    @if(strlen($product->specs ?? '') > 30)
                                        <span class="mb-2 d-block" title="{{ $product->specs }}">
                                            {{ substr($product->specs, 0, 30) }}...
                                            <i class="bi bi-info-circle-fill py_custom_tooltip" aria-hidden="true"></i>
                                        </span>
                                    @else
                                        <span class="mb-2 d-block">{{ $product->specs }}</span>
                                    @endif
                                </div>

                                <div class="col-md-1 mb-2">
                                    <label class="form-label fw-bold mb-1">Quantity / UOM</label>
                                    <span class="mb-2 d-block">{{ $product->quantity }} {{ $product->uom_name }}</span>
                                </div>

                                <div class="col-md-1 mb-2">
                                    <label class="form-label fw-bold mb-1">Start Price ({{ $auction['currency'] ?? '₹' }})</label>
                                    <span class="mb-2 d-block">{{ number_format($product->start_price, 2) }}</span>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label class="form-label fw-bold mb-1">Min Bid Increment Amount</label>
                                    <span class="mb-2 d-block">{{ number_format($product->min_bid_increment_amount, 2) }}</span>
                                </div>

                                @if($showDocumentColumn)
                                <div class="col-md-1 mb-2">
                                    <label class="form-label fw-bold mb-1">Document</label>
                                    <span class="mb-2 d-block">
                                        @if(!empty($product->file_attachment))
                                            <a href="{{ asset('assets/uploads/auction_files/'.$product->file_attachment) }}" target="_blank">View</a>
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                                @endif

                                <div class="col-md-1 mb-2">
                                    <label class="form-label fw-bold mb-1">Rank</label>
                                    <span class="mb-2 d-block rank-display" data-product-id="{{ $product->id }}">-</span>
                                </div>

                                <div class="col-md-1 mb-2">
                                    <label class="form-label fw-bold mb-1">H1</label>
                                    <span class="mb-2 d-block l1-price" data-product-id="{{ $product->id }}">-</span>
                                </div>

                                <div class="col-md-2 mb-2 mt-3 mt-sm-0">
                                    <label class="form-label mb-1 fw-bold">Price ({{ $auction['currency'] ?? '₹' }})</label>
                                    <input type="number"
                                        step="0.01"
                                        class="form-control bid-price"
                                        name="bid_price[{{ $product->id }}]"
                                        data-start="{{ $product->start_price }}"
                                        data-prev="{{ $product->submitted_price }}"
                                        data-l1="{{ $product->l1_price ?? 0 }}"
                                        data-qty="{{ $product->quantity }}"
                                        data-min-increment="{{ $product->min_bid_increment_amount }}"
                                        data-target="total_price_{{ $product->id }}"
                                        data-product-id="{{ $product->id }}"
                                        min="{{ $product->start_price }}"
                                        value="{{ $product->submitted_price ?? '' }}"
                                        placeholder="Enter Price" {{ !$isAuctionLive ? 'disabled' : '' }}>
                                </div>
                                <div class="col-md-1 mb-2 mt-3 mt-sm-0">
                                    <label class="form-label mb-1 fw-bold">Total ({{ $auction['currency'] ?? '₹' }})</label>
                                    <input type="text"
                                        class="form-control"
                                        id="total_price_{{ $product->id }}"
                                        name="total_price[{{ $product->id }}]"
                                        readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Remarks -->
            <div>
                <div class="row">
                    <div class="col-12">
                        <label for="sellerRemarks" class="fw-bold">Remarks:</label>
                        <div>
                            <span class="form-group">
                                <textarea name="seller-remarks" id="sellerRemarks" rows="4" cols="4"
                                    class="form-control"
                                    placeholder="" readonly>{{ $auction['remarks'] }}</textarea>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Auction Extra Info -->
            <div class="product-option-filter mb-4">
                <div class="row gx-3 pt-3 justify-content-center align-items-center">
                    <div class="col-12 col-md-3 mt-4 mt-sm-4">
                        <label for="priceBasis" class="fw-bold text-black font-size-12 mb-1">Price Basis</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-geo-alt" aria-hidden="true"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control"
                                    id="priceBasis" placeholder="Price Basis" value="{{ $auction['price_basis'] }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mt-4 mt-sm-4">
                        <label for="paymentTerms" class="fw-bold text-black font-size-12 mb-1">Payment Terms</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-currency-rupee" aria-hidden="true"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control"
                                    id="paymentTerms" placeholder="Payment Terms" value="{{ $auction['payment_terms'] }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mt-4 mt-sm-4">
                        <label for="deliveryPeriodInDays" class="fw-bold text-black font-size-12 mb-1">Delivery Period (In Days)</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-calendar-date" aria-hidden="true"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control"
                                    id="deliveryPeriodInDays" placeholder="Delivery Period (In Days)"
                                    value="{{ $auction['delivery_period'] }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-3 mt-4 mt-sm-4">
                        <label for="updateCurrency" class="fw-bold text-black font-size-12 mb-1">Currency <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-currency-exchange" aria-hidden="true"></span>
                            </span>
                            <div class="form-floating">
                                <select class="form-select" id="updateCurrency" aria-label="Select" disabled>
                                    @foreach($currencies as $val)
                                        @php
                                            $currency_val = ($val->currency_symbol == "रु") ? 'NPR' : $val->currency_symbol;
                                            $selected = (strtoupper($currency_val) == strtoupper(trim($auction['currency'] ?? ''))) ? 'selected' : '';
                                        @endphp
                                        <option value="{{ $currency_val }}" data-symbol="{{ $currency_val }}" {{ $selected }}>
                                            {{ $val->currency_name }} ({{ $val->currency_symbol }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($isAuctionLive)
            <div class="text-end pb-4">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
            @endif

        </form>
    </section>
</main>

@if($isAuctionLive)
<script>

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {

    // Store original values on load
    $('input[name^="bid_price"]').each(function() {
        $(this).data('original', $(this).val().trim());
    });

    // Calculate total
    function calculateTotal(input) {
        const price = parseFloat($(input).val());
        const qty = parseFloat($(input).data('qty'));
        const targetId = $(input).data('target');
        if (!isNaN(price) && !isNaN(qty)) {
            const total = price * qty;
            $('#' + targetId).val(total.toFixed(2));
        } else {
            $('#' + targetId).val('');
        }
    }
    $('.bid-price').on('input', function() { calculateTotal(this); });
    $('.bid-price').each(function() { if ($(this).val()) calculateTotal(this); });

    // Form submit
    $('#auctionReplyForm').on('submit', function(e) {
        e.preventDefault();
        let hasValidInput = false;
        let allValid = true;
        let formHasChanges = false;
        let atLeastOnePriceEntered = false;
        let ajaxCalls = [];

        $('input[name^="bid_price"]').removeClass('is-invalid');

        $('input[name^="bid_price"]').each(function() {
            const $input = $(this);
            const val = $input.val().trim();
            const originalVal = $input.data('original');
            const productId = $input.data('product-id');
            const price = parseFloat(val);
            const startPrice = parseFloat($input.data('start'));
            const prevPrice = parseFloat($input.data('prev')) || null;
            const l1Price = parseFloat($input.data('l1')) || 0;
            const minIncrement = parseFloat($input.data('min-increment'));

            if (val !== '') atLeastOnePriceEntered = true;
            if (val !== originalVal) formHasChanges = true;
            if (val === '' || val === originalVal) return;

            if (!isNaN(price)) {
                hasValidInput = true;

                // Validation logic
                if (l1Price > 0) {
                    const minNext = l1Price + minIncrement;
                    if (price < minNext) {
                        $input.addClass('is-invalid');
                        toastr.error(`Bid ({{ $auction['currency'] ?? '₹' }}${price.toFixed(2)}) must be at least {{ $auction['currency'] ?? '₹' }}${minNext.toFixed(2)} (H1: {{ $auction['currency'] ?? '₹' }}${l1Price.toFixed(2)} + {{ $auction['currency'] ?? '₹' }}${minIncrement.toFixed(2)} increment)`, 'Invalid Bid');
                        allValid = false;
                        return;
                    }
                } else {
                    if (prevPrice === null && price < startPrice) {
                        $input.addClass('is-invalid');
                        toastr.error(`First bid ({{ $auction['currency'] ?? '₹' }}${price.toFixed(2)}) cannot be less than Start Price ({{ $auction['currency'] ?? '₹' }}${startPrice.toFixed(2)})`, 'Invalid Bid');
                        allValid = false;
                        return;
                    }
                    if (prevPrice !== null) {
                        const minNext = prevPrice + minIncrement;
                        if (price < minNext) {
                            $input.addClass('is-invalid');
                            toastr.error(`New bid ({{ $auction['currency'] ?? '₹' }}${price.toFixed(2)}) must be at least {{ $auction['currency'] ?? '₹' }}${minNext.toFixed(2)} (Prev: {{ $auction['currency'] ?? '₹' }}${prevPrice.toFixed(2)} + {{ $auction['currency'] ?? '₹' }}${minIncrement.toFixed(2)} increment)`, 'Invalid Bid');
                            allValid = false;
                            return;
                        }
                    }
                }

                // Push AJAX validation call
                ajaxCalls.push(
                    $.ajax({
                        url: "{{ route('vendor.forward-auction.check-bid-rank') }}",
                        method: "POST",
                        data: {
                            auction_id: "{{ $auction['auction_id'] }}",
                            product_id: productId,
                            bid_price: price,
                            vendor_id: "{{ $vendor_id }}"
                        },
                        dataType: 'json'
                    }).then(function(res) {
                        if (!res.success && res.status === 3 && res.is_duplicate) {
                            $input.addClass('is-invalid');
                            toastr.error(res.message);
                            allValid = false;
                        }
                    }).catch(function() {
                        toastr.error('Server error during duplicate check', 'Error');
                        allValid = false;
                    })
                );
            }
        });

        if (!atLeastOnePriceEntered) {
            toastr.error('Please enter price for at least one product.');
            return;
        }
        if (!formHasChanges) {
            toastr.info('No changes detected in bid prices.');
            return;
        }

        // Wait for all AJAX validations
        $.when.apply($, ajaxCalls).then(function() {
            if (!allValid) return;

            let formData = new FormData($('#auctionReplyForm')[0]);
            $.ajax({
                url: "{{ route('vendor.forward-auction.submit-reply') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success('Bid submitted successfully!');
                        $('input[name^="bid_price"]').each(function() {
                            const val = $(this).val().trim();
                            $(this).data('original', val);
                            if (val !== '') {
                                $(this).data('prev', val);
                            }
                        });
                        setTimeout(function() { window.location.reload(); }, 300);
                    } else {
                        if (response.hasAuctionEnded) {
                            alert(response.message);
                            setTimeout(function() { window.location.reload(); }, 300);
                        } else {
                            toastr.error(response.message);
                        }
                    }
                },
                error: function(xhr) {
                    toastr.error('Server error during submission: ' + xhr.statusText);
                }
            });
        });
    });

    // Validate and calculate on blur
    $('.bid-price').on('blur', function () {
        calculateTotal(this);
        const $input = $(this);
        const val = $input.val().trim();
        if (val === '') return;

        const price = parseFloat(val);
        const startPrice = parseFloat($input.data('start'));
        const prevPrice = parseFloat($input.data('prev')) || null;
        const l1Price = parseFloat($input.data('l1')) || 0;
        const minIncrement = parseFloat($input.data('min-increment'));
        const productId = $input.data('product-id');

        $input.removeClass('is-invalid');
        if (l1Price > 0) {
            const minNext = l1Price + minIncrement;
            if (price < minNext) {
                $input.addClass('is-invalid');
                toastr.error(`Bid ({{ $auction['currency'] ?? '₹' }}${price.toFixed(2)}) must be at least {{ $auction['currency'] ?? '₹' }}${minNext.toFixed(2)} (H1: {{ $auction['currency'] ?? '₹' }}${l1Price.toFixed(2)} + {{ $auction['currency'] ?? '₹' }}${minIncrement.toFixed(2)} increment)`, 'Invalid Bid');
            }
        } else {
            if (prevPrice === null) {
                if (price < startPrice) {
                    $input.addClass('is-invalid');
                    toastr.error(`First bid ({{ $auction['currency'] ?? '₹' }}${price.toFixed(2)}) cannot be less than Start Price ({{ $auction['currency'] ?? '₹' }}${startPrice.toFixed(2)})`, 'Invalid Bid');
                }
            } else {
                const minNext = prevPrice + minIncrement;
                if (price < minNext) {
                    $input.addClass('is-invalid');
                    toastr.error(`New bid ({{ $auction['currency'] ?? '₹' }}${price.toFixed(2)}) must be at least {{ $auction['currency'] ?? '₹' }}${minNext.toFixed(2)} (Prev: {{ $auction['currency'] ?? '₹' }}${prevPrice.toFixed(2)} + {{ $auction['currency'] ?? '₹' }}${minIncrement.toFixed(2)} increment)`, 'Invalid Bid');
                }
            }
        }
        // Backend duplicate check
        $.ajax({
            url: "{{ route('vendor.forward-auction.check-bid-rank') }}",
            method: "POST",
            data: {
                product_id: productId,
                bid_price: price,
                auction_id: "{{ $auction['auction_id'] }}",
                vendor_id: "{{ $vendor_id }}"
            },
            dataType: 'json',
            success: function(result) {
                if (!result.success && result.status === 3 && result.is_duplicate) {
                    $input.addClass('is-invalid');
                    toastr.error(result.message);
                }
            }
        });
    });

});

const auctionEndTime = new Date("{{ $auction['schedule_date'] . ' ' . $auction['schedule_end_time'] }}").getTime();
function startCountdown(endTime) {
    function updateTimer() {
        const now = new Date().getTime();
        const distance = endTime - now;
        if (distance <= 0) {
            document.getElementById("auction-timer").innerText = "Auction Ended";
            window.location.reload();
            clearInterval(timerInterval);
            return;
        }
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        document.getElementById("auction-timer").innerText = `${hours}h ${minutes}m ${seconds}s`;
    }
    updateTimer();
    const timerInterval = setInterval(updateTimer, 1000);
}
startCountdown(auctionEndTime);

function fetchLiveRanks() {
    $.ajax({
        url: "{{ route('vendor.forward-auction.get-live-ranks') }}",
        type: "POST",
        data: {
            auction_id: "{{ $auction['auction_id'] }}",
            vendor_id: "{{ $vendor_id }}"
        },
        dataType: 'json',
        success: function (response) {
            if (response.status && response.is_forcestop === '1') {
                alert("Auction has ended.");
                setTimeout(function() { window.location.reload(); }, 300);
                return;
            }
            if (response.status && response.ranks) {
                for (const productId in response.ranks) {
                    const productRank = response.ranks[productId].rank;
                    const l1Price = parseFloat(response.ranks[productId].l1_price).toFixed(2);
                    $(`.rank-display[data-product-id="${productId}"]`).text(productRank).attr('data-rank', productRank);
                    $(`.l1-price[data-product-id="${productId}"]`).text(l1Price);
                    $(`.bid-price[data-product-id="${productId}"]`).attr('data-l1', l1Price);
                }
            }
        },
        error: function () { console.warn('Failed to fetch ranks'); }
    });
}
$(document).ready(function () {
    fetchLiveRanks();
    setInterval(fetchLiveRanks, 30000);
});
</script>
@endif

@endsection
