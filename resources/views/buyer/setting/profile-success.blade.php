@extends('buyer.layouts.app', ['title'=>'Buyer Profile Success', 'sub_title'=>'Create'])

@section('css')
@endsection

@section('content')
    <main class="main flex-grow-1">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="row mt-5">
                        <div class="col-md-12 text-center mt-5">
                            <img src="{{ asset('public/assets/images/raprocure-fevicon.png') }}" style="width: 8%;" class="img img-fluid">
                            <h3>Buyer Profile Status </h3>
                            <p>Thank you for registering with raProcure. Your profile is currently under verification.</p>
                            <p> You will receive a message on your registered email, once your account is verified</p>
                        </div>
                        <div class="col-md-12 text-center">
                            <div class="d-flex flex-wrap flex-md-nowrap align-items-center justify-content-center gap-3 mt-5 mb-5">
                                <a href="{{ route("buyer.profile") }}" class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10">Edit Profile</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>  
@endsection

@section('scripts')
@endsection