
$(document).ready(function() {
    if (typeof $.fn.DataTable !== "function") {
        toastr.error("DataTables is not loaded! Check script order!");
        return;
    }
    report_list_data();
    $('#branch_id, #search_product_name, #search_category_id, #search_is_active').on('change keyup', function() {
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
            url: indentreportlistdataurl,
            data: function (d) {
                d.branch_id                 =   $('#branch_id').val();
                d.search_product_name       =   $('#search_product_name').val();
                d.search_category_id        =   $('#search_category_id').val();
                d.search_is_active          =   $('#search_is_active').val();
                d.from_date                 =   $('#from_date').val();
                d.to_date                   =   $('#to_date').val();
            },
        },
        columns: [
                { data: 'IndentNumber' },
                { data: 'product', name: 'product' },
                { data: 'specification', name: 'specification' },
                { data: 'size', name: 'size' },
                { data: 'inventory_grouping', name: 'inventory_grouping' },
                { data: 'users', name: 'users' },
                { data: 'indent_qty', name: 'indent_qty' },
                { data: 'uom', name: 'uom' },
                { data: 'remarks', name: 'remarks' },
                { data: 'status', name: 'status' },
                { data: 'updated_at', name: 'updated_at' }
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
    let url= exportreportlistindenturl;
    let data= {
            _token                      :   $('meta[name="csrf-token"]').attr("content"),
            branch_id                   :   $('#branch_id').val(),
            search_product_name         :   $('#search_product_name').val(),
            search_category_id          :   $('#search_category_id').val(),
            search_is_active            :   $('#search_is_active').val(),
            from_date                   :   $('#from_date').val(),
            to_date                     :   $('#to_date').val()
        };
    inventoryFileExport(btn,url,data,deleteExcelUrl);
});

$('#showreportmodal').click(function (e) {
    e.preventDefault();
    $('#reportModal').modal('show');
});


