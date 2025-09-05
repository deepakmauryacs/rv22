@extends('buyer.layouts.app', ['title' => 'Forward Auction - Add Products'])

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/jquery.datetimepicker.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sumoselect@3.4.9/sumoselect.min.css">
<link href="{{ asset('public/assets/buyer/css/page-style.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="bg-white">
    @include('buyer.layouts.sidebar-menu')
</div>
<main class="main flex-grow-1">
    <div class="container-fluid">
        <div class="card rounded">
            <div class="card-header border-0 bg-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="font-size-18">Forward Auction - Add Products</h1>
                </div>
            </div>
            
            <div class="card-body">
                <form method="post" action="{{ route('buyer.forward-auction.store') }}" enctype="multipart/form-data" id="auction_form">
                    @csrf
                    <div class="row gx-3">
                        <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-4 mb-sm-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                                <div class="form-floating">
                                    <input type="text" name="schedule_date" id="schedule_date" class="form-control" placeholder="Select Date" autocomplete="off">
                                    <label for="schedule_date">Schedule Date <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="text-danger error" id="schedule_date_error"></div>
                        </div>

                        <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-4 mb-sm-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                                <div class="form-floating">
                                    <select name="schedule_start_time" id="schedule_start_time" class="form-control SumoUnder">
                                        <option value="">Select Time <span class="text-danger">*</span></option>
                                        @for ($hour = 0; $hour < 24; $hour++)
                                            @php
                                                $display = date('h A', mktime($hour, 0, 0));
                                                $value = date('H:i:s', mktime($hour, 0, 0));
                                            @endphp
                                            <option value="{{ $value }}">{{ $display }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="text-danger error" id="schedule_time_error"></div>
                        </div>

                        <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-4 mb-sm-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-shop"></span></span>
                                <div class="form-floating">
                                    <select class="globle-field-changes form-select" id="buyer_branch" name="buyer_branch" aria-label="Floating label select example">
                                        @if(count($branches) > 1)
                                            <option value="">Select</option>
                                        @endif
                                        @foreach($branches as $val)
                                            @if($val->branch_name != '')
                                                @php
                                                    $fullAddress = trim($val->address . ', ' . $val->state_name . ', ' . $val->country_name);
                                                @endphp
                                                <option value="{{ $val->branch_id }}" data-address="{{ htmlspecialchars($fullAddress) }}">
                                                    {{ $val->branch_name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <label for="buyer_branch">Branch/Unit: <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="text-danger error" id="buyer_branch_error"></div>
                        </div>

                        <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-4 mb-sm-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-geo-alt"></span></span>
                                <div class="form-floating">
                                    <input type="text" name="branch_address" id="branch_address" class="form-control" placeholder="Branch address" readonly>
                                    <label for="branch_address">Branch Address <span class="text-danger">*</span></label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-auto flex-grow-1">
                            <div class="form-floating position-relative">
                                <input type="search" id="search-vendor" class="form-control w-100 rounded" placeholder="Search Vendor and select..." autocomplete="off">
                                <label>Vendor(s) <span class="text-danger">*</span></label>
                                <div id="vendor-dropdown" class="dropdown-list d-none"></div>
                                <div class="text-danger error" id="vendor_error"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <hr class="mt-0 text-muted">
                        </div>

                        <div class="col-12">
                            <div class="selected-vendor-list height-inherit border rounded p-3 d-none">
                                <h3 class="font-size-11 mb-2">Selected Vendors</h3>
                                <div class="vendor-chip-container" id="aliasContainer"></div>
                            </div>
                        </div>

                        <!-- Product Details Table -->
                        <div class="col-12 py-3">
                            <h3 class="fs-6">Product Details</h3>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="product_table">
                                    <thead>
                                        <th scope="col" class="text-nowrap">Product Name<sup class="text-danger">*</sup></th>
                                        <th scope="col" class="text-nowrap">Specs</th>
                                        <th scope="col" class="text-nowrap">Quantity<sup class="text-danger">*</sup></th>
                                        <th scope="col" class="text-nowrap">UOM<sup class="text-danger">*</sup></th>
                                        <th scope="col" class="text-nowrap">Start Price<sup class="text-danger">*</sup></th>
                                        <th scope="col" class="text-wrap keep-word">Min Bid Increment (Amount)<sup class="text-danger">*</sup></th>
                                        <th scope="col" class="text-nowrap">File Attachment</th>
                                        <th scope="col" class="text-nowrap">Action</th>
                                    </thead>
                                    <tbody>
                                        <tr id="row1">
                                            <td class="mw-250 align-top">
                                                <input type="text" name="product_name[]" class="form-control product-name" maxlength="500" autocomplete="off"
                                                    oninput="this.value = this.value.toUpperCase(); if(this.value.length > 500) this.value = this.value.slice(0, 500);"
                                                    style="text-transform: uppercase;"
                                                    placeholder="Type to search products...">
                                                <div class="suggestion-dropdown" id="suggestion-dropdown-row1" style="display: none;"></div>
                                                <div class="text-danger error product-name-error"></div>
                                            </td>
                                            <td class="align-top">
                                                <input type="text" name="specs[]" class="form-control specs" maxlength="500">
                                                <div class="text-danger error specs-error"></div>
                                            </td>
                                            <td class="align-top">
                                                <input type="number" name="quantity[]" class="form-control quantity" min="0.01" step="0.01">
                                                <div class="text-danger error quantity-error"></div>
                                            </td>
                                            <td class="mw-120 align-top">
                                                <select class="form-control uom" name="uom[]">
                                                    <option value="">Select</option>
                                                    @foreach($uoms as $uom)
                                                        <option value="{{ $uom->id }}">{{ $uom->uom_name }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="text-danger error uom-error"></div>
                                            </td>
                                            <td class="align-top">
                                                <input type="number" name="start_price[]" class="form-control price" min="0.01" step="0.01"
                                                    onkeydown="return !(event.key === '+' || event.key === '-')">
                                                <div class="text-danger error price-error"></div>
                                            </td>
                                            <td class="align-top">
                                                <input type="number" name="min_bid_increment_amount[]" class="form-control min-bid-amount" min="0.01" step="0.01"
                                                    onkeydown="return !(event.key === '+' || event.key === '-')">
                                                <div class="text-danger error min-bid-amount-error"></div>
                                            </td>
                                            <td class="mw-250 align-top">
                                                <input type="file" name="file_attachment[]" class="form-control file"
                                                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.csv"
                                                    onchange="validateFile(this)">
                                                <div class="text-danger error file-error"></div>
                                            </td>
                                            <td class="align-top">
                                                <button type="button" name="add" id="add" class="ra-btn small-btn ra-btn-primary text-nowrap">
                                                    <span class="bi bi-plus-circle font-size-12"></span>
                                                    Add More
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="col-12">
                            <label>Remarks</label>
                            <textarea name="remarks" class="form-control height-inherit" rows="3"
                                placeholder="Enter any remarks here..."
                                oninput="if(this.value.length > 1000) this.value = this.value.slice(0, 1000);"></textarea>
                        </div>

                        <div class="col-12">
                            <div class="row justify-content-between pt-4">
                                <div class="col-12 col-md-4 mb-4">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="bi bi-geo-alt"></span>
                                        </span>
                                        <div class="form-floating">
                                            <input type="text" name="price_basis" class="form-control" id="priceBasis" placeholder="Price Basis"
                                                oninput="if(this.value.length > 255) this.value = this.value.slice(0, 255);">
                                            <label for="priceBasis">Price Basis</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 mb-4">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="bi bi-currency-rupee"></span>
                                        </span>
                                        <div class="form-floating">
                                            <input type="text" name="payment_terms" class="form-control" id="paymentTerm" placeholder="Payment Term"
                                                oninput="if(this.value.length > 255) this.value = this.value.slice(0, 255);">
                                            <label for="paymentTerm">Payment Term</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 mb-4">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="bi bi-calendar2-date"></span>
                                        </span>
                                        <div class="form-floating">
                                            <input type="text" name="delivery_period" class="form-control" id="deliveryPeriod" placeholder="Delivery Period (In Days)" maxlength="3"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3);">
                                            <label for="deliveryPeriod">Delivery Period (In Days)</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-2 mb-4">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="bi bi-currency-exchange"></span>
                                        </span>
                                        <div class="form-floating">
                                            <select name="currency" id="currency" class="form-select">
                                                @foreach($currencies as $val)
                                                    @if(!empty($val->currency_name))
                                                        @php
                                                            $currency_val = ($val->currency_symbol == "रु") ? 'NPR' : $val->currency_symbol;
                                                            $currency_symbol = ($val->currency_symbol == "रु") ? 'NPR' : $val->currency_symbol;
                                                        @endphp
                                                        <option value="{{ $currency_val }}" data-symbol="{{ $currency_symbol }}">
                                                            {{ $val->currency_name }} ({{ $val->currency_symbol }})
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <label>Currency<span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="text-danger error" id="currency_error"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="d-flex gap-3 justify-content-center">
                                <a href="{{ route('buyer.forward-auction.index') }}" class="ra-btn small-btn ra-btn-outline-danger">
                                    <span class="bi bi-arrow-left"></span>
                                    Back
                                </a>
                                <button type="submit" class="ra-btn small-btn ra-btn-primary">
                                    <span class="bi bi-check2-square"></span>
                                    Submit Auction
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sumoselect@3.4.9/jquery.sumoselect.min.js"></script>
<script>
var vendor_array = new Array();

$(document).ready(function() {
    // Set today's date as minimum date
    let dateToday = new Date();
    $('#schedule_date').datetimepicker({
        lang: 'en',
        timepicker: false,
        minDate: dateToday,
        format: 'd/m/Y',
        formatDate: 'd/m/Y',
    });

    // Initialize row counter
    var i = 1;

    // Function to show suggestions
    function showSuggestions(input, dropdownId) {
        var query = input.value;
        if (query.length < 2) {
            $('#' + dropdownId).hide().empty();
            return;
        }

        $.ajax({
            url: '{{ route("buyer.forward-auction.get_product_suggestions") }}',
            type: 'GET',
            data: {
                term: query
            },
            dataType: 'json',
            success: function(data) {
                var dropdown = $('#' + dropdownId);
                dropdown.empty();
                if (data.length > 0) {
                    $.each(data, function(index, product) {
                        dropdown.append(
                            '<div class="suggestion-item" style="padding: 8px; cursor: pointer;">' +
                            product + '</div>');
                    });
                    dropdown.show();
                } else {
                    dropdown.hide();
                }
            },
            error: function(xhr, status, error) {
                console.error('Suggestion error:', error);
                $('#' + dropdownId).hide().empty();
            }
        });
    }

    // Handle keyup event for product-name inputs
    $(document).on('keyup', '.product-name', function() {
        var rowId = $(this).closest('tr').attr('id');
        showSuggestions(this, 'suggestion-dropdown-' + rowId);
    });

    // Handle suggestion click
    $(document).on('click', '.suggestion-item', function() {
        var selectedValue = $(this).text();
        var dropdown = $(this).parent();
        var input = dropdown.prev('input.product-name');
        input.val(selectedValue);
        dropdown.hide().empty();
    });

    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.product-name, .suggestion-dropdown').length) {
            $('.suggestion-dropdown').hide().empty();
        }
    });

    // Add new product row
    $('#add').click(function() {
        const currentRowCount = $('#product_table tbody tr').length;

        if (currentRowCount >= 10) {
            alert('You can only add up to 10 products.');
            return;
        }

        i++;
        $('#product_table tbody').append(
            '<tr id="row' + i + '">' +
            '<td class="mw-250 align-top">' +
            '<input type="text" name="product_name[]" class="form-control product-name" maxlength="500" autocomplete="off" oninput="this.value = this.value.toUpperCase(); if(this.value.length > 500) this.value = this.value.slice(0, 500);" style="text-transform: uppercase;" placeholder="Type to search products...">' +
            '<div class="suggestion-dropdown" id="suggestion-dropdown-row' + i + '" style="display: none;"></div>' +
            '<div class="text-danger error product-name-error"></div>' +
            '</td>' +
            '<td class="align-top"><input type="text" name="specs[]" class="form-control specs" maxlength="500">' +
            '<div class="text-danger error specs-error"></div></td>' +
            '<td class="align-top"><input type="number" name="quantity[]" class="form-control quantity" min="0.01" step="0.01">' +
            '<div class="text-danger error quantity-error"></div></td>' +
            '<td class="mw-120 align-top">' +
            '<select class="form-control uom" name="uom[]">' +
            '<option value="">Select</option>' +
            @foreach($uoms as $uom)
            '<option value="{{ $uom->id }}">{{ $uom->uom_name }}</option>' +
            @endforeach
            '</select>' +
            '<div class="text-danger error uom-error"></div>' +
            '</td>' +
            '<td class="align-top"><input type="number" name="start_price[]" class="form-control price" min="0.01" step="0.01" onkeydown="return !(event.key === \'+\' || event.key === \'-\')">' +
            '<div class="text-danger error price-error"></div></td>' +
            '<td class="align-top"><input type="number" name="min_bid_increment_amount[]" class="form-control min-bid-amount" min="0.01" step="0.01" onkeydown="return !(event.key === \'+\' || event.key === \'-\')">' +
            '<div class="text-danger error min-bid-amount-error"></div></td>' +
            '<td class="mw-250 align-top">' +
            '<input type="file" name="file_attachment[]" class="form-control file" ' +
            'accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.csv" ' +
            'onchange="validateFile(this)">' +
            '<div class="text-danger error file-error"></div>' +
            '</td>' +
            '<td class="align-top"><button type="button" name="remove" id="' + i + '" class="ra-btn small-btn ra-btn-outline-danger text-nowrap btn_remove"><span class="bi bi-x-circle font-size-12"></span> Remove</button></td>' +
            '</tr>'
        );
    });

    // Remove product row
    $(document).on('click', '.btn_remove', function() {
        var button_id = $(this).attr("id");
        $('#row' + button_id).remove();
        if ($('#product_table tbody tr').length === 0) {
            i = 1;
            $('#product_table tbody').append(
                '<tr id="row1">' +
                '<td class="mw-250 align-top">' +
                '<input type="text" name="product_name[]" class="form-control product-name" maxlength="500" autocomplete="off" oninput="this.value = this.value.toUpperCase(); if(this.value.length > 500) this.value = this.value.slice(0, 500);" style="text-transform: uppercase;" placeholder="Type to search products...">' +
                '<div class="suggestion-dropdown" id="suggestion-dropdown-row1" style="display: none;"></div>' +
                '<div class="text-danger error product-name-error"></div>' +
                '</td>' +
                '<td class="align-top"><input type="text" name="specs[]" class="form-control specs" maxlength="500">' +
                '<div class="text-danger error specs-error"></div></td>' +
                '<td class="align-top"><input type="number" name="quantity[]" class="form-control quantity" min="0.01" step="0.01">' +
                '<div class="text-danger error quantity-error"></div></td>' +
                '<td class="mw-120 align-top">' +
                '<select class="form-control uom" name="uom[]">' +
                '<option value="">Select</option>' +
                @foreach($uoms as $uom)
                '<option value="{{ $uom->id }}">{{ $uom->uom_name }}</option>' +
                @endforeach
                '</select>' +
                '<div class="text-danger error uom-error"></div>' +
                '</td>' +
                '<td class="align-top"><input type="number" name="start_price[]" class="form-control price" min="0.01" step="0.01" onkeydown="return !(event.key === \'+\' || event.key === \'-\')">' +
                '<div class="text-danger error price-error"></div></td>' +
                '<td class="align-top"><input type="number" name="min_bid_increment_amount[]" class="form-control min-bid-amount" min="0.01" step="0.01" onkeydown="return !(event.key === \'+\' || event.key === \'-\')">' +
                '<div class="text-danger error min-bid-amount-error"></div></td>' +
                '<td class="mw-250 align-top">' +
                '<input type="file" name="file_attachment[]" class="form-control file" ' +
                'accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.csv" ' +
                'onchange="validateFile(this)">' +
                '<div class="text-danger error file-error"></div>' +
                '</td>' +
                '<td class="align-top"><button type="button" name="add" id="add" class="ra-btn small-btn ra-btn-primary text-nowrap"><span class="bi bi-plus-circle font-size-12"></span> Add More</button></td>' +
                '</tr>'
            );
        }
    });

    // Add this function to check for duplicate product names
    function hasDuplicateProductNames() {
        const productNames = [];
        let hasDuplicates = false;

        $('#product_table tbody tr').each(function() {
            const rawName = $(this).find('.product-name').val();

            // Normalize: trim, remove extra spaces, convert to lowercase
            const productName = rawName.trim().replace(/\s+/g, ' ').toLowerCase();

            if (productName) {
                if (productNames.includes(productName)) {
                    hasDuplicates = true;
                    $(this).find('.product-name-error').text('Duplicate product name found');
                } else {
                    productNames.push(productName);
                    $(this).find('.product-name-error').text(''); // Clear previous error if any
                }
            }
        });

        return hasDuplicates;
    }

    // Form validation
    $('#auction_form').submit(function(e) {
        e.preventDefault();
        let isValid = true;

        $('.error').text('');

        // Check for duplicate product names
        if (hasDuplicateProductNames()) {
            isValid = false;
            toastr.error('Please ensure all product names are unique');
        }

        const scheduleDate = $('input[name="schedule_date"]').val();
        if (!scheduleDate) {
            $('#schedule_date_error').text('Schedule date is required');
            isValid = false;
        } else {
            const selectedDate = parseDateFromDDMMYYYY(scheduleDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                $('#schedule_date_error').text('Schedule date cannot be in the past');
                isValid = false;
            }
        }

        if (!$('#schedule_start_time').val()) {
            $('#schedule_time_error').text('Schedule time is required');
            isValid = false;
        }

        if (!$('#buyer_branch').val()) {
            $('#buyer_branch_error').text('Branch/Unit is required');
            isValid = false;
        }
        if (!$('#currency').val()) {
            $('#currency_error').text('Currency is required');
            isValid = false;
        }

        if (vendor_array.length <= 0) {
            $('#vendor_error').text('Please select at least one vendor');
            isValid = false;
        }

        $('#product_table tbody tr').each(function(index) {
            const row = $(this);
            const productName = row.find('.product-name').val();
            const price = row.find('.price').val();
            const uom = row.find('.uom').val();
            const quantity = row.find('.quantity').val();
            const min_bid_increment_amount = row.find('.min-bid-amount').val();

            if (!productName) {
                row.find('.product-name-error').text('Product name is required');
                isValid = false;
            }

            if (!price || parseFloat(price) <= 0) {
                row.find('.price-error').text('Please enter a valid price (greater than 0)');
                isValid = false;
            }

            if (!uom) {
                row.find('.uom-error').text('UOM is required');
                isValid = false;
            }

            if (!quantity || parseFloat(quantity) <= 0) {
                row.find('.quantity-error').text('Please enter a valid quantity (greater than 0)');
                isValid = false;
            }

            if (!min_bid_increment_amount || parseFloat(min_bid_increment_amount) <= 0) {
                row.find('.min-bid-amount-error').text('Please enter a valid increment amount (greater than 0)');
                isValid = false;
            }
        });

        if (isValid) {
            var form = this;
            var formData = new FormData(form);
            vendor_array.forEach(id => {
                formData.append('vendor_id[]', id);
            });

            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                beforeSend: function() {
                    $('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
                },
                success: function(response) {
                    $('button[type="submit"]').prop('disabled', false).html('<span class="bi bi-check2-square"></span> Submit Auction');
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        window.location.href = "{{ route('buyer.forward-auction.index') }}";
                    } else {
                        if (response.errors) {
                            toastr.error(response.errors);
                        } else if (response.status === "error") {
                            toastr.error(response.message);
                        } else {
                            toastr.error("An unexpected error occurred.");
                        }
                    }
                },
                error: function(xhr, status, error) {
                    $('button[type="submit"]').prop('disabled', false).html('<span class="bi bi-check2-square"></span> Submit Auction');
                    toastr.error("AJAX error: " + error);
                }
            });
        }
    });

    function parseDateFromDDMMYYYY(dateStr) {
        const parts = dateStr.split('/');
        if (parts.length !== 3) return null;

        const [dd, mm, yyyy] = parts;
        const isoString = `${yyyy}-${mm}-${dd}`; // Convert to YYYY-MM-DD
        const date = new Date(isoString);

        return isNaN(date.getTime()) ? null : date;
    }

    // Real-time validation for product fields
    $(document).on('blur', '.product-name', function() {
        if (!$(this).val()) {
            $(this).siblings('.product-name-error').text('Product name is required');
        } else {
            $(this).siblings('.product-name-error').text('');
        }
    });

    $(document).on('blur', '.quantity', function() {
        const value = $(this).val();
        if (!value || parseInt(value) <= 0) {
            $(this).siblings('.quantity-error').text('Please enter a valid quantity (greater than 0)');
        } else {
            $(this).siblings('.quantity-error').text('');
        }
    });

    $(document).on('blur', '.price', function() {
        const value = $(this).val();
        if (!value || parseFloat(value) <= 0) {
            $(this).siblings('.price-error').text('Please enter a valid price (greater than 0)');
        } else {
            $(this).siblings('.price-error').text('');
        }
    });

    $(document).on('blur', '.min-bid-amount', function() {
        const value = $(this).val();
        if (!value || parseFloat(value) <= 0) {
            $(this).siblings('.min-bid-amount-error').text('Please enter a valid min bid increment amount (greater than 0)');
        } else {
            $(this).siblings('.min-bid-amount-error').text('');
        }
    });
});

function validateFile(input) {
    const file = input.files[0];
    const allowedTypes = [
        'image/jpeg', 'image/png', 'application/pdf',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv'
    ];
    const maxSize = 2 * 1024 * 1024; // 2 MB

    const errorDiv = input.nextElementSibling;

    if (file) {
        if (!allowedTypes.includes(file.type)) {
            errorDiv.textContent = "Only JPG, PNG, PDF, DOC, DOCX, XLS, XLSX, CSV files are allowed.";
            input.value = '';
            return;
        }

        if (file.size > maxSize) {
            errorDiv.textContent = "File size must be less than 2 MB.";
            input.value = '';
            return;
        }

        errorDiv.textContent = "";
    }
}

$(document).ready(function() {
    $('#buyer_branch').on('change', function() {
        const selected = $(this).find('option:selected');
        const address = selected.data('address') || '';
        $('#branch_address').val(address);
    });

    // If editing, fill address input automatically
    $('#buyer_branch').trigger('change');
});

function removeVendor(el, user_id) {
    // Convert user_id to number to match how it's stored in vendor_array
    user_id = parseInt(user_id);
    
    // Remove from vendor_array
    vendor_array = vendor_array.filter(id => id !== user_id);
    
    // Remove the chip from the UI
    $(el).closest('.vendor-chip-item').remove();
    
    // Remove the 'vendor-added' class from the dropdown item
    $(`.vendor-search-${user_id}`).removeClass('vendor-added');
    
    // Hide container if no vendors left
    $(".selected-vendor-list").toggleClass('d-none', vendor_array.length === 0);
}

var hasMore = true;
var currentQuery = '';

$('#search-vendor').on('input', function() {
    currentQuery = $(this).val().trim();
    currentPage = 1;
    hasMore = true;
    $('#vendor-dropdown').empty();
    if (currentQuery.length > 4) {
        fetchVendors(currentQuery, currentPage);
    } else {
        $('#vendor-dropdown').html('<div>Enter at least 5 characters to search</div>').removeClass('d-none');
    }
});

// Place this once, preferably in your main layout JS or before AJAX calls
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

function fetchVendors(query, page) {
    if (isLoading || !hasMore) return;
    isLoading = true;

    $.ajax({
        url: '{{ route("buyer.forward-auction.search_vendors") }}',
        method: 'POST',
        data: {
            q: query,
            page: page
        },
        dataType: 'json',
        success: function(res) {
            const dropdown = $('#vendor-dropdown');

            if (page === 1) dropdown.empty();
            if (res.data.length === 0) {
                hasMore = false;
                if (page === 1) dropdown.html('<div>No matches found</div>');
                return;
            }

            res.data.forEach(v => {
                let another_class = '';
                if (vendor_array.includes(parseInt(v.user_id))) {
                    another_class = 'vendor-added';
                }
                dropdown.append(
                    `<div class="vendor-row ${another_class} vendor-search-${v.id}" onclick="selectVendor(this, '${v.id}', '${v.legal_name}')">${v.legal_name}</div>`
                );
            });

            dropdown.removeClass('d-none');
            if (!res.has_more) hasMore = false;
        },
        complete: function() {
            isLoading = false;
        }
    });
}

function selectVendor(_this, user_id, vendor_name) {
    user_id = parseInt(user_id);
    
    if (!$(_this).hasClass("vendor-added") && $(".alias-container").find('.alias-tag').length > 9) {
        alert("Maximum of 10 vendors can be added at once.");
        return false;
    }

    if ($(_this).hasClass("vendor-added")) {
        // Vendor is already selected, so remove them
        vendor_array = vendor_array.filter(id => id !== user_id);
        $(`#aliasContainer .vendor-${user_id}`).remove();
        $(_this).removeClass("vendor-added");
    } else {
        // Add new vendor
        vendor_array.push(user_id);
        let html = `<span class="vendor-chip-item vendor-${user_id}">
                       ${vendor_name}
                       <span role="button" class="ra-btn ra-btn-link bi bi-x-lg p-0 ms-2 width-inherit font-size-11 remove-alias" onclick="removeVendor(this, ${user_id})"></span>
                   </span>`;
        $("#aliasContainer").append(html);
        $(_this).addClass("vendor-added");
    }
    
    // Show/hide container based on whether there are any vendors selected
    $(".selected-vendor-list").toggleClass('d-none', vendor_array.length === 0);
}

$('#vendor-dropdown').on('scroll', function() {
    const $this = $(this);
    if ($this[0].scrollTop + $this[0].clientHeight >= $this[0].scrollHeight - 5) {
        if (hasMore && !isLoading) {
            currentPage++;
            fetchVendors(currentQuery, currentPage);
        }
    }
});

// Optional: hide dropdown on outside click
$(document).on('click', function(e) {
    if (
        !$(e.target).closest('.position-relative').length &&
        !$(e.target).closest('.vendor-row').length &&
        !$(e.target).closest('#search-vendor').length
    ) {
        $('#vendor-dropdown').addClass('d-none');
        $('#search-vendor').val('');
    }
});

function toggleVendor(arr, item) {
    item = parseInt(item);
    const index = arr.indexOf(item);
    if (index > -1) {
        // If exists, remove it
        arr.splice(index, 1);
    } else {
        // If not exists, add it
        arr.push(item);
    }
    return arr;
}

$(document).ready(function() {
    $('#schedule_start_time').SumoSelect({
        placeholder: 'Select Time',
        search: false,
        csvDispCount: 1,
        floatWidth: 0
    });

    // Set up CSRF token for AJAX in Laravel
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#schedule_date').on('change', function () {
        var selectedDate = $(this).val();

        // Reset the selected time value
        $('#schedule_start_time').val('');

        $.ajax({
            url: '{{ route("buyer.forward-auction.get-booked-times") }}',
            type: 'POST',
            data: { schedule_date: selectedDate },
            success: function (response) {
                // If response is JSON array of booked times
                const bookedTimes = response; // assuming Laravel returns JSON array directly

                // Enable all options
                $('#schedule_start_time option').prop('disabled', false);

                // Disable booked options
                $('#schedule_start_time option').each(function () {
                    const value = $(this).val();
                    if (bookedTimes.includes(value)) {
                        $(this).prop('disabled', true);
                    }
                });

                // Destroy and reinitialize SumoSelect
                if ($('#schedule_start_time')[0].sumo) {
                    $('#schedule_start_time')[0].sumo.unload();
                }

                $('#schedule_start_time').SumoSelect({
                    placeholder: 'Select Time',
                    search: false,
                    csvDispCount: 1,
                    floatWidth: 0
                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching booked times:', error);
            }
        });
    });
});
</script>
@endsection