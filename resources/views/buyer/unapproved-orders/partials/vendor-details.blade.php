
@php
    $quote_details = $vendor['vendor_latest_quote'];
    $vendor_quotes = $vendor['vendorQuotes'];
    $vendor_variants = $vendor['vendor_variants'];
    $vendor_product_gsts = $vendor['vendor_product_gsts'];
    $vendor_currency = !empty($quote_details['vendor_currency']) ? $quote_details['vendor_currency'] : '₹';
    $is_indian = ($vendor['country'] == 101 && $buyer_country == 101) ? true : false;
    $grand_total = 0;
@endphp

<div id="collapseInfo-{{$vendor['vendor_user_id']}}" class="accordion-collapse collapse show" aria-labelledby="vendor-{{$vendor['vendor_user_id']}}">
    <div class="accordion-body">
        <form action="">
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
                        <tr>
                            <td>{{$s_r++}}</td>
                            <td class="text-center"><b class="font-size-12">{{$variant['product_name']}}</b></td>
                            <td class="text-center">{{$variant['specification']}}</td>
                            <td>{{$variant['size']}}</td>
                            <td>
                                <input type="text" value="{{$quotes['left_qty']}}" class=" form-control text-center bg-white product-quantity-field mx-auto">
                            </td>
                            <td class="text-center">{{$uom[$variant['uom']]}}</td>
                            <td>
                                <input type="text" value="{{$quotes['mrp']}}" class=" form-control text-center bg-white product-mrp-field mx-auto">
                            </td>
                            <td>
                                <input type="text" value="{{$quotes['discount']}}" class=" form-control text-center bg-white product-discount-field mx-auto">
                            </td>
                            <td>
                                <input type="text" value="{{$quotes['price']}}" class=" form-control text-center bg-white product-rate-field mx-auto">
                            </td>
                            @if ($is_indian)
                            <td class="text-center">{{$product_tax}}%</td>
                            @endif
                            <td class="text-end">{{ $vendor_currency}}{{IND_money_format($total)}}</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td><b>Total</b></td>
                            <td colspan="{{ $is_indian ? 9 : 8 }}"></td>
                            <td class="text-end">{{ $vendor_currency }}{{IND_money_format($grand_total)}}</td>
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
                                        <input type="text" class="form-control" id="priceBasis" placeholder="Price Basis" value="{{$quote_details['vendor_price_basis']}}">
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
                                        <input type="text" class="form-control" id="paymentTerm" placeholder="Payment Term" value="{{$quote_details['vendor_payment_terms']}}">
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
                                        <input type="text" class="form-control" id="deliveryPeriod" placeholder="Delivery Period (In Days)" value="{{$quote_details['vendor_delivery_period']}}">
                                        <label for="deliveryPeriod">Delivery Period (In Days) <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-patch-check"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="guranteeWarranty" placeholder="Gurantee/Warranty" value="{{$warranty_gurarantee}}">
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
                                        <input type="text" class="form-control" id="guranteeWarranty" placeholder="Remarks" value="{{$quote_details['vendor_remarks']}}">
                                        <label for="guranteeWarranty">Remarks</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-pencil"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="guranteeWarranty" placeholder="Additional Remarks" value="{{$quote_details['vendor_additional_remarks']}}">
                                        <label for="guranteeWarranty">Additional Remarks</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-file-earmark-text"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="guranteeWarranty" placeholder="Gurantee/Warranty">
                                        <label for="guranteeWarranty">Buyer Order Number</label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-center mt-4">
                                <button type="button" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap gap-1">
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