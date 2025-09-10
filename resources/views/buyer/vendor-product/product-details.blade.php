@extends('buyer.layouts.app', ['title'=>$product->product_name.' '])

@section('css')
    <style>
        .card-img, .vendor-img { 
            width: 280px; 
            justify-content: center; 
        }
        .vendor-img {
            width: 100px;
            border: 1px solid #efefef;
            padding: 5px;
            align-items: center;
            display: flex;
            justify-content: center; 
        }
        .breadcrumb-item.active { 
            font-weight: bold; 
        }
        .breadcrumb-vendor li:last-child {
            color: unset;
            font-weight: unset;
        }
        .product-img{
            opacity: 1; 
            width: 280px; 
            visibility: visible; 
            height: 280px; 
            border-radius: 5px;
        }
        .product-details{
            background-color: #f9f9f9;
            border-radius: 12px;
            color: #777;
        }
    </style>
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar-menu')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1">
        <div class="container-fluid">
            @php
                $vendor_profile_img = public_path('uploads/profile_img/' . $product->product_vendor->vendor_profile->profile_img);
                $product_image = public_path('uploads/product/' . $product->product_vendor->image);
                $product_catalogue = public_path('uploads/product/docs/' . $product->product_vendor->catalogue);
                $product_specification_file = public_path('uploads/product/docs/' . $product->product_vendor->specification_file);
                $product_certificates_file = public_path('uploads/product/docs/' . $product->product_vendor->certificates_file);
                $product_dealership_file = public_path('uploads/product/docs/' . $product->product_vendor->dealership_file);
            @endphp
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-4">
                    <li class="breadcrumb-item"><a href="{{ route('buyer.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">{{ $product->product_vendor->vendor_profile->legal_name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{$product->product_name}}</li>
                </ol>
            </nav>
            <div class="row">
                <!-- Main Card -->
                <div class="col-lg-8 mb-3">
                    
                    <div class="row card p-4 d-flex flex-row align-items-center1" >
                        <div class="col-md-5 card-img me-4" >
                            @if($product->product_vendor->image && file_exists($product_image))
                            <img src="{{ url('public/uploads/product/'.$product->product_vendor->image) }}" alt="Product" class="product-img">
                            @else
                            <img src="{{ url('public/web/dummy-product-image/dummy-product.png') }}" alt="Product" class="product-img">
                            @endif
                        </div>
                        <div class="col-md-8 flex-grow-1" >
                            
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-vendor">
                                    <li class="breadcrumb-item"> {{ $product->division->division_name }}</li>
                                    <li class="breadcrumb-item">{{ $product->category->category_name }}</li>
                                </ol>
                            </nav>
                            <h5 class="mb-2" style="color: var(--primary-color);">{{$product->product_name}}</h5>
                            <div class="mb-3 p-3 product-details">

                                @if($product->product_vendor->catalogue && file_exists($product_catalogue))
                                <p class="text-muted">• Catalogue: 
                                    <a href="{{ url('public/uploads/product/docs/'.$product->product_vendor->catalogue) }}" target="_blank" download="Catalogue">
                                        Download
                                    </a>
                                </p>
                                @endif

                                <p class="text-muted">• Description: {!! $product->product_vendor->description !!}</p>
                                
                                @if($product->product_vendor->specification)
                                <p class="text-muted">• Specifications: {!! $product->product_vendor->specification !!}</p>
                                @endif

                                @if($product->product_vendor->specification_file && file_exists($product_specification_file))
                                <p class="text-muted">• Specifications Attachment: 
                                    <a href="{{ url('public/uploads/product/docs/'.$product->product_vendor->specification_file) }}" target="_blank" download="Specifications Attachment">
                                        Download
                                    </a>
                                </p>
                                @endif

                                @if($product->product_vendor->size)
                                <p class="text-muted">• Size: {!! $product->product_vendor->size !!}</p>
                                @endif
                                
                                @if($product->product_vendor->certificates)
                                <p class="text-muted">• Certifications: {!! $product->product_vendor->certificates !!}</p>
                                @endif

                                @if($product->product_vendor->certificates_file && file_exists($product_certificates_file))
                                <p class="text-muted">• Certifications Attachments:: 
                                    <a href="{{ url('public/uploads/product/docs/'.$product->product_vendor->certificates_file) }}" target="_blank" download="Certifications Attachment">
                                        Download
                                    </a>
                                </p>
                                @endif
                                
                                @if($product->product_vendor->dealership)
                                <p class="text-muted">• Dealership: {!! $product->product_vendor->dealership !!}</p>
                                @endif

                                @if($product->product_vendor->dealership_file && file_exists($product_dealership_file))
                                <p class="text-muted">• Dealership Attachments:: 
                                    <a href="{{ url('public/uploads/product/docs/'.$product->product_vendor->dealership_file) }}" target="_blank" download="Dealership Attachment">
                                        Download
                                    </a>
                                </p>
                                @endif
                                
                                @if($product->product_vendor->brand)
                                <p class="text-muted">• Brand: {!! $product->product_vendor->brand !!}</p>
                                @endif
                                
                                @if($product->product_vendor->gorw_year || $product->product_vendor->gorw_month)
                                <p class="text-muted">• {!! $product->product_vendor->gorw !!}: {!! $product->product_vendor->gorw_year ? $product->product_vendor->gorw_year. ' Year' : '' !!} {!! $product->product_vendor->gorw_month ? $product->product_vendor->gorw_month. ' Month' : '' !!}</p>
                                @endif
                                
                                @if($product->product_vendor->packaging)
                                <p class="text-muted">• Packaging: {!! $product->product_vendor->packaging !!}</p>
                                @endif
                                
                                @if($product->product_vendor->model_no)
                                <p class="text-muted">• Model No: {!! $product->product_vendor->model_no !!}</p>
                                @endif
                                
                                @if($product->product_vendor->hsn_code)
                                <p class="text-muted">• HSN: {!! $product->product_vendor->hsn_code !!}</p>
                                @endif

                                {{-- <p class="text-muted">description : {{ $product->product_vendor->description }}</p>
                                <p class="text-muted">division_name : {{ $product->division->division_name }}</p>
                                <p class="text-muted">country_code : {{ $product->product_vendor->vendor_profile->user->country_code }}</p>
                                <p class="text-muted">legal_name : {{ $product->product_vendor->vendor_profile->legal_name }}</p>
                                <p class="text-muted">mobile : {{ $product->product_vendor->vendor_profile->user->mobile }}</p>
                                <p class="text-muted">city name : {{ $product->product_vendor->vendor_profile->vendor_city?->city_name }}</p>
                                <p class="text-muted">vendor_state name : {{ $product->product_vendor->vendor_profile->vendor_state?->name }}</p>
                                <p class="text-muted">vendor_country name : {{ $product->product_vendor->vendor_profile->vendor_country?->name }}</p>
                                
                                <p class="text-muted">vendor_id : {{ $product->product_vendor->vendor_id }}</p> --}}
                            </div>
                            @if(Auth::user()->user_type == 1)
                            <button class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1 add-this-vendor-product">
                                <span class="bi bi-plus-square font-size-11" aria-hidden="true"></span>
                                Add Rfq
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Vendor Info Card -->
                <div class="col-lg-4 mb-3">
                    <div class="card p-4">
                        <h5 class="mb-4" style="font-weight: bold;">Vendor Name</h5>
                        <div class="d-flex align-items-center mb-3">
                            <span class="vendor-img me-3">
                                @if($product->product_vendor->vendor_profile->profile_img && file_exists($vendor_profile_img))
                                    <img alt="{{ $product->product_vendor->vendor_profile->legal_name }}" src="{{ url('public/uploads/profile_img/'.$product->product_vendor->vendor_profile->profile_img) }}" >
                                @else
                                    <img alt="Raprocure" src="{{ url('public/web/dummy-product-image/dummy-product.png') }}">
                                @endif
                            </span>
                            <div>
                                <h5>{{ $product->product_vendor->vendor_profile->legal_name }}</h5>
                                <div class="text-muted" style="font-size: 0.95rem;">
                                    <i class="bi bi-pin-map-fill" style="font-size: 20px;"></i>
                                    {{ $product->product_vendor->vendor_profile->vendor_city ? $product->product_vendor->vendor_profile->vendor_city?->city_name.',' : '' }} 
                                    {{ $product->product_vendor->vendor_profile->vendor_state ? $product->product_vendor->vendor_profile->vendor_state?->name.',' : '' }} 
                                    {{ $product->product_vendor->vendor_profile->vendor_country?->name }}
                                </div>
                                <div class="vendor-info mb-2">
                                    <i class="bi bi-patch-check-fill" style="color: #59d908;font-size: 20px;"></i>
                                    <span><b>GST:</b> <span>{{ $product->product_vendor->vendor_profile->gstin }}</span></span>
                                </div>
                                <div class="vendor-info">
                                    <i class="bi bi-phone" style="font-size: 20px;"></i>
                                    <span><b>Phone Number:</b> {{ $product->product_vendor->vendor_profile->user->country_code ? '+'.$product->product_vendor->vendor_profile->user->country_code.' ' : '' }} {{ $product->product_vendor->vendor_profile->user->mobile }}</span>
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
    @if(Auth::user()->user_type == 1)
    <script>
        
        $(document).on("click", ".add-this-vendor-product", function () {
            let vendors_id = new Array();
            vendors_id.push('{{ $product->product_vendor->vendor_id }}');
            addToDraft(vendors_id);
        });

        function addToDraft(vendors_id) {
            $(".add-this-vendor-product").addClass("disabled");

            $.ajax({
                async: false,
                type: "POST",
                url: '{{ route("buyer.rfq.add-to-draft") }}',
                dataType: 'json',
                data: {
                    product_id: '{{ $product->id }}',
                    vendors_id: vendors_id,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {},
                success: function(responce) {
                    if (responce.status == false) {
                        toastr.error(responce.message);
                    } else {
                        window.location.href = responce.redirectUrl;
                    }
                },
                error: function() {
                    // toastr.error('Something Went Wrong..');
                },
                complete: function() {}
            });
        }

    </script>
    @endif
@endsection
