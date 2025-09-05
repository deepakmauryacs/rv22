@extends('buyer.layouts.app', ['title'=>'Blacklisted Vendors'])

@section('css')
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1 inner-main">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header py-3 bg-white">
                    <h1 class="font-size-18 mb-0">Blacklisted Vendors</h1>
                </div>
                <div class="card-body pt-4">
                    <form id="searchForm" action="{{ route('buyer.vendor.blacklist') }}" method="GET"></form>
                    <div class="table-responsive">
                        @include('buyer.vendor.partials.favourite-table', ['results' => $results])
                        <!-- <div class="alert alert-warning text-center font-size-13 border-0">
                            You haven't Blacklisted any Vendor.
                        </div>
                        <table class="product-listing-table w-100">
                            <thead>
                                <tr>
                                    <th>Sr.No.</th>
                                    <th>Vendor Name</th>
                                    <th>Product Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>TESTING VENDOR</td>
                                    <td>
                                        SUBMERSIBLE PUMP KIT, TESTINGSS, 000
                                    </td>
                                    <td><button class="ra-btn ra-btn-outline-danger">DELETE</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>TEST AMIT VENDOR 2</td>
                                    <td>
                                        DP TEST MATERIALS, GLASS SLEEVE, GLASS INSULATED COPPER LEAD WIRE
                                    </td>
                                    <td><button class="ra-btn ra-btn-outline-danger">DELETE</button></td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>TESTING VENDOR</td>
                                    <td>
                                        SUBMERSIBLE PUMP KIT, TESTINGSS, 000
                                    </td>
                                    <td><button class="ra-btn ra-btn-outline-danger">DELETE</button></td>
                                </tr>
                            </tbody>
                        </table> -->
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <script>
        function deleteFavourite(url) {
            $.ajax({
                url: url,
                type: 'DELETE',
                dataType: 'json',
                data:{
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.status) {
                         location.reload();
                    }
                }
            });
        }
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
        });
    </script>
@endsection