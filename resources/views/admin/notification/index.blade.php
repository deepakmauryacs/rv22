@extends('admin.layouts.app_second', ['title' => 'Notification', 'sub_title' => 'List'])
@section('css')
<style>
  .message-body-line a:hover {
    color: #13293b;
}
</style>
@endsection
@section('breadcrumb')
    <div class="breadcrumb-header">
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Notification</li>
                </ol>
            </nav>
        </div>
    </div>
@endsection
@section('content')
    <div class="about_page_details">
        <div class="container-fluid">
            <div class="card border-0">
                <div class="card-header bg-transparent py-3 mb-3">
                    <h1 class="fs-5">My Notification</h1>
                </div>
                <div class="card-body">
                    <div class="col-md-12 botom-border">
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 col-md-12">
                                <form id="searchForm" action="{{ route('admin.notification.index') }}" method="GET">

                                </form>
                            </div>
                        </div>

                        {{-- Start Static Nolification List --}}
                        {{-- <div class="notification-list-all">
                            <div class="message_wrap height-inherit">
                                <div class="message-wrapper Nblue">
                                    <div class="message-detail">

                                        <div class="message-head-line">
                                            <div class="person_name">
                                                <span>A KUMAR</span>
                                            </div>
                                            <p class="message-body-line">
                                                26 Mar, 2025 05:12 PM
                                            </p>
                                        </div>
                                        <p class="message-body-line">
                                            <a href="javascript:void(0)" target="_blank" rel="noopener noreferrer">'A
                                                KUMAR' has responded to your RFQ No.
                                                RATB-25-00046. You can check their quote here <i class="bi bi-eye"></i></a>
                                        </p>
                                    </div>
                                </div>
                                <div class="message-wrapper Npink">
                                    <div class="message-detail">
                                        <div class="message-head-line">
                                            <div class="person_name">
                                                <span>A KUMAR</span>
                                            </div>
                                            <p class="message-body-line">
                                                26 Mar, 2025 05:12 PM
                                            </p>
                                        </div>
                                        <p class="message-body-line">
                                            <a href="http://" target="_blank" rel="noopener noreferrer">
                                                'A KUMAR' has responded to your RFQ No.
                                                RATB-25-00046. You can check their quote here <i class="bi bi-eye"></i>
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="message-wrapper Nyellow">
                                    <div class="message-detail">
                                        <div class="message-head-line">
                                            <div class="person_name">
                                                <span>TEST AMIT VENDOR</span>
                                            </div>
                                            <p class="message-body-line">
                                                26 Mar, 2025 04:35 PM
                                            </p>
                                        </div>
                                        <p class="message-body-line">
                                            <a href="http://" target="_blank" rel="noopener noreferrer">
                                                'TEST AMIT VENDOR' has responded to your RFQ No.
                                                RATB-25-00046. You can check their quote here <i class="bi bi-eye"></i>
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="message-wrapper Ngreen">
                                    <div class="message-detail">
                                        <div class="message-head-line">
                                            <div class="person_name">
                                                <span>A KUMAR</span>
                                            </div>
                                            <p class="message-body-line">
                                                26 Mar, 2025 04:35 PM
                                            </p>
                                        </div>
                                        <p class="message-body-line">
                                            <a href="http://" target="_blank">
                                                'A KUMAR' has responded to your RFQ No.
                                                RATB-25-00046. You can check their quote here <i class="bi bi-eye"></i></a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                        {{-- End Static Nolification List --}}
                        <div class="product_listing_table_wrap" id="table-container">
                            @include('admin.notification.partials.table', [
                                'notifications' => $notifications,
                            ])
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
        });
    </script>
@endsection
