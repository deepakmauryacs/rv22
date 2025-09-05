<!-- Issue Modal -->
<div id="IssueModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="addeditIssueModalLabel">
                    <span class="bi bi-pencil" aria-hidden="true"></span> Add Issue
                </h2>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addIssueForm">
                    @csrf
                    <input type="hidden" id="issue_inventory_id" name="inventory_id">
                    <div class="table-responsive">
                        <table class="product-listing-table w-100 text-center">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Product Specification</th>
                                    <th>Product Size</th>
                                    <th>Product UOM</th>
                                    <th>Max Quantity</th>
                                    <th>Remarks</th>
                                    <th>Issued To</th>
                                    <th>Issued Quantity <span class="text-danger">*</span></th>
                                    <th>Issued From <span class="text-danger">*</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="issue_product_name"></td>
                                    <td id="issue_specification"></td>
                                    <td id="issue_size"></td>
                                    <td id="issue_uom"></td>
                                    <td id="issue_max_quantity"></td>
                                    <td>
                                        <input type="text" class="form-control bg-white w-250 specialCharacterAllowed"
                                            name="remarks" id="issue_remarks" maxlength="155">
                                    </td>
                                    <td>
                                        <select class="form-select w-150" id="issue_issuedTo" name="issued_to"></select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control bg-white smt_numeric_only" name="qty" id="qty" maxlength="22">
                                        <input type="hidden" name="stock" id="IssueStock">
                                    </td>
                                    <td>
                                        <select class="form-select w-150" id="issue_from" name="issued_return_for"></select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        <button type="submit" class="ra-btn btn-primary ra-btn-primary save_issue_button text-uppercase text-nowrap font-size-11">
                            <span class="bi bi-save font-size-11" aria-hidden="true"></span> Save Issued
                        </button>
                        
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Script -->
<script>
    $('#qty').on('input paste keyup', function () {
        let val = this.value;

        if (!/^\d*\.?\d{0,2}$/.test(val)) {
            val = val.slice(0, -1);
        }

        const maxQty = parseFloat($('#IssueStock').val()) || 0;

        if (parseFloat(val) > maxQty) {
            val = maxQty.toString();
        }

        this.value = val;
    });

    let isSubmitting = false;

    $('#addIssueForm').off('submit').on('submit', function (e) {
        e.preventDefault();

        if (isSubmitting) return;
        isSubmitting = true;

        const $submitBtn = $('.save_issue_button');
        $submitBtn.prop('disabled', true);

        let hasError = false;
        const inventory_id = $('#issue_inventory_id').val();
        const qty = $('#qty').val();

        if (!inventory_id) {
            toastr.error("Valid Inventory!");
            hasError = true;
        }

        if (!qty) {
            toastr.error("Quantity is required!");
            $("#qty").focus();
            hasError = true;
        } else if (isNaN(qty) || parseFloat(qty) < 0.01) {
            toastr.error("Please enter a valid quantity greater than 0.");
            $("#qty").focus();
            hasError = true;
        }

        let invalidMaxlengthField = null;
        $('#addIssueForm [maxlength]').each(function () {
            const max = parseInt($(this).attr('maxlength'));
            const val = $(this).val();
            if (val.length > max) {
                invalidMaxlengthField = this;
                hasError = true;
            }
        });

        if (invalidMaxlengthField) {
            const fieldName = $(invalidMaxlengthField).attr('name') || 'Field';
            toastr.error(`${fieldName} must not exceed ${$(invalidMaxlengthField).attr('maxlength')} characters.`);
            $(invalidMaxlengthField).focus();
        }

        if (hasError) {
            isSubmitting = false;
            $submitBtn.prop('disabled', false);
            return;
        }

        const formData = $(this).serialize();

        $.ajax({
            url: "{{ route('buyer.issue.store') }}",
            type: "POST",
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                isSubmitting = false;
                $submitBtn.prop('disabled', false);

                if (response.status) {
                    $('#addIssueForm')[0].reset();
                    $('#addIssueForm').find('input[type="hidden"]').val('');
                    $('#IssueModal').modal('hide');
                    toastr.success(response.message);

                    if ($.fn.DataTable.isDataTable('#inventory-table')) {
                        $('#inventory-table').DataTable().destroy();
                    }
                    inventory_list_data();
                } else {
                    toastr.error("Failed to add Issue!");
                }
            },
            error: function (xhr) {
                isSubmitting = false;
                $submitBtn.prop('disabled', false);

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        if (errors.hasOwnProperty(field)) {
                            toastr.error(errors[field][0]);
                        }
                    }
                } else if (xhr.status === 400 && xhr.responseJSON?.message) {
                    toastr.error(xhr.responseJSON.message);
                } else if (xhr.responseText) {
                    toastr.error(xhr.responseText);
                } else {
                    toastr.error("Something went wrong!");
                }
            }
        });
    });
</script>
