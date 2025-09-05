@extends('buyer.layouts.app', ['title'=>"PI's / Invoices"])

@section('css')
    <link rel="stylesheet" href="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.css') }}" />
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar-default')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1">
        <div class="container-fluid">
            <div class="bg-white active-rfq-page">                 
                <h3 class="card-head-line">PI's / Invoices</h3>
                <div class="px-2">
                    <form id="filter-rfq" action="{{ route('buyer.rfq.pi-invoice') }}" method="GET">
                        <div class="row g-3 rfq-filter-button">
                            <div class="col12 col-sm-3 col-md-3 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="order_no" placeholder="" value="{{ request('order_no') }}" id="rfq-no"/>
                                        <label for="order_no">Order No</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col12 col-sm-3 col-md-3 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-ubuntu"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="vendor_name" placeholder="" value="{{ request('vendor_name') }}" id="product-name"/>
                                        <label for="vendor_name">Vendor Name</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col12 col-sm-2 col-md-2 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="form_date" placeholder="" value="{{ request('form_date') }}" id="from_date"
                                            autocomplete="off" />
                                        <label for="form_date">From PI Date</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col12 col-sm-2 col-md-2 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="to_date" placeholder="" value="{{ request('to_date') }}" id="to_date" autocomplete="off"/>
                                        <label for="to_date">To PI Date</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col12 col-sm-auto mb-3">
                                <div class="d-flex gap-3">
                                    <button type="submit" class="ra-btn small-btn ra-btn-primary">
                                        <span class="bi bi-search"></span> Search
                                    </button>
                                    <a href="{{ route('buyer.rfq.pi-invoice') }}" class="ra-btn small-btn ra-btn-outline-danger" id="reset-filter">
                                        <span class="bi bi-arrow-clockwise"></span> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive p-2" id="table-container">
                    @include('buyer.rfq.pi.partials.table', ['results' => $results])
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
<script src="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>
<script>
    $(document).ready(function () {
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
        
        $(document).on("click", "#reset-filter", function(){
            $(".rfq-filter-button").find("input").val("");
            $(".rfq-filter-button").find("select").val("0");
        });

        var dateToday = new Date();
        $('#from_date').datetimepicker({
            lang: 'en',
            timepicker: false,
            maxDate: dateToday,
            format: 'd/m/Y',
        }).disableKeyboard();

        $(document).on('blur', '#from_date', function () {
            let date = $("#from_date").val();
            const myArray = date.split("/");
            let finel_date = myArray[2]+"/"+myArray[1]+"/"+myArray[0]+" GMT"
            let to_date = new Date(finel_date)
            
            to_date.setDate(to_date.getDate());
            $("#to_date").datetimepicker({
                format: 'd/m/Y',
                timepicker:false,
                maxDate : dateToday,
                minDate : to_date,
                scrollMonth : false,
                scrollInput : false
            }).disableKeyboard();
        }).on('change', '#from_date', function () {
            let from_date = $("#from_date").val();
            const from_array_date = from_date.split("/");
            let from_final_date = parseInt(from_array_date[2]+from_array_date[1]+from_array_date[0])
            let to_date = $("#to_date").val();
            const to_array_date = to_date.split("/");
            let to_finel_date = parseInt(to_array_date[2]+to_array_date[1]+to_array_date[0])
            if(to_finel_date<from_final_date){
                $("#to_date").val("");
            }
        });

        // function set_to_date(){
        //     var datea = $("#from_date").val()
        //     const myArray = datea.split("/");
        //     var finel_date = myArray[2]+"/"+myArray[1]+"/"+myArray[0]+" GMT";
        //     var to_date = new Date(finel_date);
        //     to_date.setDate(to_date.getDate());
        //     var today_date = new Date();
        //     $("#to_date").datetimepicker({
        //         format: 'd/m/Y',
        //         timepicker:false,
        //         maxDate : today_date,
        //         minDate : to_date,
        //         scrollMonth : false,
        //         scrollInput : false
        //     }).disableKeyboard();
        // }
        // function validate_todate() {
        //     var from_date = $("#from_date").val();
        //     const from_array_date = from_date.split("/");
        //     var from_finel_date = parseInt(from_array_date[2]+from_array_date[1]+from_array_date[0]);
        //     var to_date = $("#to_date").val();
        //     const to_array_date = to_date.split("/");
        //     var to_finel_date = parseInt(to_array_date[2]+to_array_date[1]+to_array_date[0]);
        //     if(to_finel_date<from_finel_date){
        //         $("#to_date").val("");
        //     }
        // }

    });
</script>
@endsection