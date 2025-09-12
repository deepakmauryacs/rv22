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
                        <div id="export-progress" style="display:none;">
                            <p>Export Progress: <span id="progress-text">0%</span></p>
                            <div id="progress-bar" style="width: 100%; background: #f3f3f3;">
                                <div id="progress" style="height: 20px; width: 0%; background: green;"></div>
                            </div>
                        </div>
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
                                    <button type="button" class="ra-btn small-btn ra-btn-outline-info" id="export-btn">
                                        <span class="bi bi-download"></span>
                                        Export
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
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

        $('#export-progress').hide();
        let exporting = false;

        $('#export-btn').on('click', function(e) {
            e.preventDefault();
            if (exporting) return;
            exporting = true;

            const chunkSize = 10000;
            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet('Sheet1');
            worksheet.addRow(["ORDER NO", "BUYER ORDER NUMBER", "RFQ No", "ORDER DATE", "RFQ DATE", "BRANCH/UNIT", "PRODUCT", "USER", "VENDOR", "ORDER VALUE", "STATUS"]);

            $('#export-progress').show();
            $('#progress').css('width', '0%');
            $('#progress-text').text('0%');

            const filters = {
                order_no: $('input[name="order_no"]').val(),
                rfq_no: $('input[name="rfq_no"]').val(),
                division: $('#division').val(),
                category: $('#category').val(),
                branch: $('#branch').val(),
                from_date: $('#from-date').val(),
                to_date: $('#to-date').val(),
                product_name: $('input[name="product_name"]').val(),
                vendor_name: $('input[name="vendor_name"]').val(),
                status: $('#status').val()
            };

            $('#filter-rfq').find('input, select, button').prop('disabled', true);

            function resetExport() {
                $('#export-progress').hide();
                $('#progress').css('width', '0%');
                $('#progress-text').text('0%');
                $('#filter-rfq').find('input, select, button').prop('disabled', false);
                exporting = false;
            }

            $.ajax({
                url: "{{ route('buyer.rfq.order-confirmed.exportTotal') }}",
                method: 'GET',
                data: filters,
                success: function(res) {
                    const total = res.total;
                    if (!total) {
                        alert('No data found');
                        resetExport();
                        return;
                    }

                    let fetched = 0;
                    let lastId = null;

                    const fetchBatch = () => {
                        const params = Object.assign({}, filters, { limit: chunkSize });
                        if (lastId) {
                            params.last_id = lastId;
                        }

                        $.ajax({
                            url: "{{ route('buyer.rfq.order-confirmed.exportBatch') }}",
                            method: 'GET',
                            data: params,
                            success: function(batch) {
                                batch.data.forEach(row => worksheet.addRow(row));
                                fetched += batch.data.length;
                                lastId = batch.last_id;

                                const percent = Math.round((fetched / total) * 100);
                                $('#progress').css('width', percent + '%');
                                $('#progress-text').text(percent + '%');

                                if (fetched < total && batch.data.length > 0) {
                                    fetchBatch();
                                } else {
                                    workbook.xlsx.writeBuffer().then(buffer => {
                                        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                                        const url = URL.createObjectURL(blob);
                                        const a = document.createElement('a');
                                        a.href = url;
                                        a.download = 'order-confirmed_' + Date.now() + '.xlsx';
                                        document.body.appendChild(a);
                                        a.click();
                                        document.body.removeChild(a);
                                        URL.revokeObjectURL(url);
                                    }).catch(() => {
                                        alert('Error generating file');
                                    }).finally(() => {
                                        resetExport();
                                    });
                                }
                            },
                            error: function() {
                                alert('Error fetching data');
                                resetExport();
                            }
                        });
                    };

                    fetchBatch();
                },
                error: function() {
                    alert('Error fetching total count');
                    resetExport();
                }
            });
        });
    });
    </script>
@endsection