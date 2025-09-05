
function grnPopUP(inventoryId) {
    const url = checkGrnEntry.replace('__ID__', inventoryId);
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.has_pending_order) {
                document.getElementById('stockReturnGrnTable').getElementsByTagName('tbody')[0].innerHTML = '';
                document.getElementById('orderGrnTable').getElementsByTagName('tbody')[0].innerHTML = '';
                openGrnModal(data.inventoryId);
                if (data.stockReturn && Array.isArray(data.stock_return_details) && data.stock_return_details.length > 0) {
                     const stockReturnGrnTable = document.getElementById('stockReturnGrnTable');
                    if (stockReturnGrnTable) {
                        stockReturnGrnTable.style.display = 'block';
                    }
                    createStockReturnGrnTable(data.stock_return_details);
                }
                if (data.order && Array.isArray(data.order_details) && data.order_details.length > 0) {
                    const orderGrnTable = document.getElementById('orderGrnTable');
                    if (orderGrnTable) {
                        orderGrnTable.style.display = 'block';
                    }
                    createOrderGrnTable(data.order_details);
                }
                initializeDatepickerForBillDates();
            }else {
                toastr.error('No pending order or stock return details found for this inventory to make a GRN entry.');
            }
        })
        .catch(error => {
            console.error('Error checking GRN entry:', error);
            toastr.error('Something went wrong.');
        });
}
function initializeDatepickerForBillDates() {
    $('.bill-date').datetimepicker({
        format: 'd-m-Y',
        timepicker: false,
        maxDate: 0
    });
}
function createOrderGrnTable(order_details){
    var tbody = $('#grnaddModal #orderGrnTable tbody');
    tbody.empty();
    order_details.forEach(function(order) {
        var maxGrnQty = parseFloat(((parseFloat(order.order_quantity || 0) * 1.02) - parseFloat(order.grn_entered || 0)).toFixed(2));
        const url = order.baseManualPoUrl.replace('__ID__', order.id);
        var row = `<tr>
            <td class="text-center">
                <input type="text" readonly class="form-control bg-white text-center w-180" style="width: fit-content !important;background-color: #dbeff1 !important;" id="po_number" name="po_number[]"value="${order.order_number || ''}">
                <input type="hidden" class="form-control text-center" id="order_id" name="order_id[]" value="${order.id}">
            </td>
            <td class="text-center">
                ${
                    order.order_type !== 'manual_order'
                        ? `<input type="text" readonly class="form-control bg-white text-center w-180" id="rfq_no" style="width: fit-content !important;background-color: #dbeff1 !important;" name="rfq_no[]" value="${order.rfq_number || ''}"><input type="hidden" id="grn_type" name="grn_type[]" value="1">`
                        : `- <input type="hidden" id="grn_type" name="grn_type[]" value="4">`
                }
            </td>

            <td class="text-center"> <input type="text" readonly class="form-control bg-white text-center w-180" style="width: fit-content !important;background-color: #dbeff1 !important;" value="${order.order_date || ''}"></td>
            <td class="text-center"><input type="text" readonly class="form-control bg-white text-center" id="order_qty" style="width: fit-content !important;background-color: #dbeff1 !important;" name="order_qty[]" value="${order.order_quantity || ''}"></td>
            <td class="text-center"><input type="text" readonly name="vendor_name[]" style="width: fit-content !important;background-color: #dbeff1 !important;" class="form-control bg-white text-center w-180" id="vendor_name" value="${order.vendor_name || ''}"</td>
            <td class="text-center"><input type="text" name="grn_entered[]" readonly class="form-control bg-white text-center w-180" style="width: fit-content !important;background-color: #dbeff1 !important;" value="${order.grn_entered || '0'}"></td>
            <td class="text-center">
            <input type="text" readonly class="form-control bg-white text-center w-180 "style="width: fit-content !important;background-color: #dbeff1 !important;" value="${order.ratewithcurrency || ''}">
            <input type="hidden" name="rate[]" value="${order.rate || ''}"></td>
            <td class="text-center">
                ${order.rate_in_local_currency === '1'
                    ? `<input 
                            type="text" 
                            class="form-control bg-white text-center" 
                            id="rate_in_local_currency" 
                            name="rate_in_local_currency[]" 
                            maxlength="7" 
                            value="${order.grn_buyer_rate || ''}" 
                            ${Number(order.grn_buyer_rate) > 0 ? 'readonly' : ''} 
                            style="display: block;">`
                    : `<input 
                            type="hidden" 
                            name="rate_in_local_currency[]" 
                            value="${order.grn_buyer_rate || ''}">-`
                }
            </td>           

            <td class="text-center"><input type="text" id="grn_qty" class="grn_quantity_input form-control bg-white text-center w-180" name="grn_qty[]" data-max="${maxGrnQty}" maxlength="20" min="0" step="any"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-180" id="invoice_number" name="invoice_number[]" maxlength="50"></td>
            <td class="text-center"><input type="text" class="form-control dateTimePickerStart bg-white text-center w-180 bill-date" id="bill_date" name="bill_date[]" maxlength="50" ></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-180" id="transporter_name" name="transporter_name[]" maxlength="255"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-180" id="vehicle_lr_number" name="vehicle_lr_number[]" maxlength="200"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-180 smt_numeric_only" id="gross_weight" name="gross_weight[]" maxlength="20"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-180 smt_numeric_only" id="gst" name="gst[]" maxlength="20"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-180 smt_numeric_only" id="freight_charges" name="freight_charges[]" maxlength="20" ></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-180" id="approved_by" name="approved_by[]" maxlength="255"></td>
            <td class="text-center">
                <span data-id="${order.id}">
                <a href="${url}" target="_blank" rel="noopener noreferrer">View ${order.order_type === 'manual_order' ? 'Manual PO' : 'PO'}</a>
                </span>
            </td>

        </tr>`;
        tbody.append(row);
    });
}
function createStockReturnGrnTable(stock_return_details){
    var tbody = $('#grnaddModal #stockReturnGrnTable tbody');
    tbody.empty();
    stock_return_details.forEach(function(stock) {
        var maxGrnQty = parseFloat((parseFloat(stock.qty|| 0)  - parseFloat(stock.grn_entered || 0)).toFixed(2));
        var row = `<tr>
            <td class="text-center">
                <input type="text" readonly class="form-control bg-white text-center w-100" style="width: fit-content !important;background-color: #dbeff1 !important;" id="stock_no" name="stock_no[]"value="${stock.stock_no || ''}">
                <input type="hidden" name="stock_return_id[]" value="${stock.stock_return_id}">
                <input type="hidden" name="stock_return_for[]" value="${stock.stock_return_for}">
                ${
                    stock.order_type == 'stock_return'
                        ?`<input type="hidden" name="stock_return_grn_type[]" value="3">`:``
                }
            </td>
            <td class="text-center"> <input type="text" readonly class="form-control bg-white text-center w-180" style="width: fit-content !important;background-color: #dbeff1 !important;" value="${stock.updated_at || ''}"></td>

            <td class="text-center"><input type="text" readonly class="form-control bg-white text-center w-180" id="order_qty" style="width: fit-content !important;background-color: #dbeff1 !important;" value="${stock.remarks || ''}"></td>

            <td class="text-center"><input type="text" readonly class="form-control bg-white text-center w-180" id="order_qty" style="width: fit-content !important;background-color: #dbeff1 !important;" name="stock_return_qty[]" value="${stock.qty || ''}"></td>

            <td class="text-center"><input type="text" readonly name="stock_vendor_name[]" style="width: fit-content !important;background-color: #dbeff1 !important;" class="form-control bg-white text-center w-180" id="stock_vendor_name" value="${stock.stock_vendor_name || ''}"</td>

            <td class="text-center"><input type="text" name="stock_return_grn_entered[]" readonly class="form-control bg-white text-center w-180" style="width: fit-content !important;background-color: #dbeff1 !important;" value="${stock.grn_entered || '0'}"></td>

            <td class="text-center"><input type="text" id="grn_qty" class="grn_quantity_input form-control bg-white text-center w-180"  name="grn_stock_return_qty[]" data-max="${maxGrnQty}" maxlength="20" min="0" step="any"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-180" id="invoice_number" name="stock_invoice_number[]" maxlength="50"></td>
            <td class="text-center"><input type="text" class="form-control dateTimePickerStart bg-white text-center w-180 bill-date" id="bill_date" name="stock_bill_date[]" maxlength="50" ></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-180" id="transporter_name" name="stock_transporter_name[]" maxlength="255"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-180" id="vehicle_lr_number" name="stock_vehicle_lr_number[]" maxlength="200"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-180 smt_numeric_only" id="gross_weight" name="stock_gross_weight[]" maxlength="20"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-180 smt_numeric_only" id="gst" name="stock_gst[]" maxlength="20"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-180 smt_numeric_only" id="freight_charges" name="stock_freight_charges[]" maxlength="20" ></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-180" id="approved_by" name="stock_approved_by[]" maxlength="255"></td>

        </tr>`;
        tbody.append(row);
    });
}
function openGrnModal(inventoryId){
    $('#grnaddModal').modal('show');
    $('#inventory_id').val(inventoryId);
}
     


