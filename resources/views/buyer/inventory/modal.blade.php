<!---Inventory Modal-->
<div class="modal fade" id="inventoryModal" tabindex="-1" aria-labelledby="inventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="inventoryModalLabel">Add Inventory</h2>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="inventoryForm">
                    @csrf
                    <input type="hidden" name="id" id="inventoryId">

                    <!-- Responsive Table -->
                    <div class="table-responsive">
                        <table class="product-listing-table w-100 text-center">
                            <thead>
                                <tr>
                                    <th scope="col">Product Name <span class="text-danger">*</span></th>
                                    <th scope="col">Product Specification</th>
                                    <th scope="col">Product Size</th>
                                    <th scope="col">Opening Stock <span class="text-danger">*</span></th>
                                    <th scope="col">Product UOM <span class="text-danger">*</span></th>
                                    <th scope="col">Rate<span class="text-danger">*</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <!-- Autocomplete Product Name -->
                                    <td>
                                        <span id="divisionCategory" class="text-start"></span>
                                        <input type="text" class="form-control bg-white w-180" name="product_name" id="inventory_product_name" autocomplete="off">
                                        <input type="hidden" class="form-control bg-white w-180" name="product_id" id="product_id" maxlength="20">
                                        <div id="productSuggestions" class="list-group" style="display: none;"></div>
                                    </td>
                                    <td><input type="text" class="form-control bg-white w-180 specialCharacterAllowed" name="specification" id="product_specification" maxlength="500"></td>
                                    <td><input type="text" class="form-control bg-white w-180 specialCharacterAllowed" {{-- pingki --}} name="size" id="product_size" maxlength="1500"></td>
                                    <td><input type="text" class="form-control bg-white smt_numeric_only" name="opening_stock" id="opening_stock" maxlength="10"></td>
                                    <td>
                                        <select class="form-select" name="uom_id" id="product_uom" >
                                            <option value="">Select UOM</option>
                                            @foreach($uom as $uom)
                                                <option value="{{ $uom->id }}">{{ $uom->uom_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control bg-white smt_numeric_only" name="stock_price" id="stock_price" maxlength="10"></td>
                                </tr>
                                <tr>
                                    <th scope="col">Brand </th>
                                    <th scope="col">Our Product Name </th>
                                    <th scope="col">Inventory Grouping</th>
                                    <th scope="col">Inventory Type</th>
                                    <th scope="col">Set Min Qty for Indent</th>
                                </tr>
                                <tr>
                                    <td style="vertical-align: bottom;" data-th="product brand">
                                        <input type="text" title="" name="product_brand"  id="product_brand" tab-index="1" class="form-control bg-white desc-details-field" value="" maxlength="255">
                                    </td>
                                    <td><input type="text" title="" name="buyer_product_name"  id="buyer_product_name" tab-index="1" class="form-control bg-white desc-details-field" value="" maxlength="100"></td>
                                    <td><input type="text" title="" name="inventory_grouping"  id="inventory_grouping" tab-index="1" class="form-control bg-white desc-details-field" value="" maxlength="255"></td>
                                   <td>
                                        <select class="form-select w-150 import_drop_down_sel" name="inventory_type_id" id="inventory_type" tab-index="1">
                                            <option value="">Select Type</option>
                                            @foreach($inventoryTypes as $inventoryTypes)
                                                <option value="{{ $inventoryTypes->id }}">{{ $inventoryTypes->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" title="" name="indent_min_qty"  id="indent_min_qty" tab-index="1" class="form-control bg-white desc-details-field smt_numeric_only" value="" maxlength="20"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Save Button -->
                    <div class="d-flex justify-content-center mt-3 edit_inventory_button_section" >{{-- pingki --}}
                        <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 save_inventory_button"><i class="bi bi-save font-size-11" aria-hidden="true"></i>Save Inventory</button>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('exJs')
    <script>
        $("#inventory_product_name").keyup(function () {
            let rawInput = $(this).val();          // includes spaces
            let query = rawInput.trim();            
            $('#divisionCategory').html(' ');
            $('#product_id').val(' ');
            $("#productSuggestions").empty().show();
            if (rawInput.length < 3) {
                $("#productSuggestions").html(`
                    <p class="p-2" style="color:#6aa510;">
                        Please enter more than 3 characters.
                    </p>
                `);
                return;
            }
            else{
                 $("#productSuggestions").html(`<li style="text-align: center;" class="search-loader-image">
                            <p><img src="{{ asset('public/assets/images/loader.gif') }}" style="width: 35px;"></p>
                        </li>`);                          
                $.ajax({
                    url: "{{ route('buyer.product.search') }}",
                    method: "GET",
                    data: { query: query },
                    success: function (data) {
                        if (data.length > 0) {
                            $("#productSuggestions").html(`
                                <p class="p-2">
                                    Showing result for "<strong>${rawInput}</strong>" – <strong>${data.length}</strong> records found
                                </p>
                            `);
                            data.forEach(product => {
                                $("#productSuggestions").append(`
                                    <a href="#"
                                    class="list-group-item list-group-item-action product-item text-start"
                                    data-id="${product.id}"
                                    data-name="${product.product_name}" data-division-category="${product.division_name} > ${product.category_name}">
                                        ${product.division_name} > ${product.category_name} <br> ${product.product_name}
                                    </a>
                                `);
                            });
                        } else {
                            $("#productSuggestions").html(`
                                <p class="p-2">No Product Found For <strong>"${rawInput}" Keyword</strong></p>
                            `);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Error fetching product suggestions:", error);
                    }
                });
            }

        });

        // Select product from suggestions
        $(document).on("click", ".product-item", function(e) {
            e.preventDefault();
            $("#inventory_product_name").val($(this).data("name"));
            $('#product_id').val($(this).data("id"));
            $('#divisionCategory').html($(this).data("division-category"));
            $("#productSuggestions").hide();
        });

        // Hide suggestions when clicking outside
        $(document).click(function(event) {
            if (!$(event.target).closest("#productSuggestions, #inventory_product_name").length) {
                $("#productSuggestions").hide();
            }
        });
        // Save Inventory (Add/Edit)
        $('#inventoryForm').submit(function (e) {
            e.preventDefault();
            $('.save_inventory_button').removeAttr('disabled');

            //start pingki
            var branch_id = $('#branch_id').val().trim();
            var product_id = $('#product_id').val().trim();
            var opening_stock = $('#opening_stock').val().trim();
            var product_uom = $('#product_uom').val().trim();
            var stock_price = $('#stock_price').val().trim();
            //end pingki

            function showError(msg, selector) {
                toastr.error(msg);
                if (selector) $(selector).focus();
            }

            if (branch_id === '') {
                showError("Branch Name is required!", '#branch_id');
                return;
            }
            if (product_id === '') {
                showError("Valid Product is required!", "#product_name");
                return;
            }
            if (opening_stock === '') {
                showError("Opening Stock is required!", "#opening_stock");
                return;
            }
            if (product_uom === '') {
                showError("Product UOM is required!", "#product_uom");
                return;
            }
            if (stock_price === '') {
                showError("Rate is required!", "#stock_price");
                return;
            }

            // Client side maxlength validation for all inputs with maxlength attr inside form
            var invalidMaxlengthField = null;
            $('#inventoryForm [maxlength]').each(function () {
                var max = parseInt($(this).attr('maxlength'));
                var val = $(this).val();
                if (val.length > max) {
                    invalidMaxlengthField = this;
                    return false; // break .each
                }
            });
            if (invalidMaxlengthField) {
                var fieldName = $(invalidMaxlengthField).attr('name') || 'Field';
                showError(fieldName + " must not exceed " + $(invalidMaxlengthField).attr('maxlength') + " characters.", invalidMaxlengthField);
                return;
            }

            $('.save_inventory_button').attr('disabled', 'disabled');

            $.ajax({
                type: "POST",
                url: "{{ route('buyer.inventory.store') }}",
                // headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: $('#inventoryForm').serialize() + '&buyer_branch_id=' + branch_id,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function (response) {
                    if (response.status) {
                        $('.save_inventory_button').removeAttr('disabled');
                        $('#inventoryForm')[0].reset();
                        $('#inventoryForm').find('select').val('');
                        $('#inventoryForm').find('input[type="hidden"]').val('');
                        $('#inventoryModal').modal('hide');
                        toastr.success(response.message);
                        $('#inventory-table').DataTable().destroy();
                        inventory_list_data();
                    } else {
                        toastr.error(response.message || "Failed to save data.");
                        $('.save_inventory_button').removeAttr('disabled');
                    }
                },
                error: function (xhr) {
                    $('.save_inventory_button').removeAttr('disabled');
                    // Laravel validation error format
                    if (xhr.status === 422) {
                        const response = xhr.responseJSON;

                        // 1. Handle default Laravel validation errors (in "errors" object)
                        if (response.errors) {
                            let firstInvalid = true;
                            $.each(response.errors, function (key, messages) {
                                toastr.error(messages[0]);
                                if (firstInvalid) {
                                    const field = $('[name="' + key + '"]');
                                    if (field.length) field.focus();
                                    firstInvalid = false;
                                }
                            });
                        }
                        // 2. Handle custom error string from response.error
                        else if (response.error) {
                            toastr.error(response.error);
                        }
                        // 3. Fallback error message
                        else {
                            toastr.error(response.message || "Validation failed.");
                        }
                    } else {
                        toastr.error("Something went wrong. Please try again.");
                    }
                }

            });
        });


    </script>
@endpush
