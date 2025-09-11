@extends('buyer.layouts.app', ['title'=>'Sent RFQ'])

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
            <div class="bg-white sent-rfq-page">
                <form id="filter-rfq" action="{{ route('buyer.rfq.sent-rfq') }}" method="GET">
                    <h3 class="card-head-line px-2">Sent RFQ</h3>
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
                                        <select class="form-select" id="division" name="division">
                                            <option value="0" selected=""> Select </option>
                                            @foreach ($divisions as $item)
                                                <option value="{{ $item->id }}" {{ request('division')==$item->id ? 'selected' : '' }} >{{ $item->division_name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="">Division</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-signpost"></span></span>
                                    <div class="form-floating">
                                        <select class="form-select" id="category" name="category">
                                            <option value="0" selected=""> Select </option>
                                            @foreach ($unique_category as $item => $ids)
                                                <option value="{{ implode(',', $ids) }}" {{ request('category')==implode(',', $ids) ? 'selected' : '' }}>{{ $item }}</option>
                                            @endforeach
                                        </select>
                                        <label>Category</label>
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
                                    <button type="button" onclick="javascript:window.location.href='{{ route('buyer.rfq.sent-rfq') }}'" class="ra-btn small-btn ra-btn-outline-danger">
                                        <span class="bi bi-arrow-clockwise"></span>
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive p-2" id="table-container">
                    @include('buyer.rfq.sent-rfq.partials.table', ['results' => $results])
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <!-- jQuery UI -->
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

        $(document).on("click", "#reset-filter", function(){
            $(".rfq-filter-button").find("input").val("");
            $(".rfq-filter-button").find("select").val("0");
        });

        $(document).on('click', '.reuse-rfq', function () {

            let _this = this;
            $(_this).addClass('disabled');
            let rfq_id = $(_this).data('rfq-id');

            if(rfq_id == undefined || rfq_id == '') {
                toastr.error("Something went wrong. Please try again.");
                return false;
            }

            $.ajax({
                url: "{{route('buyer.rfq.reuse')}}",
                type: 'POST',
                data: {
                    rfq_id: rfq_id,
                    _token: "{{ csrf_token() }}"
                },
                dataType: 'JSON',
                beforeSend: function () {

                },
                success: function(response) {
                    if(!response.status) {
                        $(_this).removeClass('disabled');
                        toastr.error(response.message);
                    } else if(response.status) {
                        // toastr.success(response.message);
                        setTimeout(
                            function(){
                                window.location.href = response.redirect_url;
                            }, 1000);
                    }
                }
            });

        });
    });
</script>
@endsection
