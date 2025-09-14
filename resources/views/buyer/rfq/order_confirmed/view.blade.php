@extends('buyer.layouts.app', ['title'=>'Order Confirmed'])

@section('css')
    <style>
        #cancelled-order{
            background: #ffefee;
            border-color: #ffefee;
            color: #FF4C41 !important; 
            min-width: 270px;
        }
    </style>
@endsection
@section('content')
<div class="bg-white">
    <!---Sidebar-->
    @include('buyer.layouts.sidebar-menu')
</div>

@php
    $vendor_currency = $order->vendor_currency ?? '₹';    
@endphp

<!---Section Main-->
<main class="main flex-grow-1">
    <div class="container-fluid">
        <div class="bg-white unapproved-order-page">
            <h3 class="card-head-line d-flex justify-content-between">
                Order Details
                <div>
                    @if($order->order_status == '1')
                        <button type="button" class="ra-btn small-btn ra-btn-outline-danger" id="cancel-order">
                            <span class="bi bi-x-circle" aria-hidden="true"></span> Cancel
                        </button>
                    @elseif($order->order_status == '2')
                        <span class="badge px-4 py-2 fs-6" id="cancelled-order"><strong>Order Cancelled</strong></span>
                    @endif
                    <button type="button" onclick="window.open(`{{ route('buyer.rfq.order-confirmed.print', $order->id) }}`, '_blank', 'width=800,height=600,resizable=yes,scrollbars=yes'); return false;"
                        class="ra-btn btn-outline-primary ra-btn-outline-primary small-btn text-uppercase text-nowrap">
                        <span class="bi bi-download" aria-hidden="true"></span> Download
                    </button>
                    <button type="button"  onclick="window.location.href='{{ route('buyer.rfq.order-confirmed') }}';" class="ra-btn small-btn ra-btn-primary small-btn">
                        <span class="bi bi-arrow-left-square" aria-hidden="true"></span>
                        Back
                    </button>
                </div>
            </h3>
            <div class="list-for-rfq-wrap">
                <ul class="list-for-rfq">
                    <li>Order No: {{ $order->po_number }}</li>
                    <li>Order Date: {{ date('d/m/Y', strtotime($order->created_at)) }}</li>
                    <li>Buyer Order Number: {{ $order->buyer_order_number }}</li>
                    <li>Vendor Name: {{ $order->vendor->legal_name ?? '-' }}</li>
                    <li>RFQ No: {{ $order->rfq_id ?? '-' }}</li>
                    <li>PRN Number: {{$order->rfq->prn_no}}</li>
                    <li>Gurantee/Warranty: {{$order->guranty_warranty}}</li>
                    <li>Branch/Unit: {{!empty($order->rfq->buyer_branch)?getbuyerBranchById($order->rfq->buyer_branch)->name:''}}</li>
                </ul>
                
            </div>
            <div class="table-info px-15 pb-15">
                <div class="table-responsive">
                    <table class="product-listing-table w-100">
                        <thead>
                            <tr>
                                <th> S.No.</th>
                                <th>Products</th>
                                <th>Specification</th>
                                <th>Size</th>
                                <th>Quantity</th>
                                <th>UOM</th>
                                <th>MRP({{$vendor_currency}})</th>
                                <th>Disc(%)</th>
                                <th>Rate({{$vendor_currency}})</th>
                                @if($order->int_buyer_vendor==2)
                                <th>GST</th>
                                @endif
                                <th class="text-end">Amount({{$vendor_currency}})</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $gtotal = 0;
                            @endphp
                            @foreach ($order->order_variants as $key => $value)
                                @php
                                    $gst = 0;
                                    $total=$value->order_price*$value->order_quantity;
                                    if($order->int_buyer_vendor==2){
                                        $gst = ($total*$value->product_gst)/100;
                                    }
                                    $total += $gst;
                                    $gtotal += $total;
                                @endphp
                                <tr>
                                    <td class="fw-bold text-start">{{++$key}}</td>
                                    <td class="fw-bold">{{$value->product->product_name}}</td>
                                    <td>{{$value->frq_variant->specification}}</td>
                                    <td>{{$value->frq_variant->size}}</td>
                                    <td>{{$value->order_quantity}}</td>
                                    <td>{{$value->frq_variant->uoms->uom_name}}</td>
                                    <td>{{$value->order_mrp}}</td>
                                    <td>{{$value->order_discount}}</td>
                                    <td>{{$value->order_price}}</td>
                                    @if($order->int_buyer_vendor==2)
                                    <td>{{$value->product_gst}}%</td>
                                    @endif
                                    <td class="text-end">
                                        {{IND_money_format($total)}}
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="fw-bold mt-3 pt-3">
                                <td colspan="{{$order->int_buyer_vendor==2 ? '10' : '9'}}" class="fw-bold text-start">Total</td>
                                <td class="text-end">
                                    {{$vendor_currency}}{{IND_money_format($gtotal)}}
                                </td>
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
                                                placeholder="Price Basis" value="{{$order->order_price_basis}}">
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
                                                placeholder="Payment Term" value="{{$order->order_payment_term}}">
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
                                                value="{{$order->order_delivery_period}}">
                                            <label for="deliveryPeriod">Delivery Period (In Days) <span
                                                    class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-sm-6 col-12 mt-4">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="bi bi-pencil"></span>
                                        </span>
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="guranteeWarranty"
                                                value="{{$order->order_remarks}}" placeholder="Gurantee/Warranty">
                                            <label for="guranteeWarranty">Remarks</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-6 col-12 mt-4">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="bi bi-pencil"></span>
                                        </span>
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="guranteeWarranty"
                                                value="{{$order->order_add_remarks}}"
                                                placeholder="Gurantee/Warranty">
                                            <label for="guranteeWarranty">Additional Remarks</label>
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
@endsection

@section('scripts')

<script>
    $(document).ready(function() {
        @if($order->order_status == '1')
        $(document).on("click", "#cancel-order", function() {
            if(confirm("Are you sure you want to cancel this order?")) {
                $.ajax({
                    url: "{{route('buyer.rfq.order-confirmed.cancel', $order->id)}}",
                    type: "POST",
                    data: {_token: '{{csrf_token()}}'},
                    success: function(response) {
                        if(!response.status) {
                            toastr.error(response.message);
                        } else if(response.status) {
                            toastr.success(response.message);
                            setTimeout(
                                function(){ 
                                    window.location.href = "{{route('buyer.rfq.order-confirmed')}}";
                                }, 1000);
                        }
                    }
                });
            }
        });
        @endif
    });
</script>

@endsection