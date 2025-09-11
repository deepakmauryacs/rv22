@extends('admin.layouts.app_second', [
    'title' => 'Vendor Activity',
    'sub_title' => 'Vendor Activity Report'
])

@section('css')
<style>
    .form-floating>.form-control, .form-floating>.form-select {
        width: 172px;
        max-width: 100%;
    }
    @media (max-width: 767px) {
        .form-floating>.form-control, .form-floating>.form-select {
        width: 100% !important;
    }
    }
</style>
@endsection

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <a href="{{ route('admin.vendor-activity-report.index') }}"> Vendor Activity Report</a>
                </li>
            </ol>
        </nav>
    </div>
</div>
@endsection
@section('content')
<div class="about_page_details">
    <div class="container-fluid">
        <div class="card border-0">
            <div class="card-body">
                <div class="col-md-12 botom-border">
                    <h1>Vendor Activity Report</h1>
                    <div class="row">
                        <div class="col-md-12">
                            <div id="export-progress">
                                <p>Export Progress: <span id="progress-text">0%</span></p>
                                <div id="progress-bar" style="width: 100%; background: #f3f3f3;">
                                    <div id="progress" style="height: 20px; width: 0%; background: green;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-lg-12 col-md-12">
                            <form id="searchForm" action="{{ route('admin.vendor-activity-report.index') }}" method="GET">
                                <div class="row gx-3">
                                    <div class="col-12 col-sm-auto mt-3 mt-sm-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <div class="form-floating">
                                                <input type="text" name="vendor_name" class="form-control fillter-form-control" value="{{ request('vendor_name') }}" placeholder="Vendor Name">
                                                <label>Vendor Name</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-auto mt-3 mt-sm-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="text" name="registered_address" class="form-control fillter-form-control" value="{{ request('registered_address') }}" placeholder="Register Address">
                                                <label>Register Address</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-auto mt-3 mt-sm-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                            <div class="form-floating">
                                                <input type="date" name="from_date" class="form-control fillter-form-control" value="{{ request('from_date') }}" placeholder="From Date" />
                                                <label>From Date</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-auto mt-3 mt-sm-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                            <div class="form-floating">
                                                <input type="date" name="to_date" class="form-control fillter-form-control" value="{{ request('to_date') }}" placeholder="To Date" />
                                                <label>To Date</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-auto mt-3 mt-sm-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <button type="submit" class="btn-style btn-style-primary"><i class="bi bi-search d-none d-sm-inline-block"></i> Search</button>
                                            <a href="{{ route('admin.vendor-activity-report.index') }}" class="btn-style btn-style-danger">RESET</a>
                                            <button type="button" id="export-btn" class="btn-rfq btn-rfq-white">
                                                <i class="bi bi-download d-none d-sm-inline-block"></i> Export
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="product_listing_table mt-4" id="table-container">
                        @include('admin.vendor-activity-report.partials.table', ['vendors' => $vendors])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $(document).on('submit', '#searchForm', function(e) {
        e.preventDefault();
        loadTable($(this).attr('action') + '?' + $(this).serialize());
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        loadTable($(this).attr('href'));
    });

    // Handle perPage dropdown change
    $(document).on('change', '#perPage', function() {
        const form = $('#searchForm');
        const formData = form.serialize(); // Get current search filters
        const perPage = $(this).val();
        const url = form.attr('action') + '?' + formData + '&per_page=' + perPage;

        loadTable(url);
    });

    function loadTable(url) {
        $.ajax({
            url: url,
            type: 'GET',
            beforeSend: function () {
                $('#table-container').html('<div class="text-center py-4">Loading...</div>');
            },
            success: function(response) {
                $('#table-container').html(response);
                if (history.pushState) {
                    history.pushState(null, null, url);
                }
            }
        });
    }
});

</script>
<script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>
<script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
<script>
$(document).ready(function () {
    const exporter = new Exporter({
        chunkSize: 1000,
        rowLimitPerSheet: 200000,
        headers: [
            "Name Of Vendor",
            "Primary Contact",
            "Phone No",
            "Email",
            "GST No",
            "Registered Address (Address,City,State)",
            "No. of Accounts",
            "Total RFQ Received",
            "Total Quotation Given",
            "Total Confirmed Orders (Received)",
            "Value (Of Confirmed Orders)",
            "No. Of Verified Product",
            "Last Login Date"
        ],
        totalUrl: "{{ route('admin.vendor-activity-report.exportTotal') }}",
        batchUrl: "{{ route('admin.vendor-activity-report.exportBatch') }}",
        token: "{{ csrf_token() }}",
        exportName: "Vendor-Activity-Reportt",
        expButton: '#export-btn',
        exportProgress: '#export-progress',
        progressText: '#progress-text',
        progress: '#progress',
        fillterReadOnly: '.fillter-form-control',
        getParams: function () {
            return {
                vendor_name: $('[name="vendor_name"]').val(),
                registered_address: $('[name="registered_address"]').val(),
                from_date: $('[name="from_date"]').val(),
                to_date: $('[name="to_date"]').val(),
            };
        }
    });

    $('#export-btn').on('click', function () {
        exporter.start();
    });

    $('#export-progress').hide();
});
</script>
@endsection
