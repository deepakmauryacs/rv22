@extends('buyer.layouts.app-mini-web-page',['title'=>'Web Page','sub_title'=>'Contact-us'])

@section('styles')
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
                                    <div class="like-dislike" data-id="{{ $vendorInfo->id }}">
                                        {!! $html !!}
                                    </div>
                                    @endif


                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 text-sm-end">
                        @include('web-page.menu')
                    </div>
                </div>
                <!-- Contact Info Section -->
                <div class="contact-info">
                    <div class="row">
                        <div class="col-md-6">

                            <div class="d-flex flex-column flex-lg-row gap-2 align-items-stretch pb-4">
                                <div class="email d-flex">
                                    <i class="bi bi-envelope"></i>
                                    <div class="flex-grow-1 ms-4">
                                        <h4>Email us at:</h4>
                                        <p>{{ $vendorInfo->email }} </p>
                                    </div>
                                </div>

                                <div class="phone d-flex">
                                    <i class="bi bi-phone"></i>
                                    <div class="flex-grow-1 ms-4">
                                        <h4>Call:</h4>
                                        <p>{{ $vendorInfo->mobile }}</p>
                                    </div>
                                </div>
                            </div>
                            {{-- @if (Auth::user()->user_type==1)
                            <div class="contact-form mt-4">
                                <h2 class="text-primary font-size-22">Fill Your Query</h2>
                                <div class="form-group mt-4">
                                    <input type="hidden" name="receiverId" id="receiverId"
                                        value="{{ $vendorInfo->id }}">

                                    <input type="hidden" name="subject" id="subject"
                                        value="{{ $vendorInfo->vendor->legal_name }}">


                                    <label for="message">Message</label>
                                    <textarea required type="text" class="form-control height-inherit" name="message"
                                        id="message" placeholder="Message" cols="10" rows="6"
                                        maxlength="1700"></textarea>
                                </div>
                                <div class="text-end mt-4">
                                    <button class="ra-btn ra-btn-primary d-inline-flex" id="sendMessageToVendor"><i
                                            class="bi bi-send"></i>
                                        SEND</button>
                                </div>
                            </div>
                            @endif --}}

                        </div>
                        <!-- Right side slider -->
                        <div class="col-md-6">
                            <div id="carouselExampleInterval" class="carousel slide carousel-dashboard"
                                data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <div class="carousel-item active" data-bs-interval="10000">
                                        <div class="ads-img">
                                            <img src="{{ url('public') }}/assets/images/mini-web-page/slider-1.jpg"
                                                alt="" />
                                        </div>
                                    </div>
                                    <div class="carousel-item" data-bs-interval="2000">
                                        <div class="ads-img">
                                            <img src="{{ url('public') }}/assets/images/mini-web-page/slider-2.jpg"
                                                alt="" />
                                        </div>
                                    </div>
                                </div>
                                <button class="carousel-control-prev" type="button"
                                    data-bs-target="#carouselExampleInterval" data-bs-slide="prev">
                                    <span class="btn-prev-slider" aria-hidden="true">
                                    </span>

                                </button>
                                <button class="carousel-control-next" type="button"
                                    data-bs-target="#carouselExampleInterval" data-bs-slide="next">
                                    <span class="btn-next-slider" aria-hidden="true">
                                    </span>

                                </button>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </section>

</main>
@endsection

@section('scripts')
<script>
    /***:- send message section  -:***/
    /*$('#sendMessageToVendor').click(function (e) {
        // e.preventDefault();

        let senderId='{{ Auth::user()->id }}';
        let receiverId=$('#receiverId').val();
        let subject=$('#subject').val();
        let message=$('#message').val();

        if(message==''){
            $('#message').focus();
            return toastr.error('Please type message.');
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            type: "POST",
            url:'{{ url("/message/show-popup") }}',
            dataType: 'json',
            data: { senderId, receiverId, subject, message },
            success: function (response) {
                if (response.status == false) {
                    toastr.error(response.message);
                } else {
                    toastr.success(response.message);
                }
            },
            error: function () {
                toastr.error('Something Went Wrong..');
            }
        });
    });*/


    /*function manageVendor(e, types) {
            let vendorId = $(e).parent().attr('data-id');
            $.ajax({
                url: "{{route('buyer.search-vendor.favourite-blacklist')}}",
                type: "POST",
                dataType: "json",
                data: {
                    vendor_id: vendorId,
                    types: types,
                    _token: "{{ csrf_token() }}"
                },
                sendBefore: function() {
                },
                success: function(response) {
                    $(e).parent().html(response);
                },
                error: function(error) {
                    console.log(error);
                },
                complete: function() {
                }
            });
    }*/
</script>

@include('buyer.search-vendor.vendor-fav-script')
@endsection
