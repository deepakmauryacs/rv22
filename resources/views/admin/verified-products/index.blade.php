@extends('admin.layouts.app_second', [
    'title' => 'All Verified Products'
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
                    <a href="{{ route('admin.verified-products.index') }}"> All Verified Products </a>
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
                    <h1>All Verified Products</h1>
                    <div class="row">
                        <div class="col-md-12">
                            <div id="export-progress">
                                <p>Export Progress: <span id="progress-text">0%</span></p>
                                <div id="progress-bar" style="width: 100%; background: #f3f3f3;">
                                    <div id="progress" style="height: 20px; width: 0%; background: green;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-8 col-lg-8 col-md-12">
                            <form id="searchForm" action="{{ route('admin.verified-products.index') }}" method="GET">
                                <div class="row pt-3 gx-2 gx-sm-3 align-items-center">
                                    <div class="col-12 col-sm-auto pb-3 pb-sm-0">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="text" name="product_name" id="productName" class="form-control fillter-form-control" value="{{ request('product_name') }}" placeholder="Product Name">
                                                <label for="productName">Product Name</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-auto pb-3 pb-sm-0">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="text" name="vendor_name" id="vendorName" class="form-control fillter-form-control" value="{{ request('product_name') }}" placeholder="Vendor Name">
                                                <label for="vendorName">Vendor Name</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto pb-3 pb-sm-0">
                                        <button type="submit" class="btn-style btn-style-primary">
                                            <i class="bi bi-search"></i>
                                            Search
                                        </button>
                                    </div>
                                    <div class="col-auto pb-3 pb-sm-0">
                                        <a href="{{ route('admin.verified-products.index') }}"
                                        class="btn-style btn-style-danger">RESET</a>
                                    </div>
                                    <div class="col-auto pb-3 pb-sm-0">
                                        <button class="btn-style btn-style-white" id="export-btn"><i class="bi bi-download"></i>
                                            EXPORT
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                  


                        <div class="col-xl-4 col-lg-4 col-md-12">
                        <div class="row pt-3 gx-2 gx-sm-3 align-items-center">
                            <div class="col-4 col-sm-auto">
                                <select class="form-select" id="tag-type">
                                    <option value="PRIME">Prime</option>
                                    <option value="POPULAR">Popular</option>
                                    <option value="NOTHING" selected>Nothing</option>
                                </select>
                            </div>
                            <div class="col-4 col-sm-auto">
                                <select class="form-select"  id="time-period">
                                       <option value="">Month</option>
                                       <option value="1">1 Month</option>
                                       <option value="3">3 Months</option>
                                       <option value="6">6 Months</option>
                                       <option value="9">9 Months</option>
                                       <option value="12">12 Months</option>
                                </select>
                            </div>
                            <div class="col-4 col-sm-auto">
                                <button type="button" class="btn-style btn-style-secondary" id="apply-filter">
                                        Apply
                                </button>
                            </div>
                        </div>
                            <!-- <ul class="rfq-filter-button">
                                <li>
                                    <select class="form-select" id="tag-type">
                                       <option value="PRIME">Prime</option>
                                       <option value="POPULAR">Popular</option>
                                       <option value="NOTHING" selected>Nothing</option>
                                    </select>
                                </li>

                                <li>
                                    <select class="form-select"  id="time-period">
                                       <option value="">Month</option>
                                       <option value="1">1 Month</option>
                                       <option value="3">3 Months</option>
                                       <option value="6">6 Months</option>
                                       <option value="9">9 Months</option>
                                       <option value="12">12 Months</option>
                                    </select>
                                </li>
                                <li class="move_to_end">
                                    <button type="button" class="btn-style btn-style-secondary" id="apply-filter">
                                        Apply
                                    </button>
                                </li>
                            </ul> -->
                        </div>
                    </div>
                    <div class="product_listing_table_wrap" id="table-container">
                        @include('admin.verified-products.partials.table', ['products' => $products])
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

    $(document).on('change', '.product-status-toggle', function() {
        const id = $(this).data('id');
        const status = $(this).is(':checked') ? 1 : 2;
        const checkbox = $(this);

        $.ajax({
            url: "{{ url('super-admin/verified-products') }}/" + id + "/status",
            type: "PUT",
            data: {
                _token: "{{ csrf_token() }}",
                status: status
            },
            success: function(res) {
                toastr.success(res.message);
            },
            error: function() {
                toastr.error('Something went wrong.');
                checkbox.prop('checked', !checkbox.prop('checked'));
            }
        });
    });

    $(document).on('change', '#select-all-products', function() {
        const checked = $(this).is(':checked');
        $('.product-checkbox').prop('checked', checked);
    });

    $(document).on('change', '.product-checkbox', function() {
        const allChecked = $('.product-checkbox').length === $('.product-checkbox:checked').length;
        $('#select-all-products').prop('checked', allChecked);
    });

    $(document).on('click', '.btn-delete-product', function() {
        if (!confirm('Are you sure?')) return;
        const id = $(this).data('id');

        $.ajax({
            url: "{{ url('super-admin/verified-products') }}/" + id,
            type: "DELETE",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                toastr.success(res.message);
                location.reload();
            }
        });
    });

    $(document).on('click', '#apply-filter', function () {
        let selectedProducts = [];

        $(".product-checkbox:checked").each(function () {
            selectedProducts.push($(this).val());
        });

        let prodTag = $("#tag-type").val(); // Make sure your select dropdown has this ID
        let validMonths = $("#time-period").val();

        if (selectedProducts.length === 0) {
            alert("Please select at least one product.");
            return;
        }

        if (!prodTag) {
            alert("Please select a badge type.");
            return;
        }

        if (prodTag !== "NOTHING" && !validMonths) {
            alert("Please select a valid time period.");
            return;
        }

        $.ajax({
            url: "{{ route('admin.verified-products.update-tags') }}", // Create this route
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                product_ids: selectedProducts,
                prod_tag: prodTag,
                valid_months: validMonths
            },
            dataType: "json",
            success: function (response) {
                toastr.success(response.message);
                if (response.status === "success") {
                    location.reload();
                }
            },
            error: function () {
                toastr.error("Something went wrong. Please try again.");
            }
        });
    });
});

</script>

<script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>
<script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
<script>
    $(document).ready(function () {
        const exporter = new Exporter({
            chunkSize: 1000,
            rowLimitPerSheet: 200000,
            headers: ["Vendor Name", "Division", "Category", "Product Name", "Product Alias", "Added By Vendor", "Verified By"],
            totalUrl: "{{ route('admin.verified-products.exportTotal') }}",
            batchUrl: "{{ route('admin.verified-products.exportBatch') }}",
            token: "{{ csrf_token() }}",
            exportName: "All-Verified-Products",
            expButton: '#export-btn',
            exportProgress: '#export-progress',
            progressText: '#progress-text',
            progress: '#progress',
            fillterReadOnly: '.fillter-form-control',
            getParams: function () {
                return {
                    name: $('#name').val(),
                    vendor_name: $('#vendor_name').val()
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