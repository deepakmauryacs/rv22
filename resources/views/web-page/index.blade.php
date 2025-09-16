@extends('buyer.layouts.app-mini-web-page',['title'=>'Web Page','sub_title'=>'home'])
{{-- @extends('vendor.layouts.app_second',['title'=>'Web Page','sub_title'=>'home']) --}}
@section('styles')
<style>
    .loading-spinner.text-center.mt-3 {
        position: relative;
        top: 165px;
        z-index: 1;
        backdrop-filter: drop-shadow(2px 4px 6px black);
    }
</style>
@endsection
@section('content')
<!---Section Main-->
<main class="main main-inner-page flex-grow-1 py-2 px-md-3 px-1">
    <section class="container-fluid">
        <div class="card rounded mini-web-page">
            <div class="card-body">
                <!-- Top Section -->
                <div class="row">
                    <div class="col-md-7">
                        <div class="buyer-info d-flex">
                            <div class="mini-web-page-profile-img rounded border me-3">
                                @if (optional($vendorInfo->vendor)->profile_img&&file_exists(public_path('uploads/vendor-profile/'.optional($vendorInfo->vendor)->profile_img)))
                                <img src="{{ url('public/uploads/vendor-profile',$vendorInfo->vendor->profile_img) }}"
                                    alt="">
                                @else
                                <img src="{{ url('public/assets/images/mini-web-page/company_default_logo.png') }}" alt="">
                                @endif
                            </div>

                            <div class="buyer-sort-desc">
                                <div class="d-flex">
                                    <div>
                                        <h1 class="font-size-18 mb-2">{{ $vendorInfo->vendor->legal_name }}</h1>
                                        <div class="d-flex">
                                            <span class="bi bi-pin-map-fill buyer-sort-desc-icon font-size-18"></span>
                                            {{ optional($vendorInfo->vendor->vendor_city)->city_name??'' }} {{
                                            optional($vendorInfo->vendor->vendor_state)->name??'' }}
                                            {{
                                            optional($vendorInfo->vendor->vendor_country)->name??'' }}
                                        </div>

                                        <div class="d-flex">
                                            <span
                                                class="bi bi-patch-check-fill buyer-sort-desc-icon text-light-green font-size-18"></span>
                                            <strong class="text-nowrap">GST:</strong> {{
                                            optional($vendorInfo->vendor)->gstin }}
                                        </div>
                                    </div>
                                    @if (Auth::user()->user_type==1)
                                    <div class="like-dislike" data-id="{{ $vendorInfo->id  }}">
                                        {!! $html !!}
                                    </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                    <!--:- menu -:-->
                    <div class="col-md-5 text-sm-end">
                        @include('web-page.menu')
                    </div>
                </div>
                <!-- Banner Section -->
                <div class="banner py-4">
                    <div class="row">

                        @if ($vendorInfo->vendorWebPage)
                        @if(optional($vendorInfo->vendorWebPage)->left_banner&&file_exists(public_path('uploads/web-page/'.optional($vendorInfo->vendorWebPage)->left_banner)))
                        <div class="col-md-6">
                            <img style="height: 350px; width:100%;" src="{{ url('public/uploads/web-page',optional($vendorInfo->vendorWebPage)->left_banner) }}"
                                alt="Banner" class="img-responsive mini-web-page-banner">
                        </div>
                        @else
                        <div class="col-md-6">
                            <img style="height: 350px; width:100%;" src="{{ url('public/assets/images/mini-web-page/default_banner_1.jpg') }}" alt="Banner"
                                class="img-responsive mini-web-page-banner">
                        </div>
                        @endif

                        @if (optional($vendorInfo->vendorWebPage)->right_banner&&file_exists(public_path('uploads/web-page/'.optional($vendorInfo->vendorWebPage)->right_banner)))
                        <div class="col-md-6">
                            <img style="height: 350px; width:100%;" src="{{ url('public/uploads/web-page',optional($vendorInfo->vendorWebPage)->right_banner) }}"
                                alt="Banner" class="img-responsive mini-web-page-banner">
                        </div>
                        @else
                        <div class="col-md-6">
                            <img style="height: 350px; width:100%;" src="{{ url('public/assets/images/mini-web-page/default_banner_2.jpg') }}" alt="Banner"
                                class="img-responsive mini-web-page-banner">
                        </div>
                        @endif
                        @endif

                    </div>
                </div>

                <!-- download certification Section -->
                <div class="banner py-1">
                    <div class="row">

                        @if ($vendorInfo->vendorWebPage)
                        @if (optional($vendorInfo->vendorWebPage)->catalogue&&file_exists(public_path('uploads/web-page/'.optional($vendorInfo->vendorWebPage)->catalogue)))
                        <div class="col-md-6">
                            <a href="{{ url('public/uploads/web-page',$vendorInfo->vendorWebPage->catalogue) }}"
                                download class="btn btn-outline-primary btn-md rounded-1  align-items-center">
                                Download Catalogue
                            </a>
                        </div>
                        @endif

                        @if (optional($vendorInfo->vendorWebPage)->other_credentials&&file_exists(public_path('uploads/web-page/'.optional($vendorInfo->vendorWebPage)->other_credentials)))
                        <div class="col-md-6">
                            <a href="{{ url('public/uploads/web-page',$vendorInfo->vendorWebPage->other_credentials) }}"
                                download class="btn btn-outline-primary btn-md rounded-1 d-flex1 align-items-center">
                                Download Certification
                            </a>
                        </div>
                        @endif

                        @endif

                    </div>
                </div>
                <!-- About Section -->
                <div class="my-4">
                    <h2 class="text-primary font-size-22">About Us</h2>
                    {!! optional($vendorInfo->vendorWebPage)->about_us !!}
                </div>

                <!-- Product Listing Section -->
                <div class="mt-4">
                    <h2 class="text-primary font-size-22">Our Products</h2>

                    <div class="loading-spinner text-center mt-3" style="display:none;">
                        {{-- <img src="/images/loading.gif" width="40"> --}}
                        <p><img src="{{ asset('public/assets/images/loader.gif') }}" style="width: 40px;"></p>
                    </div>

                    <div class="mini-web-page-product-listing row product-section">
                        <!--:- product list section -:-->
                    </div>

                </div>
            </div>
        </div>
    </section>

</main>
@endsection

@section('scripts')
<script>
    let page = 1;
    isLoading = false;
    let hasMore = true;

    function loadProducts() {
        if (isLoading || !hasMore) return;
        isLoading = true;
        $(".loading-spinner").hide();
        $.ajax({
            type: "POST",
            url: '{{ route("webPage.productList") }}',
            dataType: 'json',
            data: {
                _token: "{{ csrf_token() }}",
                page: page
            },
            beforeSend: function () {
                $(".loading-spinner").show();
            },
            success: function (response) {
                if (response.status) {
                    $(".product-section").append(response.products);
                    hasMore = response.hasMore;
                    if(response.hasMore){
                        console.log('1',response.hasMore);

                        page++;
                    }else{
                        console.log('pk');
                        isLoading = false;
                        hasMore = false;
                    setTimeout(() => {
                        $(".loading-spinner").hide();
                    }, 1000);
                    }

                } else {
                    hasMore = false;
                }

                $(".loading-spinner").hide();
            },
            error: function () {
                console.error("Error loading products.");
            },
            complete: function () {
                isLoading = false;
                $(".loading-spinner").hide();
            }
        });
    }

// Scroll listener
/*$(window).on('scroll', function () {

    console.log($(document).height(),$(window).scrollTop(), $(window).height(),$(window).scrollTop() + $(window).height()  );

    if ($(window).scrollTop() + $(window).height() >= $(document).height() - 10) {
            console.log('ssss');
        loadProducts();
    }
});*/


// Listen to scroll on product-section div
$('.product-section').on('scroll', function () {
    let $this = $(this);
    if ($this.scrollTop() + $this.innerHeight() >= this.scrollHeight - 10) {
        loadProducts();
    }
});


// Initial load
loadProducts();



/***:- generate rfq  -:***/
$(document).on('click', '.generateRfqBtn', function() {
    $.ajax({
        url: '{{ route("buyer.rfq.add-to-draft") }}',
        type: 'POST',
        data: {
            product_id: $(this).data('product_id'),
            vendors_id: [$(this).data('vendor_id')],
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.status) {
                toastr.success(response.message);
                window.location = `${response.redirectUrl}`;
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('Error generating RFQ!');
        }
    });
});

</script>

@include('buyer.search-vendor.vendor-fav-script')
@endsection
