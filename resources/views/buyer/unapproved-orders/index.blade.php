@extends('buyer.layouts.app', ['title'=>'Manage Role'])

@section('css')
<link rel="stylesheet" href="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.css') }}" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-dt@1.13.4/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="{{ url('/') }}/public/css/inventorytable.css">
@endsection

@section('content')
<div class="bg-white">
    <!---Sidebar-->
    @include('buyer.layouts.sidebar')
</div>

<!---Section Main-->
<main class="main flex-grow-1 inner-main">
    <div class="container-fluid">
        <div class="bg-white sent-rfq-page unapproved-orer-listing-page">
            <form class="mb-3">
                <h3 class="card-head-line px-2">Unapproved Orders</h3>
                <div class="px-2">
                    <div class="row g-3 rfq-filter-button">
                        <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-list-ul"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="po_number" placeholder="" value=""
                                        id="po_number" />
                                    <label for="po_number">Unapproved Order No</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="rfq_no" placeholder="" value="" />
                                    <label for="rfq_no">RFQ No</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-share"></span></span>
                                <div class="form-floating">
                                    <select class="form-select" id="division" name="division">
                                        <option readonly selected value=""> Select </option>
                                        @foreach ($divisions as $item)
                                        <option value="{{ $item->id }}" {{ request('division')==$item->id ? 'selected' :
                                            '' }} >{{ $item->division_name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="">Division</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-signpost"></span></span>
                                <div class="form-floating">
                                    <select class="form-select" id="category" name="category">
                                        <option selected readonly value=""> Select </option>
                                        @foreach ($unique_category as $item => $ids)
                                        <option value="{{ implode(',', $ids) }}" {{ request('category')==implode(',',
                                            $ids) ? 'selected' : '' }}>{{ $item }}</option>
                                        @endforeach
                                    </select>
                                    <label>Category</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-signpost"></span></span>
                                <div class="form-floating">
                                    <select class="form-select sync-draft-rfq-changes" id="buyer-branch"
                                        aria-label="Select Branch">
                                        <option readonly selected value="">Select</option>
                                        @foreach ($buyer_branch as $branch)
                                        <option value="{{$branch->branch_id}}">{{$branch->name}}
                                        </option>
                                        @endforeach
                                    </select>
                                    <label>Branch</label>
                                </div>
                            </div>
                        </div>


                        <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="from-date" name="From UO Date"
                                        placeholder="From UO Date" />
                                    <label>From UO Date</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="to-date" name="From UO Date"
                                        placeholder="To UO Date" />
                                    <label>To UO Date</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-ubuntu"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="product_name" name="product_name"
                                        placeholder="" value="" />
                                    <label for="">Product</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-list-ul"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="vendor_name" name="vendor_name"
                                        placeholder="" />
                                    <label for="">Vendor Name</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto mb-3">
                            <div class="d-flex gap-3">
                                <button type="submit" class="ra-btn small-btn ra-btn-primary">
                                    <span class="bi bi-search"></span>
                                    Search
                                </button>
                                <button type="button" class="ra-btn small-btn ra-btn-outline-danger">
                                    <span class="bi bi-arrow-clockwise"></span>
                                    Reset
                                </button>
                                <button id="exportBtn" type="button" class="ra-btn small-btn ra-btn-outline-primary">
                                    <span class="bi bi-download"></span>
                                    Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="table-responsive p-2">
                <table class="product-listing-table w-100">
                    <thead>
                        <tr>
                            <th class="w-300">UNAPPROVED ORDER NO</th>
                            <th class="w-300">BUYER ORDER NUMBER</th>
                            <th class="w-120">RFQ NO</th>
                            <th class="w-300">UNAPPROVED ORDER DATE</th>
                            <th class="w-300">RFQ DATE</th>
                            <th class="w-300">BRANCH/UNIT</th>
                            <th class="w-300">PRODUCT</th>
                            <th class="w-300">USER</th>
                            <th class="w-300">VENDOR</th>
                            <th class="w-300">UNAPPROVED ORDER VALUE</th>
                            <th class="w-300">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!--:- Jay Guru Dev -:-->
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>


@endsection

@section('scripts')
<script src="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
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
</script>
<script>
    $(document).ready(function () {
        let table = $('.product-listing-table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            paging: true,
            scrollY: 450,
            scrollX: true,
            pageLength: 25,
            lengthChange: false,
            destroy: true,
            ajax: {
                url: "{{ route('buyer.unapproved-orders.list') }}",
                data: function (d) {
                    d.po_number = $('#po_number').val();
                    d.rfq_no = $('input[name="rfq_no"]').val();
                    d.division = $('#division').val();
                    d.category = $('#category').val();
                    d.branch = $('#buyer-branch').val();
                    d.from_date = $('#from-date').val();
                    d.to_date = $('#to-date').val();
                    d.product_name = $('input[name="product_name"]').val();
                    d.vendor_name = $('input[name="vendor_name"]').val();
                }
            },
            columns: [
                { data: 'unapproved_order_no', orderable: false, searchable: false },
                { data: 'buyer_order_number', orderable: false, searchable: false },
                { data: 'rfq_no', orderable: false, searchable: false },
                { data: 'uo_date', orderable: false, searchable: false },
                { data: 'rfq_date', orderable: false, searchable: false },
                { data: 'branch', orderable: false, searchable: false },
                { data: 'product', orderable: false, searchable: false },
                { data: 'buyer', orderable: false, searchable: false },
                { data: 'vendor' , orderable: false, searchable: false},
                { data: 'order_value', orderable: false, searchable: false },
                { data: 'status', orderable: false, searchable: false }
            ]
        });

        // Search button
        $('.ra-btn-primary').on('click', function(e){
            e.preventDefault();
            table.ajax.reload();
        });

        // Reset button
        $('.ra-btn-outline-danger').on('click', function(){
            $('.rfq-filter-button').find("input").val("");
            $('.rfq-filter-button').find("select").val("0");
            table.ajax.reload();
        });
    });




    $('#exportBtn').on('click', function() {
        /***:- Collect search filter values  -:***/
        let filters = {
                po_number: $('#po_number').val(),
                rfq_no: $('input[name="rfq_no"]').val(),
                division: $('#division').val(),
                category: $('#category').val(),
                branch: $('#buyer-branch').val(),
                from_date: $('#from-date').val(),
                to_date: $('#to-date').val(),
                product_name: $('input[name="product_name"]').val(),
                vendor_name: $('input[name="vendor_name"]').val()
            };

        /***:- Create a hidden form and submit  -:***/
        let form = $('<form>', {
            action: '{{ route("buyer.unapproved-orders.exportPOData") }}',
            method: 'POST'
        });

        form.append('@csrf');

        /***:- filters and append  -:***/
        $.each(filters, function(key, value) {
            form.append($('<input>', {
                type: 'hidden',
                name: key,
                value: value
            }));
        });

        $('body').append(form);
        form.submit();
    });
</script>
@endsection