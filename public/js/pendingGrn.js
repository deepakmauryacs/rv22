$(document).ready(function() {
    if (typeof $.fn.DataTable !== "function") {
        toastr.error("DataTables is not loaded! Check script order!");
        return;
    }
    report_list_data();
    report_list_data();
    $('#branch_id, #search_product_name, #search_category_id').on('change keyup', function() {
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
        destroy: true,
        ajax: {
            url: pendingGrnreportlistdataurl,
            data: function (d) {
                d.branch_id                 =   $('#branch_id').val();
                d.search_product_name       =   $('#search_product_name').val();
                d.search_category_id        =   $('#search_category_id').val();
            },
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                orderable: false,
            },
            { data: 'order_number', name: 'order_number' },
            { data: 'order_date', name: 'order_date' },
            { data: 'product_name', name: 'product_name' },
            { data: 'vendor_name', name: 'vendor_name' },
            { data: 'specification', name: 'specification' },
            { data: 'size', name: 'size' },
            { data: 'inventory_grouping', name: 'inventory_grouping' },
            { data: 'added_by', name: 'added_by' },
            { data: 'added_date', name: 'added_date' },
            { data: 'uom', name: 'uom' },
            { data: 'order_quantity', name: 'order_quantity' },
            { data: 'total_grn_quantity', name: 'total_grn_quantity' },
            { data: 'pending_grn_quantity', name: 'pending_grn_quantity' }
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

$(document).on('click', '#export', function () {
    let btn = $(this);
    let url= exportreportlistPendingGrnurl;
    let data= {
             _token                      :   $('meta[name="csrf-token"]').attr("content"),
            branch_id                   :   $('#branch_id').val(),
            search_product_name         :   $('#search_product_name').val(),
            search_category_id          :   $('#search_category_id').val(),
        };
    inventoryFileExport(btn,url,data,deleteExcelUrl);
});

$('#showreportmodal').click(function (e) {
    e.preventDefault();
    $('#reportModal').modal('show');
});





