<div class="modal fade" id="grnQtyDetailsModal" tabindex="-1" aria-labelledby="grnQtyModalLabel1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="grnQtyModalLabel"><i class="bi bi-pencil"></i> GRN Details</h2>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="table-responsive">
                    <form>
                        <input type="hidden" id="id" name="id">
                        <table class="product-listing-table w-100 text-center" id="orderGrnTableGrnReport">
                            <thead class="table-light">
                                <tr>
                                    <th>Order No</th>
                                    <th>RFQ No</th>
                                    <th>Order Date</th>
                                    <th>Order Qty</th>
                                    <th>Vendor Name</th>
                                    <th>GRN No</th>
                                    <th>GRN Date</th>
                                    <th>GRN Qty</th>
                                    <th>Vendor Invoice No</th>
                                    <th>Bill Date</th>
                                    <th>Transporter Name</th>
                                    <th>Gross Wt (kgs)</th>
                                    <th>Vehicle No/LR No with Date</th>
                                    <th>GST No</th>
                                    <th>Freight/Other Charges</th>
                                    <th>Approved By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="tdOrderNo"></td>
                                    <td id="tdRfqNo"></td>
                                    <td id="tdOrderDate"></td>
                                    <td id="tdOrderQty"></td>
                                    <td id="O_tdVendorName"></td>
                                    <td id="tdGrnNo"></td>
                                    <td id="tdGrnDate"></td>
                                    <td id="O_tdGrnQty"></td>
                                    <td><input type="text" class="form-control bg-white w-180" id="O_invoice_number" name="invoice_number" maxlength="50"></td>
                                    <td><input type="text" class="form-control bill-date bg-white w-180" id="O_bill_date" name="bill_date" maxlength="50"></td>
                                    <td><input type="text" class="form-control bg-white w-180" id="O_transporter_name" name="transporter_name" maxlength="255"></td>
                                    <td><input type="text" class="form-control bg-white w-180" id="O_vehicle_lr_number" name="vehicle_lr_number" maxlength="20"></td>
                                    <td><input type="text" class="form-control bg-white w-180 smt_numeric_only" id="O_gross_weight" name="gross_weight" maxlength="20"></td>
                                    <td><input type="text" class="form-control bg-white w-180 smt_numeric_only" id="O_gst"  name="gst" maxlength="20"></td>
                                    <td><input type="text" class="form-control bg-white w-180 smt_numeric_only" id="O_freight_charges" name="freight_charges" maxlength="20"></td> {{-- pingki add maxlength="20" --}}
                                    <td><input type="text" class="form-control bg-white w-180" id="O_approved_by" name="approved_by" maxlength="255"></td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="product-listing-table w-100 text-center" id="stockReturnGrnTableGrnReport">
                            <thead class="table-light">
                                <tr>
                                    <th>Stock Return No</th>
                                    <th>Stock Return Date</th>
                                    <th>Stock Return Qty</th>
                                    <th>Vendor Name</th>
                                    <th>GRN Qty</th>
                                    <th>Vendor Invoice No</th>
                                    <th>Bill Date</th>
                                    <th>Transporter Name</th>
                                    <th>Gross Wt (kgs)</th>
                                    <th>Vehicle No/LR No with Date</th>
                                    <th>GST No</th>
                                    <th>Freight/Other Charges</th>
                                    <th>Approved By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="tdStockReturnNo"></td>
                                    <td id="tdStockReturnDate"></td>
                                    <td id="tdStockReturnQty"></td>
                                    <td id="s_tdVendorName"></td>
                                    <td id="s_tdGrnQty"></td>
                                    <td><input type="text" class="form-control bg-white w-180" id="s_invoice_number" name="invoice_number" maxlength="50"></td>
                                    <td><input type="text" class="form-control bill-date bg-white w-180" id="s_bill_date" name="bill_date"  maxlength="50"></td>
                                    <td><input type="text" class="form-control bg-white w-180" id="s_transporter_name" name="transporter_name" maxlength="255"></td>
                                    <td><input type="text" class="form-control bg-white w-180" id="s_vehicle_lr_number" name="vehicle_lr_number" maxlength="200"></td>
                                    <td><input type="text" class="form-control bg-white w-180 smt_numeric_only" id="s_gross_weight" name="gross_weight" maxlength="20"></td>
                                    <td><input type="text" class="form-control bg-white w-180 smt_numeric_only" id="s_gst"  name="gst" maxlength="20"></td>
                                    <td><input type="text" class="form-control bg-white w-180 smt_numeric_only" id="s_freight_charges" name="freight_charges" maxlength="20"></td> {{-- pingki add maxlength="20" --}}
                                    <td><input type="text" class="form-control bg-white w-180" id="s_approved_by" name="approved_by" maxlength="255"></td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 edit_grn_button">
                        <span class="bi bi-save font-size-11" aria-hidden="true"></span> Edit GRN
                    </button>
                    &nbsp;&nbsp;
                    <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 download_grn_button">
                        <span class="bi bi-download font-size-11" aria-hidden="true"></span> Download GRN
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('exJs')

    <script src="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>
    <script>
        $(document).on('click', '.download_grn_button', function (e) {
            e.preventDefault();
            let id = $('input[name="id"]').val();

            let url = downloadGrnRowdataurl.replace(':id', id);

            $.ajax({
                url: url,
                type: "GET",
                xhrFields: {
                    responseType: 'blob'   
                },
                success: function (data) {
                    let blob = new Blob([data], { type: "application/pdf" });
                    let link = document.createElement("a");
                    link.href = window.URL.createObjectURL(blob);
                    link.download = `GRN-${id}.pdf`;
                    link.click();
                },
                error: function (xhr, status, error) {
                    console.error("Download failed:", error);
                }
            });
        });
        $(document).on('click', '.edit_grn_button', function (e) {
            e.preventDefault();
            let formData = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: $('input[name="id"]').val(),
                invoice_number: $('#O_invoice_number').is(':visible') ? $('#O_invoice_number').val() : $('#s_invoice_number').val(),
                bill_date: $('#O_bill_date').is(':visible') ? $('#O_bill_date').val() : $('#s_bill_date').val(),
                transporter_name: $('#O_transporter_name').is(':visible') ? $('#O_transporter_name').val() : $('#s_transporter_name').val(),
                vehicle_lr_number: $('#O_vehicle_lr_number').is(':visible') ? $('#O_vehicle_lr_number').val() : $('#s_vehicle_lr_number').val(),
                gross_weight: $('#O_gross_weight').is(':visible') ? $('#O_gross_weight').val() : $('#s_gross_weight').val(),
                gst: $('#O_gst').is(':visible') ? $('#O_gst').val() : $('#s_gst').val(),
                freight_charges: $('#O_freight_charges').is(':visible') ? $('#O_freight_charges').val() : $('#s_freight_charges').val(),
                approved_by: $('#O_approved_by').is(':visible') ? $('#O_approved_by').val() : $('#s_approved_by').val()

            };

            $.ajax({
                url: editGrnRowdataurl,
                type: "POST",
                data: formData,
                success: function (response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        $('#grnQtyDetailsModal').modal('hide');
                        $('#report-table').DataTable().destroy();
                        report_list_data();
                        // optionally refresh table or page
                    } else {
                        toastr.error(response.message || 'Update failed');
                    }
                },
                error: function (xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        let errorMessages = '';

                        for (let key in errors) {
                            if (errors.hasOwnProperty(key)) {
                                errorMessages += errors[key].join('<br>') + '<br>';
                            }
                        }

                        toastr.error(errorMessages || 'Validation error occurred.');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Something went wrong. Please try again.');
                    }
                }

            });
        });            
        $('#grnQtyDetailsModal').on('shown.bs.modal', function () {
            $('.bill-date').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onShow: function(ct) {
                    this.setOptions({
                        maxDate: new Date()   
                    });
                }
            });
        });
    </script>
@endpush
