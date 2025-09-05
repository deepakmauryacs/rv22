
<div class="modal fade" id="manualPOModal" tabindex="-1" aria-labelledby="manualPOModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="manualPOModalLabel"><i class="bi bi-pencil" aria-hidden="true"></i> Generate Manual PO</h2>
                <button type="button" class="btn-close font-size-11" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="generate_manual_form">
                     <div class="row search-vendor-name position-relative">
                        <div class="col-12 col-sm-auto">
                            <label for="vendor_name" class="fw-bold">Vendor Name: <sup class="text-danger">*</sup></label>
                        </div>
                        <div class="col-12 col-sm-auto flex-grow-1">
                            <div class="position-relative">
                                <input type="text" class="form-control mb-2" name="vendor_name" id="vendor_name" autocomplete="off" placeholder="Vendor Name" />
                            <input type="hidden" name="vendor_user_id" id="vendor_user_id" />

                            <div class="search_text_box shadow rounded manualPOdropdown-menu show" id="vendorSuggestions" style="display: none;">
                            </div>
                            </div>

                        </div>

                    </div>
                    
                    <div class="table-responsive">
                        <table class="table border mb-4 vendorDetails">
                            <tr id="vendorNameId">
                            </tr>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="product-listing-table w-100" id="forManualPoInventoryDetailsTable">
                            <thead>
                                <tr>
                                    <th class="text-nowrap text-center align-middle">Product Name</th>
                                    <th class="text-nowrap text-center align-middle">Product Specification</th>
                                    <th class="text-nowrap text-center align-middle">Product Size</th>
                                    <th class="text-nowrap text-center align-middle">Product UOM</th>
                                    <th class="text-nowrap text-center align-middle">Quantity <span class="text-danger">*</span></th>
                                    <th class="text-nowrap text-center align-middle">Rate ({{ session('user_currency.symbol', '₹') }}) <span class="text-danger">*</span></th>
                                    <th class="text-nowrap text-center align-middle">GST <span class="text-danger">*</span></th>
                                    <th class="text-nowrap text-center align-middle">Total Amount({{ session('user_currency.symbol', '₹') }})</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="forManualPoInventoryDetailsTableBody">
                                <!-- Dynamic rows will be appended here -->
                            </tbody>

                        </table>
                    </div>
                    <div class="border_hr"></div>
                    <div class="row g-3 pt-5 pt-sm-4 mb-3">
                        <!-- Payment Terms -->
                        <div class="col-12 col-sm-4 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-journal-medical"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="paymentTerms" name="paymentTerms" placeholder="Payment Terms"  maxlength="2000">
                                    <label>Payment Terms <sup class="text-danger">*</sup></label>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Period -->
                        <div class="col-12 col-sm-4 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="deliveryPeriod" name="deliveryPeriod" placeholder="Delivery Period" maxlength="3">
                                    <label>Delivery Period <sup class="text-danger">*</sup></label>
                                </div>
                            </div>
                        </div>

                        <!-- Price Basis -->
                        <div class="col-12 col-sm-4 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-currency-rupee"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="priceBasis"  maxlength="2000" name="priceBasis" placeholder="Price Basis">
                                    <label>Price Basis <sup class="text-danger">*</sup></label>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks and Additional Remarks -->
                        <div class="col-12 col-sm-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-pencil"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control specialCharacterAllowed" placeholder="Remarks" id="remarks" name="remarks" maxlength="3000">
                                    <label>Remarks</label>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Remarks -->
                        <div class="col-12 col-sm-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-pencil"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control specialCharacterAllowed" placeholder="Additional Remarks" id="additionalRemarks" name="additionalRemarks" maxlength="3000">
                                    <label>Additional Remarks</label>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Submit Button -->
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11" id="generate_manual_po_product">
                            <i class="bi bi-save font-size-11"></i> Generate Manual PO
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@push('exJs')
    <script>
        const currencySymbol = "{{ session('user_currency.symbol', '₹') }}";
    </script>
@endpush
