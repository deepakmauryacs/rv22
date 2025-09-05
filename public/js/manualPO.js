$(document).ready(function () {
    $('.manualPO').on('click', function (e) {
        e.preventDefault();
        if (selectedIds.length > 0) {
            $('#generate_manual_form')[0].reset();
            fetchInventoryDetails(selectedIds);
        } else {
            toastr.error('Please select an inventory details.');
            return;
        }
    });

    function fetchInventoryDetails(ids) {
        $.post(manualPOFetchURL, {
            ids: ids,
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function (response) {
            if (response.status === 'error') {
                return toastr.error(response.message);
            }
            $('#vendor_name').val('');
            $('#vendorNameId').nextAll('tr').remove();
            //const table = $('#forManualPoInventoryDetailsTable');
            const table = $('#forManualPoInventoryDetailsTableBody');
            table.find('tr:gt(0)').remove();

            const inventories = response.data.inventories;
            const taxes = response.data.taxes;


            let taxOptions = `<option value="">Select Tax</option>`;
            taxes.forEach(tax => {
                taxOptions += `<option value="${tax.id}" data-tax="${tax.tax}">${tax.tax}%</option>`;
            });
            console.log(inventories);
            inventories.forEach(item => {
                const row = `
                    <tr>
                        <td class="text-center align-middle">${item.product.product_name ?? ''}<input type="hidden"  name="inventory_id[]" value="${item.id}"  /></td>
                        <td class="text-center align-middle">
                            ${item.specification && item.specification.length > 10
                                ? `<span class="d-inline-flex align-items-center">
                                        ${item.specification.substring(0, 10)}...
                                        <i class="bi bi-info-circle-fill" data-bs-toggle="tooltip" title="${item.specification}"></i>
                                </span>`
                                : item.specification ?? ''
                            }
                        </td>

                        <td class="text-center align-middle">${item.size && item.size.length > 10
                            ? `<span class="d-inline-flex align-items-center">
                                    ${item.size.substring(0, 10)}...
                                    <i class="bi bi-info-circle-fill" data-bs-toggle="tooltip" title="${item.size}"></i>
                            </span>`
                            : item.size ?? ''
                        }</td>
                        <td class="text-center align-middle">${item.uom?.uom_name ?? ''}</td>
                        <td class="text-center align-middle"><input type="text" class="form-control bg-white w-150 qty-input" name="qty[]" value="" min="0.01" step="0.01" inputmode="decimal" maxlength="10"/></td>
                        <td class="text-center align-middle"><input type="text" class="form-control bg-white w-150 rate-input" name="rate[]" value="" min="0.01" step="0.01" inputmode="decimal" maxlength="10"/></td>
                        <td class="text-center align-middle">
                            <select class="form-select gst-select form-select-sm w-150" name="gst[]">
                                ${taxOptions}
                            </select>
                        </td>
                        <td class="total-amount text-center align-middle">0.00</td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-danger width-inherit remove-row" title="Remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `;
                table.append(row);
            });

            const totalRow = `
                <tr id="total-row" class="bg-white border border-top">
                    <td colspan="7" class="text-right">
                        <strong>Total Amount (${currencySymbol}):</strong>
                    </td>
                    <td id="grand-total" class="text-center align-middle">0.00</td>
                    <td></td>
                </tr>
            `;
            table.append(totalRow);

            $("#manualPOModal").modal("show");
        }).fail(() => toastr.error('Failed to load inventory details.'));
    }

   // Allow only numbers and one dot (.) in qty and rate inputs
    $(document).on('keypress', '.qty-input, .rate-input', function (e) {
        const charCode = e.which ?? e.keyCode;
        const charStr = String.fromCharCode(charCode);
        const currentVal = $(this).val();

        // Block non-numeric characters except one dot
        if (!/[0-9.]/.test(charStr)) {
            e.preventDefault();
        }

        // Block multiple dots
        if (charStr === '.' && currentVal.includes('.')) {
            e.preventDefault();
        }
    });

    // Block invalid pasted content
    $(document).on('paste', '.qty-input, .rate-input', function (e) {
        const pasteData = e.originalEvent.clipboardData.getData('text');
        if (!/^\d*\.?\d{0,2}$/.test(pasteData)) {
            e.preventDefault();
        }
    });

    // Enforce 2 decimal places on input
    $(document).on('input', '.qty-input, .rate-input', function () {
        let val = $(this).val().replace(/[^0-9.]/g, '');
        const parts = val.split('.');

        if (parts.length > 2) {
            val = parts[0] + '.' + parts[1];
        }

        if (parts[1]?.length > 2) {
            val = parts[0] + '.' + parts[1].substring(0, 2);
        }

        $(this).val(val);
    });

    // Function to recalculate totals
    function recalculateTotals() {
        let grandTotal = 0;

        //$('#forManualPoInventoryDetailsTable tr').each(function () {
        $('#forManualPoInventoryDetailsTableBody tr').each(function () {
            const row = $(this);

            const qty = parseFloat(row.find('.qty-input').val()) || 0;
            const rate = parseFloat(row.find('.rate-input').val()) || 0;

            // Get selected option and extract GST percentage from data-tax
            const selectedGSTOption = row.find('.gst-select option:selected');
            const gst = parseFloat(selectedGSTOption.data('tax'));

            // Validate inputs
            const isValid = qty >= 0.01 && rate >= 0.01 && !isNaN(gst);

            if (isValid) {
                const amount = qty * rate;
                const gstAmount = (amount * gst) / 100;
                const total = amount + gstAmount;

                row.find('.total-amount').text(total.toFixed(2));
                grandTotal += total;
            } else {
                row.find('.total-amount').text('0.00');
            }
        });

        $('#grand-total').text(grandTotal.toFixed(2));
    }

    // On input/change — quantity, rate, or GST select
    $(document).on('input change', '.qty-input, .rate-input, .gst-select', function () {
        recalculateTotals();
    });

    // Remove row when the cross icon is clicked
    $(document).on('click', '.remove-row', function () {
        const row = $(this).closest('tr');
        //const table = $('#forManualPoInventoryDetailsTable');
        const table = $('#forManualPoInventoryDetailsTableBody');

        const dataRows = table.find('tr').filter(function () {
            return $(this).find('td').length > 0 && $(this).attr('id') !== 'total-row';
        });

        if (dataRows.length > 1) {
            row.remove();
            recalculateTotals(); // Recalculate after removal
        } else {
            toastr.error('Product is not removed. For generating Manual PO, at least one product is mandatory!');
        }
    });



    $('#vendor_name').on('keyup', function () {
        //$('#forManualPoInventoryDetailsTable tr').each(function () {
        $('#forManualPoInventoryDetailsTableBody tr').each(function () {
            $(this).find('.qty-input').val('');
            $(this).find('.rate-input').val('');
            $(this).find('.gst-select').val('');
            $(this).find('.total-amount').text('0.00');
        });

        $('#grand-total').text('0.00');
        $('#vendor_user_id').val('');
        $('#vendorNameId').nextAll('tr').remove();


        const input = $(this).val();
        let dropdown = '';

        if (input.length < 3) {
            dropdown = `<span class="manualPOdropdown-item text-danger">Minimum 3 characters required</span>`;
            $('#vendorSuggestions').html(dropdown).show();
            return;
        }

        $.ajax({
            url: searchVendorByVendornameURL,
            method: 'GET',
            data: { q: input },
            success: function (data) {
                if (data.length > 0) {
                    data.forEach(function (user) {
                        dropdown += `<a class="manualPOdropdown-item manualPOdropdown-item-border" href="#" onclick="selectVendor('${user.id}')">${user.name}</a>`;
                    });
                } else {
                    dropdown = `<span class="manualPOdropdown-item text-danger">No vendors found</span>`;
                }

                $('#vendorSuggestions').html(dropdown).show();
            }
        });
    });

    $('#deliveryPeriod').on('keyup paste', function () {
        let input = $(this).val();
        input = input.replace(/\D/g, '');
        if (input.length > 3) {
            input = '999';
        }
        let value = parseInt(input, 10);
        if (isNaN(value) || value < 1) {
            value = '';
        } else if (value > 999) {
            value = 999;
        }
        $(this).val(value);
    });



    $('#generate_manual_po_product').on('click', function (e) {
        e.preventDefault();
        const formData = $('#generate_manual_form').serialize();

        const vendorId = $('#vendor_user_id').val();
        const paymentTerms = $('#paymentTerms').val();
        const priceBasis = $('#priceBasis').val();
        const deliveryPeriod = $('#deliveryPeriod').val();
        const remarks = $('#remarks').val();
        const additionalRemarks = $('#additionalRemarks').val();

        // 1. Validate Vendor
        if (!vendorId) {
            toastr.error('Vendor cannot be empty.');
            return;
        }

        // 2. Validate Quantity (stop on first invalid)
        let qtyError = false;
        $('.qty-input').each(function () {
            const qty = $(this).val()?.trim();
            if (qty === '' || isNaN(qty) || parseFloat(qty) <= 0) {
                toastr.error('Quantity must be greater than 0.');
                qtyError = true;
                return false;
            }
        });
        if (qtyError) return;



        // 3. Validate Rate
        let rateError = false;
        $('.rate-input').each(function () {
            const rate = $(this).val()?.trim();
            if (rate === '' || isNaN(rate) || parseFloat(rate) <= 0) {
                toastr.error('Rate must be greater than 0.');
                rateError = true;
                return false;
            }
        });
        if (rateError) return;

        // 4. Validate GST
        let gstError = false;
        $('.gst-select').each(function () {
            const gst = $(this).val()?.trim();
            if (!gst) {
                toastr.error('GST must be selected.');
                gstError = true;
                return false;
            }
        });
        if (gstError) return;

        // 5. Validate Payment Terms
        if (!paymentTerms) {
            toastr.error('Payment Terms cannot be empty.');
            return;
        }
        if (paymentTerms.length > 2000) {
            toastr.error('Payment Terms cannot exceed 2000 characters.');
            return;
        }

        // 6. Validate Delivery Period
        if (!deliveryPeriod) {
            toastr.error('Delivery Period cannot be empty.');
            return;
        }

        // 7. Validate Price Basis
        if (!priceBasis) {
            toastr.error('Price Basis cannot be empty.');
            return;
        }


        if (priceBasis.length > 2000) {
            toastr.error('Price Basis cannot exceed 2000 characters.');
            return;
        }
        if (remarks.length > 3000) {
            toastr.error('Remarks cannot exceed 3000 characters.');
            return;
        }
        if (additionalRemarks.length > 3000) {
            toastr.error('Additional Remarks cannot exceed 3000 characters.');
            return;
        }

        // All good, proceed to submit
        $('#generate_manual_po_product').prop('disabled', true);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: genarateManualPOURL,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === '1') {
                    toastr.success(response.message);
                    $('#manualPOModal').modal('hide');
                    $('#generate_manual_form')[0].reset();
                    $('.inventory_chkd').prop('checked', false);
                    selectedIds = [];
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    Object.values(errors).forEach(function (messages) {
                        messages.forEach(function (message) {
                            toastr.error(message);
                        });
                    });
                } else {
                    toastr.error('Something went wrong. Please try again.');
                }
            },
            complete: function () {
                $('#generate_manual_po_product').prop('disabled', false);
            }
        });
    });



});

function selectVendor(vendorId) {
    $('#vendor_name').val('Loading...');
    $('#vendorSuggestions').hide();

    $.ajax({
        url: getVendorDetailsByNameURL,
        method: 'GET',
        data: { id: vendorId },
        success: function (vendor) {
            let detailsHtml = `
                <tr>
                    <td class="text-start text-wrap keep-word"><strong>Address</strong></td>
                    <td class="text-start text-wrap keep-word" colspan="3">${vendor.address}</td>
                </tr>
                <tr>
                    <td class="text-start text-wrap keep-word"><strong>Country</strong></td>
                    <td class="text-start text-wrap keep-word">${vendor.country}</td>
                    <td class="text-start text-wrap keep-word"><strong>State</strong></td>
                    <td class="text-start text-wrap keep-word">${vendor.state}</td>
                </tr>
                <tr>
                    <td class="text-start text-wrap keep-word"><strong>State Code</strong></td>
                    <td class="text-start text-wrap keep-word">${vendor.state_code ?? 'N/A'}</td>
                    <td class="text-start text-wrap keep-word"><strong>City</strong></td>
                    <td class="text-start text-wrap keep-word">${vendor.city}</td>
                </tr>
                <tr>
                    <td class="text-start text-wrap keep-word"><strong>Pincode</strong></td>
                    <td class="text-start text-wrap keep-word">${vendor.pincode ?? 'N/A'}</td>
                    <td class="text-start text-wrap keep-word"><strong>GST/TIN No</strong></td>
                    <td class="text-start text-wrap keep-word">${vendor.gstin}</td>
                </tr>
                <tr>
                    <td class="text-start text-wrap keep-word"><strong>Mobile No</strong></td>
                    <td class="text-start text-wrap keep-word">${vendor.country_code ?? ''} ${vendor.mobile ?? 'N/A'}</td>
                    <td class="text-start text-wrap keep-word"><strong>Email Address</strong></td>
                    <td class="text-start text-wrap keep-word">${vendor.email}</td>
                </tr>
            `;

            $('#vendorNameId').siblings('tr').remove();
            $('#vendorNameId').after(detailsHtml);

            // Set values
            $('#vendor_name').val(vendor.name);
            $('#vendor_user_id').val(vendorId);
        },
        error: function () {
            toastr.error("Failed to fetch vendor details.");
        }
    });
}



