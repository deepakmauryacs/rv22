@extends('buyer.layouts.app', ['title'=>'Scheduled RFQ'])

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
        @include('buyer.layouts.sidebar')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1 inner-main">
        <div class="container-fluid">
            <div class="bg-white sent-rfq-page">
                <form id="filter-rfq" action="{{ route('buyer.rfq.scheduled-rfq') }}" method="GET">
                    <h3 class="card-head-line px-2">Scheduled RFQ</h3>
                    <div class="px-2">
                        <div class="row g-3 rfq-filter-button">
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="rfq_no" placeholder="" value="" />
                                        <label for="">RFQ No</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-share"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="from-date" name="from_date" placeholder="" value="" autocomplete="off">
                                        <label for="">From Scheduled Date</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-signpost"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="to-date" name="to_date" placeholder="" value="" autocomplete="off">
                                        <label>To Scheduled Date</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-ubuntu"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="product_name" placeholder="" value="" />
                                        <label for="">Product Name</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="prn_number" placeholder="" value="" />
                                        <label for="">PRN Number</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-auto mb-3">
                                <div class="d-flex gap-3">
                                    <button type="submit" class="ra-btn small-btn ra-btn-primary">
                                        <span class="bi bi-search"></span>
                                        Search
                                    </button>
                                    <button type="button" onclick="javascript:window.location.href='{{ route('buyer.rfq.scheduled-rfq') }}'" class="ra-btn small-btn ra-btn-outline-danger">
                                        <span class="bi bi-arrow-clockwise"></span>
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive p-2" id="table-container">
                    @include('buyer.rfq.scheduled.partials.table', ['results' => $results])
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


        let dateToday = new Date();

        $('#from-date').datetimepicker({
            lang: 'en',
            timepicker: false,
            maxDate: dateToday,
            format: 'd/m/Y',
        }).disableKeyboard();

        let last_date_to_response = new Date();
        last_date_to_response.setDate(last_date_to_response.getDate() + 1);
        $('#to-date').datetimepicker({
            lang: 'en',
            timepicker: false,
            maxDate: dateToday,
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

    function deleteScheduledRFQ(rfq_id){
        if(confirm("Do you want to delete this Scheduled RFQ ?")){
            $.ajax({
                url: "{{ route('buyer.rfq.scheduled-rfq.delete') }}",
                type: "POST",
                data: {
                    rfq_id: rfq_id,"_token": "{{ csrf_token() }}",
                },
                success: function(response) {
                    if(response.status == 'success'){
                        loadTable(response.url);
                    }else{
                        alert(response.message);
                    }
                }
            });
        }
    }

</script>
@endsection
