@extends('vendor.layouts.app_second',['title'=>'RFQ Details','sub_title'=>''])
@section('content')
<style>
.form-control.form-control-price-basis { width: 100% !important; }
.form-control.form-control-payment-terms { width: 100% !important; }
.form-control.form-control-delivery-period { width: 100% !important; }
.form-control.form-control-price-validity { width: 100% !important; }
.form-control.form-control-dispatch-branch { width: 100% !important; }
.form-select.form-select-currency { width: 100% !important; }

/* --- Countdown styles --- */
.countdown{
    display:inline-block;
    font-size:1.05rem;
    padding:6px 10px;
    background:#f8f9fa;
    border:1px solid #dee2e6;
    border-radius:6px;
}
.live-text{
    font-weight:700;
    color:#dc3545;
    margin-right:8px;
    animation:blink 1s step-start infinite;
}
@keyframes blink{50%{opacity:0;}}
.table-danger td{ background: #fff5f5 !important; }
</style>

@php
  $is_international_vendor      = is_national();
  $is_international_buyer_check = is_national_buyer($rfq->buyer_id);
  $normal_product_data          = common_rfq_auction_data($rfq->rfq_id);
@endphp
@php
function IND_amount_format($amount) {
    $amount = (string)$amount;
    $main_amount = explode('.', $amount);
    $amount = $main_amount[0];
    $lastThree = substr($amount, -3);
    $otherNumbers = substr($amount, 0, -3);
    if ($otherNumbers != '') { $lastThree = ',' . $lastThree; }
    $res = preg_replace('/\B(?=(\d{2})+(?!\d))/', ",", $otherNumbers) . $lastThree;
    return count($main_amount) > 1 ? $res . '.' . $main_amount[1] : $res . '.00';
}
@endphp

<main class="main main-inner-page flex-grow-1 py-2 px-md-3 px-1">
    <section class="container-fluid">
        <div class="d-flex align-items-center flex-wrap justify-content-between mr-auto flex py-2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('vendor.rfq.live-auction.index') }}">Live Auction RFQ</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> RFQ Auction Details</li>
                </ol>
            </nav>
        </div>
        @php
        $branch = getbuyerBranchById($rfq->buyer_branch);
        @endphp

        <!-- RFQ Details Card -->
        <section class="rfq-vendor-listing">
            <div class="card shadow-none mb-3">
                <div class="card-body">
                    <ul>
                        <!-- Live + Timer -->
                        <li id="live-text-div" style="display:none;">
                            <span class="tit">
                                <div id="countdown" class="countdown" style="display:none;">
                                    <span id="live-text" class="live-text" style="display:none;">Live</span>
                                    <span id="timer"></span>
                                </div>
                            </span>
                        </li>

                        <li><span class="fw-bold">RFQ No:</span> <span>{{ $rfq->rfq_id }}</span></li>

                        <li><span class="fw-bold">RFQ Date:</span>
                            <span>{{ \Carbon\Carbon::parse($rfq->created_at)->format('d/m/Y') }}</span>
                        </li>

                        <li><span class="fw-bold">PRN Number:</span> <span>{{ $rfq->prn_no ?? '-' }}</span></li>

                        <li><span class="fw-bold">Buyer Name:</span>
                            <span>{{ $rfq->buyer_legal_name ?? '-' }}</span>
                        </li>

                        <li><span class="fw-bold">User Name:</span>
                            <span>{{ $rfq->buyer_user_name ?? '-' }}</span>
                        </li>

                        <li><span class="fw-bold">Branch Name:</span> <span>{!!  $branch->name ?? '-' !!}</span></li>

                        <li>
                            <span class="fw-bold">Branch Address:</span>
                            <span>
                                {{ Str::limit($branch->address ?? '-', 30) }}
                                @if(!empty($branch->address))
                                <button type="button" class="ra-btn ra-btn-link height-inherit text-black font-size-14"
                                    data-bs-toggle="tooltip" data-bs-original-title="{!! $branch->address !!}">
                                    <span class="bi bi-info-circle-fill font-size-14"></span>
                                </button>
                                @endif
                            </span>
                        </li>

                        <li><span class="fw-bold"><b class="text-primary">RFQ Terms -</b></span></li>

                        <li><span class="fw-bold">Price Basis:</span>
                            <span>{{ $rfq->buyer_price_basis ?? '-' }}</span>
                        </li>

                        <li><span class="fw-bold">Payment Terms:</span>
                            <span>{{ $rfq->buyer_pay_term ?? '-' }}</span>
                        </li>

                        <li><span class="fw-bold">Delivery Period:</span>
                            <span>{{ $rfq->buyer_delivery_period ?? '-' }} Days</span>
                        </li>

                        @php
                            $auctionDate = $auction_date ? \Carbon\Carbon::parse($auction_date)->format('d/m/Y') : '-';
                            $auctionStart = $auction_start_time ? \Carbon\Carbon::parse($auction_start_time)->format('h:i A') : '';
                            $auctionEnd = $auction_end_time ? \Carbon\Carbon::parse($auction_end_time)->format('h:i A') : '';
                        @endphp

                        <li>
                            <span class="fw-bold">Auction Date & Time:</span>
                            <span class="fw-bold" style="color: red;">
                                {{ $auctionDate }} ({{ $auctionStart }} to {{ $auctionEnd }})
                            </span>
                        </li>

                        <li>
                            <span class="tit">
                                <a href="javascript:void(0)" class="btn-rfq btn-rfq-white py-1 px-2" onclick="location.reload();">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                </a>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <form id="rfq-counter-form">
         <!-- Product Table -->
         <section class="rfq-vendor-listing-product-form">
            <div class="card shadow-none mb-3">
                <div class="card-body card-vendor-list-right-panel toggle-table-wrapper">
                    @foreach($products as $index => $product)
                    <div class="d-flex justify-content-between mb-30">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-vendor">
                                <li class="breadcrumb-item"><a href="#">{{ $index + 1 }}. {{ $product->division_name }}</a></li>
                                <li class="breadcrumb-item"><a href="#">{{ $product->category_name }}</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $product->product_name }}</li>
                            </ol>
                        </nav>
                    </div>

                    <div class="table-responsive table-product toggle-table-content">
                        <table class="table table-product-list table-d-block-mobile">
                            <thead>
                                <tr>
                                    <td>#</td>
                                    <th class="text-start" style="width: 30%;">
                                         Specification
                                    </th>
                                    <th class="text-center">Size</th>

                                    <th width="400" style="text-align: right !important;">
                                      Specs
                                      <i class="bi bi-info-circle-fill"
                                         data-bs-toggle="tooltip"
                                         data-bs-placement="top"
                                         title="If you want to change/add the specs, write here."></i>
                                    </th>

                                    <th class="text-center">Quantity/UOM</th>
                                    <th class="text-center" style="width: 12%;">Price Per Unit (<span class="currency-symbol"></span>)</th>
                                   
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                  $productVariants = $variants[$product->product_id] ?? []; 
                                @endphp
                                @foreach($productVariants as $vIndex => $variant)
                                <tr class="table-tr">
                                    <td>{{ $vIndex + 1 }}</td>
                                    <td>{{ $variant->specification }}</td>
                                    <td class="text-center">
                                        @php $sizeStr = strip_tags($variant->size); @endphp
                                        @if(strlen($sizeStr) > 5)
                                            {!!  mb_substr($sizeStr, 0, 5) !!}
                                            <button type="button" class="btn btn-link p-0 m-0 align-baseline" data-bs-toggle="tooltip" data-bs-placement="top" title="{!!  $sizeStr !!}">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                        @else
                                            {!! $variant->size !!} 
                                        @endif
                                    </td>

                                    <td>
                                        <input type="text" name="vendor_spec[{{ $variant->id }}]"
                                            id="vendor_spec_{{ $variant->id }}"
                                            class="form-control form-control-sm specs-trigger"
                                            value="{{ $variant->latest_vend_specs ?? '' }}"
                                            placeholder="Enter Specs"
                                            data-bs-toggle="modal" data-bs-target="#submitSpecification"
                                            data-target-input="vendor_spec_{{ $variant->id }}">
                                    </td>

                                    <td class="text-center">{{ $variant->quantity }} {{ getUOMName($variant->uom) }} </td>
                                   
                                  
                                    <td class="text-center"> {{ $variant->auction_start_price !== null ? number_format($variant->auction_start_price, 2) : '-' }}</td>

                                   
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endforeach

                   
                </div>
            </div>
         </section>
          @php
            $total_bid_price = 0.0;

            foreach ($products as $product) {
                $variantsForProduct = $variants[$product->product_id] ?? [];
                foreach ($variantsForProduct as $variant) {
                    $qty   = (float)($variant->quantity ?? 0);
                    $price = (float)($variant->auction_start_price ?? 0);
                    $total_bid_price += $qty * $price;
                }
            }
          @endphp

        <div class="row mt-4">
           <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md">
                                        <label class="mb-2" style="color: #015294;"><strong>Total Aggregate Value - Start Price </strong>(<span class="currency_symbol_total"></span>)</label>
                                        <div id="totalBidPrice" style="color: #015294;">{{ number_format($total_bid_price, 2) }}</div>
                                        <input type="hidden" name="total_bid_price" id="total_bid_price" value="{{ number_format($total_bid_price, 2) }}">
                                        <input type="hidden" name="start_price" id="start_price" value="{{ number_format($total_bid_price, 2) }}">
                                    </div>
                                    <div class="col-md">
                                        <label class="mb-2"><strong>L1 Price</strong>(<span class="currency-symbol"></span>)</label>
                                        <div id="l1Price">-</div>
                                    </div>
                                    <div class="col-md">
                                        <label class="mb-2"><strong>Rank</strong></label>
                                        <div id="yourRank">-</div>
                                    </div>
                                    <div class="col-md">
                                        <label class="mb-2"><strong>Min Bid Decrement (%)</strong></label>
                                        <div id="minBidDecrement">2</div>
                                        <input type="hidden" name="min_bid_decrement" id="min_bid_decrement" value="2">
                                    </div>

                                    <div class="col-md">
                                        <label class="mb-2"><strong>Last Price</strong>(<span class="currency-symbol"></span>)</label>
                                        <div id="last_price"> {{ number_format((float)($last_total_price ?? 0), 2, '.', '') }}</div>
                                    </div>

                                    
                                   <div class="col-md">
                                        <label class="mb-2" for="PriceInput"><strong>Enter your price based on the total order value</strong>(<span class="currency_price_input"></span>)</label>
                                        <input type="text" class="form-control" name="total_price" id="PriceInput" placeholder="Enter your bid"
                                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                            onblur="handlePriceBlur(this)">
                                    </div>


                                </div>

                                <!-- Note Line -->
                                <div class="mt-3 text-danger fw-bold" style="font-size: 12px;padding: 10px 0px 10px 0px;color: red !important;">
                                    NOTE: THE BIDDING IS ON BASIS OF TOTAL ORDER VALUE — PLEASE ENTER YOUR BID ACCORDINGLY. <br> SUBMIT YOUR BID ONLY IF YOU HAVE ACCEPTED ALL TERMS AND WILL BE SUPPLYING ALL ITEMS IN THIS AUCTION. <br> THE BIDDING IS BASED ON THE TOTAL ORDER VALUE, WHICH IS THE SUM OF QUANTITY MULTIPLIED BY THE RATE (OF ALL ITEMS ).
                                </div>
                            </div>
                        </div>
                    </div>
        </div>

         <!-- Bottom Control Section -->
         <section class="product-option-filter">
            <div class="card">
                <div class="card-body">
                    <div class="row gx-3 gy-4 pt-3 justify-content-center align-items-center">
                        <div class="col-12 col-sm-auto col-xxl-2">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-geo-alt" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control form-control-price-basis" id="priceBasis" name="vendor_price_basis" 
                                        placeholder="Price Basis" value="{{ $normal_product_data->vendor_price_basis ?? $rfq->buyer_price_basis ?? '' }}">
                                    <label for="priceBasis">Price Basis <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <span class="text-danger p_price_basis"></span>
                        </div>
                        <div class="col-12 col-sm-auto col-xxl-2">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-currency-rupee" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control form-control-payment-terms" id="paymentTerms" name="vendor_payment_terms" 
                                        placeholder="Payment Terms" value="{{ !empty($normal_product_data->vendor_payment_terms) ? $normal_product_data->vendor_payment_terms : ($rfq->buyer_pay_term ?? '') }}">
                                    <label for="paymentTerms">Payment Terms <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <span class="text-danger p_payment_terms"></span>
                        </div>
                        <div class="col-12 col-sm-auto col-xxl-2 delivery-period-width">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-calendar-date" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control form-control-delivery-period"
                                        id="deliveryPeriodInDays" name="vendor_delivery_period" placeholder="Delivery Period (In Days)" value="{{ !empty($normal_product_data->vendor_delivery_period) ? $normal_product_data->vendor_delivery_period : ($rfq->buyer_delivery_period ?? '') }}">
                                    <label for="deliveryPeriodInDays">Delivery Period (In Days) <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <span class="text-danger p_delivery_date"></span>
                        </div>
                        <div class="col-12 col-sm-auto col-xxl-2">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-calendar-date" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control form-control-price-validity"
                                        id="priceValidityInDays" name="vendor_price_validity" placeholder="Price Validity (In Days)" value="{{ $normal_product_data->vendor_price_validity ?? '' }}">
                                    <label for="priceValidityInDays">Price Validity (In Days)</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto col-xxl-2">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-geo-alt" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <select class="form-select form-control-dispatch-branch" id="vendorDispatchBranch" name="vendor_dispatch_branch">
                                        @if (count($branches) > 1)
                                            <option value="">Select</option>
                                        @endif
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->branch_id }}"
                                                {{ ($normal_product_data->vendor_dispatch_branch ?? '') == $branch->branch_id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="vendorDispatchBranch">Dispatch Branch <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <span class="text-danger vendor_dispatch_branch"></span>
                        </div>

                        @php
                            $is_disabled = ($is_international_vendor == '1' && $is_international_buyer_check == '1');
                        @endphp

                        <div class="col-12 col-sm-auto flex-xxl-grow-1">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-currency-exchange" aria-hidden="true"></i>
                                </span>
                                <div class="form-floating">
                                    <select class="form-select form-select-currency globle-field-changes" 
                                            id="updateCurrency" 
                                            name="vendor_currency"
                                            {{ $is_disabled ? 'disabled' : '' }}
                                            aria-label="Select">
                                        @if (!$is_disabled)
                                            <option value="">Select</option>
                                        @endif
                                        @foreach($vendor_currency ?? [] as $val)
                                            @php
                                                if ($val->currency_name == '') continue;
                                                $currency_val    = ($val->currency_symbol == 'रु') ? 'NPR' : $val->currency_symbol;
                                                $currency_symbol = ($val->currency_symbol == 'रु') ? 'NPR' : $val->currency_symbol;
                                                $selected        = ($currency_val == ($normal_product_data->vendor_currency ?? '')) ? 'selected' : '';
                                            @endphp
                                            <option value="{{ $currency_val }}" 
                                                    data-symbol="{{ $currency_symbol }}" 
                                                    {{ $selected }}>
                                                {{ $val->currency_name }} ({{ $val->currency_symbol }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($is_disabled)
                                        <input type="hidden" name="vendor_currency" value="₹">
                                    @endif
                                    <label for="updateCurrency">Currency <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <span class="text-danger vendor-currency-error"></span>
                        </div>

                    </div>
                    <input type="hidden" name="rfq_id" value="{{ $rfq->rfq_id }}">
                    

                    <?php if($current_status == 1) { ?>
                     <div class="row pt-3 gx-3 gy-3 justify-content-center align-items-center">
                        <div class="col-auto">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#sendMessage"
                                class="ra-btn ra-btn-sm px-3 ra-btn-outline-primary">
                            <i class="bi bi-send" aria-hidden="true"></i>
                            Send Message
                        </button>
                    </div>
                    <div class="col-12 col-sm-auto text-center">
                        <button type="button" class="ra-btn ra-btn-sm px-3 ra-btn-primary send-quote-btn" onclick="submitRfqCounterData(this,'quote')">
                            <i class="bi bi-check-lg" aria-hidden="true"></i>
                            Send Quote
                        </button>
                    </div>
                     </div>
                    <?php } ?>

                </div>
            </div>
         </section>
        </form>
    </section>
</main>

<!-- Modal: Specification -->
<div class="modal fade" id="submitSpecification" tabindex="-1" aria-labelledby="submitSpecificationLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header justify-content-between bg-graident text-white px-4">
                <h2 class="modal-title font-size-13" id="submitSpecificationLabel">
                    <span class="bi bi-pencil" aria-hidden="true"></span> View/Update Specs
                </h2>
                <button type="button" class="btn btn-link p-0 font-size-14 text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span class="bi bi-x-lg" aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <textarea class="form-control specifications-textarea" oninput="limitText(this, 500)" id="specificationsTextarea" rows="8"></textarea>
                </div>
                <div class="text-center">
                    <button type="button" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 submit-specification">Update</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Send Message -->
<div class="modal fade" id="sendMessage" tabindex="-1" aria-labelledby="sendMessageLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header justify-content-between bg-graident text-white px-4">
                <h2 class="modal-title font-size-13" id="sendMessageLabel">
                    <span class="bi bi-pencil" aria-hidden="true"></span> Send Message
                </h2>
                <button type="button" class="btn btn-link p-0 font-size-14 text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span class="bi bi-x-lg" aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <input type="text" value="{{ $rfq->rfq_id }}" name="subject" class="form-control" readonly placeholder="Subject">
                </div>
                <div class="mb-3">
                    <textarea name="send-msg" class="form-control specifications-textarea" rows="8" placeholder="Write your message here..."></textarea>
                </div>
                <div class="mb-3">
                    <div class="simple-file-upload">
                        <input type="file" class="real-file-input" style="display: none;">
                        <div class="file-display-box form-control text-start font-size-12 text-dark" role="button" data-bs-toggle="tooltip" data-bs-placement="top">
                            Upload file
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="text-end">
                    <button type="button" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11">Send</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ====================== JS ====================== --}}
<script>
"use strict";

// ---------- bootstrap tooltips (bs5) ----------
(function initTooltips(){
  const sel = '[data-bs-toggle="tooltip"],[data-toggle="tooltip"]';
  const els = [].slice.call(document.querySelectorAll(sel));
  els.forEach(el => {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
      // eslint-disable-next-line no-new
      new bootstrap.Tooltip(el);
    }
  });
})();
</script>

<script>
// Disable entire form if auction is not Active
$(function() {
  const status = Number(@json($current_status));
  if (status === 2 || status === 3) {
    $('input, select, textarea, button').prop('disabled', true);
    $('[data-bs-toggle="tooltip"],[data-toggle="tooltip"]').tooltip('disable');
  }
});


// Currency symbol sync (extended)
function updateCurrencySymbols() {
    const dropdown = document.getElementById('updateCurrency');
    const symbol = dropdown ? (dropdown.selectedOptions[0]?.dataset.symbol || '₹') : '₹';
    document.querySelectorAll('.currency-symbol, .currency_symbol_total, .currency_price_input')
        .forEach(el => { el.textContent = symbol; });
}
document.addEventListener('DOMContentLoaded', () => {
    updateCurrencySymbols();
    const dropdown = document.getElementById('updateCurrency');
    if (dropdown) dropdown.addEventListener('change', updateCurrencySymbols);
});

// INR format (client)
function IND_amount_format(amount) {
    const parts = String(amount).split('.');
    const integer = parts[0];
    const lastThree = integer.slice(-3);
    const others = integer.slice(0, -3);
    const formatted = others.replace(/\B(?=(\d{2})+(?!\d))/g, ',') + (others ? ',' : '') + lastThree;
    return parts.length > 1 ? `${formatted}.${parts[1]}` : `${formatted}.00`;
}
</script>

<script>
// ====== Total price validation ======
const showBidError = msg => {
    if (window.toastr) {
        toastr.error(msg);
    } else {
        alert(msg);
    }
};

const validateTotalPrice = price => {
    const totalBid  = parseFloat($('#total_bid_price').val().replace(/,/g, '')) || 0;
    const lastPrice = parseFloat($('#last_price').text().replace(/,/g, '')) || 0;
    const minDec    = parseFloat($('#min_bid_decrement').val()) || 2;
    const base      = lastPrice > 0 ? lastPrice : totalBid;
    const isFirstBid = lastPrice <= 0;

    if (base <= 0) return true;

    if (price > base) {
        const context = lastPrice > 0 ? 'the last price' : 'the Total Aggregate Value - Start Price';
        showBidError(`Your bid cannot exceed ${context}. Maximum allowed bid is ${base.toFixed(2)}.`);
        $('#PriceInput').val('');
        return false;
    }

    if (!isFirstBid) {
        const maxAllowed = parseFloat((base * (1 - minDec / 100)).toFixed(2));
        if (price > maxAllowed) {
            showBidError(`Your bid must be at least ${minDec}% lower than the last price. Maximum allowed bid is ${maxAllowed.toFixed(2)}.`);
            $('#PriceInput').val('');
            return false;
        }
    }

    const minAllowed = parseFloat((base * (1 - (minDec + 10) / 100)).toFixed(2));
    const minContext = lastPrice > 0 ? 'the last price' : 'the Total Aggregate Value - Start Price';
    if (price < minAllowed) {
        showBidError(`Your bid cannot be more than ${minDec + 10}% lower than ${minContext}. Minimum allowed bid is ${minAllowed.toFixed(2)}.`);
        $('#PriceInput').val('');
        return false;
    }

    return true;
};

const handlePriceBlur = el => {
    if (!el.value) return;
    const price = parseFloat(el.value.replace(/,/g, ''));
    if (isNaN(price)) {
        el.value = '';
        return;
    }
    el.value = price.toFixed(2);
    if (!validateTotalPrice(price)) el.value = '';
};
</script>

<script>
// ===== Spec modal wiring =====
let currentSpecInputId = null;
$(document).on('click', '.specs-trigger', function () {
    currentSpecInputId = $(this).data('target-input');
    $('#specificationsTextarea').val($(this).val());
});
$('.submit-specification').on('click', () => {
    const newSpec = $('#specificationsTextarea').val();
    if (currentSpecInputId) {
        $('#' + currentSpecInputId).val(newSpec);
    }
    $('#submitSpecification').modal('hide');
});
const limitText = (field, maxChars) => {
    if (field.value.length > maxChars) {
        field.value = field.value.substring(0, maxChars);
    }
};
</script>

<script>
// ====== Per-variant inline error helpers (kept for compatibility) ======
const showVariantInlineError = (variantId, msg) => {
  const $row = $(".rank[data-variant-id='" + variantId + "']").closest("tr");
  if (!$row.length) return;

  $row.addClass("table-danger");
  setTimeout(() => $row.removeClass("table-danger"), 2000);

  const $priceTd = $row.find("input.variant-price").closest("td");
  let $err = $priceTd.find(".variant-inline-error");
  if ($err.length === 0) {
    $err = $('<div class="variant-inline-error text-danger mt-1" style="font-size:12px;"></div>');
    $priceTd.append($err);
  }
  $err.text(msg);
};
const clearVariantInlineErrors = () => {
  $(".variant-inline-error").remove();
};
const handleVariantErrors = errMap => {
  clearVariantInlineErrors();
  let first = null;
  $.each(errMap, (vid, message) => {
    if (!first) first = message;
    showVariantInlineError(vid, message);
    if (window.toastr) toastr.error(message);
  });
  if (first && window.toastr) {
    toastr.error("Validation failed. Please fix the highlighted rows.");
  }
};
</script>

<script>
// ====== Submit (AJAX JSON) — TOTAL ORDER VALUE ONLY ======
function submitRfqCounterData(_this, action) {
    const $btn = $(_this);
    if ($btn.data('busy')) return false;              // block double-clicks
    $btn.prop('disabled', true).data('busy', true);

    // ---- read fields (using your Blade IDs) ----
    const price_basis     = $("#priceBasis").val()?.trim();
    const payment_terms   = $("#paymentTerms").val()?.trim();
    const delivery_date   = $("#deliveryPeriodInDays").val()?.trim();
    const dispatch_branch = $("#vendorDispatchBranch").val();

    const $currencyDD         = $("#updateCurrency");
    const isCurrencyDisabled  = $currencyDD.prop('disabled');
    const vendor_currency     = $currencyDD.val();

    // ---- inline validations ----
    let hasError = false;
    if (!price_basis)     { $(".p_price_basis").text("Price Basis is Required"); hasError = true; } else { $(".p_price_basis").text(""); }
    if (!payment_terms)   { $(".p_payment_terms").text("Payment Terms is Required"); hasError = true; } else { $(".p_payment_terms").text(""); }
    if (!delivery_date)   { $(".p_delivery_date").text("Delivery Period is Required"); hasError = true; } else { $(".p_delivery_date").text(""); }
    if (!dispatch_branch) { $(".vendor_dispatch_branch").text("Dispatch Branch is Required"); hasError = true; } else { $(".vendor_dispatch_branch").text(""); }

    if (!isCurrencyDisabled && !vendor_currency) {
        $(".vendor-currency-error").text("Vendor Currency is Required");
        hasError = true;
    } else {
        $(".vendor-currency-error").text("");
    }

    // total order value (lot-wise)
    const tpRaw = ($("#PriceInput").val() || "").replace(/,/g, "").trim();
    const totalPrice = tpRaw === "" ? NaN : Number(tpRaw);
    if (!isFinite(totalPrice) || totalPrice <= 0) {
        if (window.toastr) toastr.error("Please enter a valid Total Order Value (your bid).");
        return resetBtn();
    }
    $("#PriceInput").val(totalPrice.toFixed(2));

    if (!validateTotalPrice(totalPrice)) {
        return resetBtn();
    }

    if (hasError) {
        if (window.toastr) toastr.error("Please fill all the Mandatory fields marked with *");
        return resetBtn();
    }

    // ---- build FormData from your form ----
    const form = document.getElementById("rfq-counter-form");
    const fd = new FormData(form); // specs inputs already included

    // ensure required keys (names your controller expects)
    fd.set("action", action);
    fd.set("rfq_id", $("input[name='rfq_id']").val());
    fd.set("total_price", totalPrice.toFixed(2));
    fd.set("vendor_price_basis", price_basis);
    fd.set("vendor_payment_terms", payment_terms);
    fd.set("vendor_delivery_period", delivery_date);
    fd.set("vendor_price_validity", $("#priceValidityInDays").val() || "");
    if (!isCurrencyDisabled) {
        fd.set("vendor_currency", vendor_currency || "");
    }

    $.ajax({
        url: '{{ route("vendor.live-auction-singal-price.rfq.submit") }}',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,          // <-- important for $_POST
        dataType: 'json',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        timeout: 20000,

        success: function (response) {
            if (response && response.status === true) {
                if (window.toastr) toastr.success(response.message || "Submitted successfully.");
                setTimeout(function(){ window.location.reload(); }, 300);
                return;
            }

            // per-variant errors (kept for compatibility)
            if (response && response.errors && response.errors.variants) {
                handleVariantErrors(response.errors.variants);
                return;
            }

            if (response && response.hasAuctionEnded) {
                alert(response.message || "Auction has ended.");
                setTimeout(function(){ window.location.reload(); }, 300);
                return;
            }

            if (window.toastr) toastr.error((response && response.message) || "Failed to load response.");
        },

        error: function (xhr, textStatus) {
            let msg = "An error occurred while submitting the form.";
            if (textStatus === "timeout")      msg = "Request timed out. Please try again.";
            else if (textStatus === "abort")   msg = "Request cancelled.";
            else if (textStatus === "parsererror") msg = "Invalid server response.";

            if (xhr) {
                if (xhr.status === 419) msg = "Session expired (CSRF). Please refresh the page and try again.";
                else if (xhr.status === 422 && xhr.responseJSON) {
                    const rj = xhr.responseJSON;
                    if (rj.errors && rj.errors.variants) {
                        handleVariantErrors(rj.errors.variants);
                        return resetBtn();
                    }
                    if (rj.errors) {
                        const firstErr = Object.values(rj.errors).flat()[0];
                        if (firstErr) msg = firstErr;
                    } else if (rj.message) {
                        msg = rj.message;
                    }
                } else if (xhr.status === 500) {
                    msg = "Server error. Please try again in a moment.";
                } else if (xhr.responseText) {
                    try {
                        const j = JSON.parse(xhr.responseText);
                        if (j && j.message) msg = j.message;
                    } catch(e) { /* ignore non-JSON */ }
                }
            }
            if (window.toastr) toastr.error(msg);
        },

        complete: function () {
            resetBtn();
        }
    });

    function resetBtn(){
        $btn.prop('disabled', false).data('busy', false);
        return false;
    }
}
</script>

<script>
// Auto-fetch L1 price and rank every 2 minutes
$(function(){
    const rfqId = $("input[name='rfq_id']").val();
    if(!rfqId) return;

    const POLL_MS = 120000; // 2 minutes

    function fmtPrice(n){
        return n == null ? '-' : IND_amount_format(Number(n).toFixed(2));
    }

    function render(data){
        $('#l1Price').text(fmtPrice(data.l1));

        const $rank = $('#yourRank');
        if(data.rank == null){
            $rank.text('-').removeClass('text-success text-warning text-danger');
        } else {
            $rank.text(data.rank).removeClass('text-success text-warning text-danger');
            if(data.rank === 1) $rank.addClass('text-success');
            else if(data.rank <= 3) $rank.addClass('text-warning');
            else $rank.addClass('text-danger');
        }
    }

    function fetchMetrics(){
        $.post('{{ route('vendor.live-auction.rfq.total-metrics') }}', {
            _token: '{{ csrf_token() }}',
            rfq_id: rfqId
        }).done(function(res){
            if(res && res.status && res.is_forcestop === '1'){
                alert("Auction has ended.");
                setTimeout(function(){ window.location.reload(); }, 300);
                return;
            }
            if(res && res.status && res.data){
                render(res.data);
            }
        });
    }

    fetchMetrics();
    setInterval(fetchMetrics, POLL_MS);
    $(document).on('visibilitychange', function(){ if(!document.hidden) fetchMetrics(); });
});
</script>

<!-- ====== LIVE + COUNTDOWN TIMER ====== -->
<script>
(function(){
    "use strict";
    const auctionDate      = @json($auction_date ?? null);
    const auctionStartTime = @json($auction_start_time ?? null);
    const auctionEndTime   = @json($auction_end_time ?? null);

    if(!auctionDate || !auctionStartTime || !auctionEndTime){
        const ctn = document.getElementById('countdown');
        if(ctn){ ctn.style.display='none'; }
        return;
    }

    let start = new Date(`${auctionDate}T${auctionStartTime}`).getTime();
    let end   = new Date(`${auctionDate}T${auctionEndTime}`).getTime();

    const $countDiv = document.getElementById('countdown');
    const $liveText = document.getElementById('live-text');
    const $liveWrap = document.getElementById('live-text-div');
    const $timerEl  = document.getElementById('timer');
    if(!$countDiv || !$liveText || !$liveWrap || !$timerEl) return;

    $countDiv.style.display = 'none';
    $liveWrap.style.display = 'none';
    $liveText.style.display = 'none';

    function fmt(ms){
      const hours   = Math.floor((ms % 86400000) / 3600000);
      const minutes = Math.floor((ms % 3600000) / 60000);
      const seconds = Math.floor((ms % 60000) / 1000);
      return `${hours}h ${minutes}m ${seconds}s`;
    }

    function tick(){
        const now = Date.now();
        if(now < start){
            $countDiv.style.display = 'none';
            $liveWrap.style.display = 'none';
            $liveText.style.display = 'none';
            return;
        }

        if(now >= start && now <= end){
            $countDiv.style.display = 'inline-block';
            $liveWrap.style.display = 'inline';
            $liveText.style.display = 'inline';
            const distance = end - now;
            $timerEl.textContent = fmt(distance);

            if(distance <= 0){
                stop();
                $timerEl.textContent = '';
                $countDiv.style.display = 'none';
                $liveText.style.display = 'none';
                $liveWrap.style.display = 'none';
                @isset($refresh)
                @if($refresh === 'yes')
                window.location.reload();
                @endif
                @endisset
            }
            return;
        }

        // After end
        $timerEl.textContent = '';
        $countDiv.style.display = 'none';
        $liveText.style.display = 'none';
        $liveWrap.style.display = 'none';
    }

    const interval = setInterval(tick, 1000);
    function stop(){ clearInterval(interval); }

    tick();

    // Optional hook if your metrics API later returns updated end time:
    // window.updateAuctionEnd = function(newDate, newEndTime){
    //   end = new Date(`${newDate}T${newEndTime}`).getTime();
    // };
})();
</script>
@endsection
