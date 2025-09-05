$(document).ready(function() {
    if (typeof $.fn.DataTable !== "function") {
        toastr.error("DataTables is not loaded! Check script order!");
        return;
    }
    report_list_data();
     $('#branch_id, #search_product_name, #search_category_id, #search_return_type,#search_buyer_id').on('change keyup', function() {
        $('#report-table').DataTable().ajax.reload();
    });
});

function report_list_data() {
    $('#report-table').DataTable({
        processing  : true,
        serverSide  : true,
        searching   : false,
        paging      : true,
        scrollY     : 300,
        scrollX     : true,
        pageLength  : 25,
        ajax: {
            url: getStockReturnlistdataUrl,
            data: function (d) {
                d.branch_id                 =   $('#branch_id').val();
                d.search_product_name       =   $('#search_product_name').val();
                d.search_buyer_id           =   $('#search_buyer_id').val();
                d.search_return_type        =   $('#search_return_type').val();
                d.search_category_id        =   $('#search_category_id').val();
                d.from_date                 =   $('#from_date').val();
                d.to_date                   =   $('#to_date').val();
            },
        },
        columns: [
                { data: 'stock_number', name: 'stock_no' },
                { data: 'product', name: 'product' },
                { data: 'specification', name: 'specification'},
                { data: 'size', name: 'size' },
                { data: 'inventory_grouping', name: 'inventory_grouping' },
                { data: 'stock_return_type', name: 'stock_return_type' },
                { data: 'added_bY', name: 'updated_by' },
                { data: 'added_date', name: 'updated_at' },
                { data: 'quantity', name: 'qty' },
                { data: 'uom', name: 'uom' },
                { data: 'remarks', name: 'remarks' },
            ],
            columnDefs: [
                { "orderable": false, "targets": "_all" }
            ],
            order: [],
            language: {
                        processing: "<div class='spinner-border spinner-border-sm'></div> Loading..."
                    }

    });

}

$(document).on('click', '#export', function() {
    let btn = $(this);
    let url= getStockReturnExportUrl;
    let data= {
            _token                      :   $('meta[name="csrf-token"]').attr("content"),
            branch_id                   :   $('#branch_id').val(),
            search_product_name         :   $('#search_product_name').val(),
            search_category_id          :   $('#search_category_id').val(),
            search_return_type          :   $('#search_return_type').val(),
            search_buyer_id             :   $('#search_buyer_id').val(),
            from_date                   :   $('#from_date').val(),
            to_date                     :   $('#to_date').val()
        };
    inventoryFileExport(btn,url,data,deleteExcelUrl);
});


 // Open Report Modal
 $('#showreportmodal').click(function (e) {
    e.preventDefault();
    $('#reportModal').modal('show');
});
//from date
$('#from_date').datepicker({
    dateFormat: 'dd-mm-yy',
    maxDate: 0, // today
    onSelect: function (selectedDate) {
        // Set min and max date for To Date
        $('#to_date').datepicker('option', 'minDate', selectedDate);
        $('#to_date').datepicker('option', 'maxDate', 0); // today
        $('#to_date').datepicker('enable');
    }
});
// Search button click
$('#searchBtn').on('click', function (e) {
    e.preventDefault();
    const fromDate = $('#from_date').val();
    const toDate = $('#to_date').val();

    if (fromDate && toDate) {
        $('#report-table').DataTable().ajax.reload();
    } else {
        toastr.error("Please select both From Date and To Date before searching.");
    }
});
$('#to_date').datepicker({
    dateFormat: 'dd-mm-yy'
}).datepicker('disable');


$(document).on('click', '.stock-return-details', function () {
    var id = $(this).data('id');

    $.ajax({
        url: fetchStockReturnRowdataurl,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            id: id
        },
        success: function (response) {
            $('#stock_return_id').val(response.id);
            $('#tdProductName').text(response.product_name);
            $('#tdProductSpecification').html(response.specification);
            $('#tdProductSize').html(response.size);
            $('#tdProductUom').text(response.uom);
            $('#tdAddedQuantity').text(response.qty);

            $('#tdremarks').val(response.remarks);
            $('#stock_vendor_name').val(response.stock_vendor_name);
            $('#stock_vehicle_no_lr_no').val(response.stock_vehicle_no_lr_no);
            $('#stock_debit_note_no').val(response.stock_debit_note_no);
            $('#stock_frieght').val(response.stock_frieght);
            $('#stockReturnQtyDetailsModal').modal('show');
        },
        error: function (xhr) {
            let msg = 'Something went wrong. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            toastr.error(msg);
        }
    });
});
