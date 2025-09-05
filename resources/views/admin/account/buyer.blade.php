@extends('admin.layouts.app_second', 
['title' => 'Buyers Accounts List', 
'sub_title' => ''])

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Buyers Accounts List</li>
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
                    <h1>Buyers Accounts List</h1>
                    <div class="row pt-3 pt-sm-0">
                        <div class="col-xl-12 col-lg-12 col-md-12 mb-3">
                            <div>
                                <div id="export-progress">
                                    <p>Export Progress: <span id="progress-text">0%</span></p>
                                    <div id="progress-bar" style="width: 100%; background: #f3f3f3;">
                                        <div id="progress" style="height: 20px; width: 0%; background: green;"></div>
                                    </div>
                                </div>
                            </div>
                            <form id="searchForm" action="{{ route('admin.accounts.buyer') }}" method="GET">
                                <ul class="rfq-filter-button justify-content-start">
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <div class="form-floating">
                                                <input type="text" name="buyer_name" id="buyer_name" class="form-control fillter-form-control" value="{{ request('buyer_name') }}" placeholder="Buyer Name">
                                                <label for="buyer_name">Buyer Name</label>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-record2"></i></span>
                                            <div class="form-floating">
                                                <select 
                                                    name="status" 
                                                    id="seller_status" 
                                                    class="form-select fillter-form-select"
                                                >
                                                    <option value="">Select</option>
                                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                                <label for="seller_status">Status</label>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="d-none d-sm-block">
                                        <button type="submit" class="btn-style btn-style-primary">
                                            <i class="bi bi-search"></i> Search
                                        </button>
                                    </li>

                                    <li class="d-none d-sm-block">
                                        <a href="{{ route('admin.accounts.buyer') }}" class="btn-style btn-style-danger">RESET</a>
                                    </li>

                                    <li class="d-none d-sm-block">
                                        <button class="btn-style btn-style-white" id="export-btn"><i class="bi bi-download"></i>
                                            EXPORT
                                        </button>
                                    </li>
                                    <li class="d-sm-none">
                                        <div class="d-flex align-items-center gap-3">
                                            <button type="submit" class="btn-style btn-style-primary">
                                                <i class="bi bi-search"></i> Search
                                            </button>
                                            <a href="{{ route('admin.accounts.buyer') }}" class="btn-style btn-style-danger">RESET</a>
                                            <button class="btn-style btn-style-white" id="export-btn"><i class="bi bi-download"></i>
                                            EXPORT
                                            </button>
                                        </div>
                                    </li>
                                </ul>
                            </form>
                        </div>
                    </div>

                    <div id="table-container">
                        @include('admin.account.partials.buyer-table', ['results' => $results])
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

});

function assignedManager(user_id,manage_id) {
    $.ajax({
        url: "{{ route('admin.accounts.buyer.manager') }}",
        type: "post",
        data: { _token: "{{ csrf_token() }}", user_id,manage_id },
        success: function(res) {
            toastr.success(res.message);
            location.reload();
        },
        error: function() {
            toastr.error('Something went wrong.');  
        },
    });
}
 
function extendFreePlan(id,user_plan,user_id,buyer_name) {
    var extend_month = $('#extend_plan_month_'+id).val();
    if (confirm('Are you sure, you want to update free plan for '+buyer_name+'?')) {
        $.ajax({
            url: "{{ route('admin.accounts.buyer.plan.extend') }}",
            type: "post",
            data: { _token: "{{ csrf_token() }}", user_id, user_plan, extend_month },
            success: function(res) {
                if(res.status)
                {
                    toastr.success(res.message);
                    location.reload();
                }else{
                    toastr.error(res.message);
                }
            },
            error: function() {
                toastr.error('Something went wrong.');  
            },
        });
    }
} 
</script>
<script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>
<script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
<script>
    $(document).ready(function () {
        const exporter = new Exporter({
            chunkSize: 20,
            rowLimitPerSheet: 200000,
            headers: ["Buyer’s Name","Plan Name", "Invoice No", "No. Of Users", "Buyer Code","Buyer’s Contact","Buyer’s Email","Date Of Subscription","Period","Next Renewal Date","Amount","Assign Manager"],
            totalUrl: "{{ route('admin.accounts.exportBuyerTotal') }}",
            batchUrl: "{{ route('admin.accounts.exportBuyerBatch') }}",
            token: "{{ csrf_token() }}",
            exportName: "Buyer-Account",
            expButton: '#export-btn',
            exportProgress: '#export-progress',
            progressText: '#progress-text',
            progress: '#progress-bar',
            fillterReadOnly: '.fillter-form-control',
            getParams: function () {
                return {
                    buyer_name: $('#buyer_name').val(),
                    status: $('#status').val(),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val()
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
