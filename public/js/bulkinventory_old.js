$(document).ready(function () {
    // upload csv==
    $(document).on('change', '#import_product', function() {
        $('#AjaxLoader').css('display', 'flex');
        setTimeout(
            function()
            {
                $('#import_product_btn_smt').trigger('click');
            }, 1000);
    });
    $("#form").validate({
        submitHandler: function(form, event) {
            if (event) event.preventDefault();

            let form_data = new FormData(form);
            form_data.append('_token', csrf_token_js);
            // Reset invalid section
            $(".invalid-product-row").html('');
            $(".invalid-product-section").hide();

            $.ajax({
                url: bulkinventoryuploadCSVurl,
                type: "POST",
                dataType: 'json',
                data: form_data,
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function() {
                    $('#import_product_btn_smt').attr('disabled', true);
                    $('#AjaxLoader').show(); // Show loader
                },
                success: function(response) {
                    if (response.status == 1) {
                        // 👇 Delay loader hide + toast until rendering is done
                        showimportdata(response.data, response.uom_list, response.invt_type_list, function() {
                            $('#AjaxLoader').hide();
                            $('#import_product').val('');
                            $('#import_product_btn_smt').attr('disabled', false);
                            toastr.success(response.message);
                        });
                    } else {
                        $('#AjaxLoader').hide();
                        $('#import_product').val('');
                        $('#import_product_btn_smt').attr('disabled', false);
                        toastr.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Something went wrong.');
                    $('#AjaxLoader').hide();
                    $('#import_product_btn_smt').attr('disabled', false);
                    console.error('AJAX Error:', status, error);
                }
            });
        }
    });

    function showimportdata(responsedata, uom_list, invt_type_list, callback)
    {
        const dataBody = document.createDocumentFragment();
        let cnts = 1;

        const template = document.getElementById('product-row-template');
        if (!template) {
            console.error('Template #product-row-template not found!');
            return;
        }

        responsedata.forEach((p_data, i) => {
            if (!p_data.Product_Name) return; // Skip empty product names

            // Clone the template row
            const row = template.content.cloneNode(true);
            const j = i + 1;

            const row_color = (!p_data.action || /^(New Product|Product Verified)$/i.test(p_data.action)) ? 'text-success' : 'text-danger';
            const status = row_color === 'text-success' ? 1 : 2;

            // Fill in cells
            row.querySelector(".sr-no").textContent = cnts;

            row.querySelector(".message-cell").innerHTML = `<span style="vertical-align: bottom;" class="product-message ${row_color}" id="product_message_${j}">${p_data.action || 'Product verified'}</span>`;

            row.querySelector(".action-cell").innerHTML = `<span class="remove-rfq-record btn-rfq" data-srno="${p_data.srno}"><i class="bi bi-trash3 text-danger remove-bulk-rfq-btn"></i></span>`;

            // Product name input group
            const productNameDiv = document.createElement("div");
            productNameDiv.innerHTML = `
                <span id="division_${j}">${p_data.divname ? p_data.divname + ' > ' : ''}</span>
                <span id="category_${j}">${p_data.catname || ''}</span>
                <div class="bulk-rfq-product-row">
                    <input type="text" title="${p_data.Product_Name}" readonly name="product_name[${j}]" id="product_name_${j}" tab-index="${j}" class="form-control readonly-text-field bulk-product-field bulk_product_fields" value="${p_data.Product_Name}" autocomplete="off">
                    <div class="custom_scroll showSearchDivs" id="showSearchDiv_${j}">
                        <div class="related_search"><div id="suggestions_${j}"><div class="keyword_suggestion"></div></div></div>
                        <ul class="suggestProduct" id="suggestProduct_${j}"></ul>
                    </div>
                    <input type="hidden" name="product_ids[${j}]" id="product_ids_${j}" value="${p_data.Product_id || ''}">
                    <input type="hidden" name="status[${j}]" id="status_${j}" value="${status}">
                    <input type="hidden" name="status2[${j}]" id="status2_${j}" value="${status}">
                    <input type="hidden" name="division_id[${j}]" id="division_id_${j}" value="${p_data.divid || ''}">
                    <input type="hidden" name="category_id[${j}]" id="category_id_${j}" value="${p_data.catid || ''}">
                    <input type="hidden" name="product_row_id[${j}]" id="product_row_id_${j}" value="${p_data.srno || ''}">
                </div>`;
            row.querySelector(".product-name-cell").appendChild(productNameDiv);

            // Helper to create input
            const createInput = (name, id, value, title, classes) => {
                const input = document.createElement("input");
                input.name = `${name}[${j}]`;
                input.id = `${id}_${j}`;
                input.type = "text";
                input.title = title;
                input.readOnly = true;
                input.value = truncate(value || '', 100); // Make sure value is string
                input.className = `form-control readonly-text-field ${classes}`;
                input.setAttribute("tab-index", j);
                return input;
            };

            row.querySelector(".spec-cell").appendChild(createInput("ps_spec_", "ps_spec", p_data.Product_Specification, p_data.Product_Specification, "desc-details-field"));
            row.querySelector(".size-cell").appendChild(createInput("ps_size_", "ps_size", p_data.Product_Size, p_data.Product_Size, "desc-details-field"));
            row.querySelector(".stock-cell").appendChild(createInput("ps_opening_stock_", "ps_opening_stock", p_data.Opening_Stock, p_data.Opening_Stock, "smt_numeric_only opening-stock-details-field"));

            // UOM dropdown
            const uomSelect = document.createElement("select");
            uomSelect.name = `uom_list[${j}]`;
            uomSelect.id = `uom_list_${j}`;
            uomSelect.className = "form-control import_drop_down_sel";
            uomSelect.tabIndex = j;
            uomSelect.innerHTML = generateOptions(uom_list, p_data.uom);
            row.querySelector(".uom-cell").appendChild(uomSelect);

            row.querySelector(".price-cell").appendChild(createInput("ps_stock_price_", "ps_stock_price", p_data.Stock_Price, p_data.Stock_Price, "smt_numeric_only stock-price-details-field"));
            row.querySelector(".brand-cell").appendChild(createInput("ps_brand_", "ps_brand", p_data.Brand, p_data.Brand, "brand-details-field"));
            row.querySelector(".our-product-name-cell").appendChild(createInput("ps_buyer_product_name_", "ps_buyer_product_name", p_data.Our_Product_Name, p_data.Our_Product_Name, "brand-details-field"));
            row.querySelector(".inventory-group-cell").appendChild(createInput("ps_Inventory_Grouping_", "ps_Inventory_Grouping", p_data.Inventory_Grouping, p_data.Inventory_Grouping, "inventory-grouping-details-field"));

            // Inventory type dropdown
            const invtSelect = document.createElement("select");
            invtSelect.name = `invt_list[${j}]`;
            invtSelect.id = `invt_list_${j}`;
            invtSelect.className = "form-control";
            invtSelect.innerHTML = generateOptions(invt_type_list, p_data.Inventory_Type);
            row.querySelector(".inventory-type-cell").appendChild(invtSelect);

            row.querySelector(".min-qty-cell").appendChild(createInput("ps_Set_Min_Qty_for_Indent_", "ps_Set_Min_Qty_for_Indent", p_data.Set_Min_Qty_for_Indent, p_data.Set_Min_Qty_for_Indent, "smt_numeric_only Set_Min_Qty_for_Indent"));

            dataBody.appendChild(row);
            cnts++;
        });

        // Get table body
        const tableBody = document.querySelector("#example tbody.invalid-product-row");
        if (!tableBody) {
            console.error("Table body '#example tbody.invalid-product-row' not found!");
            return;
        }

        $(".invalid-product-section").removeClass('d-none');
        $(".invalid-product-section").css('display','inline');
        // Clear previous rows and append new ones
        tableBody.innerHTML = "";
        tableBody.appendChild(dataBody);

        // Re-initialize DataTable
        if ($.fn.dataTable.isDataTable('#example')) {
            $('#example').DataTable().clear().destroy();
        }

       const table =  $('#example').DataTable({
            paging: false,
            scrollY: 500,
            scrollX: true,
            scrollCollapse: true,
            searching: false,
            autoWidth: false,

        });
        table.columns.adjust();
        function updateVisibleInfo() {
            const container = $('#example_wrapper .dataTables_scrollBody')[0];
            const scrollTop = container.scrollTop;
            const containerHeight = container.clientHeight;

            const $rows = $('#example tbody tr');
            const total = table.rows({ filter: 'applied' }).data().length;

            let start = -1;
            let end = -1;

            $rows.each(function(index) {
                const rect = this.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();

                if (rect.bottom > containerRect.top && rect.top < containerRect.bottom) {
                    if (start === -1) start = index + 1; // 1-based index
                    end = index + 1;
                }
            });

            if (start !== -1 && end !== -1) {
                $("#table-info").text(`Showing ${start} to ${end} of ${total} entries`);
            } else {
                $("#table-info").text("No visible entries");
            }
        }


        // Scroll event ke throttle korar jonno:
        function throttle(fn, wait) {
            let last = 0;
            return function() {
                const now = Date.now();
                if (now - last > wait) {
                    last = now;
                    fn();
                }
            };
        }

        const throttledUpdate = throttle(updateVisibleInfo, 100);

        setTimeout(updateVisibleInfo, 200);
        $('#example_wrapper .dataTables_scrollBody').on('scroll', throttledUpdate);



        if (typeof callback === "function") {
            callback();
        }

        hideLoader();
    }

    function truncate(text, max = 100) {
        return text && text.length > max ? text.substring(0, max) : (text || '');
    }

    // Utility: generate dropdown options
    function generateOptions(list, selectedValue) {
        let options = `<option value="" ${!selectedValue ? 'selected' : ''}>Select</option>`;
        options += list.map(item =>
            `<option value="${item.id}" ${item.id === selectedValue ? 'selected' : ''}>${item.name}</option>`
        ).join('');
        return options;
    }


    function hideLoader(){
        //$(".invalid-product-section").removeClass('d-none');
        $('#AjaxLoader').hide();
    }
    $(document).on('click', '.remove-rfq-record', function() {
        const srno = $(this).data('srno');
        remove_import_row(this, srno);
    });
    function remove_import_row(row,srno){
        if(row){
            let table = $('#example').DataTable(); // Get the DataTable instance
            let rowCount = table.rows().count();
            if(Number(rowCount)>1){
                $.ajax({
                    url: bulkInventorydeleteRowurl,
                    type: "POST",
                    dataType: 'json',
                    data: {
                        srno: srno, // Pass the row ID
                        _token: csrf_token_js
                    },
                    beforeSend: function() {},
                    success: function(response) {
                        if (response.success) {
                            table.row($(row).closest('tr')).remove().draw();
                            toastr.success('Row is deleted');//pingki
                        } else {
                            toastr.error('Row not deleted');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Something went wrong!');
                    },
                    complete: function() {}
                });
            }

        }
    }
    $(document).on("click", ".bulk-product-field", function(){
        $(".suggest-str-p").html('').addClass("d-none");
        $(this).removeAttr('readonly');
    });
    $(document).on("click", ".desc-details-field", function(){
        $(".suggest-str-p").html('').addClass("d-none");
        $(this).removeAttr('readonly');
    });
    $(document).on("blur", ".desc-details-field", function(){
        var tab_index   =   $(this).attr('tab-index');
        update_row_data(tab_index);
    });
    $(document).on("click", ".opening-stock-details-field", function(){
        $(".suggest-str-p").html('').addClass("d-none");
        $(this).removeAttr('readonly');
    });
    $(document).on("keyup", ".opening-stock-details-field", function(){
        var tab_index       =   $(this).attr('tab-index');
        verifyproductlst(tab_index);
    });
    $(document).on('change','.import_drop_down_sel', function(){
        var tab_index       =   $(this).attr('tab-index');
        verifyproductlst(tab_index);
    })
    $(document).on("click", ".stock-price-details-field", function(){
        $(".suggest-str-p").html('').addClass("d-none");
        $(this).removeAttr('readonly');
    });
    $(document).on("keyup", ".stock-price-details-field", function(){
        var tab_index       =   $(this).attr('tab-index');
        verifyproductlst(tab_index);
    });
    $(document).on("click", ".brand-details-field", function(){
        $(".suggest-str-p").html('').addClass("d-none");
        $(this).removeAttr('readonly');
    });
    $(document).on("blur", ".brand-details-field", function(){
        var tab_index   =   $(this).attr('tab-index');
        update_row_data(tab_index);
    });
    $(document).on("click", ".inventory-grouping-details-field", function(){
        $(".suggest-str-p").html('').addClass("d-none");
        $(this).removeAttr('readonly');
    });
    $(document).on("blur", ".inventory-grouping-details-field", function(){
        var tab_index   =   $(this).attr('tab-index');
        update_row_data(tab_index);
    });
    $(document).on('change','.import_drop_down_inventory_type', function(){
        var tab_index       =   $(this).attr('tab-index');
        verifyproductlst(tab_index);
    })
    $(document).on("click", ".Set_Min_Qty_for_Indent", function(){
        $(".suggest-str-p").html('').addClass("d-none");
        $(this).removeAttr('readonly');
    });
    $(document).on("blur", ".Set_Min_Qty_for_Indent", function(){
        var tab_index   =   $(this).attr('tab-index');
        update_row_data(tab_index);
    });

     function verifyproductlst(tab_index){
        if(tab_index){
            var division_id     =   $('#division_id_'+tab_index).val();
            var category_id     =   $('#category_id_'+tab_index).val();
            var product_name    =   $('#product_name'+tab_index).val();
            var ps_desc         =   $('#ps_spec_'+tab_index).val();
            var size            =   $('#ps_size_'+tab_index).val();
            var opening_stock   =   $('#ps_opening_stock_'+tab_index).val();
            var stock_price     =   $('#ps_stock_price_'+tab_index).val();
            var uom_list        =   $('#uom_list_'+tab_index).val();

            var invt_type_list  =  $('#invt_list_'+tab_index).val();
            var status          =   $('#status_'+tab_index).val();
            // console.log(opening_stock);
            //console.log(invt_type_list);
            if((Number(stock_price) && Number(stock_price) >=0)  && (Number(opening_stock) && Number(opening_stock)>=0)  && uom_list !='' && division_id!='' && division_id!='undefined' && division_id!='null' && category_id!='' && category_id!='undefined' && category_id!='null'){
                $('#product_message_'+tab_index).removeClass('text-danger');
                $('#product_message_'+tab_index).addClass('text-success');
                $('#prows_'+tab_index).removeClass('text-danger');
                $('#prows_'+tab_index).addClass('text-success');
                $('#product_message_'+tab_index).html('Product Verified');
                $('#status_'+tab_index).val('1');
            }
            else if((Number(stock_price) && Number(stock_price) ==0)  && (Number(opening_stock) && Number(opening_stock)==0)  && uom_list !='' && division_id!='' && division_id!='undefined' && division_id!='null' && category_id!='' && category_id!='undefined' && category_id!='null'){
                $('#product_message_'+tab_index).removeClass('text-danger');
                $('#product_message_'+tab_index).addClass('text-success');
                $('#prows_'+tab_index).removeClass('text-danger');
                $('#prows_'+tab_index).addClass('text-success');
                $('#product_message_'+tab_index).html('Product Verified');
                $('#status_'+tab_index).val('1');
            }
            else{
                var error_msg='';
                if(division_id=='' || category_id==""){
                    // if(status!='2'){
                        error_msg='Invalid Product ';
                    // }

                }
                if(product_name==''){
                    if(error_msg==''){
                        error_msg='Invalid Product Name';
                    }
                    else{
                        error_msg +=', </br> Invalid Product Name';
                    }
                }

                if (stock_price === '' || opening_stock === '') {
                    if(error_msg==''){
                        error_msg='Invalid opening stock or Stock price';
                    }
                    else{
                        error_msg +=', </br> Invalid opening stock or Stock price';
                    }
                }
                if(uom_list==''){
                    if(error_msg==''){
                        error_msg='Invalid UOM';
                    }
                    else{
                        error_msg +=', </br> Invalid UOM';
                    }
                }

                if (opening_stock !== '' && Number(opening_stock) > 0 && (stock_price === '' || Number(stock_price) <= 0)) {
                    if (error_msg === '') {
                        error_msg = 'Stock Price Required';
                    } else {
                        error_msg += ', </br> Stock Price Required';
                    }
                }

                if (stock_price !== '' && Number(stock_price) > 0 && (opening_stock === '' || Number(opening_stock) <= 0)) {
                    if (error_msg === '') {
                        error_msg = 'Opening Stock Required';
                    } else {
                        error_msg += ', </br> Opening Stock Required';
                    }
                }
                if(error_msg==''){
                    $('#product_message_'+tab_index).removeClass('text-danger');
                    $('#product_message_'+tab_index).addClass('text-success');
                    $('#prows_'+tab_index).removeClass('text-danger');
                    $('#prows_'+tab_index).addClass('text-success');

                     if(uom_list !='' && division_id!='' && division_id!='undefined' && division_id!='null' && category_id!='' && category_id!='undefined' && category_id!='null' && product_name!='' && product_name!='undefined' && product_name!='null'){
                        $('#product_message_'+tab_index).removeClass('text-danger');
                        $('#product_message_'+tab_index).addClass('text-success');

                        $('#prows_'+tab_index).removeClass('text-danger');
                        $('#prows_'+tab_index).addClass('text-success');
                        $('#product_message_'+tab_index).html('Product Verified');
                        $('#status_'+tab_index).val('1');
                    }
                }
                else{

                    $('#product_message_'+tab_index).addClass('text-danger');
                    $('#product_message_'+tab_index).removeClass('text-success');
                    $('#product_message_'+tab_index).removeClass('text-success');
                    $('#prows_'+tab_index).addClass('text-danger');
                    $('#prows_'+tab_index).removeClass('text-success');
                    var error ='<span style="color:red;font-weight: bold;">Error :</span></br>';
                    if((error_msg=='Product Verified' || error_msg=='Product verified' )){
                        error ="";
                    }
                    var error_msgs = error + error_msg;
                    $('#status_'+tab_index).val('2');
                    $('#product_message_'+tab_index).html(error_msgs);
                }
            }
            update_row_data(tab_index);
        }
    }

    //=====update_row_data====//
    function update_row_data(tab_index){
        var divid                   =   $('#division_id_'+tab_index).val();
        var catid                   =   $('#category_id_'+tab_index).val();
        var Product_Name            =   $('#product_name_'+tab_index).val();
        var Product_id              =   $('#product_ids_'+tab_index).val();
        var Product_Specification   =   $('#ps_spec_'+tab_index).val();
        var Product_Size            =   $('#ps_size_'+tab_index).val();
        var Opening_Stock           =   $('#ps_opening_stock_'+tab_index).val();
        var Product_UOM             =   $('#uom_list_'+tab_index).val();
        var Stock_Price             =   $('#ps_stock_price_'+tab_index).val();
        var Brand                   =   $('#ps_brand_'+tab_index).val();
        var Our_Product_Name        =   $('#ps_buyer_product_name_'+tab_index).val();
        var Inventory_Grouping      =   $('#ps_Inventory_Grouping_'+tab_index).val();
        var Inventory_Type          =   $('#invt_list_'+tab_index).val();
        var Set_Min_Qty_for_Indent  =   $('#ps_Set_Min_Qty_for_Indent_'+tab_index).val();
        var status                  =   $('#status_'+tab_index).val();
        var srno                    =   $('#product_row_id_'+tab_index).val();
        var action                  =   $('#product_message_'+tab_index).html();
        if((Number(Stock_Price) && Number(Stock_Price) >=0)  && (Number(Opening_Stock) && Number(Opening_Stock)>=0)  && Product_UOM !='' && divid!='' && divid!='undefined' && divid!='null' && catid!='' && catid!='undefined' && catid!='null'){
            var is_verify          =    1;
        }
        else if((Number(Stock_Price))  && (Number(Opening_Stock))  && Product_UOM !='' && divid!='' && divid!='undefined' && divid!='null' && catid!='' && catid!='undefined' && catid!='null'){
            var is_verify          =    1;
        }
        else if(action=='Product Verified'){
            var is_verify          =    1;
        }
        else{
            var is_verify          =    2;
        }
        $.ajax({
            url: bulkinventoryupdateRowurl,
            type: "POST",
            dataType: 'json',
            data: {
                Product_Name: Product_Name,
                Product_Specification: Product_Specification,
                Product_Size: Product_Size,
                Opening_Stock: Opening_Stock,
                Product_UOM: Product_UOM,
                Stock_Price: Stock_Price,
                Brand: Brand,
                Our_Product_Name: Our_Product_Name,
                Inventory_Grouping: Inventory_Grouping,
                Inventory_Type: Inventory_Type,
                Set_Min_Qty_for_Indent: Set_Min_Qty_for_Indent,
                Product_id: Product_id,
                catid: catid,
                divid: divid,
                srno: srno,
                action: action,
                is_verify: is_verify,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });

    }
    //=====update_row_data====//
    //===bulk-product-field===//

    $(document).on("input", ".bulk-product-field", function(){
        var searchfrom      =   '';
        var input_data      =   $(this).val();
        var tab_index       =   $(this).attr('tab-index');

        $('#division_'+tab_index).val('');
        $('#division_id_'+tab_index).val('');
        $('#product_ids_'+tab_index).val('');
        $('#category_'+tab_index).val('');
        $('#category_id_'+tab_index).val('');
        //====extra code==//
        $("#division_"+tab_index).html('');
        $("#category_"+tab_index).html('');
        $("#division_id_"+tab_index).val('');
        $("#category_id_"+tab_index).val('');
        $("#status_"+tab_index).val('3');
        verifyproductlst(tab_index);
        $('.showSearchDivs').hide();
        $('#showSearchDiv_'+tab_index).show();
        //====extra code==//
        var search_type     =   'existing';
        var page            =   1;
        if (input_data.length == 0) {
            $('#suggestions_'+tab_index).hide();
            $('#showSearchDiv_'+tab_index).hide();
        } else if (input_data.length < 3) {

            $('#showSearchDiv_'+tab_index).show();
            $(".related_search").show();
            $('#suggestions_'+tab_index).show();
            $('#suggestProduct_'+tab_index).html('');
            $('#search-loader').hide();
            $('.keyword_suggestion').html('<font style="color:#6aa510;">Please enter more than 3 characters.</font>');
        } else {
            // if(search_request){
            //     search_request.abort();
            // }

            searchfrom = 'seachdata';
            $(".related_search").show();
            $('.keyword_suggestion').html('Showing result for <b>' + ucwords(input_data) + '</b>');
            $('#search-loader').show();
            $('#suggestionslist').show();
            $.ajax({
                url: searchallproducturl,
                type: "POST",
                dataType: "json",
                data: {
                    search_data: input_data,
                    search_type:search_type,
                    page_no:1,
                    _token: $('meta[name="csrf-token"]').attr("content")
                },
                success: function(html) {
                    $('.add-new-pdt-btn').addClass('d-none');
                    var jObj = (html);
                    var lp = '';

                    if (jObj.search_result == '') {
                        $('.searchDatafound').val(2);
                    } else {
                        $('.searchDatafound').val(1);
                    }

                    if (jObj.status == "pass") {
                        var allproducts     = jObj.data;
                        search_pro_list     = jObj;
                        var totalRecords    = jObj.totalRecords;
                        var divisions_array = jObj.divisions;
                        var category_array  = jObj.category;

                        for (let i = 0; i < allproducts.length; i++) {
                            let cat_id = allproducts[i].cat_id,
                                div_id = allproducts[i].div_id,
                                division_name = allproducts[i].div_name,
                                category_name = allproducts[i].cat_name;

                            if (division_name && category_name) {
                                lp += '<li style="padding:10px 10px 0 10px;">';
                                lp += '<a href="javascript:void(0)" class="fill-product-data search_text" tab-index="' + tab_index + '" data-pid="' + allproducts[i].prod_id + '"  data-div_id="' + div_id + '" data-div-name="' + division_name + '" data-cat-id="' + cat_id + '" data-cat-name="' + category_name + '">';
                                lp += '<p class="search_text1" style="margin-bottom: 0rem;">' + division_name + ' &gt; ' + category_name + '</p>';
                                lp += '<p class="search_text" style="font-size: 16px;">' + allproducts[i].prod_name + '</p>';
                                lp += '<p></p>';
                                lp += '</a>';
                                lp += '</li>';
                            }
                        }

                        if (searchfrom == 'seachdata') {
                            $('.keyword_suggestion').html('Showing result for <b>"' + ucwords(input_data) + '"</b> ' + totalRecords + ' records found');
                            if (page == 1) {
                                $('#suggestProduct_' + tab_index).html('');
                                $('#suggestProduct_' + tab_index).append(lp);
                            } else {
                                $('#suggestProduct_' + tab_index).append(lp);
                            }

                            if (lp == '') {
                                $('#suggestProduct_' + tab_index).hide();
                                $('#suggestions_' + tab_index).hide();
                                $('#showSearchDiv_' + tab_index).hide();
                            } else {
                                $('#suggestProduct_' + tab_index).show();
                                $('#suggestions_' + tab_index).show();
                                $('#showSearchDiv_' + tab_index).show();
                            }
                            $('#search-loader').hide();
                        }
                    }
                    else if (jObj.status == "exist") {
                        alert('Product "' + ucwords(input_data) + '" is already added in your list');
                        resetProductFields(tab_index);
                    }
                    else if (jObj.status == "nodata") {
                        $('.keyword_suggestion').html('No Product found for <b>"' + ucwords(input_data) + '"</b> keyword');
                        $('#suggestions_' + tab_index).show();
                        $('#suggestProduct_' + tab_index).html('').hide();
                        $('#suggestionslist').hide();
                        $('#search-loader').hide();
                        resetProductFields(tab_index);
                    }
                    else if (jObj.status == "found") {
                        toastr.error('Product Already Exist');
                        resetProductFields(tab_index);
                    }
                    else if (jObj.status == "fail" || jObj.status == "error") {
                        $('#search-loader').hide();
                        $('#suggestions_' + tab_index).show();
                        $('#suggestionslist').html('<div><ul><li></li><li><span style="color:#fc151b;text-align:center">Please enter valid string</span></li></ul>');
                        $('#suggestionslist').show();
                        resetProductFields(tab_index);
                    } else {
                        $('.keyword_suggestion2').html('');
                        $('#search-loader').hide();
                    }
                }
            });
        }
    });
    //===bulk-product-field===//
    function ucwords(str, force) {
        str = force ? str.toLowerCase() : str;
        return str.replace(/(\b)([a-zA-Z])/g,
            function(firstLetter) {
                return firstLetter.toUpperCase();
            });
    }
    $(document).on('click', '.fill-product-data', function() {
        var tab_index       =   $(this).attr('tab-index');
        $('#showSearchDiv_'+tab_index).hide();
        var allproducts     =   search_pro_list.data,
        pro_id              =   $(this).data('pid');
        var data_div_id     =   $(this).attr('data-div_id');
        var data_div_name   =   $(this).attr('data-div-name');
        var data_cat_id   =   $(this).attr('data-cat-id');
        var data_cat_name   =   $(this).attr('data-cat-name');
        var all_pro_ids = [];
        $('.product_ids_data_lst').each(function(){
            all_pro_ids.push(parseInt($(this).val()));
        });
        //alert(all_pro_ids);
        //alert(pro_id);
        for (i = 0; i < allproducts.length; i++) {
            if (allproducts[i].prod_id == pro_id) {
                if (all_pro_ids.indexOf(parseInt(pro_id)) === -1) {
                    $("#product_name_"+tab_index).val(allproducts[i].prod_name);
                    $("#division_"+tab_index).html(data_div_name+' > ');
                    $("#category_"+tab_index).html(data_cat_name);
                    $("#division_id_"+tab_index).val(data_div_id);
                    $("#category_id_"+tab_index).val(data_cat_id);
                    $("#status_"+tab_index).val('1');
                    $('#product_ids_'+tab_index).val(pro_id);
                    verifyproductlst(tab_index);
                }

            }
        }

    });
    $(document).on('click','#upload_bulk_product',function(){
        $("#upload_bulk_product").addClass("disabled");
        $("#upload_bulk_product").prop("disabled",true);
        $('#AjaxLoader').css('display', 'flex');
        $.ajax({
            url: bulkinventorycheckurl,
            type: "POST",
            dataType: 'json',
            data: {
                _token          :   $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {},
            success: function(responce) {
                if(responce==1){
                    update_inventory_data();
                }
                else{
                    $('#AjaxLoader').hide();
                    $("#outer-preloader").html('');
                    $("#upload_bulk_product").removeClass("disabled");
                    $("#upload_bulk_product").prop("disabled",false);
                    toastr.error('Atleast one product should be verified');
                }
            },
            error: function() {
                $('#AjaxLoader').hide();
                $("#outer-preloader").html('');
                $("#upload_bulk_product").removeClass("disabled");
                $("#upload_bulk_product").prop("disabled",false);
                toastr.error('Something Went Wrong..');
            },
            complete: function() {}
        });
    });

    function update_inventory_data(){
        var buyer_branch = $('#branch_id').val();
        $.ajax({
            url: bulkinventoryupdateProductsurl,
            type: "POST",
            dataType: 'json',
            data: {
                buyer_branch    :   buyer_branch,
                _token          :   $('meta[name="csrf-token"]').attr('content')
                },
            beforeSend: function() {},
            success: function(responce) {
                $('#AjaxLoader').hide();
                $("#outer-preloader").html('');
                $("#upload_bulk_product").removeClass("disabled");
                $("#upload_bulk_product").prop("disabled",false);
                if(responce.status==0){
                    toastr.error(esponce.msg);
                }
                else{
                    $("#outer-preloader").html('');
                    var duplicate = responce.duplicate;
                    var continues = true;
                    $.each(duplicate, function(key, value) {
                        continues = false;
                        $('#product_message_'+key).removeClass('text-succes');
                        $('#product_message_'+key).removeClass('text-success');
                        $('#product_message_'+key).removeClass('text-danger');
                        $('#product_message_'+key).addClass('text-danger');
                        $('#product_message_'+key).html('Already Exists');
                    });
                    toastr.error(responce.message);
                    if(continues){
                        if (responce.status == 1) {
                            $(".invalid-product-row").html('');
                            $(".invalid-product-section").addClass('d-none');
                            toastr.success(responce.message);
                            var delay = 1000;
                            setTimeout(
                                function(){
                                    window.location = inventoryindexsurl;
                                }, delay);

                        } else {
                            $("#upload_bulk_product").removeClass("disabled");
                            toastr.error(responce.message);
                        }
                    }
                }
            },
            error: function() {
                $("#outer-preloader").html('');
                $("#upload_bulk_product").removeClass("disabled");
                $("#upload_bulk_product").prop("disabled",false);
                toastr.error('Something Went Wrong..');
            },
            complete: function() {}
        });
    }


    function validateProductFile(filename,filcounts){
        if(filename && filcounts){
            var ext = filename.split(".");
            ext = ext[ext.length-1].toLowerCase();
            var arrayExtensions = ["jpg" , "jpeg", "png", "bmp", "gif"];

            if (arrayExtensions.lastIndexOf(ext) == -1) {
                alert("Wrong extension type.");
                $("#Product_image_"+filcounts).val("");
            }
        }
    }
    $(document).on("paste", ".bulk-product-field", function(){
        toastr.error('Paste not allowed.');
        return false;
    });

});
