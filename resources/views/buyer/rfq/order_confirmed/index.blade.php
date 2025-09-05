@extends('buyer.layouts.app', ['title'=>'Order Confirmed'])

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
            <div class="bg-white sent-rfq-page unapproved-orer-listing-page">
                <form id="filter-rfq" action="{{ route('buyer.rfq.order-confirmed') }}" method="GET">
                    <h3 class="card-head-line px-2">Orders Confirmed</h3>
                    <div class="px-2">
                        <div class="row g-3 rfq-filter-button">
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-list-ul"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="order_no" placeholder="" value="" />
                                        <label for="">Order No</label>
                                    </div>
                                </div>
                            </div>
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
                                        <select name="division" id="division" class="form-select cw-200">
                                            <option value="" selected=""> Select </option>
                                            @foreach ($divisions as $item)
                                                <option value="{{ $item->id }}" {{ request('division')==$item->id ? 'selected' : '' }} >{{ $item->division_name }}</option>                                                
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
                                        <select name="category" id="category" class="form-select cw-200">
                                            <option value="" selected=""> Select </option>
                                             @foreach ($unique_category as $item => $ids)
                                                <option value="{{ implode(',', $ids) }}" {{ request('category')==implode(',', $ids) ? 'selected' : '' }}>{{ $item }}</option>                                                
                                            @endforeach
                                        </select>
                                        <label for="">Category</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-signpost"></span></span>
                                    <div class="form-floating">
                                        <select name="branch" id="branch" class="form-select cw-200">
                                            <option value="">Select</option>
                                            @foreach ($branchs as $branch)
                                                <option value="{{ $branch->id }}" >{{ $branch->name }}</option>                                                
                                            @endforeach
                                        </select>
                                        <label for="">Branch</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="from-date" name="from_date" placeholder="" value="" autocomplete="off">
                                        <label for="">From PO Date</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="to-date" name="to_date" placeholder="" value="" autocomplete="off">
                                        <label>To PO Date</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-ubuntu"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="product_name" placeholder="" value="" />
                                        <label for="">Product</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-list-ul"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="vendor_name" placeholder="" value="" />
                                        <label for="">Vendor Name</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-signpost"></span></span>
                                    <div class="form-floating">
                                        <select name="status" id="status" class="form-select cw-200">
                                            <option value="">Select</option>
                                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Confirmed</option>
                                            <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                        <label for="">Status</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-auto mb-3">
                                <div class="d-flex gap-3">
                                    <button type="submit" class="ra-btn small-btn ra-btn-primary">
                                        <span class="bi bi-search"></span>
                                        Search
                                    </button>
                                    <button type="button" onclick="javascript:window.location.href='{{ route('buyer.rfq.order-confirmed') }}'" class="ra-btn small-btn ra-btn-outline-danger">
                                        <span class="bi bi-arrow-clockwise"></span>
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive p-2" id="table-container">
                    @include('buyer.rfq.order_confirmed.partials.table', ['results' => $results])
                </div>
            </div>
        </div>    
    </main>
@endsection

@section('scripts')
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
    }); 
    </script>  
@endsection