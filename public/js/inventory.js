$(document).ready(function () {
    function toggleInventoryFields(fields, enabled = true, style = '') {
        fields.forEach(function(id) {
            const $el = $('#' + id);
            $el.prop('disabled', !enabled)
            .prop('readonly', !enabled);
            if (style) {
                $el.attr('style', style);
            } else {
                $el.removeAttr('style');
            }
        });
    }

    const inventoryFields = [
        'inventory_product_name', 'product_specification', 'product_size',
        'opening_stock', 'product_uom', 'stock_price', 'product_brand',
        'buyer_product_name', 'inventory_grouping', 'inventory_type',
        'indent_min_qty'
    ];

    $('#addInventoryBtn').click(function (e) {
        e.preventDefault();
        $('#inventoryModalLabel').html('<i class="bi bi-pencil"></i> Add Inventory');
        $('#inventoryForm')[0].reset();
        $('#inventoryForm').find('input[type="hidden"], select').val('');
        $('#divisionCategory').html('');
        $('#inventoryModal').modal('show');

        toggleInventoryFields(inventoryFields, true);

        if ($('.edit_inventory_button_section .inventory_non_edit_button').length === 0) {
            $('.edit_inventory_button_section').html(`
                <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 save_inventory_button">
                    <i class="bi bi-save font-size-11" aria-hidden="true"></i>Save Inventory
                </button>
            `);
        }
    });

    $('#editInventoryBtn').click(function (e) {
        e.preventDefault();

        toggleInventoryFields(inventoryFields, true);

        if ($('.edit_inventory_button_section .inventory_non_edit_button').length === 0) {
            $('.edit_inventory_button_section').html(`
                <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 save_inventory_button">
                    <i class="bi bi-save font-size-11" aria-hidden="true"></i>Update Inventory
                </button>
            `);
        }

        const checked = $("input[name='inv_checkbox[]']:checked");
        if (checked.length === 1) {

            const id = checked.val();
            let url = editInventoryDetailsUrl.replace('__ID__', id);
            $.get(url, function (response) {
                if (response.success) {
                    let data = response.data;
                    $('#inventoryModalLabel').html('<i class="bi bi-pencil"></i> Edit Inventory');
                    $('.save_inventory_button').html(function (_, html) {
                        return html.replace('Save', 'Update');
                    });

                    $('#inventoryId').val(data.id);
                    $('#divisionCategory').html(`${data.product.division.division_name} -> ${data.product.category.category_name}`);
                    $('#inventory_product_name').val(data.product.product_name);
                    $('#product_id').val(data.product_id);
                    $('#product_specification').val(response.specification);
                    $('#product_size').val(data.size);
                    $('#opening_stock').val(data.opening_stock);
                    $('#product_uom').val(data.uom_id).trigger('change');
                    $('#stock_price').val(data.stock_price);
                    $('#product_brand').val(data.product_brand);
                    $('#buyer_product_name').val(data.buyer_product_name);
                    $('#inventory_grouping').val(data.inventory_grouping);
                    $('#inventory_type').val(data.inventory_type_id).trigger('change');
                    $('#indent_min_qty').val(data.indent_min_qty);

                    if (response.edit_data == '0') {
                        toggleInventoryFields(['inventory_product_name', 'product_specification', 'product_size'], false, 'background-color: #dbeff1 !important; color: #000;');
                    }

                    if (response.non_edit_env == '1') {
                        toggleInventoryFields(inventoryFields, false, 'background-color: #dbeff1 !important; color: #000;');
                        $('.inventory_non_edit_button').remove();
                        $('.edit_inventory_button_section').html('<span class="btn-rfq btn-rfq-danger text-danger">Inventory has issued QTY, so you can’t edit this inventory</span>');
                    }

                    $('#inventoryModal').modal('show');
                } else {
                    toastr.error('No Inventory Found');
                }
            });
        } else {
            toastr.error(checked.length > 1 ? 'You may select only one inventory.' : 'Select at least one inventory');
        }
    });


    // Reset form fields when modal is hidden
    $('#inventoryModal').on('hidden.bs.modal', function () {
        $('#inventoryForm')[0].reset();
        $('#divisionCategory').html('');
        $('#inventory_product_name').val('');
    });

    //====show indent modal===
    window.one_row_show_indent_modal = function () {
        let checkedItems = $("input[name='inv_checkbox[]']:checked");
        
        if (checkedItems.length === 1) {
            let inventoryId = checkedItems.val();
            $.ajax({
                url: getInventoryDetailsUrl,
                type: "POST",
                data: {
                    inventory_id: inventoryId,
                    _token: $('meta[name="csrf-token"]').attr("content") // Secure CSRF token retrieval
                },
                success: function (response) {
                    if (response.status === 1) {
                        var data = response.data;
                        $('#indent_inventory_id').val(inventoryId);
                        $("#indent_product_name").text(data.product?.product_name || '');
                        $("#indent_specification").next('i.bi-info-circle-fill').remove();
                        $("#indent_size").next('i.bi-info-circle-fill').remove();
                        $("#indent_specification").html(
                                data.specification && data.specification.length > 10
                                    ? `
                                        ${data.specification.substring(0, 10)}...
                                        <i class="bi bi-info-circle-fill ms-1" data-bs-toggle="tooltip" title="${data.specification}"></i>
                                    `
                                    : data.specification ?? ''
                            );

                        $("#indent_size").html(
                                data.size && data.size.length > 10
                                    ? `
                                        ${data.size.substring(0, 10)}...
                                        <i class="bi bi-info-circle-fill ms-1" data-bs-toggle="tooltip" title="${data.size}"></i>
                                    `
                                    : data.size ?? ''
                            );
                        $("#indent_uom").html(data.uom?.uom_name || '');

                        $("#indent_remarks").text('');
                        $("#indent_qty").text('');
                        //==show indent modal
                        $('#addeditindentModalLabel').html('<i class="bi bi-pencil"></i> Add Indent');
                        $('.save_indent_button').html(function(_, html) {
                            return html.replace('Update','Save');
                        });
                        $('.delete_indent_button').remove();
                        $("#indentModal").modal("show");
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function () {
                    toastr.error("Something went wrong while fetching inventory details.");
                }
            });

        } else {
            if (checkedItems.length > 1) {
                toastr.error('You may select only one inventory.');
            } else {
                toastr.error('Please select an inventory.');
            }
        }
    };
    window.show_indent_modal = function () {
        let checkedItems = $("input[name='inv_checkbox[]']:checked");

        if (checkedItems.length >= 1) {
            let inventoryIds = [];

            checkedItems.each(function () {
                inventoryIds.push($(this).val());
            });

            $.ajax({
                url: getInventoryDetailsUrl,
                type: "POST",
                data: {
                    inventory_ids: inventoryIds,
                    _token: $('meta[name="csrf-token"]').attr("content")
                },
                traditional: false,
                success: function (response) {
                    if (response.status === 1) {
                        let inventories = response.data;
                        let tbodyHtml = '';

                        inventories.forEach(function (item) {
                            let specificationHtml = item.specification?.length > 10
                                ? `${item.specification.substring(0, 10)}...
                                <i class="bi bi-info-circle-fill ms-1" data-bs-toggle="tooltip" title="${item.specification}"></i>`
                                : item.specification || '';

                            let sizeHtml = item.size?.length > 10
                                ? `${item.size.substring(0, 10)}...
                                <i class="bi bi-info-circle-fill ms-1" data-bs-toggle="tooltip" title="${item.size}"></i>`
                                : item.size || '';

                            tbodyHtml += `
                                <tr>
                                    <input type="hidden" name="inventory_id[]" value="${item.id}">
                                    <td>${item.product?.product_name || ''}</td>
                                    <td>${specificationHtml}</td>
                                    <td>${sizeHtml}</td>
                                    <td>${item.uom?.uom_name || ''}</td>
                                    <td><input type="text" class="form-control bg-white specialCharacterAllowed" name="remarks[]" maxlength="100"></td>
                                    <td><input type="text" class="form-control bg-white smt_numeric_only" name="indent_qty[]" maxlength="10"></td>
                                </tr>
                            `;
                        });

                        $('#indent_tbody').html(tbodyHtml);

                        // Update modal header, button, etc.
                        $('#addeditindentModalLabel').html('<i class="bi bi-pencil"></i> Add Indent');
                        $('.save_indent_button').html(function (_, html) {
                            return html.replace('Update', 'Save');
                        });
                        $('.delete_indent_button').remove();
                        $('#indentModal').modal('show');

                        // Initialize tooltips
                        $('[data-bs-toggle="tooltip"]').tooltip();

                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function () {
                    toastr.error("Something went wrong while fetching inventory details.");
                }
            });
        } else {
            toastr.error('Please select at least one inventory.');
        }
    };

    //====show indent modal===

    //===open indent div==//
    //function open_indent_tds(inventory) {
    $(document).on("click", ".open_indent_tds", function (event) {
        let inventory = $(this).attr("tab-index");
        if (inventory) {
            event.preventDefault();
            $.ajax({
                url: postindentlisturl, // Laravel route
                type: "POST",
                dataType: "json",
                data: {
                    inventory: inventory,
                    _token: $('meta[name="csrf-token"]').attr("content") // Secure CSRF token retrieval
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
                        html += "<th></th>";
                        html += '<th colspan="2" class="text-center">Added Date</th>';
                        html += '<th class="text-center">Added By</th>';
                        html += '<th class="text-center">Updated By</th>';
                        html += '<th colspan="3" class="text-center">Remarks</th>';
                        html += '<th colspan="2" class="text-center">Indent Number</th>';
                        html += '<th class="text-center">Indent Quantity</th>';
                        html += '<th colspan="2" class="text-center">Status</th>';
                        html += "<th></th></tr>";

                        responsedata.forEach((p_data) => {
                            var formattedDate = new Date(p_data.created_at).toLocaleString("en-US", {
                                year: "numeric",
                                month: "long",
                                day: "numeric",
                                hour: "numeric",
                                minute: "numeric",
                                hour12: true,
                            });

                            var final_qty = p_data.indent_qty;
                            var remarks = p_data.remarks ? p_data.remarks : "";

                            html += '<tr class="extra_tr_' + inventory + ' append_tr">';
                            html += "<td></td>";
                            html += "<td colspan='2'>" + formattedDate + "</td>";
                            html += "<td>" + (p_data.created_by || "") + "</td>";
                            html += "<td>" + (p_data.updated_by || "") + "</td>";

                            html += remarks.length > 40
                                ? `<td colspan="3">${remarks.substr(0, 40)}<i class="bi bi-info-circle-fill" title="${remarks}"></i></td>`
                                : `<td colspan="3">${remarks}</td>`;

                            html += "<td colspan='2'>" + p_data.inventory_unique_id + "</td>";

                            if (p_data.is_active == 1) {
                                var iseditindent = 1;
                                if(iseditindent){
                                    html+=`<td><span class="indent_qty_quant show_edit_indent_model" style="cursor:pointer;color:blue;" id="indent_qty_sel_${p_data.id}" data-indent="${p_data.id}">${final_qty}</span></td>`;
                                }
                                else{
                                    html+=`<td><span class="indent_qty_quant" style="cursor:pointer;color:blue;" id="indent_qty_sel_${p_data.id}" data-indent="${p_data.id}">${final_qty}</span></td>`;
                                }
                                html += `<td colspan="2" class="text-center">Approved</td>`;
                            } else {
                                html += `<td><span class="indent_qty_quant show_edit_indent_model" data-indent="${p_data.id}">${final_qty}</span></td>`;
                                html += `<td colspan="2" class="text-center">Unapproved</td>`;
                            }

                            html += "<td></td></tr>";
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

    //===indent modal open in edit mode====//
   
    $(document).on("click", ".show_edit_indent_model", function () {
        const indentId = $(this).data("indent");
        if (!indentId) {
            toastr.error("Invalid Indent ID");
            return;
        }

        $.ajax({
            url: postindentdataurl,
            type: "POST",
            data: {
                indent_id: indentId,
                _token: $('meta[name="csrf-token"]').attr("content")
            },
            success: function (response) {
                if (response.status === 1) {
                    const data = response.data;

                    let specification = data.specification ?? '';
                    let size = data.size ?? '';

                    let specHtml = specification.length > 10
                        ? `${specification.substring(0, 10)}...
                            <i class="bi bi-info-circle-fill ms-1" data-bs-toggle="tooltip" title="${specification}"></i>`
                        : specification;

                    let sizeHtml = size.length > 10
                        ? `${size.substring(0, 10)}...
                            <i class="bi bi-info-circle-fill ms-1" data-bs-toggle="tooltip" title="${size}"></i>`
                        : size;

                    const row = `
                        <tr>
                            <input type="hidden" name="inventory_id" value="${data.inventory_id}">
                            <td id="indent_product_name">${data.product_name}</td>
                            <td id="indent_specification">${specHtml}</td>
                            <td id="indent_size">${sizeHtml}</td>
                            <td id="indent_uom">${data.uom_name}</td>
                            <td><input type="text" class="form-control bg-white specialCharacterAllowed" name="remarks" id="indent_remarks" value="${data.remarks ?? ''}" maxlength="100"></td>
                            <td><input type="text" class="form-control bg-white smt_numeric_only" name="indent_qty" id="indent_qty" value="${data.indent_qty}" maxlength="10"></td>
                        </tr>
                    `;

                    $('#indent_tbody').html(row);
                    $('#indent_id').val(data.id);
                    $('#indent_inventory_id').val(data.inventory_id);
                    $('#addeditindentModalLabel').html('<i class="bi bi-pencil"></i> Edit Indent');
                    $('.save_indent_button').html(function(_, html) {
                        return html.replace('Save', 'Update');
                    });

                    
                    $('.delete_indent_button').remove(); // Always remove first

                    
                    if (data.showDelete) {
                        $('.save_indent_button').after(`
                            <button type="button" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 ms-2 delete_indent_button">
                                <i class="bi bi-trash"></i> Delete Indent
                            </button>
                        `);
                    }
                    
                    $('#indentModal').modal('show');
                    $('[data-bs-toggle="tooltip"]').tooltip();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function () {
                toastr.error("Something went wrong while fetching indent details.");
            }
        });
    });


    //===indent modal in edit mode===//

    // Open Report Modal
    $('#showreportmodal').click(function (e) {
        e.preventDefault();
        $('#reportModal').modal('show');
    });

    // Open issuedto Modal
    $('#issuedtoBtn').click(function (e) {
        e.preventDefault();
        $('.tr_add_more_row').each(function() {
            $(this).remove();
        });
        $('.first_issue_to_name').val('');
        $.get(getissuedtoUrl, function (response) {
            if (response.status && Array.isArray(response.data) && response.data.length > 0) {
                var j = 1;
                var html = '';
                for (var i = 0; i < response.data.length; i++) {
                    var issue_to_data = response.data[i];
                    html +='<tr class="text-center tr_add_more_row" id="IssueToRow_'+j+'">';
                        html+='<input type="hidden" name="issue_to_id['+j+']" value="'+issue_to_data['id']+'">';
                        html+='<td class="text-center issueto_count">'+j+'</td>';
                        html+='<td class="text-center"><span id="smt_noninput_box_itn_'+j+'">'+issue_to_data['name']+'</span><span id="smt_input_box_itn_'+j+'" style="display:none"><input type="text" title="" name="issue_to_name['+j+']"  id="issue_to_name_'+j+'" tab-index="1" class="form-control bg-white desc-details-field issue_to_name remove_first_space" value="'+issue_to_data['name']+'" maxlength="100" style="width:100%"></span></td>';
                        html+='<td class="text-center"><button type="button" role="button" aria-label="Edit Row" class="btn btn-link width-inherit py-0 px-2" onclick="editdbissuetorow('+j+','+issue_to_data['id']+')"><i class="bi bi-pencil-square text-dark" id="edit_issue_to_icon_'+j+'" aria-hidden="true"></i></button><button type="button" role="button" aria-label="Cross Row" class="btn btn-link width-inherit py-0 px-2" id="cross_issue_to_icon_'+j+'" onclick="crossIssuetorow('+j+','+issue_to_data['id']+')"><i class="bi bi-x-circle-fill text-dark" aria-hidden="true"></i></button><button type="button" role="button" aria-label="Delete Row" class="btn btn-link width-inherit py-0 px-2"  onclick="deletedbissuetorow('+j+','+issue_to_data['id']+')"><i class="bi bi-trash3 text-dark" aria-hidden="true"></i></button></td>';
                    html +='</tr>';
                    j++;
                }

            }
            if(html!=''){
                $('.issue_to_details_response').html(html);
                $('#add_more_isssueto_counter').val(j);
                for (var k = 1; k <= response.data.length; k++) {
                    $('#cross_issue_to_icon_' + k).hide();
                }
            }

            $('#issuedtoModal').modal('show');
        });
    });
});
$(document).on('click', '#add_more_issue_to', function(){
    var add_more_isssueto_counter = $('#add_more_isssueto_counter').val();
    let i = 1;
    $('.issueto_count').each(function() {
       i++;
    });
    // add_more_isssueto_counter = i;
    var z = false;
    if(parseInt(i)>50){
        z =true;
    }
    if(!z){
        var html = '';
        html +='<tr class="text-center tr_add_more_row" id="IssueToRow_'+add_more_isssueto_counter+'">';
            html+='<td class="text-center issueto_count"></td>';
            html+='<td class="text-center"><input type="text" title="" name="issue_to_name['+add_more_isssueto_counter+']"  id="issue_to_name_'+add_more_isssueto_counter+'" tab-index="1" class="form-control bg-white desc-details-field issue_to_name remove_first_space" value="" maxlength="100" style="width:100%"></td>';
            html+='<td class="text-center"><button type="button" role="button" aria-label="Delete Row" class="btn btn-link width-inherit py-0 px-2" onclick="deleteissuetorow('+add_more_isssueto_counter+')"><i class="bi bi-trash3 text-dark" aria-hidden="true"></i></button></td>';
        html +='</tr>';
        add_more_isssueto_counter_nxt =parseInt(add_more_isssueto_counter)+1;
        $('#add_more_isssueto_counter').val(add_more_isssueto_counter_nxt);
        $('.issue_to_details_response').append(html);
        let i = 1;
        $('.issueto_count').each(function() {
        $(this).html(i);
        i++;
        });
    }
    else{
        toastr.error('You can add only 50 Issue To Name');
    }
});
function editdbissuetorow(rowIndex) {
    $('#smt_noninput_box_itn_' + rowIndex).hide();
    $('#smt_input_box_itn_' + rowIndex).show();
    $('#edit_issue_to_icon_' + rowIndex).hide();
    $('#cross_issue_to_icon_' + rowIndex).show();
    $('#issue_to_name_' + rowIndex).focus();
}

function crossIssuetorow(rowIndex){
    $('#edit_issue_to_icon_' + rowIndex).show();
    $('#smt_noninput_box_itn_' + rowIndex).show();
    $('#smt_input_box_itn_' + rowIndex).hide();
    $('#cross_issue_to_icon_' + rowIndex).hide();
}

function deleteissuetorow(id){
    if(id){
        if(confirm("Are you sure, you want to delete this row!")){
            $('#IssueToRow_'+id).remove();
            // var add_more_isssueto_counter = $('#add_more_isssueto_counter').val();
            // $('#add_more_isssueto_counter').val(add_more_isssueto_counter-1);

            let i = 1;
            $('.issueto_count').each(function() {
                $(this).html(i);
                i++;
            });
        }
    }
}
$(document).on('click', '#save_issue_to_button', function(event) {
    event.preventDefault();
    var z = false;
    var all_issue_to_name = [];
    $('.issue_to_name').each(function() {
        $(this).css('border', '');
        var nameval = this.value.toLowerCase();
        if(nameval == ""){
            toastr.error('Issue To Name is Required');
            $(this).focus();
            $(this).css('border', '1px solid red');
            z = true;
        }
        if (all_issue_to_name.indexOf(nameval) !== -1) {
            $(this).focus();
            $(this).css('border', '1px solid red');
            toastr.error('Issue To Name Cannot Be Duplicated');
            z = true;
        }
        all_issue_to_name.push(nameval);
    });

    const formData = $('#issue_to_data_form').serialize();

    if(!z){
        $('#save_issue_to_button').prop('disabled', true);
        $.ajax({
            url: saveissuedtoUrl,
            type: "POST",
            dataType: 'json',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.status == '1') {
                    toastr.success(response.msg);
                    $('#save_issue_to_button').prop('disabled', false);
                    $('#issuedtoModal').modal('hide');
                } else {
                    $('#save_issue_to_button').prop('disabled', false);
                    toastr.error(response.msg);
                }
            },
            error: function() {
                $('#save_issue_to_button').prop('disabled', false);
                toastr.error('Something Went Wrong..');
            }
        });
    }
});


function deletedbissuetorow(ids,dbid){
    if(ids){
        if(confirm("Are you sure, you want to delete this Record!")){
            $.ajax({
                url: deleteissuedtoUrl,
                type: "POST",
                dataType: 'json',
                data: {
                    dbid: dbid,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {},
                success: function(response) {
                    if (response.status == '1') {
                        toastr.success(response.msg);
                        $('#IssueToRow_' + ids).remove();
                    } else {
                        toastr.error(response.msg);
                    }
                    let i=1;
                    $('.issueto_count').each(function() {
                        $(this).html(i);
                        i++;
                    });
                },
                error: function() {
                    toastr.error('Something Went Wrong..');
                },
                complete: function() {}
            });
        }
    }
}
