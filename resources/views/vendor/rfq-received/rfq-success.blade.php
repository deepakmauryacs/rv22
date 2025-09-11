@extends('vendor.layouts.app_second', ['title'=>'RFQ Quotation Status', 'sub_title'=>'Create'])

@section('css')
<style>
    h3.headline {
        font-size: 28px;
        font-weight: 700;
    }
    .subtext {
        font-size: 18px;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
    <main class="success-wrap">
    <section class="success-card">

        <!-- logo (replace src with your logo path) -->
        <div class="text-center mt-5">
            <img class="brand-mark" src="https://tech.guruworkwithit.online/assets/uploads/web_setup/raprocure-fevicon.png" style="width: 8%;" alt="Brand Logo">
        </div>

        <!-- main line -->
        <h3 class="headline text-center">
            Your {{$page=='quotation' ? 'Quotation' : 'Counter Offer'}} for RFQ No. <span class="text-uppercase">{{$rfq_id}}</span> has been sent successfully.
        </h3>

        <!-- sub line -->
        <div class="subtext text-center">
            The Buyer will get back to you shortly.
        </div>

        <!-- actions -->
        <div class="d-flex justify-content-center gap-2 flex-wrap mt-3">
            <a href="{{ route('vendor.rfq.reply', ['rfq_id' => $rfq_id]) }}" class="ra-btn ra-btn-outline-primary-light py-2 height-inherit">
            <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('vendor.rfq.received.index') }}" class="ra-btn ra-btn-primary py-2 height-inherit">
            <i class="bi bi-file-earmark-play"></i> View All RFQ Received
            </a>
        </div>

        </section>
    </main>
    {{-- <div class="bg-white mt-10">
        <div class="order_confirm">
            <div class="raprosvg-container">
                <img src="{{ asset('public/assets/images/raprocure-fevicon.png') }}">
            </div>
            <div class="order_confirm_head text-center animated growIn go">
                <h3>Vendor Profile Status </h3>
                <p>Your <?php echo $page=='quotation' ? 'Quotation' : 'Counter Offer'; ?> for RFQ No. {{$rfq_id}} has been sent successfully.</p>
                <p> The Buyer will get back to you shortly.</p>
                <a href="{{ route('vendor.rfq.reply', ['rfq_id' => $rfq_id]) }}" class="btn-rfq btn-rfq-primary profile_edit_btn">Back</a>
                <a href="{{ route('vendor.rfq.received.index') }}" class="btn-rfq btn-rfq-primary profile_edit_btn">View All RFQ Received</a>
            </div>
        </div>
    </div> --}}
@endsection

@section('scripts')
@endsection
