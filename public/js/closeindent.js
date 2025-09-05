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
            url: closeindentreportlistdataurl,
            data: function (d) {
                d.branch_id                 =   $('#branch_id').val();
                d.search_product_name       =   $('#search_product_name').val();
                d.search_category_id        =   $('#search_category_id').val();
                d.from_date                 =   $('#from_date').val();
                d.to_date                   =   $('#to_date').val();
            },
        },
        columns: [
                { data: 'details' },
                { data: 'product', name: 'product' },
                { data: 'specification', name: 'specification' },
                { data: 'size', name: 'size' },
                { data: 'inventory_grouping', name: 'inventory_grouping' },
                { data: 'users', name: 'users' },
                { data: 'uom', name: 'uom' },
                { data: 'indent_qty', name: 'indent_qty' },
                { data: 'rfq_qty', name: 'rfq_qty' },
                { data: 'order_qty', name: 'order_qty' },
                { data: 'grn_qty', name: 'grn_qty' }
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
    let url= exportreportlistcloseindenturl;
    let data= {
            _token                      :   $('meta[name="csrf-token"]').attr("content"),
            branch_id                   :   $('#branch_id').val(),
            search_product_name         :   $('#search_product_name').val(),
            search_category_id          :   $('#search_category_id').val(),
            from_date                   :   $('#from_date').val(),
            to_date                     :   $('#to_date').val(),
        };
    inventoryFileExport(btn,url,data,deleteExcelUrl);
});
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
// Initialize To Date (disabled until From Date is selected)
$('#to_date').datepicker({
    dateFormat: 'dd-mm-yy'
}).datepicker('disable');

//===open indent div==//

$(document).on("click", ".open_indent_tds", function (event) {
    let inventory = $(this).attr("tab-index");
    if (inventory) {
        event.preventDefault();
        $.ajax({
            url: opentdcloseindenturl,
            type: "POST",
            dataType: "json",
            data: {
                inventory: inventory,
                _token: $('meta[name="csrf-token"]').attr("content")
            },
            beforeSend: function () {

            },
            success: function (response) {
                var html = "";
                $(".extra_tr_" + inventory).remove();
                $("#header_" + inventory).remove();

                if (response.status == 1) {
                    var responsedata = response.resp;
                    $("#minus_" + inventory).css("display", "inline");
                    $("#plus_" + inventory).css("display", "none");

                    html += '<tr id="header_' + inventory + '" class="append_tr">';
                    html += '<th colspan="2" style="text-align: center;">Added Date</th>';
                    html += '<th style="text-align: center;">Added By</th>';
                    html += '<th style="text-align: center;">Updated By</th>';
                    html += '<th colspan="3" style="text-align: center;">Remarks</th>';
                    html += '<th colspan="2" style="text-align: center;">Indent Number</th>';
                    html += '<th colspan="2" style="text-align: center;">Indent Quantity</th>';
                    html += "</tr>";

                    responsedata.forEach((p_data) => {
                        var formattedDate = new Date(p_data.created_at).toLocaleString("en-US", {
                            year: "numeric",
                            month: "long",
                            day: "numeric",
                            hour: "numeric",
                            minute: "numeric",
                            hour12: true,
                        });

                        var final_qty = parseFloat(p_data.indent_qty);
                        var remarks = p_data.remarks ? p_data.remarks : "";

                        html += '<tr class="extra_tr_' + inventory + ' append_tr">';
                        html += "<td colspan='2'>" + formattedDate + "</td>";
                        html += "<td>" + (p_data.created_by || "") + "</td>";
                        html += "<td>" + (p_data.updated_by || "") + "</td>";

                        html += remarks.length > 40
                            ? `<td colspan="3">${remarks.substr(0, 40)}<i class="bi bi-info-circle-fill" title="${remarks}"></i></td>`
                            : `<td colspan="3">${remarks}</td>`;

                        html += "<td colspan='2'>" + p_data.inventory_unique_id + "</td>";
                        html +=`<td colspan="2">${final_qty.toFixed(2)}</td>`;

                        html += "</tr>";
                    });

                    $(".accordion_parent_" + inventory).parent().parent().after(html);
                } else {
                    $("#minus_" + inventory).hide();
                    $("#plus_" + inventory).show();
                    toastr.error(response.message);
                }
            },
            error: function () {
                toastr.error("Something Went Wrong..");
            },
        });
    }
});
//===open indent div==//

//===Close indent div==//
$(document).on("click", ".close_indent_tds", function (event) {
    let inventory = $(this).attr("tab-index");
    if (inventory) {
        event.preventDefault();
        $(".extra_tr_" + inventory).remove();
        $("#header_" + inventory).remove();
        $("#plus_" + inventory).css("display", "inline");
        $("#minus_" + inventory).css("display", "none");
    }
});
//===Close indent div==//
