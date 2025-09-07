@extends('buyer.layouts.app', ['title'=>'Live Auction RFQs'])

@section('css')
    <link rel="stylesheet" href="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.css') }}" />
    <style>
        .clickable-td{
            cursor: pointer;
        }
    </style>
@endsection
 
@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar-menu')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1">
        <div class="container-fluid">
            <div class="bg-white active-rfq-page">                 
                <h3 class="card-head-line">Live Auction RFQs</h3>
                <div class="px-2">
                    <form id="filter-rfq" action="{{ route('buyer.auction.index') }}" method="GET">
                        <div class="row g-3 rfq-filter-button">
                            <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="rfq_no" placeholder="" value="{{ request('rfq_no') }}" id="rfq-no"/>
                                        <label for="rfq-no">RFQ No</label>
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
                                    <a href="{{ route('buyer.auction.index') }}" class="ra-btn small-btn ra-btn-outline-danger" id="reset-filter">
                                        <span class="bi bi-arrow-clockwise"></span> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
              
                <div class="table-responsive p-2" id="table-container">
                    @include('buyer.auction.partials.table', ['results' => $results])
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
<!-- jQuery UI -->
<script src="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>
<script>
    $(document).ready(function () {
        $(".clickable-td").click(function() {
            window.location.href = $(this).find('a').attr('href');
        });
        $(document).on('submit', '#filter-rfq', function(e) {
            e.preventDefault();
            loadTable($(this).attr('action') + '?' + $(this).serialize());
        });

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            loadTable($(this).attr('href'));
        });

        $(document).on('change', '#perPage', function () {
            const form = $('#filter-rfq');
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

        $(document).on('click', '.close-auction', function(e) {
            e.preventDefault();
            const rfqNo = $(this).data('rfq');
            if (!rfqNo) return;
            if (confirm("Are you sure you want to close this auction?")) {
                $.ajax({
                    url: "{{ route('buyer.auction.close') }}",
                    type: 'POST',
                    data: {
                        rfq_no: rfqNo,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        alert(response.message || 'Failed to close auction.');
                        if (response.success) {
                            loadTable(window.location.href);
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Failed to close auction.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        alert(msg);
                    }
                });
            }
        });

        let dateToday = new Date();

        $('#auction-date').datetimepicker({
            lang: 'en',
            timepicker: false,
            format: 'd/m/Y',
        }).disableKeyboard();

    
        $(document).on('blur', '#from-date', function () {
            let date = $("#from-date").val()
            const myArray = date.split("/");
            let finel_date = myArray[2]+"/"+myArray[1]+"/"+myArray[0]+" GMT"
            let to_date = new Date(finel_date)
            
            to_date.setDate(to_date.getDate());
            $("#to-date").datetimepicker({
                format: 'd/m/Y',
                timepicker:false,
                minDate : to_date,
                scrollMonth : false,
                scrollInput : false
            });
        }).on('change', '#from-date', function () {
            let from_date = $("#from-date").val();
            const from_array_date = from_date.split("/");
            let from_final_date = parseInt(from_array_date[2]+from_array_date[1]+from_array_date[0])
            let to_date = $("#to-date").val();
            const to_array_date = to_date.split("/");
            let to_finel_date = parseInt(to_array_date[2]+to_array_date[1]+to_array_date[0])
            if(to_finel_date<from_final_date){
                $("#to-date").val("");
            }
        }).on("click", "#reset-filter", function(){
            $(".rfq-filter-button").find("input").val("");
            $(".rfq-filter-button").find("select").val("0");
        });

    });
</script>
@endsection