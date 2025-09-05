@extends('admin.layouts.app_second', [
    'title' => 'Manage Plan',
    'sub_title' => 'List'
])

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Plan Module List</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
<div class="page-start-section">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h1 class="mb-0">Plan Module List</h1>
                            <a href="{{ route('admin.plan.create') }}" class="btn-rfq btn-rfq-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Add
                            </a>
                        </div>
                        <div id="table-container">
                          @include('admin.plan.partials.table', ['plans' => $plans])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')

@endsection