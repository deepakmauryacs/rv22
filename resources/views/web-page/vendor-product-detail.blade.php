@extends('buyer.layouts.app-mini-web-page',['title'=>'Product','sub_title'=>'Details'])

@section('content')

<main class="main main-inner-page flex-grow-1 py-2 px-md-3 px-1">
    <section class="container-fluid">
        <div class="card rounded mini-web-page">
            <div class="card-body">
                <div class="row">
                    <!-- Vertical Thumbnails -->
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-2 float-end text-sm-end">
                        @include('web-page.menu')
                    </div>
                    <div class="col-lg-1 col-md-1 col-sm-12">
                        <ul id="vertical">
                            @forelse ($productImages->gallery as $item)
                            <li data-src="{{ url('public/uploads/product',$item->image) }}">
                                <img style="height: 100px; width:85px;" class="img img-thumbnail"
                                    src="{{ url('public/uploads/product',$item->image) }}" />
                            </li>
                            @empty
                            <li data-src="{{ url('public/web/dummy-product-image/dummy-product.png') }}">
                                <img src="{{ url('public/web/dummy-product-image/dummy-product.png') }}" />
                            </li>
                            @endforelse

                        </ul>
                    </div>

                    <!-- Main Image -->
                    <div class="col-lg-3 col-md-3 col-sm-12">
                        @if ($productImages->gallery->first())
                        <img id="zoomImage"
                            src="{{ url('public/uploads/product') }}/{{ optional($productImages->gallery[0])->image }}"
                            data-zoom-image="{{ url('public/uploads/product') }}/{{ optional($productImages->gallery[0])->image }}"
                            class="img-fluid main-img" alt="Product">
                        @else
                        <img id="zoomImage" src="{{ url('public/web/dummy-product-image/dummy-product.png') }}"
                            data-zoom-image="{{ url('public/web/dummy-product-image/dummy-product.png') }}"
                            class="img-fluid main-img" alt="Product">
                        @endif

                    </div>

                    <div class="col-lg-5 col-md-5 col-sm-12">


                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">{{
                                        optional($productImages->product->division)->division_name }} </a></li>
                                <li class="breadcrumb-item active">{{
                                    optional($productImages->product->category)->category_name}}</li>
                            </ol>
                        </nav>
                        <h5 class="text-primary">{{$productImages->product->product_name }}</h5>
                        {{-- <ul class="list-unstyled bg-white p-3 rounded shadow-sm">
                            <li><strong>Description:</strong> asdf</li>
                            <li><strong>Size:</strong> prod_size is size</li>
                            <li><strong>Dealership:</strong> prod_dealership is prod_dealership</li>
                            <li><strong>Packaging:</strong> prod_packaging is</li>
                            <li><strong>HSN:</strong> 121212</li>
                        </ul> --}}

                        {!! html_entity_decode($productImages->description )!!}

                        @if (Auth::user()->user_type==1)


                        <p><button type="button" data-vendor_id="{{ session()->get('vendorId') }}"
                                data-product_id="{{ $pID }}" class="btn btn-primary ra-btn-sm mt-1 generateRfqBtn"
                                id="generateRfqBtn">Add
                                RFQ</button>
                        </p>

                        @endif
                    </div>


                    <!-- Vendor Info -->
                    <div class="col-lg-3 col-md-3 col-sm-12">
                        <div class="card vendor-card p-3 shadow-sm">

                            <div class="d-flex align-items-center">
                                @if (optional($vendorInfo->vendor)->profile_img)
                                <img width="100px"
                                    src="{{ url('public/uploads/vendor-profile',$vendorInfo->vendor->profile_img) }}"
                                    alt="">
                                @else
                                <img src="{{ url('public/uploads/vendor-profile/default-logo.png') }}" alt="">
                                @endif

                                <div class="ms-2">
                                    <div class="fw-bold">{{ $vendorInfo->vendor->legal_name }}</div>
                                    <div class="small"> {{ optional($vendorInfo->vendor->vendor_city)->city_name??'' }},
                                        {{
                                        optional($vendorInfo->vendor->vendor_state)->name??'' }},
                                        {{
                                        optional($vendorInfo->vendor->vendor_country)->name??'' }}</div>
                                </div>
                            </div>
                            <hr>
                            <p class="mb-1"><strong>GST:</strong> {{
                                optional($vendorInfo->vendor)->gstin }}</p>
                            <p class="mb-1"><strong>Phone Number:</strong> {{
                                $vendorInfo->mobile }}</p>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

</main>

@endsection

@include('web-page.product-details-js')