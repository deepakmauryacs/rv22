<!---indent Modal-->
<div id="indentModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="addeditindentModalLabel">Add Indent</h2>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addIndentForm">
                    @csrf
                    <input type="hidden" id="indent_inventory_id" name="inventory_id">
                    <input type="hidden" id="indent_id" name="indent_id">
                    <div class="table-responsive">
                        <table class="product-listing-table w-100 text-center">
                            <thead>
                                <tr>
                                    <th scope="col">Product Name</th>
                                    <th scope="col">Product Specification</th>
                                    <th scope="col">Product Size</th>
                                    <th scope="col">Product UOM</th>
                                    <th scope="col">Remarks</th>
                                    <th scope="col">QTY <span class="text-danger">*</span></th>
                                </tr>
                            </thead>
                            <tbody id ="indent_tbody">
                                <tr>
                                    <!-- Autocomplete Product Name -->
                                    <td id="indent_product_name"></td>
                                    <td id="indent_specification"></td>
                                    <td id="indent_size"></td>
                                    <td id="indent_uom"></td>
                                    <td><input type="text" class="form-control bg-white specialCharacterAllowed" name="remarks" id="indent_remarks" maxlength="100"></td>
                                    <td><input type="text" class="form-control bg-white smt_numeric_only" name="indent_qty" id="indent_qty" maxlength="10"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                     <!-- Save Button -->
                      <div class="d-flex justify-content-center mt-3">
                        <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 save_indent_button"><span class="bi bi-save font-size-11" aria-hidden="true"></span> Save Indent</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--indent Modal--->
<script>
    //===save indent data==  
    let isSaveIndentSubmitting = false; 
    $("#addIndentForm").off('submit').on('submit', function (e) {
        e.preventDefault();

        if (isSaveIndentSubmitting) return;

        const $form = $(this);
        const $saveButton = $('.save_indent_button');
        let hasError = false;

        const branchId = $('#branch_id').val();
        
        
        if (!branchId) {
            toastr.error("Branch Name is required");
            hasError = true;
        }

        const inventoryIds = $form.find('input[name="inventory_id[]"]').map(function () {
            return $(this).val();
        }).get();

        const indentQtyArr = $form.find('input[name="indent_qty[]"]').map(function () {
            return $(this).val();
        }).get();
        let minIndentQtyArr = [];

        const indentQtyField = $form.find('input[name="indent_qty"]');
        const minIndentQtyField = $form.find('input[name="min_indent_qty"]');

        if (indentQtyField.length > 0 && minIndentQtyField.length > 0) {
            const indentQty = parseFloat(parseFloat(indentQtyField.val()).toFixed(2));
            const minQty = parseFloat(parseFloat(minIndentQtyField.val()).toFixed(2));

            if (!isNaN(indentQty) && !isNaN(minQty) && indentQty < minQty) {
                toastr.error(`Indent quantity cannot be less than existing RFQ quantity (${minQty.toFixed(2)})`);
                hasError = true;
            }
        }

        for (let i = 0; i < inventoryIds.length; i++) {
            if (!inventoryIds[i]) {
                toastr.error(`Row ${i + 1}: Valid Inventory is required`);
                hasError = true;
            }

            const qty = parseFloat(parseFloat(indentQtyArr[i]).toFixed(2));
           
            if (!indentQtyArr[i]) {
                toastr.error(`Row ${i + 1}: QTY is required`);
                hasError = true;
            } else if (isNaN(qty) || qty < 0.01) {
                toastr.error(`Row ${i + 1}: QTY must be a number and at least 0.01`);
                hasError = true;
            }
        }

        $form.find('[maxlength]').each(function () {
            const max = parseInt($(this).attr('maxlength'));
            const val = $(this).val() || '';
            if (val.length > max) {
                const fieldName = $(this).attr('name') || 'Field';
                toastr.error(fieldName + ` must not exceed ${max} characters.`);
                hasError = true;
                return false;
            }
        });

        if (hasError) {
            isSaveIndentSubmitting = false;
            return;
        }

        isSaveIndentSubmitting = true;
        $saveButton.prop('disabled', true);

        let formData = $form.serialize();
        formData += '&buyer_branch_id=' + encodeURIComponent(branchId);

        $.ajax({
            url: "{{ route('buyer.indent.store') }}",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: formData,
            success: function (response) {
                isSaveIndentSubmitting = false;
                $saveButton.prop('disabled', false);
                if (response.status) {
                    $form[0].reset();
                    $form.find('input[type="hidden"]').val('');
                    $('#indentModal').modal('hide');
                    toastr.success(response.message);
                    if (inventoryTable) {
                            inventoryTable.ajax.reload();
                        }
                } else {
                    toastr.error(response.message || "Failed to add indent!");
                }
            },
            error: function (xhr) {
                isSaveIndentSubmitting = false;
                $saveButton.prop('disabled', false);
                const res = xhr.responseJSON || {};
                if (xhr.status === 422 && res.errors) {
                    let first = true;
                    $.each(res.errors, function (key, messages) {
                        toastr.error(messages[0]);
                        if (first) {
                            $('[name="' + key + '"]').focus();
                            first = false;
                        }
                    });
                } else {
                    toastr.error(res.message || "Something went wrong!");
                }
            }
        });
    });
    //===Save Indent Data==

    $(document).on('click', '.delete_indent_button', function () {
    const indentId = $('#indent_id').val();
    const inventoryId = $('#indent_inventory_id').val();

    if (!indentId || !inventoryId) {
        toastr.error("Invalid indent or inventory ID.");
        return;
    }

    if (confirm("Are you sure you want to delete this indent?")) {
        $('.delete_indent_button').attr('disabled', 'disabled');
        $.ajax({
            url: '{{ route("buyer.indent.delete", ":id") }}'.replace(':id', indentId),
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                indent_inventory_id: inventoryId
            },
            success: function (response) {
                if (response.status) {
                    $('#addIndentForm')[0].reset();
                    $('#addIndentForm').find('input[type="hidden"]').val('');
                    $('#indentModal').modal('hide');

                    toastr.success(response.message || 'Indent deleted successfully.');
                    $('.delete_indent_button').removeAttr('disabled');
                    if (inventoryTable) {
                            inventoryTable.ajax.reload();
                        }
                } else {
                    $('.delete_indent_button').removeAttr('disabled');
                    toastr.error(response.message || 'Failed to delete indent.');
                }
            },
            error: function () {
                $('.delete_indent_button').removeAttr('disabled');
                toastr.error('Server error. Please try again.');
            }
        });

    }
});


</script>
