@extends('admin.layouts.app_second', ['title' => 'Bulk Products for Approval', 'sub_title' => 'Approval List'])

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <a href="{{ route('admin.bulk-products.index') }}"> Bulk Products for Approval</a>
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
                    <h1>Products for Approval</h1>
                    <div class="row pt-3 pt-sm-0">
                        <div class="col-xl-12 col-lg-12 col-md-12">
                            <form id="searchForm" action="{{ route('admin.bulk-products.index') }}" method="GET">
                                <ul class="rfq-filter-button">
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="text" name="product_name" class="form-control fillter-form-control" value="{{ request('product_name') }}" placeholder="Product Name">
                                                <label>Product Name</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <div class="form-floating">
                                                <input type="text" name="vendor_name" class="form-control fillter-form-control" value="{{ request('vendor_name') }}" placeholder="Vendor Name">
                                                <label>Vendor Name</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="d-flex align-items-center gap-3">
                                            <button type="submit" class="btn-style btn-style-primary"><i class="bi bi-search"></i> Search</button>
                                            <a href="{{ route('admin.bulk-products.index') }}" class="btn-style btn-style-danger">RESET</a>
                                        </div>
                                    </li>
                                   
                                </ul>
                            </form>
                        </div>
                     
                    </div>

                    <div class="product_listing_table_wrap" id="table-container">
                        @include('admin.bulk-approval-products.partials.table', ['products' => $products])
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

    $(document).on('change', '#perPage', function () {
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

    $(document).on('change', '.product-status-toggle', function() {
        const id = $(this).data('id');
        const status = $(this).is(':checked') ? 1 : 0;
        const checkbox = $(this);

        $.ajax({
            url: "{{ url('admin/product-approvals') }}/" + id + "/status",
            type: "PUT",
            data: { _token: "{{ csrf_token() }}", status: status },
            success: function(res) {
                toastr.success(res.message);
            },
            error: function() {
                toastr.error('Something went wrong.');
                checkbox.prop('checked', !checkbox.prop('checked'));
            }
        });
    });

    $(document).on('click', '.btn-delete-product', function() {
        if (!confirm('Are you sure?')) return;
        const id = $(this).data('id');

        $.ajax({
            url: "{{ url('admin/product-approvals') }}/" + id,
            type: "DELETE",
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                toastr.success(res.message);
                location.reload();
            }
        });
    });
});
</script>
@endsection
