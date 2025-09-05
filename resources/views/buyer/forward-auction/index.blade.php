@extends('buyer.layouts.app', ['title'=>'Active RFQs/CIS'])
@section('css')
<link rel="stylesheet" href="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.css') }}" />
<link href="{{ asset('public/assets/buyer/css/page-style.css') }}" rel="stylesheet">
<style>
.clickable-td{
    cursor: pointer;
}
</style>
@endsection
@section('content')
<div class="bg-white">
    @include('buyer.layouts.sidebar-menu')
</div>
<main class="main flex-grow-1">
    <div class="container-fluid">
        <div class="bg-white active-rfq-page">                 
            <h3 class="card-head-line">Active RFQs/CIS</h3>
            <div class="px-2">
                <form id="searchForm" action="{{ route('buyer.forward-auction.index') }}" method="GET">
                    <div class="row g-3 rfq-filter-button">
                        <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="auction_no" placeholder="" value="{{ request('auction_no') }}"  id="auction-no">
                                    <label for="rfq-no">Auction No</label>
                                </div>
                            </div>
                        </div>
                 
                        <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-ubuntu"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="product_name" placeholder="" value="{{ request('product_name') }}" id="product-name"/>
                                    <label for="product-name">Product Name</label>
                                </div>
                            </div>
                        </div>
                 
                        <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="auction-date" name="auction_date" placeholder="" value="{{ request('auction_date') }}" autocomplete="off" />
                                    <label for="from-date">Auction Date</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col12 col-sm-auto mb-3">
                            <div class="d-flex gap-3">
                                <button type="submit" class="ra-btn small-btn ra-btn-primary">
                                    <span class="bi bi-search"></span> Search
                                </button>
                                <a href="{{ route('buyer.forward-auction.index') }}" class="ra-btn small-btn ra-btn-outline-danger" id="reset-filter">
                                    <span class="bi bi-arrow-clockwise"></span> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
          
            <div class="table-responsive p-2" id="table-container">
                 @include('buyer.forward-auction.partials.table', ['results' => $results])
            </div>
        </div>
    </div>
</main>
@endsection
@section('scripts')
<script src="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>
<script>
$(document).ready(function() {
     $('#auction-date').datetimepicker({
        lang: 'en',
        timepicker: false,
        format: 'd/m/Y',
    }).disableKeyboard();

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

$(document).on('click', '.delete-auction-btn', function(e){
    e.preventDefault();
    var auctionId = $(this).data('auction-id');
    var rowId = $(this).data('row-id');
    if(confirm('Are you sure you want to delete this auction?')) {
        $.ajax({
            url: '{{ route("buyer.forward-auction.destroy", ":auction_id") }}'.replace(':auction_id', auctionId),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            },
            success: function(response){
                toastr.success('Auction deleted successfully');
                $('#' + rowId).fadeOut(400, function(){ $(this).remove(); });
            },
            error: function(xhr){
                let msg = "Failed to delete auction.";
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                toastr.error(msg);
            }
        });
    }
});
</script>
@endsection