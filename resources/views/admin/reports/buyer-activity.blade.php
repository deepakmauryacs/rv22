@extends('admin.layouts.app_second', ['title' => 'Products for Approval', 'sub_title' => 'Approval List'])
@section('breadcrumb')
    <div class="breadcrumb-header">
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="{{ route('admin.reports.buyer-activity') }}"> Buyer Activity Reports</a>
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
                        <h1 class="">Buyer Activity Reports</h1>
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
                                <form id="searchForm" action="{{ route('admin.reports.buyer-activity') }}" method="GET">
                                    <div class="row gx-3">
                                        <div class="col-12 col-sm-auto mt-3 mt-sm-4">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                                <div class="form-floating">
                                                    <input type="text" name="buyer_name" id="buyer_name"
                                                        class="form-control fillter-form-control w-100"
                                                        value="{{ request('buyer_name') }}" placeholder="Buyer Name">
                                                    <label>Buyer Name</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-auto mt-3 mt-sm-4">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                                <div class="form-floating">
                                                    <input type="date" name="from_date" name="from_date"
                                                        class="form-control fillter-form-control w-100"
                                                        value="{{ request('from_date') }}" placeholder="From Date">
                                                    <label>From Date</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-auto mt-3 mt-sm-4">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                                <div class="form-floating">
                                                    <input type="date" name="to_date" id="to_date"
                                                        class="form-control fillter-form-control w-100"
                                                        value="{{ request('to_date') }}" placeholder="To Date">
                                                    <label>To Date</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto mt-3 mt-sm-4">
                                            <button type="submit" class="btn-style btn-style-primary"><i
                                                    class="bi bi-search d-none d-sm-inline-block"></i> Search</button>
                                        </div>
                                        <div class="col-auto mt-3 mt-sm-4">
                                            <a href="{{ route('admin.reports.buyer-activity') }}"
                                                class="btn-style btn-style-danger">RESET</a>
                                        </div>
                                        <div class="col-auto mt-3 mt-sm-4">
                                            <button class="btn-style btn-style-white" id="export-btn"><i
                                                    class="bi bi-download d-none d-sm-inline-block"></i>
                                                EXPORT
                                            </button>
                                        </div>
                                    </div>
                                    <!-- <ul class="rfq-filter-button">
                                        <li>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                                <div class="form-floating">
                                                    <input type="text" name="buyer_name" id="buyer_name" class="form-control fillter-form-control" value="{{ request('buyer_name') }}" placeholder="Buyer Name">
                                                    <label>Buyer Name</label>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                                <div class="form-floating">
                                                    <input type="date" name="from_date" name="from_date" class="form-control fillter-form-control" value="{{ request('from_date') }}" placeholder="From Date">
                                                    <label>From Date</label>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                                <div class="form-floating">
                                                    <input type="date" name="to_date" id="to_date" class="form-control fillter-form-control" value="{{ request('to_date') }}" placeholder="To Date">
                                                    <label>To Date</label>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="notShow_on_mobile">
                                            <button type="submit" class="btn-style btn-style-primary"><i class="bi bi-search"></i> Search</button>
                                        </li>
                                        <li class="notShow_on_mobile">
                                            <a href="{{ route('admin.reports.buyer-activity') }}" class="btn-style btn-style-danger">RESET</a>
                                        </li>
                                        <li class="notShow_on_mobile">
                                            <button class="btn-style btn-style-white" id="export-btn"><i class="bi bi-download"></i>
                                                EXPORT
                                            </button>
                                        </li>
                                    </ul> -->
                                </form>
                            </div>
                        </div>
                        <div class="product_listing_table_wrap" id="table-container">
                            @include('admin.reports.partials.buyer-activity-table', [
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

    <script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>
    <script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
    <script>
        $(document).ready(function() {
            const exporter = new Exporter({
                chunkSize: 100,
                rowLimitPerSheet: 200000,
                headers: ["Name Of Buyer", "Primary Contact", "Phone No", "Email",
                    "No. of Accounts Created", "Total RFQ Generated", "Total Bulk RFQ Generated",
                    "Total Offers Received", "Total Orders Confirmed", "Value (Of Confirmed Orders)",
                    "Last Login Date"
                ],
                totalUrl: "{{ route('admin.buyer-activity.exportTotal') }}",
                batchUrl: "{{ route('admin.buyer-activity.exportBatch') }}",
                token: "{{ csrf_token() }}",
                exportName: "Buyer-activity-report",
                expButton: '#export-btn',
                exportProgress: '#export-progress',
                progressText: '#progress-text',
                progress: '#progress',
                fillterReadOnly: '.fillter-form-control',
                getParams: function() {
                    return {
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
