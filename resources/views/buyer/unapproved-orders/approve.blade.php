@extends('buyer.layouts.app', ['title'=>'Unapproved Order Confirmation'])

@section('css')
<style>
    .btn-grp {
        position: absolute;
        top: 4%;
        right: 3%;
    }
    tr.variant-error td {
        color: red;
    }
    tr.variant-error td input {
        border: 1px solid red;
        color: red;
    }
</style>
@endsection

@section('content')
<div class="bg-white">
    <!---Sidebar-->
    @include('buyer.layouts.sidebar-menu')
</div>

@php
$isIndian=$orders->vendor->country==101;
@endphp
<!---Section Main-->
<main class="main flex-grow-1">
    <div class="container-fluid">
        <div class="bg-white unapproved-order-page">
            <h3 class="card-head-line">Unapproved Order Details</h3>
            <div class="d-flex justify-content-end gap-2 btn-grp">
                <button data-po-number="{{ $orders->po_number }}" type="button"
                    class="ra-btn btn-outline-danger ra-btn-outline-danger small-btn text-uppercase text-nowrap cancelPOBtn">
                    <span class="bi bi-x" aria-hidden="true"></span> Cancel
                </button>
                <button type="button" class="ra-btn btn-outline-primary ra-btn-outline-primary small-btn text-uppercase text-nowrap" id="approve-po">
                    <span class="bi bi-check-lg" aria-hidden="true"></span> Approve PO
                </button>
                <button type="button" onclick="printdiv();"
                    class="ra-btn btn-outline-primary ra-btn-outline-primary small-btn text-uppercase text-nowrap">
                    <span class="bi bi-download" aria-hidden="true"></span> Download
                </button>
                <a href="{{ route('buyer.unapproved-orders.list') }}" type="submit"
                    class="ra-btn small-btn ra-btn-primary small-btn">
                    <span class="bi bi-arrow-left-square" aria-hidden="true"></span>
                    Back
                </a>
            </div>

            <div class="list-for-rfq-wrap">
                <ul class="list-for-rfq">
                    <li>Unapproved Order No: {{ $orders->po_number }}</li>
                    <li>Unapproved Order Date: {{ \Carbon\Carbon::parse($orders->created_at )->format('d/m/Y')}} </li>
                    <li>Vendor Name : {{ $orders->vendor->legal_name }}</li>
                    <li>RFQ No : {{ $orders->rfq->rfq_id }}</li>
                    <li>PRN Number : {{ $orders->rfq->prn_no }}</li>
                    <li>Branch/Unit : {{ $orders->rfq->buyerBranch->name }}</li>
                </ul>
            </div>
            <div class="table-info px-15 pb-15">
                <div id="collapseInfoTwo" class="accordion-collapse collapse show" aria-labelledby="companyInfoTwo">
                    <div class="accordion-body">
                        <form class="" id="order-confirmation-form">
                            @csrf
                            <input type="hidden" name="vendor_currency" class="current-vendor-currency-symbol" value="{{ $orders->vendor_currency }}" >
                            <input type="hidden" name="po_number" class="current-vendor-currency-symbol" value="{{ $orders->po_number }}" >
                            <input type="hidden" name="order_id" class="current-vendor-currency-symbol" value="{{ $orders->id }}" >
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
                                            <th class="text-center">MRP ({{ $orders->vendor_currency }})</th>
                                            <th class="text-center">Disc.(%)</th>
                                            <th class="text-center">Rate ({{ $orders->vendor_currency }})</th>
                                            @if($isIndian)
                                            <th class="text-center">GST</th>
                                            @endif
                                            <th class="text-center">Amount ({{ $orders->vendor_currency }})</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                        $grandTotal=0;
                                        @endphp
                                        @foreach ($orders->order_variants as $key=>$item)

                                        @php
                                            $orderQuantity = $item->order_quantity;
                                            $orderMRP = $item->order_mrp;
                                            $orderDiscount = $item->order_discount;
                                            $orderPrice = $item->order_price;
                                            $productGST = $item->product_gst;

                                            $productTax = $isIndian ? $productGST : 0;

                                            $itemTotal = $orderPrice * $orderQuantity;
                                            $taxAmount = $isIndian ? (($itemTotal * $productTax) / 100) : 0;
                                            $total = $itemTotal + $taxAmount;

                                            $grandTotal += $total;
                                        @endphp

                                        <tr>
                                            <td>{{ $key+1; }}</td>
                                            <td class="text-center">
                                                <b class="font-size-12">{{ optional($item->product)->product_name }}</b>
                                            </td>
                                            <td class="text-center">{{ $item->frq_variant->specification }}</td>
                                            <td class="text-center">{{ $item->frq_variant->size }}</td>

                                            <td>
                                                <input type="text" value="{{ $orderQuantity }}" name="order_quantity[{{ $item->rfq_product_variant_id }}]"
                                                    data-gst="{{$productTax}}" data-price="{{ $orderPrice }}" data-pro-qty="{{ $orderQuantity }}"
                                                    class="form-control text-center bg-white product-quantity-field price-field mx-auto">
                                            </td>
                                            <td class="text-center">{{ optional($item->frq_variant->uoms)->uom_name }}</td>
                                            <td>
                                                <input type="text" value="{{ $orderMRP }}" name="order_mrp[{{ $item->rfq_product_variant_id }}]"
                                                    class="form-control text-center bg-white product-mrp-field price-field mx-auto">
                                            </td>
                                            <td>
                                                <input type="text" value="{{ $orderDiscount }}" name="order_discount[{{ $item->rfq_product_variant_id }}]"
                                                    class="form-control text-center bg-white product-discount-field price-field mx-auto">
                                            </td>
                                            <td>
                                                <input type="text" value="{{ $orderPrice }}" name="order_rate[{{ $item->rfq_product_variant_id }}]"
                                                    class="form-control text-center bg-white product-rate-field price-field mx-auto">
                                            </td>
                                            @if($isIndian)
                                            <td class="text-center">{{ $productGST }}%</td>
                                            @endif

                                            <td class="text-end product-subtotal-price">{{ $orders->vendor_currency }}{{ number_format($total, 2) }}</td>
                                        </tr>
                                        @endforeach

                                        <tr>
                                            <td><b>Total</b></td>
                                            <td colspan="{{ $isIndian ? 9 : 8 }}"></td>
                                            <td class="text-end grand-total-price" data-total-amount="{{ $grandTotal }}">{{ $orders->vendor_currency }}{{ number_format($grandTotal, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="pt-15">
                                <div class="border-bottom pb-15">
                                    <div class="blue-light-bg p-15 rounded">
                                        <div class="row justify-content-between">

                                            <div class="col-md-4 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <span class="bi bi-geo-alt"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control required" id="priceBasis"
                                                            placeholder="Price Basis" name="order_price_basis" maxlength="200"
                                                            value="{{ $orders->order_price_basis }}">
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
                                                        <input type="text" class="form-control required" id="paymentTerm"
                                                            placeholder="Payment Term" name="order_payment_term" maxlength="200"
                                                            value="{{ $orders->order_payment_term }}">
                                                        <label for="paymentTerm">Payment Term <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <span class="bi bi-calendar2-date"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control required" id="deliveryPeriod" maxlength="3" oninput="this.value=this.value.replace(/[^0-9]/,'')"
                                                            placeholder="Delivery Period (In Days)" name="order_delivery_period"
                                                            value="{{ $orders->order_delivery_period }}">
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
                                                        <input type="text" class="form-control" id="guranteeWarranty" name="order_gurantee_warranty"
                                                            placeholder="Gurantee/Warranty" maxlength="255">
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
                                                            placeholder="Remarks" value="{{ $orders->order_remarks }}" maxlength="2000"
                                                            name="order_remarks">
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
                                                        <input type="text" class="form-control" id="order_add_remarks" name="order_add_remarks"
                                                            placeholder="Additional Remarks" maxlength="2000"
                                                            value="{{ $orders->order_add_remarks }}">
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
                                                        <input type="text" class="form-control" id="buyer_order_number" name="buyer_order_number"
                                                            placeholder="Gurantee/Warranty" name="buyer_order_number" maxlength="255"
                                                            value="{{ $orders->buyer_order_number }}">
                                                        <label for="buyer_order_number">Buyer Order Number</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<div class="modal fade" id="generate-po-loader-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="compose-rfq-loader-label" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 text-center">
                        <img src="{{ asset('public/assets/images/raprocure-fevicon.png') }}" style="width: 7%;" class="img img-fluid">
                        <h4 class="po-generate-status">Purchase Order Generated Successfully</h4>
                        <button data-href="" class="center-block ra-btn small-btn ra-btn-primary po-success-button">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $(document).on('click', '.cancelPOBtn', function (e) {
            e.preventDefault();
            if(!confirm("Are you sure you want to cancel this Unapproved order?")){
                return false;
            }
            disable_on_cancel_order();

            let po_number = $(this).data('po-number');

            $.ajax({
                url: "{{ route('buyer.unapproved-orders.deletePO') }}",
                method: "POST",
                data: {po_number, _token: "{{ csrf_token() }}"},
                success: function (response) {
                    if (response.status) {
                        toastr.success(`Unapproved po deleted successfully.`);
                        setTimeout(() => {
                            window.location.href = response.url;
                        }, 2000);
                    } else {
                        // console.log(response.message);
                        toastr.error(`${response.message}`);
                    }
                    disable_on_cancel_order(false);
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    alert("Server error!");
                }
            });
        });

        let is_focused = false;
        let qty_focous = setTimeout(update_qty_focous, 1000);
        $(document).on('focus', ".product-quantity-field", function() {
            clearTimeout(qty_focous);
            is_focused = true;
        });
        function update_qty_focous() {
            is_focused = false;
        }
        function disable_on_cancel_order(is_true=true) {
            if(is_true){//disable btns
                $("#approve-po, .cancelPOBtn").addClass("disabled");
            }else{
                $("#approve-po, .cancelPOBtn").removeClass("disabled");
            }
        }
        function disableSubmitButton(is_true, _this=''){
            if(is_true==true){
                $(_this).attr("disabled", "disabled").addClass("disabled").html("Generating PO");
                $('.cancelPOBtn').addClass("disabled");
            }else{
                $(_this).removeAttr("disabled").removeClass("disabled").html('<i class="bi bi-check2"></i> Approve PO');
                $('.cancelPOBtn').removeClass("disabled");
            }
        }

        $(document).on('blur', ".product-quantity-field", function() {
            let p_qty = $(this).val();
            let actual_p_qty = $(this).data('pro-qty');
            let gst = $(this).data('gst');

            if($.isNumeric(p_qty)==false){
                $(this).val(actual_p_qty);
                p_qty = actual_p_qty;
            }

            if(parseFloat(actual_p_qty)<parseFloat(p_qty)){
                toastr.error("Product Quantity can not be greater that Unapproved Order Product Quantity");
                $(this).val(actual_p_qty);
                p_qty = actual_p_qty;
            }
            let p_price  =  $(this).parents('tr').find(".product-rate-field").val();

            let total1 = p_qty * p_price;
            if(gst!=' '){
                total1=total1 + (total1 * gst / 100);
            }
            let total = parseFloat(total1).toFixed(2);
            let vendor_currency_symbol = $(".current-vendor-currency-symbol").val();

            $(this).parents('tr').find(".product-subtotal-price").html(vendor_currency_symbol + IND_amount_format(total));

            rewiseGrandTotalPrice(this);
            qty_focous = setTimeout(update_qty_focous, 1000);
        });
        $(document).on('blur change', ".price-field", function () {
            const row = $(this).closest('tr'); // Get the parent row

            // Get values from the respective fields
            let p_qty = parseFloat(row.find(".product-quantity-field").val()) || 0;
            let p_mrp = parseFloat(row.find(".product-mrp-field").val()) || 0;
            let p_discount = parseFloat(row.find(".product-discount-field").val()) || 0;

            // Calculate the price after applying the discount
            let p_price = 0;
            if(p_mrp > 0 && p_discount > 0){
                p_price = p_mrp - (p_mrp * p_discount / 100);
                row.find(".product-rate-field").val(p_price.toFixed(2)); // Update price field
            }else{
                if(p_mrp > 0){
                    row.find(".product-rate-field").val(p_mrp.toFixed(2)); // Update price field
                }
                p_price = row.find(".product-rate-field").val();
            }

            // Get GST
            let gst = parseFloat(row.find(".product-quantity-field").data('gst')) || 0;

            // Calculate total price with GST
            let price_total = p_qty * p_price;
            if (gst) {
                price_total += (price_total * gst / 100);
            }
            let total = parseFloat(price_total).toFixed(2);

            // Format and update the subtotal price field
            let vendor_currency_symbol = $(".current-vendor-currency-symbol").val();
            row.find(".product-subtotal-price").html(vendor_currency_symbol + IND_amount_format(total));

            // Recalculate the grand total price
            rewiseGrandTotalPrice(this);
        });
        function rewiseGrandTotalPrice(_this) {
            let grand_total1 = 0;
            let t = 0;
            $(_this).parents("tbody").find(".product-quantity-field").each(function() {
                let gst  =  $(this).parents('tr').find(".product-quantity-field").data('gst');
                grand_total1 =$(this).parents("tr").find(".product-rate-field").val() * $(this).parents("tr").find(".product-quantity-field").val();

                if(gst!=''){
                    grand_total1+= (grand_total1 * gst / 100);
                    t +=grand_total1;
                }else{
                    t +=grand_total1;
                }
            });
            let grand_total = parseFloat(t).toFixed(2);
            let vendor_currency_symbol = $(".current-vendor-currency-symbol").val();
            $(_this).parents("tbody").find(".grand-total-price").html(vendor_currency_symbol + IND_amount_format(grand_total)).attr("data-total-amount", grand_total);
        }

        $(document).on("click", "#approve-po", function(){
            if (validateFormFields('order-confirmation-form', for_class = ".required")) {
                let is_valid_qty = true, is_valid_price = true;
                $('#order-confirmation-form').find(".product-quantity-field").each(function(){
                    if($(this).val()<=0){
                        is_valid_qty = false;
                    }
                });
                $('#order-confirmation-form').find(".product-rate-field").each(function(){
                    if($(this).val()<=0){
                        is_valid_price = false;
                    }
                });
                if(!is_valid_qty || !is_valid_price){
                    let error_msg='';
                    if(!is_valid_qty){
                        error_msg += "Quantity";
                    }
                    if(!is_valid_price){
                        error_msg += (error_msg!='' ? ' and ' : '')+"Rate";
                    }
                    toastr.error("Product "+error_msg+" should be greater than 0");
                    return false;
                }

                let ven_total_amount_value = parseFloat($(".grand-total-price").attr("data-total-amount")).toFixed(2);
                if(ven_total_amount_value<=0){
                    toastr.error("Total amount should be greater than 0");
                    return false;
                }
                disableSubmitButton(true, this);

                $("#order-confirmation-form").submit();
            }
        });

        $(document).on('submit', "#order-confirmation-form", function(event) {
            event.preventDefault();
            $.ajax({
                type: 'POST',
                url: "{{ route('buyer.rfq.unapproved-order.approve') }}",
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function(response) {},
                success: function(response) {                    
                    if (response.status==true) {
                        $(".po-success-button").data("href", response.redirect_url);
                        $("#generate-po-loader-modal").modal('show');
                        
                        disableSubmitButton(false, "#approve-po");
                    } else {
                        toastr.error(response.message);
                    } 
                },
                error: function(error) {
                    console.log(error);
                    disableSubmitButton(false, "#approve-po");
                },
                complete: function() {
                }
            });
        });

        $(document).on("click", ".po-success-button", function(){
            let redirect_url = $(this).data("href");
            window.location.href = redirect_url;
        });
        function validateFormFields(form_id, for_class = ".required") {
            var error_flags = true;
            $('#' + form_id + ' ' + for_class).each(function() {
                appendError(this);
                if ($(this).val() == null || $(this).val() == '') {
                    error_flags = false;
                    appendError(this, "This Field is Required");
                }
            });
            return error_flags;
        }

        function appendError(obj, msg = '') {
            $(obj).parent().find('.error-message').remove();
            if (msg) {
                $(obj).parent().append('<span class="text-danger error-message">' + msg + '</span>');
            }
        }
    });
    function printdiv() {
        $.ajax({
            url: "{{ route('buyer.unapproved-orders.print') }}",
            method: "POST",
            processData: false,
            contentType: false,
            data: new FormData($('#order-confirmation-form')[0]),
            success: function(response) {
                // Open a new window
                var printWindow = window.open('', '_blank', 'width=800,height=600');
                // Write the HTML content
                printWindow.document.open();
                printWindow.document.write(response);
                printWindow.document.close();
                // Wait for content to load, then print
                printWindow.onload = function () {
                    printWindow.focus();
                    printWindow.print();
                    // Optional: Close after printing
                    printWindow.onafterprint = function () {
                        printWindow.close();
                    };
                };
            },
            error: function(xhr) {
                alert("Failed to fetch print content.");
            }
        });
    }

</script>
@endsection
