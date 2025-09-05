@php
    use Illuminate\Support\Str;
@endphp
@extends('buyer.layouts.appInventory', ['title'=> 'Inventory Dashboard'])
@push('styles')
    <link rel="stylesheet" href="{{ asset('public/css/addInventoryModal.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/suggestions.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/manualPO.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/inventorytable.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
@endpush

@section('content')
    <div class="card rounded">
        <div class="card-header bg-white">
            <div class="row align-items-center gx-3 gy-2 pt-0 pt-sm-4 pb-2">
                <div class="col-12 col-sm-auto">
                    <h1 class="font-size-18 mb-3 mb-sm-0">Inventory</h1>
                </div>

                <div class="col-12 col-sm-auto">
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1"><span class="bi bi-shop"></span></span>
                            <div class="form-floating">
                                <select name="branch_id" id="branch_id" class="form-select">
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->branch_id }}" {{ session('branch_id') == $branch->branch_id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label>Branch/Unit</label>
                            </div>
                    </div>
                </div>


                <div class="col-6 col-sm-auto">
                    <button type="button" class="ra-btn ra-btn-outline-primary font-size-11 w-100 justify-content-center" onclick="show_issue_modal()">
                        <span class="bi bi-plus-square font-size-11 d-none d-sm-inline-flex" aria-hidden="true"></span>
                        Issue
                    </button>
                </div>

                <div class="col-6 col-sm-auto">
                    <button type="button" class="ra-btn ra-btn-outline-warning text-black font-size-11 w-100 justify-content-center" onclick="show_indent_modal()">
                        <span class="bi bi-plus-square text-black font-size-11 d-none d-sm-inline-flex" aria-hidden="true"></span>
                        Add Indent
                    </button>
                </div>

                <div class="col-6 col-sm-auto">
                    <button type="button" class="ra-btn ra-btn-primary font-size-11 w-100 justify-content-center" data-bs-toggle="modal" data-bs-target="#addRfqModal" onclick="show_rfq_modal()">
                        <span class="bi bi-plus-square font-size-11 d-none d-sm-inline-flex" aria-hidden="true"></span>
                        Add to RFQ
                    </button>
                </div>

                <div class="col-6 col-sm-auto">
                    <button type="button" class="ra-btn ra-btn-outline-danger font-size-11 w-100 justify-content-center"
                    onClick="show_stock_return_modal()">
                    <span class="bi bi-plus-square font-size-11 d-none d-sm-inline-flex" aria-hidden="true"></span>
                    Stock Return
                    </button>
                </div>

                <div class="col-6 col-sm-auto">
                    <button type="button" class="ra-btn ra-btn-outline-danger font-size-11 w-100 justify-content-center px-2 px-sm-3" onclick="show_issue_return_modal()">
                        <span class="bi bi-plus-square font-size-11 d-none d-sm-inline-flex" aria-hidden="true"></span>
                        Issued Return
                    </button>
                </div>

                <!-- Other Links -->
                <div class="col-6 col-sm-auto ms-xl-auto">
                    <div class="dropdown-container w-100">
                        <button type="button" id="dropdownToggleOtherLink" class="ra-btn ra-btn-outline-danger font-size-11 w-100 justify-content-center">
                            <span class="bi bi-plus-square font-size-11 d-none d-sm-inline-flex" aria-hidden="true"></span>
                            Other Links
                        </button>

                        <div class="dropdown-menu-custom" id="dropdownMenuOtherLink">
                            <ul>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100" id="addInventoryBtn">
                                        <span class="bi bi-plus-square" aria-hidden="true"></span> Add Inventory
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100"
                                        data-bs-toggle="modal" id="editInventoryBtn">
                                        <span class="bi bi-pencil-square" aria-hidden="true"></span> Edit
                                        Inventory
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100" onclick="window.location.href='{{ route('buyer.bulk.inventory.import') }}'">
                                        <span class="bi bi-box-arrow-in-down" aria-hidden="true"></span> Bulk
                                        Import
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100"  id="issuedtoBtn">
                                        <span class="bi bi-plus-square" aria-hidden="true"></span> Issue To
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100 deleteInventory">
                                        <span class="bi bi-trash3" aria-hidden="true"></span> Delete
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100 resetIndentRFQ">
                                        <span class="bi bi-arrow-clockwise" aria-hidden="true"></span> Reset
                                        Indent, RFQ
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100 manualPO">
                                        <span class="bi bi-plus-square" aria-hidden="true"></span> Manual PO
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>





            </div>

        </div>

        <div class="card-body">
            <!-- Inventry Filter Section -->
            <div class="row gx-3 gy-2 pt-3 mb-3">
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                        <div class="form-floating">
                            <input type="text" class="form-control" name="search_product_name" id="search_product_name" placeholder="" value="" />
                            <label for="search_product_name">Product Name</label>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-shop"></span></span>
                        <div class="form-floating">
                            <select class="form-select" name="category_id" id="search_category_id">
                                <option value="" selected>Select</option>
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
                            <label for="search_category_id">Category:</label>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-0 mb-sm-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-record2"></span></span>
                        <div class="form-floating">
                            <select class="form-select" name="ind_non_ind" id="ind_non_ind">
                                <option value="" selected>Select</option>
                                <option value="2">Indent</option>
                                <option value="3">Non Indent</option>
                            </select>
                            <label for="ind_non_ind">Indent Filter:</label>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-0 mb-sm-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-shop"></span></span>
                        <div class="form-floating">
                            <select class="form-select" name="inventory_type_id" id="search_inventory_type_id" >
                                <option value="" selected>Select</option>
                                @foreach($inventoryTypes as $inventoryType)
                                    <option value="{{ $inventoryType->id }}" {{ request('inventory_type_id') == $inventoryType->id ? 'selected' : '' }}>
                                        {{ $inventoryType->name }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="search_inventory_type_id">Inventory Type :</label>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-auto mb-0 mb-sm-3">
                    <button type="button" onclick="window.location.href='{{ route('buyer.inventory.index') }}'" class="ra-btn ra-btn-outline-danger w-100 justify-content-center">
                        <span class="bi bi-arrow-clockwise" aria-hidden="true"></span>
                        Reset
                    </button>
                </div>
                <div class="col-6 col-sm-auto mb-0 mb-sm-3">
                    <button type="button" class="ra-btn ra-btn-primary w-100 justify-content-center" id="showreportmodal">
                        All Reports
                    </button>
                </div>
                <div class="col-6 col-sm-auto mb-0 mb-sm-3">
                    <button type="button" class="ra-btn ra-btn-outline-primary w-100 justify-content-center" id="export">
                        <span class="bi bi-download" aria-hidden="true"></span>
                        Export
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="product-listing-table invengtory-all-table" id="inventory-table">
                    <thead>
                        <tr>
                            <th class="text-center border-bottom-dark text-wrap keep-word">Select</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word" style="width: 100px">Product</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word">Category</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word">Our Product Name
                            </th>
                            <th class="text-center border-bottom-dark text-wrap keep-word">Specification</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word">Size</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word">Brand</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word">Inventory
                                Grouping</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word">Current Stock
                            </th>
                            <th class="text-center border-bottom-dark text-wrap keep-word">UOM</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word">Indent Qty</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word">RFQ Qty</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word">Order Qty</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word">GRN Qty</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <!--main-->

    @include('buyer.inventory.modal')
    @include('buyer.inventory.grnaddmodal')
    @include('buyer.indent.modal')
    @include('buyer.issue.modal')
    @include('buyer.issueReturn.modal')
    @include('buyer.stockReturn.modal')
    @include('buyer.report.modal')
    @include('buyer.inventory.issuedto')
    @include('buyer.inventory.addRFQ')
    @include('buyer.inventory.orderDetailsModal')
    @include('buyer.inventory.manualPOModal')

    @push('exJs')
        @once
        
            <script src="{{ asset('public/js/inventory.js') }}"></script>
            <script src="{{ asset('public/js/xlsx.full.min.js') }}"></script>
            <script src="{{ asset('public/js/deleteInventory.js') }}"></script>
            <script src="{{ asset('public/js/resetIndentRFQ.js') }}"></script>
            <script src="{{ asset('public/js/manualPO.js') }}"></script>
            <script src="{{ asset('public/js/selectAll.js') }}"></script>
            <script src="{{ asset('public/js/addgrn.js') }}"></script>
            <script src="{{ asset('public/js/addRfq.js') }}"></script>
            <script src="{{ asset('public/js/addIssue.js') }}"></script>
            <script src="{{ asset('public/js/addRfq.js') }}"></script>
            <script src="{{ asset('public/js/showOrderDetails.js') }}"></script>
            <script src="{{ asset('public/js/addIssueReturn.js') }}"></script>
            <script src="{{ asset('public/js/addStockReturn.js') }}"></script>
            <script src="{{ asset('public/js/inventoryFileExport.js') }}"></script>
        @endonce

        <script>
            var getInventoryDetailsUrl = "{{ route('buyer.inventory.getDetailsByID') }}";
            var editInventoryDetailsUrl = "{{ route('buyer.inventory.edit', ['id' => '__ID__']) }}";
            var postindentlisturl = "{{ route('buyer.indent.fetchIndentData') }}";
            var postindentdataurl = "{{ route('buyer.indent.getIndentData') }}";
            var manualPOFetchURL = "{{ route('buyer.manualPO.fetchInventory') }}";
            var searchVendorByVendornameURL = "{{ route('buyer.manualPO.search.vendors') }}";
            var getVendorDetailsByNameURL = "{{ route('buyer.manualPO.get.vendordetails') }}";
            var genarateManualPOURL = "{{ route('buyer.manualPO.store') }}";
            var checkGrnEntry = "{{ route('buyer.grn.checkGrnEntry', ['inventoryId' => '__ID__']) }}";
            var getInventoryDetailsForIssueUrl = "{{ route('buyer.issue.fetchInventoryDetails') }}";
            var getissuedtoUrl = "{{ route('buyer.issued.getissuedto') }}";
            var saveissuedtoUrl = "{{ route('buyer.issued.save') }}";
            var deleteissuedtoUrl = "{{ route('buyer.issued.delete') }}";
            var getInventoryDetailsForIssueReturnUrl ="{{ route('buyer.issue_return.fetchInventoryDetails') }}";
            var getInventoryDetailsForStockReturnUrl ="{{ route('buyer.stock_return.fetchInventoryDetails') }}";
            var deleteInventoryUrl ="{{ route('buyer.inventory.delete') }}";
            var resetInventoryUrl ="{{ route('buyer.inventory.reset') }}";
            var fetchInventoryDetailsForAddRfqUrl ="{{ route('buyer.inventory.fetchInventoryDetailsForAddRfq') }}";
            var activeRfqUrl ="{{ route('buyer.inventory.activeRfq', ['inventoryId' => '__ID__']) }}";
            var activeRfqDetailsbyIdUrl = "{{ route('buyer.rfq.details', ['rfq_id' => '__RFQ_ID__']) }}";
            var orderDetailsbyIdUrl = "{{ route('buyer.rfq.details', ['rfq_id' => '__RFQ_ID__']) }}";
            var orderDetailsUrl ="{{ route('buyer.inventory.orderDetails', ['inventoryId' => '__ID__']) }}";

        </script>

        <script>
            $(document).ready(function() {
                $('input').val('');
                $('#ind_non_ind').val('');
                $('#search_category_id').val('');
                $('#search_inventory_type_id').val('');
                if (typeof $.fn.DataTable !== "function") {
                    toastr.error("DataTables is not loaded! Check script order!");
                    return;
                }
                inventory_list_data();

                $('#branch_id, #search_product_name, #search_category_id, #ind_non_ind, #search_inventory_type_id').on('change keyup', function() {
                    $('#inventory-table').DataTable().ajax.reload();
                });
            });

            function inventory_list_data() {
                $('#inventory-table').DataTable({
                    processing: true,
                    serverSide: true,
                    searching: false,
                    paging: true,
                    scrollY: 300,
                    scrollX: true,
                    pageLength: 25,
                    destroy: true,
                    ajax: {
                        url: "{{ route('buyer.inventory.data') }}",
                        data: function(d) {
                            d.branch_id = $('#branch_id').val();
                            d.search_product_name = $('#search_product_name').val();
                            d.search_category_id = $('#search_category_id').val();
                            d.ind_non_ind = $('#ind_non_ind').val();
                            d.search_inventory_type_id = $('#search_inventory_type_id').val(); // fixed typo here
                        }
                    },
                    columns: [
                        { data: 'select', orderable: false, searchable: false },
                        { data: 'product', name: 'product' },
                        { data: 'category', name: 'category' },
                        { data: 'our_product_name', name: 'our_product_name' },
                        { data: 'specification', name: 'specification' },
                        { data: 'size', name: 'size' },
                        { data: 'brand', name: 'brand' },
                        { data: 'inventory_grouping', name: 'inventory_grouping' },
                        { data: 'current_stock', name: 'current_stock' },
                        { data: 'uom', name: 'uom' },
                        { data: 'indent_qty', name: 'indent_qty' },
                        { data: 'rfq_qty', name: 'rfq_qty' },
                        { data: 'order_qty', name: 'order_qty' },
                        { data: 'grn_qty', name: 'grn_qty' }
                    ],
                    order: [],
                    columnDefs: [
                        { orderable: false, targets: '_all' }
                    ],
                    language: {
                        processing: "<div class='spinner-border spinner-border-sm'></div> Loading..."
                    }
                });
            }
            $(document).on('click', '#export', function () {
                let btn = $(this);
                let url= "{{route('buyer.inventory.exportData')}}";
                let data= {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        branch_id: $('#branch_id').val(),
                        search_product_name: $('#search_product_name').val(),
                        search_category_id: $('#search_category_id').val(),
                        ind_non_ind: $('#ind_non_ind').val(),
                        search_inventory_type_id: $('#search_inventory_type_id').val()
                    };
                let deleteExcelUrl="{{route('buyer.delete.export.file')}}";
                inventoryFileExport(btn,url,data,deleteExcelUrl);
            });
        </script>
    @endpush
@endsection
