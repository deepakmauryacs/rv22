@php
    use App\Helpers\NumberFormatterHelper;
@endphp
@extends('buyer.layouts.appInventory')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/manualPODetailsPage.css') }}">
@endpush
@push('headJs')
    @once
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    @endonce
@endpush
@section('content')


    <div class="card rounded">
        <div class="card-header bg-white">
            <div class="row align-items-center justify-content-between my-3">
                <div class="col-12 col-sm-auto">
                    <h1 class="font-size-22 mb-0">Order Details</h1>
                </div>
                <div class="col-12 col-sm-auto mt-3 mt-sm-0">
                    <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2" id="cancelSection">
                        @if ($order->order_status=='1')
                            <a href="#" class="ra-btn ra-btn-outline-danger font-size-11 filterBtn cancelManualOrderBtn" data-order-id="{{ $order->id }}">
                                <span class="bi bi-x-circle d-none d-sm-inline-block" aria-hidden="true"></span> CANCEL
                            </a>
                            <a href="{{ route('buyer.report.manualpo.download', $order->id) }}"  class="ra-btn ra-btn-primary font-size-11 merge-selected-rfq-btn export-btn " id="export">
                                <span class="bi bi-download d-none d-sm-inline-block" aria-hidden="true"></span> DOWNLOAD
                            </a>
                        @elseif($order->order_status=='2')
                            <div class="alert alert-danger font-size-12 fw-bold mb-0 filterBtn">Order Cancelled</div>
                        @endif

                    </div>
                    <a href="{{ route('buyer.report.manualpo') }}" class="ra-btn ra-btn-primary font-size-11 width-inherit"><span class="bi bi-arrow-left-square bi-md font-size-12 d-none d-sm-inline-block" aria-hidden="true"></span> BACK</a>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-body">
            <div class="unapproved-order-page shadow-none list-for-rfq-wrap mb-3">
                <ul class="list-for-rfq">
                    <li><strong>Order No:</strong> {{$order->manual_po_number}}</li>
                    <li><strong>Order Date:</strong> {{ $order->created_at->format('d/m/Y') }}</li>
                    <li><strong>Vendor Name:</strong> {{ strtoupper($order->vendor->name) }}</li>
                    <li><strong>Branch/Unit:</strong> {{$order->branch_name}}</li>

                </ul>

            </div>

            <div class="table-responsive">
                <table class="product-listing-table w-100">
                <thead>
                <tr>
                    <th>S.No</th>
                    <th>Products</th>
                    <th>Specification</th>
                    <th class="text-center">Size</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-center">UOM</th>
                    <th class="text-center">Rate({{session('user_currency')['symbol'] ?? '₹'}})</th>
                    <th class="text-center">GST</th>
                    <th class="text-end">Amount ({{session('user_currency')['symbol'] ?? '₹'}})</th>
                </tr>
                </thead>
                <tbody>
                @php $currency=session('user_currency')['symbol'] ?? '₹';$totalAmount=0; @endphp
                @foreach($order->products as $key => $product)
                    <tr class="highlight">
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $product->product->product_name }}</td>
                        <td class="text-wrap">{{ $product->inventory->specification }}</td>
                        <td class="text-center text-wrap">{{ $product->inventory->size }}</td>
                        <td class="text-center">{{NumberFormatterHelper::formatQty($product->product_quantity,$currency)}}</td>
                        <td class="text-center">{{ $product->inventory->uom->uom_name }}</td>
                        <td class="text-center">{{NumberFormatterHelper::formatCurrency($product->product_price,$currency)}}</td>
                        <td class="text-center">{{ $product->tax->tax ?? '0' }} %</td>
                        <td class="text-end">
                            @php
                                $price = $product->product_price;
                                $qty = $product->product_quantity;
                                $taxPercent = floatval($product->tax->tax ?? 0);

                                $subtotal = $price * $qty;
                                $gstAmount = $subtotal * ($taxPercent / 100);
                                $totalWithGst = $subtotal + $gstAmount;
                                $totalAmount+= $totalWithGst;
                            @endphp
                            {{NumberFormatterHelper::formatCurrency($totalWithGst,$currency)}}</td>
                    </tr>
                @endforeach

                </tbody>
                <tfoot>
                <tr>
                    <th colspan="8" class="text-end">Total</th>
                    <th class="text-end">{{NumberFormatterHelper::formatCurrency($totalAmount,$currency)}}</th>
                </tr>
                </tfoot>
            </table>
            </div>



            <div class="form-section mt-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Price Basis</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-geo-alt"></i>
                            </span>
                            <input type="text" class="form-control" value="{{$order->order_price_basis}}" disabled>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Terms</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="text" class="form-control" value="{{$order->order_payment_term}}" disabled>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Delivery Period (in Days)</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-calendar"></i>
                            </span>
                            <input type="text" class="form-control" value="{{$order->order_delivery_period}}" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Remarks</label>
                        <input type="text" class="form-control" disabled  value="{{$order->order_remarks}}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Additional Remarks</label>
                        <input type="text" class="form-control" disabled value='{{$order->order_add_remarks}}'>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- start pingki --}}
    @push('exJs')
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.js"></script>

        <script>
            $(document).on('click', '.cancelManualOrderBtn', function (e) {
                e.preventDefault();

                if (!confirm('Are you sure you want to cancel this order?')) {
                    return;
                }

                let orderId = $(this).data('order-id');
                let token = $('meta[name="csrf-token"]').attr('content');

               $.ajax({
                    url: "{{ route('buyer.report.manualpo.cancelOrder') }}",
                    type: 'POST',
                    data: {
                        _token: token,
                        order_id: orderId
                    },
                    success: function (response) {
                        if (response.status === 1) {
                            toastr.success(response.message);
                            $('#cancelSection').html(`<div class="btn-rfq btn-rfq-danger filterBtn">Order Cancelled</div>`);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function () {
                        toastr.error('Something went wrong. Please try again.');
                    }
                });

            });

        </script>
    @endpush
    {{-- end pingki --}}
@endsection
