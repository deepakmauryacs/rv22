<!---Rfq Modal-->
<div id="RfqModal" class="modal fade" tabindex="-1" role="dialog">  
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="addRfqModalLabel"><i class="bi bi-pencil"></i> Generate RFQ</h2>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addRfqForm">
                    @csrf
                    <div class="table-responsive">
                        <table class="product-listing-table w-100 text-center" id="rfqInventoryTable">
                            <thead>
                                <tr>
                                    <th scope="col">Product Name</th>
                                    <th scope="col">Product Specification</th>
                                    <th scope="col">Product Size</th>
                                    <th scope="col">Product UOM</th>
                                    <th scope="col">Max Quantity</th>
                                    <th scope="col">QTY <span class="text-danger">*</span></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                     <!-- Save Button -->
                      <div class="d-flex justify-content-center mt-3">
                        <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 save_rfq_button"><span class="bi bi-save font-size-11" aria-hidden="true"></span> Generate RFQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--Rfq Modal--->

<!---Active Rfq Details Modal-->
<div id="ActiveRfqDetailsModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="addRfqModalLabel"><i class="bi bi-pencil"></i> Active RFQ Details </h2>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addRfqForm">
                    @csrf
                    <div class="table-responsive">
                        <table class="product-listing-table w-100 text-center" id="rfqdetailsTable">
                            <thead>
                                <tr>
                                    <th scope="col">RFQ No</th>
                                    <th scope="col">RFQ Date</th>
                                    <th scope="col">RFQ Closed</th>
                                    <th scope="col">RFQ Qty</th>
                                    <th scope="col">View</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>                     
                </form>
            </div>
        </div>
    </div>
</div>
<!--Active Rfq Details Modal--->
<script>
    //===save indent data==

    $("#addRfqForm").submit(function (e) {
        e.preventDefault();

        let branch_id = $('#branch_id').val();
        if (!branch_id) {
            toastr.error("Branch Name is required");
            return;
        }

        let allValid = true;

        $('.rfq_qty_input').each(function () {
            let qty = parseFloat($(this).val());
            let max = parseFloat($(this).attr('max'));


            if (isNaN(qty) || qty <= 0) {
                toastr.error("Quantity Is Mandatory Against Each Product");
                $(this).focus();
                allValid = false;
                return false; // stop loop
            }

            if (qty > max) {
                toastr.error(`Please Enter Quantity Less than or Equal To ${max} For This Product`);
                $(this).focus();
                allValid = false;
                return false; // stop loop
            }
        });

        if (!allValid) return;

        let formData = $(this).serialize() + '&branch_id=' + encodeURIComponent(branch_id);

        $('.save_rfq_button').attr('disabled', true);

        $.ajax({
            url: "{{ route('buyer.inventory.generateRFQ') }}", // replace with correct route
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            success: function (response) {
                $('.save_rfq_button').removeAttr('disabled');
                if (response.status) {
                    $('#addRfqForm')[0].reset();
                    $("#RfqModal").modal('hide');
                    // toastr.success(response.message);
                    $('#inventory-table').DataTable().ajax.reload();
                    if (response.url) {
                        window.open(response.url, '_blank');
                    }
                } else {
                    toastr.error(response.message || "Failed to generate RFQ!");
                }
            },
            error: function (xhr) {
                $('.save_rfq_button').removeAttr('disabled');
                const res = xhr.responseJSON;
                if (xhr.status === 422 && res.errors) {
                    $.each(res.errors, function (k, m) {
                        toastr.error(m[0]);
                    });
                } else {
                    toastr.error(res?.message || res?.error || "Something went wrong!");
                }
            }
        });
    });

    //===Save Indent Data==
    $(document).on('input', 'input[name="rfq_qty[]"]', function () {
        let $input = $(this);
        let qty = parseFloat($input.val()) || 0;

        // Find the max quantity from the same row (adjust selector as needed)
        let maxQty = parseFloat($(this).attr('max'))||0;

        if (qty > maxQty) {
            toastr.error(`Please Enter Quantity Less than or Equal To ${maxQty} For This Product`);
            $input.val(''); // reset input or set it to maxQty if you want
        }
    });



</script>
