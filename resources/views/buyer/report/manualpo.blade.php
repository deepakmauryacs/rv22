@php
    use Illuminate\Support\Str;
@endphp
@extends('buyer.layouts.appInventory', ['title'=> 'Manual PO Report List'])
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
    <div class="card rounded">

        <div class="card-header bg-white">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h1 class="font-size-22 mb-0">Manual PO Report</h1>
                <div class="input-group w-200 mt-4">
                    <span class="input-group-text"><span class="bi bi-shop"></span></span>
                    <div class="form-floating">
                        <select class="form-select globle-field-changes form-select branch_unit" name="branch_id" id="branch_id">
                            @foreach($branches as $branch)
                                <option value="{{ $branch->branch_id }}" {{ session('branch_id') == $branch->branch_id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <label>Branch/Unit:</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Inventry Filter Section -->
            <div class="row g-3 pt-3 mb-3">
                <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                        <div class="form-floating">
                            <input type="text" class="form-control reportinputdiv"  name="search_product_name" id="search_product_name" value="" class="form-control globle-field-changes cart-action-qty-input filterinbox" placeholder="Product Name" />
                            <label for="search_product_name">Product Name</label>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-list-ul"></span></span>
                        <div class="form-floating">
                            <input type="text" class="form-control reportinputdiv"  name="search_order_no" id="search_order_no" value="" class="form-control globle-field-changes cart-action-qty-input filterinbox" placeholder="Order No" />
                            <label for="search_order_no">Order No</label>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-list-ul"></span></span>
                        <div class="form-floating">
                            <input type="text" class="form-control reportinputdiv"  name="search_vendor_name" id="search_vendor_name" value="" class="form-control globle-field-changes cart-action-qty-input filterinbox" placeholder="Vendor Name" />
                            <label for="search_vendor_name">Vendor Name</label>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-signpost"></span></span>
                        <div class="form-floating">
                            <select class="form-select" name="order_status" id="order_status">
                                <option value="">Select</option>
                                <option value="1">Confirmed</option>
                                <option value="2">Cancelled</option>
                            </select>
                            <label for="order_status">Status:</label>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-shop"></span></span>
                        <div class="form-floating">
                            <select class="form-select"  name="category_id" id="search_category_id" >
                                <option value="">Select</option>
                            @foreach ($categories as $id => $categoryName)
                                @php
                                    $isLong = strlen($categoryName) > 14;
                                @endphp
                                <option
                                    value="{{ $categoryName }}"
                                    {{ request('category_id') == $categoryName ? 'selected' : '' }}
                                    title="{{ $isLong ? $categoryName : '' }}"
                                >
                                    {{ $isLong ? Str::limit($categoryName, 14) : $categoryName }}
                                    @if ($isLong)
                                        <i class="bi bi-info-circle" title="{{ $categoryName }}"></i>
                                    @endif
                                </option>
                            @endforeach
                            </select>
                            <label>Category:</label>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                        <div class="form-floating">
                            <input type="text" class="form-control dateTimePickerStart" name="from_date" id="from_date"
                                placeholder="From Date">
                            <label for="from_date">From Date</label>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                        <div class="form-floating">
                            <input type="text" class="form-control dateTimePickerEnd" name="to_date" id="to_date"
                                placeholder="To Date">
                            <label for="to_date">To Date</label>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-auto">
                    <button type="button"
                        class="ra-btn ra-btn-primary w-100 justify-content-center font-size-11" id="searchBtn">
                        <span class="bi bi-search" aria-hidden="true"></span>
                        Search
                    </button>
                </div>
                <div class="col-6 col-sm-auto">
                    <button type="button"
                        class="ra-btn ra-btn-outline-danger w-100 justify-content-center font-size-11" onClick="window.location.href='{{ route('buyer.report.manualpo') }}'">
                        <span class="bi bi-arrow-clockwise" aria-hidden="true"></span>
                        Reset
                    </button>
                </div>
                <div class="col-6 col-sm-auto">
                    <button type="button"
                        class="ra-btn ra-btn-primary w-100 justify-content-center font-size-11 px-2 px-sm-3" onClick="window.location.href='{{ route('buyer.inventory.index') }}'">
                        Back to Inventory
                    </button>
                </div>
                <div class="col-6 col-sm-auto">
                    <button type="button"
                        class="ra-btn ra-btn-primary w-100 justify-content-center font-size-11" id="showreportmodal">
                        All Reports
                    </button>
                </div>
                <div class="col-6 col-sm-auto">
                    <button type="button"
                        class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-11" id="export">
                        <span class="bi bi-download" aria-hidden="true"></span>
                        Export
                    </button>
                </div>


            </div>

            <!-- Start Datatable -->

            <div class="table-responsive table-inventory">
                <table class="product-listing-table w-100 dataTables-example1"  id="report-table">
                    <thead>
                        <tr>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Serial Number</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Order Number</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Order Date</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Product Name</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Vendor Name</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Added BY</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Order Value ({{ session('user_currency.symbol', '₹')}})</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Status</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <!-- End Datatable -->
        </div>
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
                    $('#branch_id, #search_product_name, #search_category_id, #search_order_no,#search_vendor_name,#order_status').on('change keyup', function() {
                        $('#report-table').DataTable().ajax.reload();
                    });
                });

                function report_list_data() {
                    $('#report-table').DataTable({
                        processing  : true,
                        serverSide  : true,
                        searching   : false,
                        paging      : true,
                        scrollY     : 300,
                        pageLength  : 25,
                        destroy: true,
                        ajax: {
                            url: "{{ route('buyer.report.manualPO.listdata') }}",
                            data: function (d) {
                                d.from_date = $('#from_date').val();
                                d.to_date = $('#to_date').val();
                                d.branch_id           = $('#branch_id').val();
                                d.search_product_name = $('#search_product_name').val();
                                d.search_order_no     = $('#search_order_no').val();
                                d.search_vendor_name  = $('#search_vendor_name').val();
                                d.search_category_id  = $('#search_category_id').val();
                                d.order_status        = $('#order_status').val();
                            },
                        },
                        columns: [
                                {
                                    data: null,
                                    render: function (data, type, row, meta) {
                                        return meta.row + meta.settings._iDisplayStart + 1;
                                    },
                                    orderable: false,
                                },
                                { data:'manual_po_number', name: 'manual_po_number' },
                                { data:'order_date', name:'order_date' },
                                { data: 'product_names', name:'product_name'},
                                { data:'vendor_name', name:'vendor_name' },
                                { data:'prepared_by', name:'prepared_by' },
                                { data:'total_amount', name:'total_amount' },
                                { data:'status', name:'status' },
                            ],
                            columnDefs: [
                                { "orderable": false, "targets": "_all" }
                            ],
                            order: [],
                            language: {
                                        processing: "<div class='spinner-border spinner-border-sm'></div> Loading..."
                                    }
                    });
                    $('#report-table').on('draw.dt', function () {
                        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(function (tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl);
                        });
                    });

                }

                $(document).on('click', '#export', function() {
                    let btn = $(this);
                    let url= "{{ route('buyer.report.manualpoReport.export') }}";
                    let data= {
                            _token                      :   $('meta[name="csrf-token"]').attr("content"),
                            branch_id                   :   $('#branch_id').val(),
                            search_product_name         :   $('#search_product_name').val(),
                            search_category_id          :   $('#search_category_id').val(),
                            search_order_no             :   $('#search_order_no').val(),
                            search_vendor_name          :   $('#search_vendor_name').val(),
                            order_status                :   $('#order_status').val(),
                            from_date                   :   $('#from_date').val(),
                            to_date                     :   $('#to_date').val()
                        };
                    var deleteExcelUrl="{{route('buyer.delete.export.file')}}";
                    inventoryFileExport(btn,url,data,deleteExcelUrl);                    
                });

                // Open Report Modal
                $('#showreportmodal').click(function (e) {
                    e.preventDefault();
                    $('#reportModal').modal('show');
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
