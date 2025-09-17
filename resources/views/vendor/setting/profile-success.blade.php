@extends('vendor.layouts.app_second', ['title'=>'Vendor Profile Success', 'sub_title'=>'Create'])

@section('css')
@endsection

@section('content')

    <main class="success-wrap">
        <section class="text-center success-card">

            <!-- logo (replace src with your logo path) -->
            <div class="mt-5">
                <img class="brand-mark" src="{{ asset('public/assets/images/raprocure-fevicon.png') }}" style="width: 8%;" alt="Brand Logo">
            </div>

            <!-- main line -->
            <h3>Vendor Profile Status </h3>
            <p>Thank you for registering with raProcure. Your profile is currently under verification.</p>
            <p> You will receive a message on your registered email, once your account is verified</p>

            <!-- actions -->
            <div class="d-flex justify-content-center gap-2 flex-wrap mt-3">
                <a href="{{ route("vendor.profile") }}" class="ra-btn ra-btn-outline-primary-light py-2 height-inherit">
                    Edit Profile
                </a>
            </div>

        </section>
    </main>
@endsection

@section('scripts')
@endsection