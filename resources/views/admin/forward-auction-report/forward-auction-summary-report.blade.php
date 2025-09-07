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
                                <div id="export-progress">
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

    <script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
    <script>
        $(document).ready(function () {
            var exporting = false;

            $('#export-btn').on('click', function (e) {
                e.preventDefault();
                if (exporting) return;

                exporting = true;
                $('#export-btn').prop('disabled', true);
                $('#export-progress').show();
                setProgress(0);

                var filters = {
                    auction_id: $('#auction_id').val(),
                    vendor_name: $('#vendor_name').val(),
                    buyer_name: $('#buyer_name').val(),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val()
                };

                $.ajax({
                    url: "{{ route('admin.forward-auctions-summary.exportTotal') }}",
                    method: 'GET',
                    dataType: 'json',
                    data: filters
                }).done(function (init) {
                    var total = init && init.total ? parseInt(init.total, 10) : 0;
                    var limit = init && init.chunk_size ? parseInt(init.chunk_size, 10) : 500; // chunk size
                    var offset = 0;

                    if (total === 0) {
                        alert('No data found for the selected filters.');
                        reset();
                        return;
                    }

                    var wb = new ExcelJS.Workbook();
                    var ws = wb.addWorksheet('Forward Auctions');
                    ws.addRow(['Auction ID', 'Product Details', 'Buyer Name', 'Vendor Name', 'Start Date & Time', 'Participated']);

                    var fetchChunk = function () {
                        if (offset >= total) {
                            wb.xlsx.writeBuffer().then(function (buffer) {
                                var blob = new Blob([buffer], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
                                var url = URL.createObjectURL(blob);
                                var a = document.createElement('a');
                                a.href = url;
                                a.download = 'forward-auction-report-' + Date.now() + '.xlsx';
                                document.body.appendChild(a);
                                a.click();
                                a.remove();
                                URL.revokeObjectURL(url);
                                setProgress(100);
                                reset();
                            });
                            return;
                        }

                        var params = $.extend({}, filters, {start: offset, limit: limit});
                        $.ajax({
                            url: "{{ route('admin.forward-auctions-summary.exportBatch') }}",
                            method: 'GET',
                            dataType: 'json',
                            data: params
                        }).done(function (rows) {
                            (rows.data || []).forEach(function (r) {
                                ws.addRow(r);
                            });
                            offset += (rows.data || []).length;
                            var pct = Math.round(Math.min(offset, total) / total * 100);
                            setProgress(pct);
                            fetchChunk();
                        }).fail(function () {
                            alert('Export error: request failed');
                            reset();
                        });
                    };

                    fetchChunk();
                }).fail(function () {
                    alert('Export init failed.');
                    reset();
                });

                function setProgress(pct) {
                    $('#progress').css('width', pct + '%');
                    $('#progress-text').text(pct + '%');
                }

                function reset() {
                    exporting = false;
                    $('#export-btn').prop('disabled', false);
                    $('#export-progress').hide();
                }
            });

            $('#export-progress').hide();
        });
    </script>
@endsection
