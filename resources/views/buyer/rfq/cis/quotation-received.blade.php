@extends('buyer.layouts.app', ['title'=>'Quotation Received'])

@section('css')
@endsection

@section('content')
<div class="bg-white">
    <!---Sidebar-->
    @include('buyer.layouts.sidebar-menu')
</div>

<!---Section Main-->
<main class="main flex-grow-1">
    <div class="container-fluid">
        <!-- Start of Breadcrumb -->
        <nav class="breadcrumb mb-2 font-size-13">
            <a class="breadcrumb-item text-muted" href="{{ route('buyer.dashboard') }}">Dashboard</a>
            <a class="breadcrumb-item text-muted" href="{{ route('buyer.rfq.cis-sheet',$rfq_id) }}">CIS</a>
            <span class="breadcrumb-item active text-dark" aria-current="page">Quotation Received</span>
        </nav>
        <!-- RFQ Listing -->
        <section class="rfq-vendor-listing card rounded shadow-none mb-4">
            <div class="card-body">
                <ul>
                    <li>
                        <span>RFQ No:</span>
                        <span class="fw-bold">{{$rfq->rfq_id}}</span>
                    </li>
                    <li>
                        <span>RFQ Date:</span>
                        <span
                            class="fw-bold">{{!empty($rfq->created_at)?date('d/m/Y',strtotime($rfq->created_at)):''}}</span>
                    </li>
                    <li>
                        <span>PRN Number:</span>
                        <span class="fw-bold">{{$rfq->prn_no}}</span>
                    </li>
                    <li>
                        <span>Vendor Name:</span>
                        <span class="fw-bold">{{$rfq_vendor->legal_name}}</span>
                    </li>
                    <li>
                        @php $branch=getbuyerBranchById($rfq->buyer_branch);@endphp
                        <span>Branch Name:</span>
                        <span class="fw-bold">{{$branch->name}}</span>
                    </li>
                    <li>
                        <span>Branch Address:</span>
                        <span class="fw-bold">
                            {{$branch->address}}
                            <span role="button" type="button"
                                class="btn btn-link p-0 height-inherit text-black font-size-14" data-bs-toggle="tooltip"
                                data-placement="top" data-bs-original-title="{{$branch->address}}">
                                <span class="bi bi-info-circle-fill font-size-14"></span>
                            </span>
                        </span>
                    </li>
                    <li>
                        <span>Last Date to Response:</span>
                        <span
                            class="fw-bold">{{ $rfq->last_response_date ? \Carbon\Carbon::parse($rfq->last_response_date)->format('d/m/Y') : '-' }}</span>
                    </li>
                    <li>
                        <span class="fw-bold"><b class="text-primary-blue">RFQ Terms</b> - Price Basis:</span>
                        <span class="fw-bold">{{$rfq->buyer_price_basis}}</span>
                    </li>

                    <li>
                        <span>Payment Terms:</span>
                        <span class="fw-bold">{{$rfq->buyer_pay_term}}</span>
                    </li>
                    <li>
                        <span>Delivery Period:</span>
                        <span class="fw-bold">{{$rfq->buyer_delivery_period}}</span>
                    </li>
                    <li>
                        <button type="button" onclick="window.open(`{{ route('buyer.rfq.quotation-received.print',['rfq_id'=> $rfq_id,'vendor_id'=>$vendor_id]) }}`, '_blank', 'width=800,height=600,resizable=yes,scrollbars=yes'); return false;"
                            class="ra-btn ra-btn-sm px-3 ra-btn-outline-primary send-quote-btn font-size-11">
                            Download Quotation PDF
                        </button>
                    </li>
                </ul>
            </div>
        </section>
        @foreach($rfq->rfqProducts as $key=> $product)
        <!---RFQ Vendor list section-->
        <section class="mx-0 mx-md-2 py-2">
            <div class="card card-vendor-list">
                <!-- Top breadcrumb -->
                <div class="d-flex justify-content-between mb-30">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-vendor">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">{{++$key}}.
                                        {{$product->masterProduct->division->division_name}}</a></li>
                                <li class="breadcrumb-item"><a
                                        href="javascript:void(0);">{{$product->masterProduct->category->category_name}}</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    {{$product->masterProduct->product_name}}
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <!-- Product List Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive table-product px-2">
                            <table class="table table-product-list min-width-1100">
                                <thead>
                                    @php
                                        $showAttachment = $product->productVariants->contains(function ($v) {
                                            return !empty($v->attachment);
                                        });
                                    @endphp
                                    <tr>
                                        <th scope="col" class="text-nowrap text-dark">
                                            #
                                        </th>
                                        <th scope="col" class="text-nowrap text-dark text-start long-spec">
                                            Specification
                                        </th>
                                        <th scope="col" class="text-nowrap text-dark">
                                            Size
                                        </th>
                                        <th scope="col" class="text-nowrap text-dark">
                                            Quantity/UOM
                                        </th>
                                        @if ($showAttachment)
                                        <th>Attachment</th>
                                        @endif
                                        <th scope="col" class="text-nowrap text-dark">
                                            Price(₹)
                                        </th>
                                        <th scope="col" class="text-nowrap text-dark">
                                            MRP(₹)
                                        </th>
                                        <th scope="col" class="text-nowrap text-dark">
                                            Disc.(%)
                                        </th>
                                        <th scope="col" class="text-nowrap text-dark">
                                            Total(₹)
                                        </th>
                                        <th scope="col" class="text-nowrap text-dark">
                                            Counter <br>Offer
                                        </th>
                                        <th scope="col" class="text-nowrap text-dark">
                                            Historical <br>Price
                                        </th>
                                        <th scope="col" class="text-nowrap text-dark text-start short-spec">
                                            Specs
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($product->productVariants as $keyj => $variant)

                                    <tr>
                                        <td>{{++$keyj}}.</td>
                                        <td>
                                            <span class="text-muted">{!!$variant->specification!!}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted text-nowrap">{!!$variant->size!!}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted text-nowrap">{{$variant->quantity}}
                                                {{getUOMName($variant->uom)}}</span>
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
                                        <td class="text-center">
                                            <span
                                                class="text-muted text-nowrap">{{$variant->latestVendorQuotation($vendor_id)?->price}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="text-muted text-nowrap">{{$variant->latestVendorQuotation($vendor_id)?->mrp}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="text-muted text-nowrap">{{$variant->latestVendorQuotation($vendor_id)?->discount}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted text-nowrap">
                                                @if($variant->latestVendorQuotation($vendor_id)?->price>0)
                                                {{$variant->latestVendorQuotation($vendor_id)?->vendor_currency}}
                                                {{IND_money_format($variant->quantity*$variant->latestVendorQuotation($vendor_id)?->price)}}
                                                @endif
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted text-nowrap">
                                                @if(optional($variant->vendorQuotations->where('buyer_price', '>',
                                                0)->where('vendor_id',$vendor_id)->sortByDesc('updated_at')->first())->buyer_price)
                                                {{ optional($variant->vendorQuotations->where('vendor_id',$vendor_id)->where('buyer_price', '>', 0)->sortByDesc('updated_at')->first())->buyer_price }}
                                                <button type="button" class="btn btn-link p-0 text-light-gery"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $variant->vendorQuotations->where('vendor_id',$vendor_id)
                                                                        ->where('buyer_price', '>', 0)
                                                                        ->map(function ($q) {
                                                                            return $q->buyer_price . ' (' . \Carbon\Carbon::parse($q->created_at)->format('d-M') . ')';
                                                                        })
                                                                        ->implode(', ')}}">
                                                    <span class="bi bi-info-circle-fill font-size-13 "></span>
                                                </button>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">

                                                @php
                                                $latestQuotation =
                                                $variant->vendorQuotations->where('vendor_id',$vendor_id)->sortByDesc('created_at')->first();
                                                @endphp

                                                @if ($latestQuotation)
                                                <button type="button" class="btn btn-link p-0 text-light-gery"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $variant->vendorQuotations->where('vendor_id',$vendor_id)->map(function($q) {
                                                                        return $q->price . ' (' . \Carbon\Carbon::parse($q->created_at)->format('d-M') . ')';
                                                                    })->implode(', ') }}">
                                                    <span class="bi bi-info-circle-fill font-size-13 "></span>
                                                </button>

                                                {{ $latestQuotation->price }}
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <span class="d-block position-relative">
                                                <span class="text-muted text-truncate view-spec"
                                                    title="{{$variant->latestVendorQuotation($vendor_id)?->specification}}"
                                                    onclick="showSpecs('{{$variant->latestVendorQuotation($vendor_id)?->specification}}');">
                                                    {{ substr($variant->latestVendorQuotation($vendor_id)?->specification, 0, 25) }}
                                                </span>
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>


                <!-- Search by Brand and Remarks -->
                <div class="row mt-4 gy-2 rfq-details-top-filter">
                    <div class="col-md-4 mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-pencil"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="remarks" value="{{ $product->remarks}}"
                                    placeholder="Remarks" readonly disabled>
                                <label for="remarks" class="font-size-13">Remarks</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-tags"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="brand" placeholder="Brand" readonly disabled
                                    value="{{ $product->brand }}">
                                <label for="brand" class="font-size-13">Brand</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <span class="form-control" readonly disabled>Attachment File...</span>
                        {{$rfq->getLastRfqVendorQuotation->vendor_attachment_file}}
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-tags"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="sellerBrand" placeholder="Seller Brand"
                                    value="{{$rfq->getLastRfqVendorQuotation->vendor_brand}}" readonly disabled>
                                <label for="sellerBrand" class="font-size-13">Seller Brand</label>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </section>
        @endforeach

        <!-- Section Remarks -->
        <section>
            <div class="mb-3">
                <p class="fw-bold">Remarks:</p>
                <p>{{$rfq_vendor_quotation->vendor_remarks??''}}</p>
            </div>
            <div class="mb-3">
                <p class="fw-bold">Additional Remarks:</p>
                <p>{{$rfq_vendor_quotation->vendor_additional_remarks??''}}</p>
            </div>
        </section>

        <!-- Product more details -->
        <section class="product-option-filter">
            <div class="card shadow-none">
                <div class="card-body">
                    <div class="row gx-3 gy-4 pt-3 justify-content-center align-items-center">
                        <div class="col-12 col-sm-auto col-xxl-2">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-geo-alt" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control form-control-price-basis" id="priceBasis"
                                        placeholder="Price Basis"
                                        value="{{$rfq_vendor_quotation->vendor_price_basis??''}}" readonly disabled>
                                    <label for="priceBasis">Price Basis</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto col-xxl-2">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-currency-rupee" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control form-control-payment-terms" id="paymentTerms"
                                        placeholder="Payment Terms"
                                        value="{{$rfq_vendor_quotation->vendor_payment_terms??''}}" readonly disabled>
                                    <label for="paymentTerms">Payment Terms</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto col-xxl-2">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-calendar-date" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control form-control-delivery-period"
                                        id="deliveryPeriodInDays" placeholder="Delivery Period (In Days)"
                                        value="{{$rfq_vendor_quotation->vendor_delivery_period??''}}" readonly disabled>
                                    <label for="deliveryPeriodInDays">Delivery Period (In Days)</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto col-xxl-2">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-calendar-date" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control form-control-price-validity"
                                        id="priceValidityInDays" placeholder="Price Validity (In Days)"
                                        value="{{$rfq_vendor_quotation->vendor_price_validity??''}}" readonly disabled>
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
                                    <input type="text" class="form-control form-control-dispatch-branch"
                                        id="dispatchBranch" placeholder="Dispatch Branch"
                                        value="{{!empty($rfq_vendor_quotation->vendor_dispatch_branch)? getVendorBranchById($rfq_vendor_quotation->vendor_dispatch_branch)->name:''}}"
                                        readonly disabled>
                                    <label for="dispatchBranch">Dispatch Branch</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto col-xxl-2">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-currency-exchange" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <select class="form-select form-select-currency" id="updateCurrency"
                                        aria-label="Select" readonly disabled>
                                        <option value="{{$rfq_vendor_quotation->vendor_currency??''}}"
                                            data-symbol="{{$rfq_vendor_quotation->vendor_currency??''}}" selected="">
                                            {{$rfq_vendor_quotation->vendor_currency??''}}</option>
                                    </select>
                                    <label for="updateCurrency">Currency</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row pt-3 gx-3 gy-3 justify-content-center align-items-center">
                        <div class="col-12 col-sm-auto text-center">
                            <a href="{{ route('buyer.rfq.cis-sheet', ['rfq_id'=>$rfq_id]) }}"
                                class="ra-btn ra-btn-sm px-3 ra-btn-primary send-quote-btn">
                                <span class="bi bi-arrow-left-short font-size-14" aria-hidden="true"></span>
                                Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Spec Modal -->

<div class="modal fade" id="viewSpec" tabindex="-1" aria-labelledby="viewSpecLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-12" id="viewSpecLabel">View Specs</h2>
                <button type="button" class="btn-close font-size-10 text-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3" id="specs_result">
                    this is specs from the vendor side
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function showSpecs(data){
        $('#viewSpec').modal('show');
        $('#specs_result').html(data);
    }
</script>
@endsection
