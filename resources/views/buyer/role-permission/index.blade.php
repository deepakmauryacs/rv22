@extends('buyer.layouts.app', ['title'=>'Manage Role'])

@section('css')
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1 inner-main">
        <div class="container-fluid">
            <div class="bg-white favourite-vendor user-manage-role">
                <div class="card-head-line">
                    <h3>Manage Role</h3>
                    <a href="{{route('buyer.role-permission.create-role')}}" class="btn ra-btn ra-btn-primary small-btn">+ Add New Role</a>
                </div>
                <div class="table-responsive">
                      @include('buyer.role-permission.partials.table', ['results' => $results])
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')

@endsection