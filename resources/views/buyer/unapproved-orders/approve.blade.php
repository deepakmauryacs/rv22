@extends('buyer.layouts.app', ['title'=>'Unapproved Order Confirmation'])

@section('css')
<style>
    .btn-grp {
        position: absolute;
        top: 4%;
        right: 3%;
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
                <button type="button"
                    class="ra-btn btn-outline-primary ra-btn-outline-primary small-btn text-uppercase text-nowrap">
                    <span class="bi bi-check-lg" aria-hidden="true"></span> Approve PO
                </button>
                <button type="button"
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
                                        <th class="text-center">MRP (₹)</th>
                                        <th class="text-center">Disc.(%)</th>
                                        <th class="text-center">Rate (₹)</th>
                                        <th class="text-center">GST</th>
                                        <th class="text-center">Amount (₹)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $grandTotal=0;
                                    @endphp
                                    @foreach ($orders->order_variants as $key=>$item)

                                    @php
                                    $orderQuantity=$item->order_quantity;
                                    $orderMRP=$item->order_mrp;
                                    $orderDiscount=$item->order_discount;
                                    $orderPrice=$item->order_price;
                                    $productGST=$item->product_gst;

                                    $productTax = $isIndian ? $productGST : 0;

                                    $itemTotal = $orderPrice * $orderQuantity;
                                    $taxAmount = $itemTotal * $productTax;
                                    $total = $itemTotal + $taxAmount;

                                    $grandTotal += $total;
                                    @endphp

                                    <tr>
                                        <td>{{ $key+1; }}</td>
                                        <td class="text-center"><b class="font-size-12">{{
                                                optional($item->product)->product_name }}</b></td>
                                        <td class="text-center">{{ $item->frq_variant->specification }}</td>
                                        <td class="text-center">{{ $item->frq_variant->size }}</td>

                                        <td>
                                            <input type="text" value="{{ $orderQuantity }}"
                                                class=" form-control text-center bg-white product-quantity-field mx-auto">
                                        </td>
                                        <td class="text-center">{{ optional($item->frq_variant->uoms)->uom_name }}</td>
                                        <td>
                                            <input type="text" value="{{  $orderMRP }}"
                                                class=" form-control text-center bg-white product-mrp-field mx-auto">
                                        </td>
                                        <td><input type="text" value="{{  $orderDiscount }}"
                                                class=" form-control text-center bg-white product-discount-field mx-auto">
                                        </td>
                                        <td><input type="text" value="{{ $orderPrice }}"
                                                class=" form-control text-center bg-white product-rate-field mx-auto">
                                        </td>
                                        <td class="text-center">{{ $productGST }}%</td>


                                        <td class="text-end">{{ $orders->vendor_currency }}{{ number_format($total,2 )
                                            }}</td>
                                    </tr>
                                    @endforeach



                                    <tr>
                                        <td><b>Total</b></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-end">{{ $orders->vendor_currency }}{{ number_format($grandTotal,
                                            2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="pt-15">
                            <div class="border-bottom  pb-15">
                                <form class="blue-light-bg p-15 rounded">
                                    <div class="row justify-content-between">

                                        <div class="col-md-4 col-sm-6 col-12 mt-4">
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <span class="bi bi-geo-alt"></span>
                                                </span>
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="priceBasis"
                                                        placeholder="Price Basis"
                                                        value="{{ $orders->order_price_basis }}">
                                                    <label for="priceBasis">Price Basis <span
                                                            class="text-danger">*</span></label>
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
                                                        placeholder="Payment Term"
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
                                                    <input type="text" class="form-control" id="deliveryPeriod"
                                                        placeholder="Delivery Period (In Days)"
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
                                                    <input type="text" class="form-control" id="guranteeWarranty"
                                                        placeholder="Gurantee/Warranty">
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
                                                    <input type="text" class="form-control" id="order_remarks"
                                                        placeholder="Remarks" value="{{ $orders->order_remarks }}"
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
                                                    <input type="text" class="form-control" id="order_add_remarks"
                                                        placeholder="Additional Remarks"
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
                                                    <input type="text" class="form-control" id="buyer_order_number"
                                                        placeholder="Gurantee/Warranty" name="buyer_order_number"
                                                        value="{{ $orders->buyer_order_number }}">
                                                    <label for="buyer_order_number">Buyer Order Number</label>
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
        </div>
    </div>
</main>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
    $(document).on('click', '.cancelPOBtn', function (e) {
        e.preventDefault();
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
                    console.log(response.message);
                    toastr.error(`${response.message}`);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert("Server error!");
            }
        });
    });
});

</script>
@endsection