@php
    use Illuminate\Support\Str;
@endphp
@extends('buyer.layouts.appInventory', ['title'=> 'Product Wise Stock Ledger Report List'])
@push('styles')
    <link rel="stylesheet" href="{{ asset('public/css/report.css') }}">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
@endpush
@push('headJs')
    @once
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    @endonce
@endpush
@section('content')
    <div class="container-fluid py-4">
        <div class="main" id="main">
            <div class="myaccount-box-right1">
                <div class="mt-15 Inventory">
                    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 notification ticket_list mb-0">
                        <!-- Page Title & Branch Dropdown -->
                        <div class="row align-items-center g-3 mb-4 top_part">
                            <div class="col-md-8">
                                <h3 class="mb-0 text-wrap">Product Wise Stock Ledger Report</h3>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shop"></i></span>
                                    <div class="form-floating flex-grow-1">
                                        <select class="form-select" disabled>
                                            <option value="{{ $inventory->branch->branch_id }}">{{ $inventory->branch->name }}</option>
                                        </select>
                                        <label>Branch/Unit:</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Info Card -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light fw-bold fs-5">
                                {{ $inventory->product->product_name ?? 'Product Name' }}
                            </div>
                            <div class="card-body p-3">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0 text-nowrap">
                                        <tbody>
                                            <tr>
                                                <th class="text-nowrap">Specification</th>
                                                <td class="text-start">{{ $inventory->specification ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-nowrap">Size</th>
                                                <td class="text-start">{{ $inventory->size ?? 'N/A' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Date Filters and Action Buttons -->
                        <form method="GET" class="row g-3 align-items-end mb-4">
                            <div class="col-12 col-md-3">
                                <label for="from_date" class="form-label">From Date</label>
                                <input type="text" id="from_date" name="from_date" class="form-control date-picker">
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="to_date" class="form-label">To Date</label>
                                <input type="text" id="to_date" name="to_date" class="form-control date-picker">
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                                    <a href="#" class="btn-rfq btn-rfq-primary" id="searchBtn">
                                        <i class="bi bi-search"></i> Search
                                    </a>
                                    <a href="{{ route('buyer.report.productWiseStockLedger.index', ['id' => $inventory->id]) }}" class="btn-rfq btn-rfq-danger filterBtn">
                                        <i class="bi bi-arrow-clockwise"></i> Reset
                                    </a>
                                    <a href="{{ route('buyer.inventory.index') }}" class="btn-rfq btn-rfq-primary">
                                        <i class="bi bi-arrow-left-square bi-md" style="font-size: 14px;" ></i> Back to Inventory
                                    </a>
                                    <a href="{{ route('buyer.report.stockLedger') }}" class="btn-rfq btn-rfq-primary">
                                        <i class="bi bi-arrow-left"></i> Stock Ledger
                                    </a>
                                    <a href="#" class="btn-rfq btn-rfq-white merge-selected-rfq-btn export-btn" id="export">
                                        <i class="bi bi-download"></i> Export
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Report Table -->
                        <div class="card shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered text-nowrap mb-0 dataTables-example1" id="report-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th rowspan="2" class="text-center align-middle">Date</th>
                                                <th rowspan="2" class="text-center align-middle">Particulars / Description</th>
                                                <th rowspan="2" class="text-center align-middle">No.</th>
                                                <th rowspan="2" class="text-center align-middle">Reference Number</th>
                                                <th colspan="2" class="text-center align-middle">Inwards</th>
                                                <th colspan="2" class="text-center align-middle">Outwards</th>
                                                <th colspan="2" class="text-center align-middle">Closing</th>
                                            </tr>
                                            <tr>
                                                <th class="text-center align-middle">Quantity ({{ $inventory->uom->uom_name }})</th>
                                                <th class="text-center align-middle">Total Amount ({{ session('user_currency.symbol', '₹') }})</th>
                                                <th class="text-center align-middle">Quantity ({{ $inventory->uom->uom_name }})</th>
                                                <th class="text-center align-middle">Total Amount ({{ session('user_currency.symbol', '₹') }})</th>
                                                <th class="text-center align-middle">Quantity ({{ $inventory->uom->uom_name }})</th>
                                                <th class="text-center align-middle">Total Amount ({{ session('user_currency.symbol', '₹') }})</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dynamic table data -->
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination Responsive Wrapper -->
                                <div class="d-flex flex-wrap align-items-center justify-content-between p-3">
                                    <div class="d-flex align-items-center mb-2 mb-md-0">
                                        <!-- Show entries dropdown will auto appear here (datatable) -->
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                        <!-- Pagination buttons will auto appear here (datatable) -->
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div> <!-- notification end -->
                </div> <!-- Inventory end -->
            </div> <!-- myaccount-box-right1 end -->
        </div> <!-- main end -->
    </div>

    <!--main-->
    @include('buyer.report.modal')
    @push('exJs')
        @once
            <script src="{{ asset('public/js/xlsx.full.min.js') }}"></script>
            <script src="{{ asset('public/js/datepicker.js') }}"></script>
            <script src="{{ asset('public/js/inventoryFileExport.js') }}"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                $(document).ready(function() {
                    $('input').val('');

                    if (typeof $.fn.DataTable !== "function") {
                        toastr.error("DataTables is not loaded! Check script order!");
                        return;
                    }
                    report_list_data();
                });

                function report_list_data() {
                    var inventory_id = "{{ $inventory->id ?? '' }}";
                    $('#report-table').DataTable({
                        processing  : true,
                        serverSide  : true,
                        searching   : false,
                        paging      : true,
                        "scrollY"   : 300,
                        pageLength  : 25,
                        ajax: {
                            url: "{{ route('buyer.report.productWiseStockLedger.listdata') }}",
                            data: function (d) {
                                d.inventory_id = inventory_id;
                                d.from_date = $('#from_date').val();
                                d.to_date = $('#to_date').val();
                            },
                        },
                        columns: [
                            { data: 'date', name: '' },
                            { data: 'description', name: '' },
                            { data: 'no', name: '' },
                            { data: 'reference_number', name: '' },
                            { data: 'inward_quantity', name: '' },
                            { data: 'inward_total_amount', name: '' },
                            { data: 'outward_quantity', name: '' },
                            { data: 'outward_total_amount', name: '' },
                            { data: 'closing_quantity', name: '' },
                            { data: 'closing_total_amount', name: '' },
                        ],
                        columnDefs: [
                            { "orderable": false, "targets": "_all" }
                        ],
                        order: [],

                    });
                    $('#report-table').on('draw.dt', function () {
                        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(function (tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl);
                        });
                    });

                }

                $(document).on('click', '#export', function() {
                    var inventory_id = "{{ $inventory->id ?? '' }}";
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('buyer.report.productWiseStockLedger.export') }}",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr("content"),
                            inventory_id: inventory_id,
                            from_date: $('#from_date').val(),
                            to_date: $('#to_date').val()
                        },
                        dataType: 'json',
                        success: function(result) {
                            if (result.count != 0) {
                                var createXLSLFormatObj = [];
                                var xlsHeader = [
                                    "Date",
                                    "Particulars / Description",
                                    "No.",
                                    "Reference Number",
                                    "Inward Quantity ({{ $inventory->uom->uom_name }})",
                                    "Inward Total Amount ({{ session('user_currency.symbol', '₹') }})",
                                    "Outward Quantity ({{ $inventory->uom->uom_name }})",
                                    "Outward Total Amount ({{ session('user_currency.symbol', '₹') }})",
                                    "Closing Quantity ({{ $inventory->uom->uom_name }})",
                                    "Closing Total Amount ({{ session('user_currency.symbol', '₹') }})"
                                ];
                                var xlsRows = result.data;
                                var s_no = 1;
                                createXLSLFormatObj.push(xlsHeader);

                                $.each(xlsRows, function(index, value) {
                                    var innerRowData = [];
                                    $.each(value, function(ind, val) {
                                        innerRowData.push(val);
                                    });
                                    createXLSLFormatObj.push(innerRowData);
                                });

                                createXLSLFormatObj.unshift([
                                    "","Size: {{ $inventory->size ?? '-' }}", "", "", "", "","", "", "", ""
                                ]);

                                createXLSLFormatObj.unshift([
                                    "", "Specification: {{ $inventory->specification ?? '-' }} ", "", "", "", "","", "", "",  ""
                                ]);
                                createXLSLFormatObj.unshift([
                                    "","Branch Name: {{ $inventory->branch->name}}", "", "", "", "", "", "", "",  ""
                                ]);
                                createXLSLFormatObj.unshift([
                                    "","Product Name: {{ $inventory->product->product_name ?? 'Product Name' }}", "", "", "", "", "", "", "",  ""
                                ]);
                                var date = new Date();
                                var dd = date.getDate();
                                var mm = date.getMonth() + 1;
                                var yy = date.getFullYear();
                                var today = ('0' + dd).slice(-2) + '-' + ('0' + mm).slice(-2) + '-' + yy;

                                var filename = "Product Wise Stock Ledger Report " + today + ".xlsx";

                                var ws_name = "Reports";
                                var wb = XLSX.utils.book_new(),
                                    ws = XLSX.utils.aoa_to_sheet(createXLSLFormatObj);

                                ws['!merges'] = [
                                    { s: { r: 0, c: 1 }, e: { r: 0, c: 9 } },
                                    { s: { r: 1, c: 1 }, e: { r: 1, c: 9 } },
                                    { s: { r: 2, c: 1 }, e: { r: 2, c: 9 } },
                                    { s: { r: 3, c: 1 }, e: { r: 3, c: 9 } },
                                ];
                                XLSX.utils.book_append_sheet(wb, ws, ws_name);
                                XLSX.writeFile(wb, filename);
                            } else {
                                toastr.error('No record found, Try another search!');
                            }
                        }
                    });
                });

                //from date
                $('#from_date').datepicker({
                    dateFormat: 'dd-mm-yy',
                    maxDate: 0, // today
                    onSelect: function (selectedDate) {
                        // Set min and max date for To Date
                        $('#to_date').datepicker('option', 'minDate', selectedDate);
                        $('#to_date').datepicker('option', 'maxDate', 0); // today
                        $('#to_date').datepicker('enable');
                    }
                });
                // Search button click
                $('#searchBtn').on('click', function (e) {
                    e.preventDefault();
                    const fromDate = $('#from_date').val();
                    const toDate = $('#to_date').val();

                    if (fromDate && toDate) {
                        $('#report-table').DataTable().ajax.reload();
                    } else {
                        toastr.error("Please select both From Date and To Date before searching.");
                    }
                });
                // Initialize To Date (disabled until From Date is selected)
                $('#to_date').datepicker({
                    dateFormat: 'dd-mm-yy'
                }).datepicker('disable');
            </script>
        @endonce
    @endpush
@endsection
