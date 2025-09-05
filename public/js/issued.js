$(document).ready(function() {
    if (typeof $.fn.DataTable !== "function") {
        toastr.error("DataTables is not loaded! Check script order!");
        return;
    }
    report_list_data();
    $('#branch_id, #search_product_name, #search_category_id, #search_buyer_id,#search_issueto_id').on('change keyup', function() {
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
            url: getissuedlistdataUrl,
            data: function (d) {
                d.branch_id                 =   $('#branch_id').val();
                d.search_product_name       =   $('#search_product_name').val();
                d.search_issueto_id         =   $('#search_issueto_id').val();
                d.search_buyer_id           =   $('#search_buyer_id').val();
                d.search_category_id        =   $('#search_category_id').val();
                d.from_date                 =   $('#from_date').val();
                d.to_date                   =   $('#to_date').val();
            },
        },
        columns: [
                { data: 'Select' },
                { data: 'issued_number', name: 'issued_no' },
                { data: 'product', name: 'product' },
                { data: 'division', name: 'division' },
                { data: 'category', name: 'category' },
                { data: 'specification', name: 'specification'},
                { data: 'size', name: 'size' },
                { data: 'inventory_grouping', name: 'inventory_grouping' },
                { data: 'issued_quantity', name: 'issued_quantity' },
                { data: 'uom', name: 'uom' },
                { data: 'amount', name: 'amount' },
                { data: 'added_bY', name: 'updated_by' },
                { data: 'added_date', name: 'added_date' },
                { data: 'remarks', name: 'remarks' },
                { data: 'issued_to', name: 'issued_to' }
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
    let url= getIssuedExportUrl;
    let data= {
            _token                      :   $('meta[name="csrf-token"]').attr("content"),
            branch_id                   :   $('#branch_id').val(),
            search_product_name         :   $('#search_product_name').val(),
            search_issueto_id           :   $('#search_issueto_id').val(),
            search_category_id          :   $('#search_category_id').val(),
            search_buyer_id             :   $('#search_buyer_id').val(),
            search_is_active            :   $('#search_is_active').val(),
            from_date                   :   $('#from_date').val(),
            to_date                     :   $('#to_date').val()
        };
    inventoryFileExport(btn,url,data,deleteExcelUrl);
});
$(document).on('click', '#consume', function(e) {
    e.preventDefault();
    var checked = $('input[name="id[]"]:checked');
    if (checked.length === 0) {
        toastr.error('Please select one issue details to consume.');
        return;
    }
    if (checked.length > 1) {
        toastr.error('Please select only one issue details.');
        return;
    }
    var maxQty = checked.first().data('maxqty');
    var consumeQty = checked.first().data('consumeqty');
    var issuereturnqty = checked.first().data('issuereturnqty');
    var stockqty = checked.first().data('stockqty');
    var id = checked.first().val();

   openConsumeModal(maxQty, consumeQty,issuereturnqty,stockqty, id);
});
function openConsumeModal(maxQty, consumeQty,issuereturnqty,stockqty, id) {
    $('#max_qty').text(maxQty);
    $('#consume_qty').text(consumeQty);
    $('#available_stock_qty').val(stockqty);
    $('#issue_return_qty').text(issuereturnqty);
    $('#issue_id').val(id);
    const action = (stockqty == '0.00') ? 'hide' : 'show';

    $('#consumeTable thead th:nth-child(3)')[action]();

    $('#consumeTable tbody td:nth-child(3)')[action]();

    $('#consumeTable tr').each(function() {
        $(this).find('td:eq(3), th:eq(3)').remove();
    });

    $('#save_consume_button')[action]();
    $('#qty').val(' ');
    $('#IssueConsumeModal').modal('show');
}


$(document).on('click', '.open-consume', function(e) {
    e.preventDefault();
    var maxQty = $(this).data('maxqty');
    var stockqty = $(this).data('stockqty');
    var consumeQty = $(this).data('consumeqty');
    var issuereturnqty = $(this).data('issuereturnqty');
    var id = $(this).data('id');

    openConsumeModal(maxQty, consumeQty,issuereturnqty,stockqty, id);
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
