@extends('admin.layouts.app_second', ['title' => 'Products for Approval', 'sub_title' => 'Auction RFQs Summary'])
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
                    <a href="{{ route('admin.reports.auction-rfqs-summary') }}"> Auction RFQs Summary Reports</a>
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
                    <h1>Auction RFQs Summary Reports</h1>
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
                            <form id="searchForm" action="{{ route('admin.reports.auction-rfqs-summary') }}"
                                method="GET">
                                <div class="row gx-3">
                                    <div class="col-12 col-sm-auto mt-3 mt-sm-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="text" name="buyer_name" id="buyer_name"
                                                    class="form-control fillter-form-control"
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
                                                    class="form-control fillter-form-control"
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
                                                    class="form-control fillter-form-control"
                                                    value="{{ request('to_date') }}" placeholder="To Date">
                                                <label>To Date</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto mt-3 mt-sm-4">
                                        <button type="submit" class="btn-style btn-style-primary"><i
                                                class="bi bi-search"></i> Search</button>
                                    </div>
                                    <div class="col-auto mt-3 mt-sm-4">
                                        <a href="{{ route('admin.reports.auction-rfqs-summary') }}"
                                            class="btn-style btn-style-danger">RESET</a>
                                    </div>
                                    <div class="col-auto mt-3 mt-sm-4">
                                        <button class="btn-style btn-style-white" id="export-btn"><i
                                                class="bi bi-download d-none d-sm-inline-flex"></i>
                                            EXPORT
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="product_listing_table_wrap" id="table-container">
                        @include('admin.reports.partials.auction-rfq-summary-report-table', [
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
<script>
    $(document).ready(function() {
        $('#export-progress').hide();

        let exporting = false;

        $('#export-btn').on('click', function(e) {
            e.preventDefault();
            if (exporting) return;
            exporting = true;

            const chunkSize = 1000;
            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet('Sheet1');
            worksheet.addRow(["RFQ No", "Auction Date", "Auction Time", "Buyer Name", "Products", "Vendor Name", "Email", "Mobile", "Status", "Is Participated", "Order Confirmed"]);

            $('#export-progress').show();
            $('#progress').css('width', '0%');
            $('#progress-text').text('0%');
            $('.fillter-form-control').attr('readonly', true);
            $(this).attr('disabled', true);

            const filters = {
                buyer_name: $('#buyer_name').val(),
                from_date: $('#from_date').val(),
                to_date: $('#to_date').val()
            };

            function resetExport() {
                $('#export-progress').hide();
                $('#progress').css('width', '0%');
                $('#progress-text').text('0%');
                $('.fillter-form-control').attr('readonly', false);
                $('#export-btn').attr('disabled', false);
                exporting = false;
            }

            $.ajax({
                url: "{{ route('admin.auction-rfqs-summary.exportTotal') }}",
                method: 'GET',
                data: filters,
                success: function(res) {
                    const total = res.total;
                    if (!total) {
                        alert('No data found');
                        resetExport();
                        return;
                    }

                    let fetched = 0;
                    let lastRfqId = null;
                    let lastVendorId = null;

                    const fetchBatch = () => {
                        const params = Object.assign({}, filters, { limit: chunkSize });
                        if (lastRfqId !== null && lastVendorId !== null) {
                            params.last_rfq_id = lastRfqId;
                            params.last_vendor_id = lastVendorId;
                        }

                        $.ajax({
                            url: "{{ route('admin.auction-rfqs-summary.exportBatch') }}",
                            method: 'GET',
                            data: params,
                            success: function(batch) {
                                batch.data.forEach(row => worksheet.addRow(row));
                                fetched += batch.data.length;
                                lastRfqId = batch.last_rfq_id;
                                lastVendorId = batch.last_vendor_id;

                                const percent = Math.round((fetched / total) * 100);
                                $('#progress').css('width', percent + '%');
                                $('#progress-text').text(percent + '%');

                                if (fetched < total && batch.data.length > 0) {
                                    fetchBatch();
                                } else {
                                    workbook.xlsx.writeBuffer().then(buffer => {
                                        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                                        const url = URL.createObjectURL(blob);
                                        const a = document.createElement('a');
                                        a.href = url;
                                        a.download = 'auction-rfq-summary-report_' + Date.now() + '.xlsx';
                                        document.body.appendChild(a);
                                        a.click();
                                        document.body.removeChild(a);
                                        URL.revokeObjectURL(url);
                                    }).catch(() => {
                                        alert('Error generating file');
                                    }).finally(() => {
                                        resetExport();
                                    });
                                }
                            },
                            error: function() {
                                alert('Error fetching data');
                                resetExport();
                            }
                        });
                    };

                    fetchBatch();
                },
                error: function() {
                    alert('Error fetching total count');
                    resetExport();
                }
            });
        });
    });
</script>
@endsection
