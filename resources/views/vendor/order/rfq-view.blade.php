@extends('vendor.layouts.app_second',['title'=>'Orders Confirmed','sub_title'=>'View Order'])
@section('css')
 
@endsection
@section('breadcrumb')
 
<div class="breadcrumb-header">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vendor.rfq_order.index') }}">Orders Confirmed</a></li>
                <li class="breadcrumb-item active" aria-current="page">View Order</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
<section class="container-fluid">
    <!-- Start Breadcrumb Here -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-global py-2 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item">RFQ Received</li>
            <li class="breadcrumb-item active" aria-current="page">Order Details</li>
        </ol>
    </nav>
    <!-- Start Content Here -->
    <section class="manage-product">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between bg-white py-3">
                <h1 class="card-title font-size-18 mb-0">Order Details</h1>
                <div class="row g-3">
                    <div class="col-auto">
                        <button type="button" onclick="window.open(`{{ route('vendor.rfq_order.print', $order->id) }}`, '_blank', 'width=800,height=600,resizable=yes,scrollbars=yes'); return false;"
                            class="ra-btn ra-btn-outline-primary mw-inherit-mobile d-flex align-items-center font-size-12">
                            <span class="bi bi-download font-size-11 fs-16-mobile"></span>
                            <span class="font-size-11 d-none d-sm-block">Download</span>
                        </button>
                    </div>
                    <div class="col-auto">
                        <button type="button" onclick="window.location.href='{{ route('vendor.rfq_order.index') }}';"
                            class="ra-btn ra-btn-primary d-flex align-items-center font-size-12">
                            <span class="bi bi-arrow-left-square font-size-11"></span>
                            <span class="font-size-11">Back</span>
                        </button>
                    </div>

                </div>

            </div>
            <div class="card-body add-product-section">
                <div class="rfq-vendor-listing-details mb-3">
                    <ul>
                        <li>Order No: {{ $order->po_number }}</li>
                        <li>Order Date: {{ date('d/m/Y', strtotime($order->created_at)) }}</li>
                        <li>Buyer Order Number: {{ $order->buyer_order_number ?? '-' }}</li>
                        <li>Buyer Name: {{ $order->buyer->legal_name ?? '-' }}</li>
                        <li>RFQ No: {{ $order->rfq_id ?? '-' }}</li>
                        <li>PRN Number: </li>
                        <li>Branch/Unit: </li>
                    </ul>
                </div>
                <div class="rfq-order-details">
                    <div class="table-responsive">
                        <table class="table ra-table ra-table-stripped rfq-order-details-table">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-uppercase">
                                        S.No.
                                    </th>
                                    <th scope="col" class="text-uppercase">
                                        PRODUCTS
                                    </th>
                                    <th scope="col" class="text-uppercase">
                                        SPECIFICATION
                                    </th>
                                    <th scope="col" class="text-uppercase">
                                        SIZE
                                    </th>
                                    <th scope="col" class="text-uppercase">
                                        QUANTITY
                                    </th>
                                    <th scope="col" class="text-uppercase">
                                        UOM
                                    </th>
                                    <th scope="col" class="text-uppercase">
                                        MRP(₹)
                                    </th>
                                    <th scope="col" class="text-uppercase">
                                        DISCOUNT(%)
                                    </th>
                                    <th scope="col" class="text-uppercase">
                                        Rate(₹)
                                    </th>
                                    <th scope="col" class="text-uppercase">
                                        GST
                                    </th>
                                    <th scope="col" class="text-uppercase">
                                        Amount(₹)
                                    </th>
                                </tr>
                            </thead>
                            
                            <tbody>
                                @php 
                                $total=0;$gtotal=0; 
                                @endphp
                                @foreach ($order->order_variants as $key => $value)
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
                                    <td>0%</td>
                                    <td class="text-end">
                                        @php
                                        $total=$value->order_price*$value->order_quantity;
                                        $gtotal+=$total;
                                        @endphp
                                        ₹{{$total}}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="10" class="fw-bold text-start">Total</td>
                                    <td class="text-end">
                                        ₹{{$gtotal}}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="rfq-order-details-form bg-light rounded mt-4 mt-sm-3">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-journal-medical" aria-hidden="true"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="paymentTerms"
                                    placeholder="Payment Terms" value="{{$order->order_payment_term}}" readonly>
                                <label for="paymentTerms">Payment Terms</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-calendar-date" aria-hidden="true"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="deliveryPeriod"
                                    placeholder="Delivery Period" value="{{$order->order_delivery_period}}" readonly>
                                <label for="deliveryPeriod">Delivery Period</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-currency-rupee" aria-hidden="true"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="priceBasis"
                                    placeholder="Price Basis" value="{{$order->order_price_basis}}" readonly>
                                <label for="priceBasis">Price Basis</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-pencil" aria-hidden="true"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="remarks"
                                    placeholder="Remarks" value="{{$order->order_remarks}}" disabled>
                                <label for="remarks">Remarks</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-tag-fill" aria-hidden="true"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="additionalRemarks"
                                    placeholder="Additional Remarks"  value="{{$order->order_add_remarks}}" disabled>
                                <label for="additionalRemarks">Additional Remarks</label>
                            </div>
                        </div>
                    </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</section>
@endsection

@section('scripts')


@endsection