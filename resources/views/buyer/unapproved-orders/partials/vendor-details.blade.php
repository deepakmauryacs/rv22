@php
$quote_details = $vendor['vendor_latest_quote'];
$vendor_quotes = $vendor['vendorQuotes'];
$vendor_variants = $vendor['vendor_variants'];
$vendor_product_gsts = $vendor['vendor_product_gsts'];
$vendor_currency = !empty($quote_details['vendor_currency']) ? $quote_details['vendor_currency'] : '₹';
$is_indian = ($vendor['country'] == 101 && $buyer_country == 101) ? true : false;
$grand_total = 0;
@endphp

<div id="collapseInfo-{{$vendor['vendor_user_id']}}" class="accordion-collapse collapse show"
    aria-labelledby="vendor-{{$vendor['vendor_user_id']}}">
    <div class="accordion-body">
        <form class="unapprovedPoForm" method="POST" id="{{ $vendor_id }}">
            <input type="hidden" name="rfq_id" value="{{ $quote_details['rfq_id'] }}">

            <input type="hidden" name="buyer_name" value="{{ session('legal_name') }}">
            <input type="hidden" name="buyer_branch_name" value="{{$unapprovedOrder['rfq']['buyer_branch_name']}}">
            <input type="hidden" name="buyer_prn_no" value="{{ $unapprovedOrder['rfq']['prn_no'] }}">
            <input type="hidden" name="buyer_branch_address"
                value="{{$unapprovedOrder['rfq']['buyer_branch_address']}}">


            <input type="hidden" name="vendor_name" value="{{ $vendor['legal_name'] }}">
            <input type="hidden" name="vendor_address" value="{{ $vendor['address'] ?? '-' }}">
            <input type="hidden" name="vendor_currency" value="{{ $quote_details['vendor_currency'] }}">
            <input type="hidden" name="vendor_id" value="{{ $vendor['vendor_user_id'] }}">
            <input type="hidden" name="order_total_amount" value="">
            <input type="hidden" name="int_buyer_vendor" value="{{$is_indian==true?2:1  }}">

            @csrf
            <div class="table-responsive">
                <table class="product-listing-table w-100">
                    <thead>
                        <tr>
                            <th class="text-center">S.No</th>
                            <th class="text-center">Products</th>
                            <th class="text-center w-300">Specification</th>
                            <th class="text-center w-120">Size</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-center w-120">UOM</th>
                            <th class="text-center">MRP ({{ $vendor_currency}})</th>
                            <th class="text-center">Disc.(%)</th>
                            <th class="text-center">Rate ({{ $vendor_currency}})</th>
                            @if ($is_indian)
                            <th class="text-center">GST</th>
                            @endif
                            <th class="text-center">Amount ({{ $vendor_currency}})</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $s_r = 1;
                        @endphp
                        @foreach ($vendor_variants as $key => $variant_id)
                        @php
                        $variant = $variants[$variant_id];
                        $quotes = $vendor_quotes[$variant_id];

                        $product_tax = $is_indian ? $taxes[$vendor_product_gsts[$variant['product_id']]] : 0;

                        $item_total = $quotes['price'] * $quotes['left_qty'];
                        $tax_amount = $item_total * $product_tax;
                        $total = $item_total + $tax_amount;

                        $grand_total += $total;
                        @endphp
                        <tr class="variant-row" data-variant-id="{{ $variant_id }}">

                            <input type="hidden" name="variants[{{ $key }}][rfq_product_variant_id]"
                                value="{{ $variant_id }}">
                            <input type="hidden" name="variants[{{ $key }}][rfq_quotation_variant_id]"
                                value="{{ $quotes['id']  }}">
                            <input type="hidden" name="variants[{{ $key }}][rfq_id]"
                                value="{{ $quote_details['rfq_id']}}">
                            <input type="hidden" name="variants[{{ $key }}][product_id]"
                                value="{{ $variant['product_id'] }}">
                            <input type="hidden" name="vendor_currency" value="{{  $vendor_currency }}">
                            <input class="variant_amount" type="hidden" name="variants[{{ $key }}][total]"
                                value="{{ $total  }}">

                            <!--:- data for pdf -:-->
                            <input type="hidden" name="variants[{{ $key }}][product_name]"
                                value="{{$variant['product_name']}}">
                            <input type="hidden" name="variants[{{ $key }}][specification]"
                                value="{{$variant['specification']}}">
                            <input type="hidden" name="variants[{{ $key }}][uom]" value="{{$uom[$variant['uom']]}}">

                            <td>{{$s_r++}}</td>
                            <td class="text-center"><b class="font-size-12">

                                    {{$variant['product_name']}}</b></td>
                            <td class="text-center">

                                {{$variant['specification']}}
                            </td>
                            <td>
                                <input type="hidden" name="variants[{{ $key }}][size]" value="{{$variant['size']}}">
                                {{$variant['size']}}
                            </td>
                            <td>
                                <input type="number"
                                    class=" form-control text-center bg-white product-quantity-field mx-auto order-qty"
                                    min="0" step="any" min="1" name="variants[{{ $key }}][order_quantity]"
                                    data-variant-qty="{{$quotes['variant_quantity']}}" value="{{$quotes['left_qty']}}"
                                    required>
                            </td>
                            <td class="text-center">{{$uom[$variant['uom']]}}</td>
                            <td>
                                <input type="number" step="0.01" min="0" value="{{$quotes['mrp']}}"
                                    name="variants[{{ $key }}][order_mrp]"
                                    class=" form-control text-center bg-white product-mrp-field mx-auto order-mrp"
                                    required>
                            </td>
                            <td>
                                <input type="number" value="{{$quotes['discount']}}" step="0.01" min="0"
                                    name="variants[{{ $key }}][order_discount]"
                                    class=" form-control text-center bg-white product-discount-field mx-auto order-discount"
                                    required>
                            </td>
                            <td>
                                <input type="number" value="{{$quotes['price']}}" step="0.01" min="0"
                                    name="variants[{{ $key }}][order_price]"
                                    class=" form-control text-center bg-white product-rate-field mx-auto order-price"
                                    required>
                            </td>
                            @if ($is_indian)
                            <input type="hidden" value="{{ $product_tax }}" name="variants[{{ $key }}][product_gst]">

                            <td class="text-center">{{$product_tax}}%</td>
                            @endif
                            <td class="text-end variant-amount">{{ $vendor_currency}}{{IND_money_format($total)}}</td>
                        </tr>
                        @endforeach
                        <tr class="grand-total-row">
                            <td><b>Total</b></td>
                            <td colspan="{{ $is_indian ? 9 : 8 }}"></td>
                            <td class="text-end grand-total">{{ $vendor_currency }}{{IND_money_format($grand_total)}}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="pt-15">
                <div class="border-bottom  pb-15">
                    <div class="blue-light-bg p-15 rounded">
                        <div class="row justify-content-between">
                            <div class="col-md-4 col-sm-6 col-12 mt-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-geo-alt"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="priceBasis" name="order_price_basis"
                                            placeholder="Price Basis" value="{{$quote_details['vendor_price_basis']}}">
                                        <label for="priceBasis">Price Basis <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 col-12 mt-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-currency-rupee"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="paymentTerm"
                                            placeholder="Payment Term" name="order_payment_term"
                                            value="{{$quote_details['vendor_payment_terms']}}">
                                        <label for="paymentTerm">Payment Term <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 col-12 mt-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-calendar2-date"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="deliveryPeriod"
                                            placeholder="Delivery Period (In Days)" name="order_delivery_period"
                                            value="{{$quote_details['vendor_delivery_period']}}">
                                        <label for="deliveryPeriod">Delivery Period (In Days) <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-patch-check"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="guranteeWarranty"
                                            name="order_guarantee_warranty" placeholder="Gurantee/Warranty"
                                            value="{{$warranty_gurarantee}}">
                                        <label for="guranteeWarranty">Gurantee/Warranty</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-pencil"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="order_remarks" name="order_remarks"
                                            placeholder="Remarks" value="{{$quote_details['vendor_remarks']}}">
                                        <label for="order_remarks">Remarks</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-pencil"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="order_add_remarks"
                                            placeholder="Additional Remarks" name="order_add_remarks"
                                            value="{{$quote_details['vendor_additional_remarks']}}">
                                        <label for="order_add_remarks">Additional Remarks</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-file-earmark-text"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="buyer_order_number"
                                            name="buyer_order_number" placeholder="Gurantee/Warranty">
                                        <label for="buyer_order_number">Buyer Order Number</label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-center mt-4">
                                <button type="submit"
                                    class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap gap-1 generatePoBtn">
                                    GENERATE Unapproved PO
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>