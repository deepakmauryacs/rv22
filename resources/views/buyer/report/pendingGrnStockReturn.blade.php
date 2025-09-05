@php
    use Illuminate\Support\Str;
@endphp
@extends('buyer.layouts.appInventory', ['title'=> 'Closed Indent Report List'])
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
                <h1 class="font-size-22 mb-0">Pending GRN for Stock Return Report</h1>
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
                        class="ra-btn ra-btn-outline-danger w-100 justify-content-center font-size-11" onClick="window.location.href='{{ route('buyer.report.pendingGrnStockReturn') }}'">
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
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">GRN Number</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Stock Return No</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Product Name</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Specification</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Size</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Vendor Name</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Added By</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Added Date</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">UOM</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Stock Return Quantity</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Total Grn Quantity</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Pending GRN Quantity</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <!-- End Datatable -->
        </div>
    </div>
    <!--main-->
    @include('buyer.report.modal')
    @include('buyer.report.grnQtyDetailsModal')

    @push('exJs')
        @once
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                var pendingGrnStockReturnreportlistdataurl        =    "{{ route('buyer.report.pendingGrnStockReturnReportlistdata') }}";
                var exportreportlistPendingGrnStockReturnurl      =    "{{ route('buyer.report.exportPendingGrnStockReturnReport') }}";
                var deleteExcelUrl="{{route('buyer.delete.export.file')}}";
            </script>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
            <script src="{{ asset('public/js/xlsx.full.min.js') }}"></script>
            <script src="{{ asset('public/js/pendingGrnStockReturn.js') }}"></script>
            <script src="{{ asset('public/js/inventoryFileExport.js') }}"></script>
        @endonce
    @endpush
@endsection
