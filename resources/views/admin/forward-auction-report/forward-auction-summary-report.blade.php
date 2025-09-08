@extends('admin.layouts.app_second', ['title' => 'Forward Auction Reports', 'sub_title' => 'Forward Auction Reports Summary'])
@section('css')
    <style>
        .form-floating>.form-control,
        .form-floating>.form-select {
            width: 172px;
            max-width: 100%;
        }

        @media (max-width: 767px) {
            .form-floating>.form-control,
            .form-floating>.form-select {
                width: 100% !important;
            }

            ul.rfq-filter-button li {
                display: block;
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
                        <a href="{{ route('admin.reports.forward-auctions-summary') }}"> Forward Auction Reports</a>
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
                        <h1>Forward Auction Reports</h1>
                        <div class="row">
                            <div class="col-md-12">
                                <div id="export-progress" style="display:none;">
                                    <p>Export Progress: <span id="progress-text">0%</span></p>
                                    <div id="progress-bar" style="width: 100%; background: #f3f3f3;">
                                        <div id="progress" style="height: 20px; width: 0%; background: green;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12">
                                <form id="searchForm" action="{{ route('admin.reports.forward-auctions-summary') }}" method="GET">
                                    <div class="row gx-3">
                                        <div class="col-12 col-sm-auto mt-3 mt-sm-4">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                                <div class="form-floating">
                                                    <input type="text" name="auction_id" id="auction_id" class="form-control fillter-form-control" value="{{ request('auction_id') }}" placeholder="Auction ID">
                                                    <label>Auction ID</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-auto mt-3 mt-sm-4">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                                <div class="form-floating">
                                                    <input type="text" name="vendor_name" id="vendor_name" class="form-control fillter-form-control" value="{{ request('vendor_name') }}" placeholder="Vendor Name">
                                                    <label>Vendor Name</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-auto mt-3 mt-sm-4">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                                <div class="form-floating">
                                                    <input type="text" name="buyer_name" id="buyer_name" class="form-control fillter-form-control" value="{{ request('buyer_name') }}" placeholder="Buyer Name">
                                                    <label>Buyer Name</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-auto mt-3 mt-sm-4">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                                <div class="form-floating">
                                                    <input type="date" name="from_date" id="from_date" class="form-control fillter-form-control" value="{{ request('from_date') }}" placeholder="From Date">
                                                    <label>From Date</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-auto mt-3 mt-sm-4">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                                <div class="form-floating">
                                                    <input type="date" name="to_date" id="to_date" class="form-control fillter-form-control" value="{{ request('to_date') }}" placeholder="To Date">
                                                    <label>To Date</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto mt-3 mt-sm-4">
                                            <button type="submit" class="btn-style btn-style-primary"><i class="bi bi-search"></i> Search</button>
                                        </div>
                                        <div class="col-auto mt-3 mt-sm-4">
                                            <a href="{{ route('admin.reports.forward-auctions-summary') }}" class="btn-style btn-style-danger">RESET</a>
                                        </div>
                                        <div class="col-auto mt-3 mt-sm-4">
                                            <button class="btn-style btn-style-white" id="export-btn"><i class="bi bi-download d-none d-sm-inline-flex"></i> EXPORT
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="product_listing_table_wrap" id="table-container">
                            @include('admin.forward-auction-report.partials.forward-auction-summary-report-table', [
                                'results' => $results,
                            ])
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

            $(document).on('change', '#perPage', function() {
                const form = $('#searchForm');
                const formData = form.serialize();
                const perPage = $(this).val();
                const url = form.attr('action') + '?' + formData + '&per_page=' + perPage;
                loadTable(url);
            });

            function loadTable(url) {
                $.ajax({
                    url: url,
                    type: 'GET',
                    beforeSend: function() {
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
    <script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
    <script>
        $(document).ready(function() {
            const exporter = new Exporter({
                chunkSize: 10000,
                rowLimitPerSheet: 200000,
                headers: ["Auction ID", "Product Details", "Buyer Name", "Vendor Name", "Start Date & Time", "Participated"],
                totalUrl: "{{ route('admin.forward-auctions-summary.exportTotal') }}",
                batchUrl: "{{ route('admin.forward-auctions-summary.exportBatch') }}",
                token: "{{ csrf_token() }}",
                exportName: "forward-auction-report",
                expButton: '#export-btn',
                exportProgress: '#export-progress',
                progressText: '#progress-text',
                progress: '#progress',
                fillterReadOnly: '.fillter-form-control',
                cursorKeys: ['last_auction_id','last_vendor_id'],
                getParams: function() {
                    return {
                        auction_id: $('#auction_id').val(),
                        vendor_name: $('#vendor_name').val(),
                        buyer_name: $('#buyer_name').val(),
                        from_date: $('#from_date').val(),
                        to_date: $('#to_date').val()
                    };
                }
            });

            $('#export-btn').on('click', function() {
                exporter.start();
            });

            $('#export-progress').hide();
        });
    </script>
@endsection
