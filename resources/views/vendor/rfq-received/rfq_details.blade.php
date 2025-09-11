@extends('vendor.layouts.app_second', ['title' => 'RFQ Details', 'sub_title' => ''])

@section('content')
<style>
    .form-control.form-control-price-basis,
    .form-control.form-control-payment-terms,
    .form-control.form-control-delivery-period,
    .form-control.form-control-price-validity,
    .form-control.form-control-dispatch-branch,
    .form-select.form-select-currency {
        width: 100% !important;
    }
</style>

@php
$is_international_vendor = is_national();
$is_international_buyer_check = is_national_buyer($rfq->buyer_id);
$normal_product_data = common_rfq_data($rfq->rfq_id);

/**
* Format amount in Indian currency style (e.g., 1,23,456.78)
* @param string $amount
* @return string
*/
function IND_amount_format($amount) {
    $amount = (string)$amount;
    $main_amount = explode('.', $amount);
    $amount = $main_amount[0];
    $lastThree = substr($amount, -3);
    $otherNumbers = substr($amount, 0, -3);

    if ($otherNumbers != '') {
    $lastThree = ',' . $lastThree;
    }

    $res = preg_replace('/\B(?=(\d{2})+(?!\d))/', ",", $otherNumbers) . $lastThree;

    return count($main_amount) > 1 ? $res . '.' . $main_amount[1] : $res . '.00';
}
@endphp

<section class="container-fluid">
    <!-- Breadcrumb and Header -->
    <div class="d-flex align-items-center flex-wrap justify-content-between mr-auto flex py-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vendor.rfq.received.index') }}">RFQ Received</a></li>
                <li class="breadcrumb-item active" aria-current="page">RFQ Details</li>
            </ol>
        </nav>
        <div>
            @if($is_international_vendor=='0' && $is_international_buyer_check=='0')
            <h2 class="font-size-15 fw-bold">Please Quote Rate without GST</h2>
            @endif
        </div>
    </div>

    @php
    $branch = getbuyerBranchById($rfq->buyer_branch);
    @endphp

    <!-- RFQ Details Card -->
    <section class="rfq-vendor-listing">
        <div class="card shadow-none mb-3">
            <div class="card-body">
                <ul>
                    <li><span class="fw-bold">RFQ No:</span> <span>{{ $rfq->rfq_id }}</span></li>
                    <li><span class="fw-bold">RFQ Date:</span> <span>{{
                            \Carbon\Carbon::parse($rfq->created_at)->format('d/m/Y') }}</span></li>
                    <li><span class="fw-bold">PRN Number:</span> <span>{{ $rfq->prn_no ?? '-' }}</span></li>
                    <li><span class="fw-bold">Buyer Name:</span> <span>{{ $rfq->buyer_legal_name ?? '-' }}</span></li>
                    <li><span class="fw-bold">User Name:</span> <span>{{ $rfq->buyer_user_name ?? '-' }}</span></li>
                    <li><span class="fw-bold">Branch Name:</span> <span>{!! $branch->name ?? '-' !!}</span></li>
                    <li>
                        <span class="fw-bold">Branch Address:</span>
                        <span>
                            {{ Str::limit($branch->address ?? '-', 30) }}
                            @if (!empty($branch->address))
                            <button type="button" class="ra-btn ra-btn-link height-inherit text-black font-size-14"
                                data-bs-toggle="tooltip" data-bs-original-title="{!! $branch->address !!}">
                                <span class="bi bi-info-circle-fill font-size-14"></span>
                            </button>
                            @endif
                        </span>
                    </li>
                    <li><span class="fw-bold">Last Date to Response:</span>
                        <span>{{ $rfq->last_response_date ?
                            \Carbon\Carbon::parse($rfq->last_response_date)->format('d/m/Y') : '-' }}</span>
                    </li>
                    <li><span class="fw-bold">Last Edited Date:</span>
                        <span>{{ $rfq->updated_at ? \Carbon\Carbon::parse($rfq->updated_at)->format('d/m/Y') : '-'
                            }}</span>
                    </li>
                    <li><span class="fw-bold"><b class="text-primary">RFQ Terms -</b></span></li>
                    <li><span class="fw-bold">Price Basis:</span> <span>{{ $rfq->buyer_price_basis ?? '-' }}</span></li>
                    <li><span class="fw-bold">Payment Terms:</span> <span>{{ $rfq->buyer_pay_term ?? '-' }}</span></li>
                    <li><span class="fw-bold">Delivery Period:</span> <span>{{ $rfq->buyer_delivery_period ?? '-' }}
                            Days</span></li>
                </ul>
            </div>
        </div>
    </section>

    <!-- RFQ Counter Form -->
    <form id="rfq-counter-form">
        <!-- Product Table -->
        <section class="rfq-vendor-listing-product-form">
            <div class="card shadow-none mb-3">
                <div class="card-body card-vendor-list-right-panel toggle-table-wrapper">
                    @foreach ($products as $index => $product)
                    <div class="d-flex mb-30">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-vendor">
                                <li class="breadcrumb-item"><a href="#">{{ $index + 1 }}.{{ $product->division_name
                                        }}</a></li>
                                <li class="breadcrumb-item"><a href="#">{{ $product->category_name }}</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $product->product_name }}</li>
                            </ol>
                        </nav>
                        @if ($product->is_product == 'no')
                        <p>
                            <span class="text-danger">
                                (Product is not in your profile.
                                <a href="javascript:void(0);" class="add-this-product" data-product-id="{{ $product->product_id }}" data-product-name="{{ $product->product_name }}">
                                    Click Here
                                </a>
                                to add this product so that you can Quote.)
                                {{-- data-bs-toggle="modal" data-bs-target="#addProductModal" --}}
                            </span>
                        </p>
                        @endif
                    </div>

                    <div class="table-responsive table-product toggle-table-content">
                        @php
                        $productVariants = $variants[$product->product_id] ?? collect();
                        $showCounterOffer = $productVariants->contains(function ($v) {
                            $hasHistRel = !empty($v->buyer_counter_offers) && count($v->buyer_counter_offers) > 0;
                            $hasSingle = !empty(optional($v->vendor_quotation)->counter_offer);
                            return $hasHistRel || $hasSingle;
                        });
                        $showHistPrice = $productVariants->contains(function ($v) {
                            $hasHistRel = !empty($v->vendor_price_history) && count($v->vendor_price_history) > 0;
                            $hasSingle = !empty(optional($v->vendor_quotation)->hist_price);
                            return $hasHistRel || $hasSingle;
                        });
                        $showAttachment = $productVariants->contains(function ($v) {
                            return !empty($v->attachment);
                        });
                        @endphp

                        <table class="table table-product-list table-d-block-mobile">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th style="text-align: start !important;">Specification</th>
                                    <th>Size</th>
                                    <th width="125">Quantity/UOM</th>
                                    @if ($showAttachment)
                                    <th>Attachment</th>
                                    @endif
                                    <th width="125"><b>Price (<span class="currency-symbol"></span>)</b> <i
                                            class="bi bi-info-circle-fill text-primary" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="Net Price after Discount"></i></th>
                                    <th width="125">MRP (<span class="currency-symbol"></span>) </th>
                                    <th width="100">Disc.(%) <i class="bi bi-info-circle-fill text-primary"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Discount on MRP"></i></th>
                                    <th width="125">Total (<span class="currency-symbol"></span>)</th>
                                    @if ($showCounterOffer)
                                    <th width="125">Counter Offer (<span class="currency-symbol"></span>)</th>
                                    @endif
                                    @if ($showHistPrice)
                                    <th width="125">Hist. Price (<span class="currency-symbol"></span>)</th>
                                    @endif
                                    <th width="400">Specs</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($productVariants as $vIndex => $variant)
                                <tr>
                                    <td>{{ $vIndex + 1 }}</td>
                                    <td>{{ $variant->specification }}</td>
                                    <td class="text-center">
                                        @php $sizeStr = strip_tags($variant->size); @endphp
                                        @if (strlen($sizeStr) > 5)
                                        {!! mb_substr($sizeStr, 0, 5) !!}
                                        <button type="button" class="btn btn-link p-0 m-0 align-baseline"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="{!! $sizeStr !!}">
                                            <i class="bi bi-info-circle"></i>
                                        </button>
                                        @else
                                        {!! $variant->size !!}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $variant->quantity }} {{ getUOMName($variant->uom) }}
                                    </td>
                                    @if ($showAttachment)
                                    <td class="text-center">
                                        @if (!empty($variant->attachment))
                                        <a href="{{ asset('public/uploads/rfq-attachment/' . $variant->attachment) }}" target="_blank">
                                            {!!
                                                strlen($variant->attachment) > 10
                                                    ? substr($variant->attachment, 0, 10)
                                                    : $variant->attachment
                                            !!}
                                        </a>
                                        @endif
                                    </td>
                                    @endif
                                    <td>
                                        <input type="number" name="price[{{ $variant->id }}]"
                                            class="form-control form-control-sm variant-price price-change"
                                            value="{{ optional($variant->vendor_quotation)->price ?? '' }}" {{
                                            $product->is_product == 'no' ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        <input type="number" name="mrp[{{ $variant->id }}]"
                                            class="form-control form-control-sm variant-mrp price-change"
                                            value="{{ optional($variant->vendor_quotation)->mrp ?? '' }}" {{
                                            $product->is_product == 'no' ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        <input type="number" name="disc[{{ $variant->id }}]"
                                            class="form-control form-control-sm variant-discount price-change"
                                            value="{{ optional($variant->vendor_quotation)->discount ?? '' }}" {{
                                            $product->is_product == 'no' ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        @php
                                        $price = optional($variant->vendor_quotation)->price ?? 0;
                                        $quantity = $variant->quantity;
                                        $total = $price * $quantity;
                                        @endphp
                                        <input type="text" class="form-control form-control-sm totalAmounts"
                                            value="{{ $total > 0 ? IND_amount_format($total) : '' }}" readonly {{
                                            $product->is_product == 'no' ? 'disabled' : '' }}>
                                        <input type="hidden" class="totalQty" value="{{ $variant->quantity }}">
                                    </td>

                                    @if ($showCounterOffer)
                                    @php
                                    $coItems = collect($variant->buyer_counter_offers ?? []);
                                    if ($coItems->isEmpty() &&
                                    !empty(optional($variant->vendor_quotation)->buyer_price)) {
                                    $coItems = collect([(object)[
                                    'buyer_price' => optional($variant->vendor_quotation)->buyer_price,
                                    'created_at' => optional($variant->vendor_quotation)->updated_at ??
                                    optional($variant->vendor_quotation)->created_at,
                                    ]]);
                                    }
                                    $coLatest = optional($coItems->first())->buyer_price;
                                    $coContent = $coItems->map(function ($item) {
                                    $amt = number_format((float)$item->buyer_price, 2);
                                    $dt = $item->updated_at ? \Carbon\Carbon::parse($item->updated_at)->format('d-M') :
                                    '';
                                    return "<div>{$amt} <small class='text-white'>({$dt})</small></div>";
                                    })->implode('');
                                    @endphp
                                    <td data-th="Counter Offer" class="counter-offer">
                                        <span class="form-control h-30 d-inline-flex align-items-center">
                                            @if ($coItems->isNotEmpty())
                                            <span class="buyer-old-price" data-bs-toggle="tooltip"
                                                data-bs-placement="top" data-bs-original-title="{{ $coContent }}">
                                                <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                                            </span>&nbsp;
                                            {{ number_format((float)$coLatest, 2) }}
                                            @endif
                                        </span>
                                    </td>
                                    @endif

                                    @if ($showHistPrice)
                                    @php
                                    $hpItems = collect($variant->vendor_price_history ?? []);
                                    if ($hpItems->isEmpty() && !empty(optional($variant->vendor_quotation)->hist_price))
                                    {
                                    $hpItems = collect([(object)[
                                    'price' => optional($variant->vendor_quotation)->hist_price,
                                    'created_at' => optional($variant->vendor_quotation)->updated_at ??
                                    optional($variant->vendor_quotation)->created_at,
                                    ]]);
                                    }
                                    $hpLatest = optional($hpItems->first())->price;
                                    $hpContent = $hpItems->map(function ($item) {
                                    $amt = number_format((float)$item->price, 2);
                                    $dt = $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d-M') :
                                    '';
                                    return "<div>{$amt} <small class='text-white'>({$dt})</small></div>";
                                    })->implode('');
                                    @endphp
                                    <td data-th="Historical Price">
                                        <span class="form-control h-30 d-inline-flex align-items-center">
                                            @if ($hpItems->isNotEmpty())
                                            <span class="vendor-old-price" data-bs-toggle="tooltip"
                                                data-bs-placement="top" data-bs-original-title="{{ $hpContent }}">
                                                <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                                            </span>&nbsp;
                                            {{ number_format((float)$hpLatest, 2) }}
                                            @endif
                                        </span>
                                    </td>
                                    @endif

                                    <td>
                                        <input type="text" name="vendor_spec[{{ $variant->id }}]"
                                            id="vendor_spec_{{ $variant->id }}"
                                            class="form-control form-control-sm specs-trigger"
                                            value="{{ optional($variant->vendor_quotation)->specification ?? '' }}"
                                            placeholder="Enter Specs" data-bs-toggle="modal"
                                            data-bs-target="#submitSpecification"
                                            data-target-input="vendor_spec_{{ $variant->id }}" {{ $product->is_product
                                        == 'no' ? 'disabled' : '' }}>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Search by Brand and Remarks -->
                    <div class="row mt-4">
                        <div class="col-md-4 mb-4">
                            <div class="input-group disabled">
                                <span class="input-group-text">
                                    <span class="bi bi-pencil" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="remarks" value="{{ $product->brand }}"
                                        placeholder="Remarks" disabled>
                                    <label for="remarks">Remarks</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group disabled">
                                <span class="input-group-text">
                                    <span class="bi bi-tag-fill" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="brand" value="{{ $product->remarks }}"
                                        placeholder="Brand" disabled>
                                    <label for="brand">Brand</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-paperclip" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <div class="form-floating-tooltip">
                                        <button type="button"
                                            class="ra-btn ra-btn-link height-inherit text-danger font-size-18"
                                            data-bs-toggle="tooltip" data-placement="top"
                                            data-bs-original-title="(Maximum allowed file size 1MB, PDF, DOC, Excel, Image)">
                                            <span class="bi bi-question-circle font-size-18"></span>
                                        </button>
                                    </div>
                                    <span class="form-floating-label"
                                        for="uploadFile_{{ $productVariants[0]->id }}">Upload File</span>
                                    <div class="simple-file-upload d-flex align-items-center">
                                        <input type="file" id="uploadFile_{{ $productVariants[0]->id }}"
                                            name="vendor_attachment[{{ $productVariants[0]->id }}]"
                                            class="real-file-input vendor-attachment" style="display: none;"
                                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" {{ $product->is_product
                                        == 'no' ? 'disabled' : '' }}>
                                        <div class="file-display-box form-control text-start font-size-12 text-dark flex-grow-1"
                                            role="button" data-bs-toggle="tooltip" data-bs-placement="top">
                                            Attach file
                                        </div>
                                        <span class="text-danger ms-2 remove-attachment" style="display:none; cursor:pointer;">
                                            <i class="bi bi-x-circle"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @php
                            $existingFile = optional($productVariants[0]->vendor_quotation)->vendor_attachment_file ??
                            null;
                            $fileName = $existingFile ? basename($existingFile) : 'Attach file';
                            @endphp
                            @if ($existingFile)
                            <a href="{{ asset('public/uploads/rfq-attachment/' . $existingFile) }}"
                                target="_blank" class="btn btn-link btn-sm ms-2" title="View file">
                                {{ $existingFile }}
                            </a>
                            @endif
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-tag-fill" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="sellerBrand"
                                        name="sellerbrand[{{ $productVariants[0]->id }}]"
                                        value="{{ optional($productVariants[0]->vendor_quotation)->vendor_brand ?? '' }}"
                                        placeholder="Seller Brand" {{ $product->is_product == 'no' ? 'disabled' : '' }}>
                                    <label for="sellerBrand">Seller Brand</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Remarks Section -->
        <section>
            <div class="row">
                <div class="col-12">
                    <label for="sellerRemarks">Remarks:</label>
                    <textarea name="seller-remarks" id="sellerRemarks" rows="4" class="form-control"
                        placeholder="If there is any change in quantity, please specify here.">{{ $normal_product_data->vendor_remarks ?? '' }}</textarea>
                </div>
                <div class="col-12 my-3">
                    <label for="sellerAdditionalRemarks">Additional Remarks:</label>
                    <textarea name="Seller-Additional-Remarks" id="sellerAdditionalRemarks" rows="4"
                        class="form-control"
                        placeholder="Any details about warranty/guarantee, please specify here.">{{ $normal_product_data->vendor_additional_remarks ?? '' }}</textarea>
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
                                    <input type="text" class="form-control form-control-price-basis" id="priceBasis"
                                        name="vendor_price_basis" placeholder="Price Basis"
                                        value="{{ $normal_product_data->vendor_price_basis ?? $rfq->buyer_price_basis ?? '' }}">
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
                                    <input type="text" class="form-control form-control-payment-terms" id="paymentTerms"
                                        name="vendor_payment_terms" placeholder="Payment Terms"
                                        value="{{ !empty($normal_product_data->vendor_payment_terms) ? $normal_product_data->vendor_payment_terms : ($rfq->buyer_pay_term ?? '') }}">
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
                                        id="deliveryPeriodInDays" name="vendor_delivery_period"
                                        placeholder="Delivery Period (In Days)"
                                        value="{{ !empty($normal_product_data->vendor_delivery_period) ? $normal_product_data->vendor_delivery_period : ($rfq->buyer_delivery_period ?? '') }}">
                                    <label for="deliveryPeriodInDays">Delivery Period (In Days) <span
                                            class="text-danger">*</span></label>
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
                                        id="priceValidityInDays" name="vendor_price_validity"
                                        placeholder="Price Validity (In Days)"
                                        value="{{ $normal_product_data->vendor_price_validity ?? '' }}">
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
                                    <select class="form-select form-control-dispatch-branch" id="vendorDispatchBranch"
                                        name="vendor_dispatch_branch">
                                        @if (count($branches) > 1)
                                        <option value="">Select</option>
                                        @endif
                                        @foreach ($branches as $branch)
                                        <option value="{{ $branch->branch_id }}" {{ ($normal_product_data->
                                            vendor_dispatch_branch ?? '') == $branch->branch_id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <label for="vendorDispatchBranch">Dispatch Branch <span
                                            class="text-danger">*</span></label>
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
                                        id="updateCurrency" name="vendor_currency" {{ $is_disabled ? 'disabled' : '' }}
                                        aria-label="Select">
                                        @if (!$is_disabled)
                                        <option value="">Select</option>
                                        @endif
                                        @foreach ($vendor_currency ?? [] as $val)
                                        @php
                                        if ($val->currency_name == '') continue;
                                        $currency_val = ($val->currency_symbol == 'रु') ? 'NPR' : $val->currency_symbol;
                                        $currency_symbol = ($val->currency_symbol == 'रु') ? 'NPR' :
                                        $val->currency_symbol;
                                        $selected = ($currency_val == ($normal_product_data->vendor_currency ?? '')) ?
                                        'selected' : '';
                                        @endphp
                                        <option value="{{ $currency_val }}" data-symbol="{{ $currency_symbol }}" {{
                                            $selected }}>
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
                    <div class="row pt-3 gx-3 gy-3 justify-content-center align-items-center">
                        <div class="col-auto">
                            <button type="button"
                                onclick="messageModal('{{ route('message.showPopUp') }}','{{ auth()->user()->id }}','{{ $rfq->buyer_id }}','{{ $rfq->rfq_id }}','','')"
                                class="ra-btn ra-btn-sm px-3 ra-btn-outline-primary">
                                <i class="bi bi-send" aria-hidden="true"></i>
                                Send Message
                            </button>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="ra-btn ra-btn-outline-primary-light"
                                onclick="rfq_counter_submit_data(this, 'save')">
                                <i class="bi bi-journal-bookmark" aria-hidden="true"></i>
                                Save
                            </button>
                        </div>
                        <div class="col-12 col-sm-auto text-center">
                            <button type="button" class="ra-btn ra-btn-sm px-3 ra-btn-primary send-quote-btn"
                                onclick="rfq_counter_submit_data(this, 'quote')">
                                <i class="bi bi-check-lg" aria-hidden="true"></i>
                                Send Quote
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </form>
</section>

<!-- Modal: Add Product -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-graident">
                <h5 class="modal-title text-white" id="addProductModalLabel">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-product-to-vendor-profile" method="POST" action="javascript:void(0);">
                    @csrf
                    
                    <table class="table table-striped table-responsive">
                        <thead>
                            <tr>
                                <th scope="col" class="text-center">Product Name</th>
                                <th scope="col" class="text-center" width="250px">Product Description <span class="text-danger">*</span></th>
                                <th scope="col" class="text-center">Dealer Type <span class="text-danger">*</span></th>
                                @if($is_international_vendor=="1")
                                <th scope="col" class="text-center">GST/Sales Tax Rate <span class="text-danger">*</span></th>
                                @endif
                                <th scope="col" class="text-center">HSN Code <span class="text-danger">*</span></th>
                            </tr>
                        </thead>
                        <tbody class="">
                            <tr class="">
                                <td data-th="Product Name">
                                    <p id="new-product-name" class="mt-2"></p>
                                    <input type="hidden" name="product_id" id="product-id" value="">
                                    <input type="hidden" name="rfq_id" id="rfq-id" value="<?php echo $rfq->rfq_id; ?>">
                                </td>
                                <td data-th="Product Description">
                                    <input type="text" name="product_description" class="form-control" id="product-description" value="" maxlength="500">
                                </td>
                                <td data-th="Dealer Type">
                                    <select class="form-control" name="dealer_type">
                                        <option value="">Select Dealer Type</option>
                                        @foreach ($dealertypes as $dealer)
                                            <option value="<?php echo $dealer->id ?>"><?php echo $dealer->dealer_type ?></option>
                                        @endforeach
                                    </select>
                                </td>
                                @if($is_international_vendor=="1")
                                <td data-th="GST Rate">
                                    <select class="form-control" name="tax_class">
                                        <option value="">Select</option>
                                        @foreach ($taxes as $taxs)
                                            <option value="<?php echo $taxs->id ?>"> <?php echo $taxs->tax ?> % </option>
                                        @endforeach
                                    </select>
                                </td>
                                @endif
                                <td data-th="HSN Code">
                                    <input type="text" name="hsn_code" class="form-control" minlength="2" maxlength="8" oninput="this.value = this.value.replace(/[^0-9]/g,'');" onpaste="this.value = this.value.replace(/[^0-9]/g,'');">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-center">
                                    <button type="submit" class="ra-btn ra-btn-primary font-size-12" id="add-product-to-vendor-profile">Submit</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>


                    {{-- <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" id="modal_product_name" name="product_name" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Description *</label>
                        <input type="text" name="product_description" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dealer Type *</label>
                        <select name="dealer_type" class="form-select" required>
                            <option value="">Select Dealer Type</option>
                            <option value="Manufacturer">Manufacturer</option>
                            <option value="Dealer">Dealer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">GST/Sales Tax Rate *</label>
                        <input type="text" name="gst_rate" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">HSN Code *</label>
                        <input type="text" name="hsn_code" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button> --}}
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Specification -->
<div class="modal fade" id="submitSpecification" tabindex="-1" aria-labelledby="submitSpecificationLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header justify-content-between bg-graident text-white px-4">
                <h2 class="modal-title font-size-13" id="submitSpecificationLabel">
                    <span class="bi bi-pencil" aria-hidden="true"></span> View/Update Specs
                </h2>
                <button type="button" class="btn btn-link p-0 font-size-14 text-white" data-bs-dismiss="modal"
                    aria-label="Close">
                    <span class="bi bi-x-lg" aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <textarea class="form-control specifications-textarea" oninput="limitText(this, 500)"
                        id="specificationsTextarea" rows="8"></textarea>
                </div>
                <div class="text-center">
                    <button type="button"
                        class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 submit-specification">Update</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Send Message -->
{{-- <div class="modal fade" id="sendMessage" tabindex="-1" aria-labelledby="sendMessageLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header justify-content-between bg-graident text-white px-4">
                <h2 class="modal-title font-size-13" id="sendMessageLabel">
                    <span class="bi bi-pencil" aria-hidden="true"></span> Send Message
                </h2>
                <button type="button" class="btn btn-link p-0 font-size-14 text-white" data-bs-dismiss="modal"
                    aria-label="Close">
                    <span class="bi bi-x-lg" aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <input type="text" value="RONI-25-00043" name="subject" class="form-control" readonly
                        placeholder="Subject">
                </div>
                <div class="mb-3">
                    <textarea name="send-msg" class="form-control specifications-textarea" rows="8"
                        placeholder="Write your message here..."></textarea>
                </div>
                <div class="mb-3">
                    <div class="simple-file-upload">
                        <input type="file" class="real-file-input" style="display: none;">
                        <div class="file-display-box form-control text-start font-size-12 text-dark" role="button"
                            data-bs-toggle="tooltip" data-bs-placement="top">
                            Upload file
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="text-end">
                    <button type="button"
                        class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11">Send</button>
                </div>
            </div>
        </div>
    </div>
</div> --}}

<script>
    // Fill product name when clicking "Click Here"
// document.addEventListener('click', function (e) {
//     if (e.target.matches('[data-product-name]')) {
//         document.getElementById('modal_product_name').value = e.target.dataset.productName;
//     }
// });

// Price input logic
$(document).on("blur", ".price-change", function () {
    const row = $(this).closest('tr');
    let price = parseFloat(row.find(".variant-price").val().replace(/[^0-9.-]/g, '') || 0);
    let mrp = parseFloat(row.find(".variant-mrp").val().replace(/[^0-9.-]/g, '') || 0);
    let discount = parseFloat(row.find(".variant-discount").val().replace(/[^0-9.-]/g, '') || 0);
    const totalQty = parseFloat(row.find(".totalQty").val() || 0);

    // Format price fields
    row.find(".variant-price").val(price > 0 ? price.toFixed(2) : '');
    row.find(".variant-mrp").val(mrp > 0 ? mrp.toFixed(2) : '');
    if (discount > 0 && discount <= 99) {
        row.find(".variant-discount").val(discount.toFixed(2));
    } else if (discount > 99) {
        alert("Discount cannot be greater than 100%");
        discount = 99;
        row.find(".variant-discount").val(discount);
    } else {
        row.find(".variant-discount").val('');
        discount = 0;
    }

    // Recalculate price based on MRP and discount
    if (mrp > 0 && discount > 0) {
        let discountedPrice = mrp - (mrp * discount / 100);
        if (discountedPrice.toFixed(2) == 0) {
            alert("Price cannot be 0");
            row.find(".variant-price").val('');
        } else {
            row.find(".variant-price").val(discountedPrice.toFixed(2));
        }
    } else if (mrp > 0 && !discount) {
        row.find(".variant-price").val(mrp.toFixed(2));
    }

    // Recalculate total
    let finalPrice = parseFloat(row.find(".variant-price").val() || 0);
    let total = finalPrice * totalQty;
    row.find(".totalAmounts").val(total > 0 ? IND_amount_format(total.toFixed(2)) : '');
});

function updateCurrencySymbols() {
    const currencyDropdown = document.getElementById('updateCurrency');
    const currencyTargets = document.querySelectorAll('.currency-symbol');

    if (currencyDropdown) {
        const selectedOption = currencyDropdown.options[currencyDropdown.selectedIndex];
        const symbol = selectedOption.dataset.symbol || '₹';
        currencyTargets.forEach(el => el.textContent = symbol);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const currencyDropdown = document.getElementById('updateCurrency');
    updateCurrencySymbols();
    if (currencyDropdown) {
        currencyDropdown.addEventListener('change', updateCurrencySymbols);
    }
});

function IND_amount_format(amount) {
    amount = String(amount);
    const main_amount = amount.split('.');
    amount = main_amount[0].toString();
    let lastThree = amount.substring(amount.length - 3);
    let otherNumbers = amount.substring(0, amount.length - 3);
    if (otherNumbers != '') lastThree = ',' + lastThree;
    const res = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree;
    return main_amount.length > 1 ? res + '.' + main_amount[1] : res + '.00';
}

let currentSpecInputId = null;

$(document).on('click', '.specs-trigger', function () {
    currentSpecInputId = $(this).data('target-input');
    let existingVal = $(this).val();
    $('#specificationsTextarea').val(existingVal);
});

$('.submit-specification').on('click', function () {
    let newSpec = $('#specificationsTextarea').val();
    if (currentSpecInputId) {
        $('#' + currentSpecInputId).val(newSpec);
    }
    $('#submitSpecification').modal('hide');
});

function limitText(field, maxChars) {
    if (field.value.length > maxChars) {
        field.value = field.value.substring(0, maxChars);
    }
}

function rfq_counter_submit_data(_this, action) {
    let error_counter = false;
    const delivery_date = $("#deliveryPeriodInDays").val();
    const payment_terms = $("#paymentTerms").val();
    const price_basis = $("#priceBasis").val();
    const dispatch_branch = $("#vendorDispatchBranch").val();
    const vendor_currency = $("#updateCurrency").val();
    const is_currency_disabled = $('#updateCurrency').prop('disabled');

    // Validate mandatory fields
    price_basis ? $(".p_price_basis").html('') : ($(".p_price_basis").html("Price Basis is Required"), error_counter = true);
    payment_terms ? $(".p_payment_terms").html('') : ($(".p_payment_terms").html("Payment Terms is Required"), error_counter = true);
    delivery_date ? $(".p_delivery_date").html('') : ($(".p_delivery_date").html("Delivery Period is Required"), error_counter = true);
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

    let price_fill = 0;
    $(".variant-price").each(function () {
        if ($(this).val() !== '') price_fill++;
    });

    if (price_fill === 0) {
        alert("You have not quoted for any product. Kindly quote to proceed.");
        return false;
    }

    const total_p_count = $(".variant-price").length;
    const saveId = $(_this).data("save_id");
    if (saveId == 1) {
        if (!confirm(`You have quoted for ${price_fill}/${total_p_count} products. Are you sure you want to send the quote?`)) {
            return false;
        }
    }

    let formData = new FormData($('#rfq-counter-form')[0]);
    formData.append("action", action);
    formData.append("_token", '{{ csrf_token() }}');

    $(_this).prop("disabled", true);

    $.ajax({
        url: '{{ route("vendor.rfq.submit") }}',
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            if (response.status) {
                // alert(response.message || "Submitted successfully.");
                window.location.href = response.redirect_url;// || window.location.href;
            } else {
                alert(response.message || "Something went wrong.");
            }
        },
        error: function (xhr, status, error) {
            alert("Error occurred: " + error);
        },
        complete: function () {
            $(_this).prop("disabled", false);
        }
    });
}

$(document).on('change', '.vendor-attachment', function () {
    const fileInput = this;
    const maxSize = 1 * 1024 * 1024; // 1MB
    const allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png'
    ];

    const $wrapper = $(this).closest('.simple-file-upload');
    const $displayBox = $wrapper.find('.file-display-box');
    const $removeIcon = $wrapper.find('.remove-attachment');

    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        if (file.size > maxSize) {
            alert('File size exceeds 1MB limit');
            fileInput.value = '';
            $displayBox.text('Attach file');
            $removeIcon.hide();
            return;
        }
        if (!allowedTypes.includes(file.type)) {
            alert('Only PDF, DOC, Excel, and Image files are allowed');
            fileInput.value = '';
            $displayBox.text('Attach file');
            $removeIcon.hide();
            return;
        }
        $displayBox.text(file.name);
        $removeIcon.show();
    } else {
        $displayBox.text('Attach file');
        $removeIcon.hide();
    }
});

$(document).on('click', '.remove-attachment', function (e) {
    e.stopPropagation();
    const $wrapper = $(this).closest('.simple-file-upload');
    $wrapper.find('.vendor-attachment').val('');
    $wrapper.find('.file-display-box').text('Attach file');
    $(this).hide();
});

$(document).on('click', '.file-display-box', function () {
    const $fileInput = $(this).siblings('.vendor-attachment');
    if (!$fileInput.val()) {
        $fileInput.trigger('click');
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (el) {
        return new bootstrap.Tooltip(el, { html: true });
    });
});

$(document).on("click", ".add-this-product", function(){
    let product_id = $(this).data("product-id");
    let product_name = $(this).data("product-name");
    $("#new-product-name").html(product_name);
    $("#product-id").val(product_id);
    $("#product-description").val(product_name);
    $("#addProductModal").modal("show");
});
$("#add-product-to-vendor-profile").submit(function(){
    // console.log("Validating...");
    let productId = $('[name="product_id"]').val();
    let rfqId = $('[name="rfq_id"]').val();
    let productDescription = ($('[name="product_description"]').val()).trim();
    let dealerType = $('[name="dealer_type"]').val();
    @if($is_international_vendor=="1")
    let taxClass = $('[name="tax_class"]').val();
    @endif
    let hsnCode = $('[name="hsn_code"]').val();
    // validate
    if(productId=='' || rfqId==''){
        alert("Something went wrong...");
        return false;
    }
    let is_error = false, error_msg = '';
    if(productDescription==''){
        is_error = true;
    }
    if(dealerType==''){
        is_error = true;
    }
    @if($is_international_vendor=="1")
    if(taxClass==''){
        is_error = true;
    }
    @endif
    if(hsnCode==''){
        is_error = true;
    }else if(hsnCode.length<2 || hsnCode.length>8){
        is_error = true;
        error_msg = "Invalid HSN Code, ";
    }
    if(is_error){
        alert(error_msg+" Manadatory field is required.");
        return false;
    }
    // submit by ajax
    // console.log("Submitting...");

    $("#add-product-to-vendor-profile").addClass("disabled");

    let formData = new FormData(this);
    $.ajax({
        type: "POST",
        url: '{{ route("vendor.rfq.add-product-to-vendor-profile") }}',
        dataType: 'json',
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        success: function (response) {
            if (response.status == false) {
                $("#add-product-to-vendor-profile").removeClass("disabled");
                toastr.error(response.message);
            } else {
                toastr.success(response.message);
                setTimeout(function(){
                   window.location.reload();
                }, 300);
            }
        },
        error: function () {
            $("#add-product-to-vendor-profile").removeClass("disabled");
            toastr.error('Something Went Wrong..');
        }
    });
});
</script>

@endsection