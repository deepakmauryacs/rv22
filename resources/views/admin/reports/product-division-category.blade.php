@extends('admin.layouts.app_second', ['title' => 'Products for Approval', 'sub_title' => 'Approval List'])
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <a href="{{ route('admin.reports.product-division-category') }}"> Products Division & Category Wise</a>
                </li>
            </ol>
        </nav>
    </div>
</div>
@endsection
@section('content')
<div class="about_page_details">
    <div class="container-fluid">
        <div class="card border-0">
            <div class="card-body">
                <div class="col-md-12 botom-border">
                    <h1>Products Category And Division Wise</h1>
                    <div class="row">
                        <div class="col-md-12">
                            <div id="export-progress">
                                <p>Export Progress: <span id="progress-text">0%</span></p>
                                <div id="progress-bar" style="width: 100%; background: #f3f3f3;">
                                    <div id="progress" style="height: 20px; width: 0%; background: green;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-lg-12 col-md-12">
                            <form id="searchForm" action="{{ route('admin.reports.product-division-category') }}" method="GET">
                                <div class="row gx-3">
                                    <div class="col-12 col-sm-auto mt-3 mt-sm-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="text" name="product_name" id="product_name" class="form-control fillter-form-control w-100" value="{{ request('product_name') }}" placeholder="Product Name">
                                                <label>Product Name</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-auto mt-3 mt-sm-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="date" name="from_date" name="from_date" class="form-control fillter-form-control w-100" value="{{ request('from_date') }}" placeholder="From Date">
                                                <label>From Date</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-auto mt-3 mt-sm-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="date" name="to_date" id="to_date" class="form-control fillter-form-control w-100" value="{{ request('to_date') }}" placeholder="To Date">
                                                <label>To Date</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto mt-3 mt-sm-4">
                                        <button type="submit" class="btn-style btn-style-primary"><i class="bi bi-search d-none d-sm-inline-block"></i> Search</button>
                                    </div>
                                    <div class="col-auto mt-3 mt-sm-4">
                                        <a href="{{ route('admin.reports.product-division-category') }}" class="btn-style btn-style-danger">RESET</a>
                                    </div>
                                    <div class="col-auto mt-3 mt-sm-4">
                                        <button class="btn-style btn-style-white" id="export-btn"><i class="bi bi-download d-none d-sm-inline-block"></i>
                                            EXPORT
                                        </button>
                                    </div>
                                </div>
                                <!-- <ul class="rfq-filter-button">
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="text" name="product_name" id="product_name" class="form-control fillter-form-control" value="{{ request('product_name') }}" placeholder="Product Name">
                                                <label>Product Name</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="date" name="from_date" name="from_date" class="form-control fillter-form-control" value="{{ request('from_date') }}" placeholder="From Date">
                                                <label>From Date</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="date" name="to_date" id="to_date" class="form-control fillter-form-control" value="{{ request('to_date') }}" placeholder="To Date">
                                                <label>To Date</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="notShow_on_mobile">
                                        <button type="submit" class="btn-style btn-style-primary"><i class="bi bi-search"></i> Search</button>
                                    </li>
                                    <li class="notShow_on_mobile">
                                        <a href="{{ route('admin.reports.product-division-category') }}" class="btn-style btn-style-danger">RESET</a>
                                    </li>
                                    <li class="notShow_on_mobile">
                                        <button class="btn-style btn-style-white" id="export-btn"><i class="bi bi-download"></i>
                                            EXPORT
                                        </button>
                                    </li>
                                </ul> -->
                            </form>
                        </div>
                    </div>
                    <div class="product_listing_table_wrap" id="table-container">
                        @include('admin.reports.partials.product-division-category-table', ['results' => $results])
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
    $(document).on('change', '.product-status-toggle', function() {
        const productId = $(this).data('id');
        const isActive = $(this).is(':checked') ? '1' : '2';
        const $checkbox = $(this);

        $.ajax({
            url: "{{ route('admin.products.updateStatus', ['id' => ':id']) }}".replace(':id', productId),
            type: "PUT",
            data: {
                _token: "{{ csrf_token() }}",
                status: isActive
            },
            success: function(response) {
                response.success ? toastr.success(response.message) : toastr.error(response.message);
                if (!response.success) $checkbox.prop('checked', !$checkbox.prop('checked'));
            },
            error: function() {
                toastr.error('An error occurred. Please try again.');
                $checkbox.prop('checked', !$checkbox.prop('checked'));
            }
        });
    });

    $(document).on('click', '.btn-delete-product', function() {
        if (!confirm('Are you sure?')) return;
        const id = $(this).data('id');

        $.ajax({
            url: "{{ url('admin/product-approvals') }}/" + id,
            type: "DELETE",
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                toastr.success(res.message);
                location.reload();
            }
        });
    });
});
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
<script>
    $(document).ready(function () {
        function getParams() {
            return {
                product_name: $('#product_name').val(),
                from_date: $('#from_date').val(),
                to_date: $('#to_date').val(),
                _token: "{{ csrf_token() }}"
            };
        }

        function startExport() {
            $('#export-btn').attr('disabled', true);
            $('#export-progress').show();
            $('#progress-text').text('0%');
            $('.fillter-form-control').attr('readonly', true);

            let workbook = new ExcelJS.Workbook();
            let sheet = workbook.addWorksheet('Sheet1');
            const headers = ["Name Of Product", "Division", "Category", "Master Alias", "Vendor Alias", "No Of Vendor Allocated", "Added Since","Total RFQ Generated","Total Order Confirmed","Product Status"];
            sheet.addRow(headers);
            let sheetRows = 0;
            let sheetCount = 1;
            let lastId = null;
            let chunk = 0;
            let totalChunks = 0;

            $.ajax({
                url: "{{ route('admin.product-division-category.exportTotal') }}",
                type: 'GET',
                data: getParams(),
                dataType: 'json',
                success: function(res){
                    totalChunks = Math.ceil(res.total / 100);
                    fetchChunk();
                },
                error: function(){ showError('Error fetching total count'); }
            });

            function fetchChunk(){
                $.ajax({
                    url: "{{ route('admin.product-division-category.exportBatch') }}",
                    type: 'GET',
                    data: {
                        ...getParams(),
                        limit: 100,
                        last_id: lastId
                    },
                    dataType: 'json',
                    success: function(response){
                        const rows = response.data;
                        if (rows.length){
                            rows.forEach(row => {
                                if (sheetRows >= 200000){
                                    sheetCount++;
                                    sheet = workbook.addWorksheet('Sheet'+sheetCount);
                                    sheet.addRow(headers);
                                    sheetRows = 0;
                                }
                                sheet.addRow(row);
                                sheetRows++;
                            });
                            lastId = response.last_id;
                        }

                        chunk++;
                        const progress = Math.round((chunk/totalChunks)*100);
                        $('#progress').css('width', progress + '%');
                        $('#progress-text').text(progress + '%');

                        if (chunk < totalChunks && rows.length){
                            fetchChunk();
                        } else {
                            finish();
                        }
                    },
                    error: function(){ showError('Error fetching data chunk'); }
                });
            }

            function finish(){
                const now = new Date();
                const date = now.toLocaleDateString('en-GB').replace(/\//g,'-');
                const time = now.toLocaleTimeString('en-GB',{hour12:false}).replace(/:/g,'-');
                const fileName = `Product-division-category-report-${date}-${time}.xlsx`;
                workbook.xlsx.writeBuffer().then(buffer => {
                    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = fileName;
                    link.click();

                    $('#export-progress').hide();
                    $('#progress').css('width','0%');
                    $('.fillter-form-control').attr('readonly', false);
                    $('#export-btn').attr('disabled', false);
                }).catch(() => showError('Error generating file'));
            }

            function showError(message){
                alert(message);
                $('#export-progress').hide();
                $('#progress-text').text('0%');
                $('#progress').css('width','0%');
                $('.fillter-form-control').attr('readonly', false);
                $('#export-btn').attr('disabled', false);
            }
        }

        $('#export-btn').on('click', function(e){
            e.preventDefault();
            startExport();
        });

        $('#export-progress').hide();
    });
</script>
@endsection
