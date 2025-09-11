@extends('admin.layouts.app')
@section('title', 'Verified Products')
@section('content')
<style>
    .pagination .page-link { border-radius: 8px; margin: 0 3px; font-size: 16px; color: #3b82f6; }
    .pagination .active .page-link { background-color: #3b82f6; color: white; border-color: #3b82f6; }
</style>
<div class="container-fluid">
    <div class="card shadow mb-4 mt-4">
        <div class="card-header py-3 d-flex justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">All Verified Products</h6>
        </div>
        <div class="card-body">
            <form id="searchForm" action="{{ route('admin.verified-products.index') }}" method="GET">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <input type="text" name="product_name" class="form-control" value="{{ request('product_name') }}" placeholder="Product Name">
                    </div>
                     <div class="col-md-4">
                        <input type="text" name="vendor_name" class="form-control" value="{{ request('product_name') }}" placeholder="Vendor Name">
                    </div>
                    
                    <div class="col-md-4 d-flex align-items-end">
                      <div class="form-group">
                        <button type="submit" class="btn btn-primary">SEARCH</button>
                        <a href="{{ route('admin.verified-products.index') }}" class="btn btn-secondary ml-2">RESET</a>
                        <a href="{{ route('admin.verified-products.export', request()->query()) }}" class="btn btn-success ml-2">EXPORT</a>
                      </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive" id="table-container">
                @include('admin.verified-products.partials.table', ['products' => $products])
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
    $(document).on('change', '#perPage', function () {
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
        const status = $(this).is(':checked') ? 1 : 2;
        const checkbox = $(this);

        $.ajax({
            url: "{{ url('super-admin/verified-products') }}/" + id + "/status",
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
            url: "{{ url('super-admin/verified-products') }}/" + id,
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
