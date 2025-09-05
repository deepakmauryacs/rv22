@extends('buyer.layouts.app', ['title'=>'Draft RFQ'])

@section('css')
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar-default')
    </div>
    <main class="main flex-grow-1">
            <div class="container-fluid">
                <div class="pb-2">
                    <!-- Start Breadcrumb Here -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('buyer.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">My Notifications</li>
                        </ol>
                    </nav>
                </div>

                <div class="card rounded">
                    <div class="card-header bg-white py-3">
                        <h1 class="font-size-18 mb-0">Notification</h1>
                    </div>
                    <div class="card-body">
                        <form id="searchForm" action="{{ route('buyer.notification.index') }}" method="GET">
                    
                        </form>
                        <div class="notification-list-all" id="table-container">
                           
                            @include('buyer.notification.partials.table', ['notifications' => $notifications])
                             

                            <!-- <div class="ra-pagination pt-3 pt-sm-0">
                                <div class="row gy-3 align-items-center justify-content-between">
                                    <div class="col-12 col-sm-auto">
                                        <div class="d-flex align-items-center justify-content-center justify-content-sm-start gap-2">
                                            <div>
                                                <select name="datatables-length" aria-controls="datatables" class="select-items-no" id="dt-length-1">
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                </select>
                                            </div>

                                            <label for="dt-length-1"> entries per page</label>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-auto">
                                        <div class="d-flex justify-content-center justify-content-sm-end">
                                            <nav aria-label="Page navigation example">
                                                <ul class="pagination mb-0">
                                                    <li class="page-item">
                                                        <a class="page-link" href="#" aria-label="Previous">
                                                            <span class="bi bi-chevron-left font-size-11" aria-hidden="true"></span>
                                                            <span class="visually-hidden-focusable">Previous</span>
                                                        </a>
                                                    </li>
                                                    <li class="page-item active"><a class="page-link" href="#">1</a>
                                                    </li>
                                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                                    <li class="page-item">
                                                        <a class="page-link" href="#" aria-label="Next">
                                                            <span class="bi bi-chevron-right font-size-11" aria-hidden="true"></span>
                                                            <span class="visually-hidden-focusable">Next</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>

                                    </div>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </main>

 

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
</script>
@endsection
