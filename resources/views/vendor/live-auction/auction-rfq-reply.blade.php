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
  $normal_product_data          = common_rfq_data($rfq->rfq_id);
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
                            use Carbon\Carbon;
                            $auctionDate = $auction_date ? Carbon::parse($auction_date)->format('d/m/Y') : '-';
                            $auctionStart = $auction_start_time ? Carbon::parse($auction_start_time)->format('h:i A') : '';
                            $auctionEnd = $auction_end_time ? Carbon::parse($auction_end_time)->format('h:i A') : '';
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
                                    <th>#</th>
                                    <th style="text-align: left !important;">Specification</th>
                                    <th class="text-center">Size</th>
                                    <th class="text-center">Quantity/UOM</th>
                                    <th class="text-center">Start Price (<span class="currency-symbol"></span>)</th>
                                    <th class="text-center">Min Bid <br>Decrement ( % )</th>
                                    @if($current_status == 1)
                                    <th class="text-center">L1</th>
                                    <th class="text-center">Rank</th>
                                    @endif
                                    <th class="text-center">Last Price</th>
                                    <th class="text-center" width="100"><b>Price (<span class="currency-symbol"></span>)</b></th>
                                    <th class="text-center" width="100">Total (<span class="currency-symbol"></span>)</th>
                                    <th width="400" style="text-align: right; !important;">
                                      Specs
                                      <i class="bi bi-info-circle-fill"
                                         data-bs-toggle="tooltip"
                                         data-bs-placement="top"
                                         title="If you want to change/add the specs, write here."></i>
                                    </th>
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
                                            {!!  mb_substr($sizeStr, 0, length: 5) !!}
                                            <button type="button" class="btn btn-link p-0 m-0 align-baseline" data-bs-toggle="tooltip" data-bs-placement="top" title="{!!  $sizeStr !!}">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                        @else
                                            {!! $variant->size !!} 
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $variant->quantity }} {{ getUOMName($variant->uom) }} </td>
                                    <td class="text-center"> {{ $variant->auction_start_price !== null ? number_format($variant->auction_start_price, 2) : '-' }}</td>
                                    <td class="text-center">{{ $min_bid_decrement }}</td>

                                    @if($current_status == 1)
                                    <td class="text-center l1 price_l1"
                                        data-lowest-price="{{ $variant->l1_price !== null ? number_format($variant->l1_price, 2, '.', '') : '' }}">
                                        {{ $variant->l1_price !== null ? number_format($variant->l1_price, 2) : '-' }}
                                    </td>

                                    <td class="text-center rank" data-variant-id="{{ $variant->id }}">
                                        {{ $variant->vendor_rank ?? '' }}
                                    </td>
                                    @endif

                                    <td class="text-center">
                                        {{ $variant->latest_vend_price !== null ? number_format($variant->latest_vend_price, 2) : '-' }}
                                    </td>

                                    <td>
                                        <input type="number" name="price[{{ $variant->id }}]"
                                            class="form-control form-control-sm variant-price price-change"
                                            value=""
                                            data-variant-grp-id="{{ $variant->id }}"
                                            data-current-price="{{ $variant->latest_vend_price !== null ? number_format($variant->latest_vend_price, 2, '.', '') : '' }}"
                                            data-start-price="{{ $variant->auction_start_price !== null ? number_format($variant->auction_start_price, 2, '.', '') : '' }}"
                                            data-min-bid="{{ (float) $min_bid_decrement }}">
                                    </td>

                                    <td>
                                        @php
                                            $price = $variant->latest_vend_price ?? 0;
                                            $quantity = $variant->quantity;
                                            $total = $price * $quantity;
                                        @endphp
                                        <input type="text" class="form-control form-control-sm totalAmounts" 
                                               value="{{ $total > 0 ? IND_amount_format(number_format($total,2,'.','')) : '' }}" readonly>
                                        <input type="hidden" class="totalQty" value="{{ $variant->quantity }}">
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
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endforeach
                </div>
            </div>
         </section>

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
                                    <input type="text" class="form-control form-control-delivery-period enforce-min-max"
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
                                    <input type="text" class="form-control form-control-price-validity enforce-min-max"
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
                            <button type="button" class="ra-btn ra-btn-sm px-3 ra-btn-primary send-quote-btn" onclick="rfq_counter_submit_data(this,'quote')">
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
  var is_disable = Number(@json($current_status));
  if (is_disable === 2 || is_disable === 3) {
    $('input, select, textarea, button').prop('disabled', true);
    $('[data-bs-toggle="tooltip"],[data-toggle="tooltip"]').tooltip('disable');
  }
});

// Enforce 1-999 range for numeric fields
$(document).on('input', '.enforce-min-max', function(){
    let val = this.value.replace(/[^0-9]/g, '');
    if (val === '0') val = '';
    this.value = val;
});
$(document).on('blur', '.enforce-min-max', function(){
    if (this.value === '') return;
    let num = parseInt(this.value, 10);
    if (isNaN(num) || num <= 0){
        this.value = '';
        return;
    }
    if (num < 1) num = 1;
    if (num > 999) num = 999;
    this.value = num;
});

// Price input validation on blur
$(document).on("blur", ".price-change", function () {
    const $row = $(this).closest('tr');
    const $tableTr = $(this).closest('.table-tr');

    let raw = $(this).val() || '';
    // sanitize number
    raw = raw.replace(/[^0-9.\-]/g, '');
    raw = raw.replace(/\./g, (m,i,o)=> i === o.indexOf('.') ? m : '');
    $(this).val(raw);

    const currentPrice = parseFloat(this.getAttribute('data-current-price')) || 0;
    const l1Price      = parseFloat($tableTr.find('.price_l1').data('lowest-price')) || 0;
    const startPrice   = parseFloat(this.getAttribute('data-start-price')) || 0;
    const minDecPct    = parseFloat(this.getAttribute('data-min-bid')) || 0;
    const enteredPrice = parseFloat($(this).val()) || 0;

    // Empty -> recalc total with currentPrice
    if (raw === '' || enteredPrice === currentPrice) {
      const qty = parseFloat($row.find(".totalQty").val() || 0);
      const tot = (raw === '' ? currentPrice : enteredPrice) * qty;
      $row.find(".totalAmounts").val(tot > 0 ? IND_amount_format(tot.toFixed(2)) : '');
      return;
    }

    let price = parseFloat($row.find(".variant-price").val());
    if (!isFinite(price) || price <= 0) {
      $row.find(".variant-price").val('');
      price = NaN;
    } else {
      $row.find(".variant-price").val(price.toFixed(2));
    }

    const $currency = $('#updateCurrency');
    const symbol = ($currency.length ? ($currency.find('option:selected').data('symbol') || '₹') : '₹');

    const qty = parseFloat($row.find(".totalQty").val() || 0);
    const priceForTotal = isNaN(price) ? (currentPrice || 0) : price;
    const totalAmt = priceForTotal * qty;
    $row.find(".totalAmounts").val(totalAmt > 0 ? IND_amount_format(totalAmt.toFixed(2)) : '');

    const cap = l1Price > 0 ? l1Price : startPrice;
    if (enteredPrice > 0 && cap > 0 && enteredPrice > cap) {
        toastr.error('Entered price cannot exceed ' + (l1Price>0?'L1':'Start Price') + ' of ' + symbol + cap.toFixed(2));
        $(this).val('');
        const fbTotal = (currentPrice || 0) * qty;
        $row.find(".totalAmounts").val(fbTotal > 0 ? IND_amount_format(fbTotal.toFixed(2)) : '');
        return false;
    }

    if (l1Price > 0 && minDecPct > 0) {
        const minDecAmt = (minDecPct / 100) * l1Price;
        const minAcceptable = l1Price - minDecAmt;
        if (enteredPrice > minAcceptable) {
            toastr.error('Bid must be at least ' + minDecPct + '% lower than L1. Max allowed is ' + symbol + minAcceptable.toFixed(2));
            $(this).val('');
            const fbTotal2 = (currentPrice || 0) * qty;
            $row.find(".totalAmounts").val(fbTotal2 > 0 ? IND_amount_format(fbTotal2.toFixed(2)) : '');
            return false;
        }
    }

    if (typeof checkVendPrice === 'function') { checkVendPrice(this); }
});

// Currency symbol sync
function updateCurrencySymbols() {
    const $drop = $('#updateCurrency');
    const symbol = ($drop.length ? ($drop.find('option:selected').data('symbol') || '₹') : '₹');
    document.querySelectorAll('.currency-symbol').forEach(el => el.textContent = symbol);
}
document.addEventListener('DOMContentLoaded', function () {
    updateCurrencySymbols();
    const dd = document.getElementById('updateCurrency');
    if (dd) dd.addEventListener('change', updateCurrencySymbols);
});

// INR format (client)
function IND_amount_format(amount) {
    amount = String(amount);
    const parts = amount.split('.');
    let a = parts[0];
    let lastThree = a.substring(a.length - 3);
    let otherNumbers = a.substring(0, a.length - 3);
    if (otherNumbers !== '') lastThree = ',' + lastThree;
    const res = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree;
    return parts.length > 1 ? res + '.' + parts[1] : res + '.00';
}
</script>

<script>
// ===== Spec modal wiring =====
let currentSpecInputId = null;
$(document).on('click', '.specs-trigger', function () {
    currentSpecInputId = $(this).data('target-input');
    $('#specificationsTextarea').val($(this).val());
});
$('.submit-specification').on('click', function () {
    const newSpec = $('#specificationsTextarea').val();
    if (currentSpecInputId) { $('#' + currentSpecInputId).val(newSpec); }
    $('#submitSpecification').modal('hide');
});
function limitText(field, maxChars) {
    if (field.value.length > maxChars) field.value = field.value.substring(0, maxChars);
}
</script>

<script>
// ====== Per-variant server errors (inline + toast) ======
function showVariantInlineError(variantId, msg){
  const $row = $(".rank[data-variant-id='"+variantId+"']").closest("tr");
  if (!$row.length) return;

  $row.addClass("table-danger");
  setTimeout(function(){ $row.removeClass("table-danger"); }, 2000);

  const $priceTd = $row.find("input.variant-price").closest("td");
  let $err = $priceTd.find(".variant-inline-error");
  if ($err.length === 0){
    $err = $('<div class="variant-inline-error text-danger mt-1" style="font-size:12px;"></div>');
    $priceTd.append($err);
  }
  $err.text(msg);
}
function clearVariantInlineErrors(){
  $(".variant-inline-error").remove();
}
function handleVariantErrors(errMap){
  clearVariantInlineErrors();
  let first = null;
  $.each(errMap, function(vid, message){
    if (!first) first = message;
    showVariantInlineError(vid, message);
    toastr.error(message);
  });
  if (first){
    toastr.error("Validation failed. Please fix the highlighted rows.");
  }
}
</script>

<script>
// ====== Submit (AJAX JSON) ======
function rfq_counter_submit_data(_this, action) {
    let error_counter = false;

    const delivery_date   = $("#deliveryPeriodInDays").val();
    const payment_terms   = $("#PaymentTerms").val() || $("#paymentTerms").val();
    const price_basis     = $("#PriceBasis").val() || $("#priceBasis").val();
    const dispatch_branch = $("#vendorDispatchBranch").val();
    const vendor_currency = $("#updateCurrency").val();
    const is_currency_disabled = $('#updateCurrency').prop('disabled');

    price_basis     ? $(".p_price_basis").html('')          : ($(".p_price_basis").html("Price Basis is Required"), error_counter = true);
    payment_terms   ? $(".p_payment_terms").html('')        : ($(".p_payment_terms").html("Payment Terms is Required"), error_counter = true);
    delivery_date   ? $(".p_delivery_date").html('')        : ($(".p_delivery_date").html("Delivery Period is Required"), error_counter = true);
    dispatch_branch ? $(".vendor_dispatch_branch").html('') : ($(".vendor_dispatch_branch").html("Dispatch Branch is Required"), error_counter = true);

    if (!is_currency_disabled && !vendor_currency) {
        $(".vendor-currency-error").html("Vendor Currency is Required");
        error_counter = true;
    } else {
        $(".vendor-currency-error").html("");
    }

    if (error_counter) {
        toastr.error("Please fill all the Mandatory fields marked with *");
        return false;
    }

    // Build price/spec maps
    const prices = {};
    const specs  = {};
    $(".variant-price").each(function () {
        const vId = $(this).data("variant-grp-id");
        const val = $(this).val();
        if (vId && val !== '') prices[vId] = parseFloat(val);
    });
    $(".specs-trigger").each(function () {
        const name = $(this).attr("name"); // e.g., vendor_spec[14102]
        const match = name && name.match(/vendor_spec\[(\d+)\]/);
        if (match) specs[match[1]] = $(this).val() || null;
    });

    if (Object.keys(prices).length === 0) {
        toastr.error("You have not quoted for any product. Kindly quote to proceed.");
        return false;
    }

    const payload = {
        action: action,
        rfq_id: $("input[name='rfq_id']").val(),
        price: prices,
        vendor_spec: specs,
        vendor_price_basis: $("#priceBasis").val(),
        vendor_payment_terms: $("#paymentTerms").val(),
        vendor_delivery_period: $("#deliveryPeriodInDays").val(),
        vendor_price_validity: $("#priceValidityInDays").val(),
        vendor_dispatch_branch: $("#vendorDispatchBranch").val(),
        vendor_currency: $("#updateCurrency").val()
    };

    $(_this).prop("disabled", true);

    $.ajax({
        url: '{{ route("vendor.live-auction.rfq.submit") }}',
        method: "POST",
        dataType: "json",
        contentType: "application/json",
        data: JSON.stringify(payload),
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        timeout: 20000,
        success: function (response) {
            if (!response || typeof response !== 'object') {
                toastr.error("Failed to load response.");
                return;
            }

            if (response.status) {
                toastr.success(response.message || "Submitted successfully.");
                setTimeout(function () {
                    window.location.reload();
                }, 300); 
                return;
            }

            if (response.errors && response.errors.variants) {
                handleVariantErrors(response.errors.variants);
                return;
            }

            toastr.error(response.message || "Failed to load response.");
        },
        error: function (xhr, textStatus) {
            let msg = "Failed to load response.";

            if (xhr.status === 422 && xhr.responseJSON) {
                const rj = xhr.responseJSON;
                if (rj.errors && rj.errors.variants) {
                    handleVariantErrors(rj.errors.variants);
                    return;
                }
                if (rj.errors) {
                    const firstErr = Object.values(rj.errors).flat()[0];
                    if (firstErr) msg = firstErr;
                    toastr.error(msg);
                    return;
                }
                if (rj.message) {
                    toastr.error(rj.message);
                    return;
                }
            }

            if (textStatus === "timeout")         msg = "Request timed out. Please try again.";
            else if (textStatus === "abort")      msg = "Request cancelled.";
            else if (textStatus === "parsererror") msg = "Invalid server response.";

            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            } else if (xhr && xhr.responseText) {
                try {
                    const j = JSON.parse(xhr.responseText);
                    if (j && j.message) msg = j.message;
                } catch(e) { /* ignore */ }
            }

            if (xhr.status === 419)      msg = "Session expired (CSRF). Please refresh the page and try again.";
            else if (xhr.status === 500) msg = "Server error. Please try again in a moment.";

            toastr.error(msg);
        },
        complete: function () {
            $(_this).prop("disabled", false);
        }
    });
}

// Submit on Enter (from any price field)
$(document).on('keydown', '.variant-price', function(e){
  if (e.key === 'Enter') {
    e.preventDefault();
    $('.send-quote-btn').trigger('click');
  }
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

<script>
@if($current_status == 1)
<script>
// ====== Live rank/L1 polling ======
$(function () {
    const rfqId   = @json($rfq->rfq_id);
    const POLL_MS = 120000; // 2 minutes

    function numberFmt(n) { return n == null ? '' : Number(n).toFixed(2); }

    function gatherVariantIds() {
        const ids = [];
        $(".rank[data-variant-id]").each(function () {
            const id = parseInt($(this).data("variant-id"), 10);
            if (!isNaN(id)) ids.push(id);
        });
        return ids;
    }

    function updateOne(variantId, payload) {
        const $row = $(".rank[data-variant-id='" + variantId + "']").closest("tr");
        if (!$row.length) return;

        const $l1Cell = $row.find(".price_l1");
        if ($l1Cell.length) {
            const l1 = payload.l1;
            $l1Cell.text(l1 == null ? '-' : numberFmt(l1));
            $l1Cell.attr("data-lowest-price", l1 == null ? '' : l1);
            $l1Cell.addClass("bg-light");
            setTimeout(function () { $l1Cell.removeClass("bg-light"); }, 500);
        }

        const $rankCell = $row.find(".rank[data-variant-id]");
        if ($rankCell.length) {
            $rankCell.text(payload.rank == null ? '' : payload.rank);
            $rankCell.removeClass("text-success text-warning text-danger");
            if (payload.rank === 1) $rankCell.addClass("text-success");
            else if (payload.rank && payload.rank <= 3) $rankCell.addClass("text-warning");
            else if (payload.rank) $rankCell.addClass("text-danger");
        }

        const $lastPriceCell = $row.find("td.text-center:eq(8)");
        if ($lastPriceCell.length && payload.vendorPrice != null) {
            $lastPriceCell.text(numberFmt(payload.vendorPrice));
        }
    }

    function fetchMetrics() {
        const variantIds = gatherVariantIds();
        if (!variantIds.length) return;

        $.post("{{ route('vendor.live-auction.rfq.metrics') }}", {
            _token: "{{ csrf_token() }}",
            rfq_id: rfqId,
            variant_ids: variantIds
        })
        .done(function (res) {
            if (res && res.status && res.is_forcestop === '1') {
                alert("Auction has ended.");
                setTimeout(function(){ window.location.reload(); }, 300);
                return;
            }
            if (!res || !res.status || !res.data) return;
            $.each(res.data, function (vid, payload) {
                updateOne(parseInt(vid, 10), payload || {});
            });

            // If later you add end time in response, you could sync timer:
            // if (res.end_date && res.end_time && window.updateAuctionEnd) {
            //   window.updateAuctionEnd(res.end_date, res.end_time);
            // }
        })
        .fail(function () {
            // optional toast/log
        });
    }

    fetchMetrics();
    setInterval(fetchMetrics, POLL_MS);

    $(document).on("visibilitychange", function () {
        if (!document.hidden) fetchMetrics();
    });
});
</script>
@endif
@endsection
