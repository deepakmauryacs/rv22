@extends('buyer.layouts.app', ['title'=>'RFQ Details'])

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
        <!---RFQ Filter section-->
        <section class="rfq-details-top-filter mt-2 mb-2 mx-0 mx-md-2 pt-2">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="row align-items-center justify-content-xl-center flex-wrap gx-3">
                        <div class="col-6 col-md-auto mb-4">
                            <div class="input-group generate-rfq-input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-file-earmark-text"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="rfqNumber" placeholder="RFQ Number"
                                        value="{{$rfq->rfq_id}}" readonly disabled>
                                    <label for="rfqNumber" class="font-size-13">RFQ Number</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-md-auto mb-4">
                            <div class="input-group generate-rfq-input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-file-earmark-text"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="prnNumber" placeholder="PRN Number"
                                        value="{{$rfq->prn_no}}" readonly disabled>
                                    <label for="prnNumber" class="font-size-13">PRN Number</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-md-auto mb-4">
                            <div class="input-group generate-rfq-input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-shop"></span>
                                </span>
                                <div class="form-floating">
                                    <select class="form-select" id="selectBranch" aria-label="Select Branch" readonly
                                        disabled>
                                        <option value="">Select</option>
                                        @foreach ($buyer_branch as $branch)
                                        <option value="{{$branch->branch_id}}"
                                            {{$branch->branch_id==$rfq->buyer_branch ? "selected" : ""}}>
                                            {{$branch->name}}</option>
                                        @endforeach
                                    </select>
                                    <label for="selectBranch" class="font-size-13">Branch/Unit</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-md-auto mb-4">
                            <div class="input-group generate-rfq-input-group" id="datepicker">
                                <span class="input-group-text">
                                    <span class="bi bi-calendar2-date"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control dateTimepicker" id="rfqDate"
                                        placeholder="RFQ Date"
                                        value="{{!empty($rfq->created_at)?date('d/m/Y',strtotime($rfq->created_at)):''}}"
                                        readonly disabled>
                                    <label for="rfqDate" class="font-size-13">RFQ Date</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-md-auto mb-4">
                            <div class="input-group generate-rfq-input-group" id="datepicker">
                                <span class="input-group-text">
                                    <span class="bi bi-calendar2-date"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control dateTimepicker" id="lastResponseDate"
                                        placeholder="Last Response Date" value="{{!empty($rfq->last_response_date)?date('d/m/Y',strtotime($rfq->last_response_date)):''}}" readonly
                                        disabled>
                                    <label for="lastResponseDate" class="font-size-13">Last Response
                                        Date</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-auto mb-4">
                            <button onclick="window.location.href = '{{ url()->previous() }}';" class="ra-btn btn-sm ra-btn-primary font-size-11 px-2 px-md-3">
                                <span class="bi bi-arrow-left-square font-size-10"></span>
                                Back
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </section>
        <!---RFQ Vendor list section-->
        <section class="mx-0 mx-md-2 py-2">
            <div class="card-vendor-list p-0">
                <div class="card-vendor-list-wrapper gap-3">
                    <!-- Vendor Left column -->
                    <div class="card shadow-sm px-3 pt-3 card-vendor-list-left-panel">
                        <h3 class="font-size-18 mb-3">Vendor List</h3>
                        <div class="card-vendor-list-search-panel mb-3">
                            <div class="card-vendor-list-search-list mt-2 scrollSection">
                                <div class="filter-list scroll-list scroll-list-rfq-details">
                                    @foreach ($rfq_vendors as $vendor)
                                        <div class="filter-list-item">
                                            <p class="item-name">{{$vendor->rfqVendorProfile->legal_name}}</p>
                                            <p class="item-contact">{{$vendor->rfqVendorProfile->user->mobile}}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Vendor right column -->
                    <div class="card-vendor-list-right-panel">
                        @foreach($rfq->rfqProducts as $key=> $product)
                        <div class="card shadow-sm px-3 pt-3 card-vendor-list-item pb-0 mb-3">
                            <!-- Top breadcrumb -->
                            <div class="d-flex justify-content-between mb-30">
                                <div>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb breadcrumb-vendor">
                                            <li class="breadcrumb-item"><a href="javascript:void(0);">{{++$key}}. {{$product->masterProduct->division->division_name}}</a></li>
                                            <li class="breadcrumb-item"><a href="javascript:void(0);">{{$product->masterProduct->category->category_name}}</a></li>
                                            <li class="breadcrumb-item active" aria-current="page">{{$product->masterProduct->product_name}}
                                            </li>
                                        </ol>
                                    </nav>
                                </div>
                            </div>

                            <!-- Product List Table -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive table-product px-2">
                                        <table class="table table-product-list">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="text-nowrap">
                                                        <div class="table-product-list-sl"></div>Sr. No.
                                                    </th>
                                                    <th scope="col" class="text-nowrap">
                                                        <div class="table-product-list-specification">Specification
                                                        </div>
                                                    </th>
                                                    <th scope="col" class="text-nowrap">
                                                        <div class="table-product-list-size">Size</div>
                                                    </th>
                                                    <th scope="col" class="text-nowrap">
                                                        <div class="table-product-list-qty">
                                                            Quantity
                                                        </div>
                                                    </th>
                                                    <th scope="col" class="text-nowrap">
                                                        <div class="table-product-list-uom">
                                                            UOM
                                                        </div>
                                                    </th>
                                                    <th scope="col" class="text-nowrap">
                                                        <div class="table-product-list-attachment">
                                                            Attachment
                                                        </div>
                                                    </th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach($product->productVariants as $keyj => $variant)
                                                <tr>
                                                    <td>{{++$keyj}}</td>
                                                    <td>
                                                        <input type="text" name="Specification" title="" value="{!!$variant->specification!!}"
                                                            class="form-control form-control-sm" maxlength="500"
                                                            data-bs-toggle="modal" data-bs-target="#submitSpecification"
                                                            readonly disabled>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="Size" value="{!!$variant->size!!}"
                                                            class="form-control form-control-sm" oninput=""
                                                            maxlength="255" readonly disabled>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="quantity" value="{{$variant->quantity}}"
                                                            class="form-control form-control-sm" maxlength="10" readonly
                                                            disabled>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="uom" value="{{getUOMName($variant->uom)}}"
                                                            class="form-control form-control-sm" maxlength="10" readonly
                                                            disabled>
                                                    </td>
                                                    <td>
                                                        @if(!empty($variant->attachment))
                                                        <div class="position-relative">
                                                            <div class="file-info" style="display: block;">
                                                                <div class="d-flex align-item-center gap-1">
                                                                    <a href="{{ asset('public/uploads/rfq-attachment/'.$variant->attachment) }}" target="_blank">
                                                                        <span class="display-file text-light-gery  font-size-13">
                                                                            <?php echo $variant->attachment ? ( strlen($variant->attachment) > 15 ? substr($variant->attachment, 0, 15) . ' <i title="' . $variant->attachment . '" class="bi bi-info-circle-fill" data-bs-toggle="tooltip" data-bs-placement="top" aria-hidden="true"></i>' : $variant->attachment) : ''; ?>
                                                                        </span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>


                            <!-- Search by Brand and Remarks -->
                            <div class="row mt-4">
                                <div class="col-md-6 mb-4">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="bi bi-tags"></span>
                                        </span>
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="brand" placeholder="Brand"
                                                value="{{ $product->brand }}" readonly disabled>
                                            <label for="brand">Brand</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="bi bi-pencil"></span>
                                        </span>
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="remarks" placeholder="Remarks"
                                               value="{{ $product->remarks}}" readonly disabled>
                                            <label for="remarks">Remarks</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </section>

        <!-- Fill more details -->
        <section>
            <div class="row justify-content-between fill-more-details mb-0">
                <div class="col-12 col-md-4 mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <span class="bi bi-geo-alt"></span>
                        </span>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="priceBasis" value="{{ $rfq->buyer_price_basis }}" placeholder="Price Basis" readonly
                                disabled>
                            <label for="priceBasis">Price Basis</label>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <span class="bi bi-currency-rupee"></span>
                        </span>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="paymentTerm" value="{{ $rfq->buyer_pay_term }}" placeholder="Payment Term" readonly
                                disabled>
                            <label for="paymentTerm">Payment Term</label>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-2 mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <span class="bi bi-calendar2-date"></span>
                        </span>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="deliveryPeriod"
                                placeholder="Delivery Period (In Days)" value="{{ $rfq->buyer_delivery_period }}" readonly disabled>
                            <label for="deliveryPeriod">Delivery Period (In Days)</label>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <span class="bi bi-patch-check"></span>
                        </span>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="guranteeWarranty"
                                placeholder="Gurantee/Warranty" value="{{ $rfq->warranty_gurarantee }}" readonly disabled>
                            <label for="guranteeWarranty">Gurantee/Warranty</label>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>
</main>

@endsection

@section('scripts')
<script>
    // Start of Card vendor list scroll
    function matchAllScrollHeights() {
        const scrollSections = document.querySelectorAll('.card-vendor-list-search-panel');
        const mainContents = document.querySelectorAll('.card-vendor-list-right-panel');

        if (window.innerWidth < 768) {
            // Remove inline height on mobile view
            scrollSections.forEach(section => {
                section.style.removeProperty('height');
            });
        } else {            
            // Match heights on larger screens
            for (let i = 0; i < scrollSections.length; i++) {
                if (mainContents[i]) {
                    const extraHeight = 20;
                    const removeExtraHeight = 98;
                    scrollSections[i].style.height = (mainContents[i].offsetHeight + extraHeight - removeExtraHeight) + 'px';
                }
            }
        }
    }

    window.addEventListener('load', matchAllScrollHeights);
    window.addEventListener('resize', matchAllScrollHeights);
    // End of Card vendor list scroll
</script>
@endsection