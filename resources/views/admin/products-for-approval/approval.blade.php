@extends('admin.layouts.app_second', [
    'title' => 'Products For Approvel',
    'sub_title' => 'Edit Products For Approvel',
])
@section('breadcrumb')
    <div class="breadcrumb-header">
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.product-approvals.index') }}">Products For
                            Approvel</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Update Product</li>
                </ol>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
    <style>
        .bootstrap-tagsinput {
            width: 100%;
            min-height: 32px;
            padding: 0.25rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }

        .bootstrap-tagsinput .tag {
            margin-right: 2px;
            color: white;
            background-color: #0d6efd;
            padding: 2px 5px;
            border-radius: 3px;
        }

        .capital {
            text-transform: uppercase;
        }

        .ck-editor__editable {
            min-height: 200px;
        }

        .text-warning {
            color: #ffc107 !important;
        }

        ul {
            list-style: outside none none;
            margin: 0;
            padding: 0;
        }

        .char-count {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .char-count.warning {
            color: #ffc107;
        }

        .char-count.error {
            color: #dc3545;
        }
    </style>
    <div class="page-start-section from-start-section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between"
                            style="background-color: transparent;padding: 15px;border: none !important;">
                            <ul class="nav nav-tabs card-header-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#general_information_menu"
                                        style="color: #015294;background-color: #fff;border-color: #fff;border-bottom: 1px solid #015294 !important;">
                                        <i class="bi bi-info-circle me-2"></i>
                                        General Information
                                    </a>
                                </li>
                            </ul>
                            <div class="ms-auto">
                                <a class="btn-rfq btn-rfq-primary" onclick="raise_query()" href="javascript:void(0)">
                                    Raise Query
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="basic-form pro_edit">
                                <form id="editProductForm" method="POST" enctype="multipart/form-data" novalidate>
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="id" value="{{ $product->id }}">
                                    <input type="hidden" name="product_id" value="{{ $product->product_id }}">
                                    <input type="hidden" name="vendor_id" value="{{ $product->vendor_id }}">

                                    @php
                                        $isInternationalVendor = !($isNationalVendor ?? (optional($product->vendor_profile)->country == 101));
                                        $taxCollection = collect($taxes ?? []);
                                        $formatTaxLabel = static function ($tax) {
                                            $name = trim((string) ($tax->tax_name ?? ''));
                                            $percentage = is_numeric($tax->tax)
                                                ? rtrim(rtrim(number_format((float) $tax->tax, 2, '.', ''), '0'), '.')
                                                : trim((string) $tax->tax);
                                            $percentageLabel = $percentage !== '' ? $percentage . '%' : '';

                                            if ($name !== '' && $percentageLabel !== '') {
                                                return $name . ' (' . $percentageLabel . ')';
                                            }

                                            if ($name !== '') {
                                                return $name;
                                            }

                                            return $percentageLabel !== '' ? $percentageLabel : 'N/A';
                                        };
                                    @endphp

                                    <!-- Product Name -->
                                    <div class="row mb-3">
                                        <div class="col-md-3 d-flex align-items-center">
                                            <label class="form-label mb-0">Product Name<span
                                                    class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" name="product_name" id="product_name"
                                                value="{{ $product->product->product_name }}" readonly>
                                            <span class="text-danger error-text product_name_error"></span>
                                        </div>
                                    </div>

                                    <!-- Upload Picture -->
                                    <div class="row mb-3">
                                        <div class="col-md-3 d-flex align-items-center">
                                            <label class="form-label mb-0">Upload Picture</strong></label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="file" name="product_image" id="product_image"
                                                class="form-control" accept=".jpeg,.jpg,.png,.gif"
                                                onchange="validateProductFile(this, 'JPEG/JPG/PNG/GIF')">
                                            <span class="text-danger error-text product_image_error"></span>
                                            @if ($product->image)
                                                <div class="mt-2">
                                                    <img src="{{ asset('public/uploads/product/thumbnails/100/' . $product->image) }}"
                                                        alt="Product Image" style="max-width: 200px; max-height: 200px;">
                                                    <a href="{{ asset('public/uploads/product/thumbnails/100/' . $product->image) }}"
                                                        target="_blank" class="ms-2"> <b>View Full Image </b></a>
                                                    <input type="hidden" name="existing_product_image"
                                                        value="{{ $product->image }}">
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-3 pt-2">
                                            <span class="text-danger font-size-11">(JPEG/JPG/PNG/GIF)</span>
                                        </div>
                                    </div>

                                    <!-- Product Description -->
                                    <div class="row mb-3">
                                        <div class="col-md-3 d-flex align-items-center">
                                            <label class="form-label mb-0">Product Description<span
                                                    class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-md-9">
                                            <span class="char-count prod-des-count">Characters:
                                                {{ strlen($product->description) }}/500</span>
                                            <textarea class="form-control" id="product_description" name="product_description">{{ $product->description }}</textarea>
                                            <span class="text-danger error-text product_description_error"></span>
                                        </div>
                                    </div>

                                    <!-- Dealer Type -->
                                    <div class="row mb-3">
                                        <div class="col-md-3 d-flex align-items-center">
                                            <label class="form-label mb-0">Dealer Type<span
                                                    class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-md-4">
                                            <select class="form-select" id="product_dealer_type" name="product_dealer_type">
                                                <option value="">Select Dealer Type</option>
                                                <option value="1"
                                                    {{ $product->dealer_type_id == '1' ? 'selected' : '' }}>Manufacturer
                                                </option>
                                                <option value="2"
                                                    {{ $product->dealer_type_id == '2' ? 'selected' : '' }}>Trader</option>
                                            </select>
                                            <span class="text-danger error-text product_dealer_type_error"></span>
                                        </div>
                                    </div>

                                    <!-- UOM -->
                                    <div class="row mb-3">
                                        <div class="col-md-3 d-flex align-items-center">
                                            <label class="form-label mb-0">UOM</strong></label>
                                        </div>
                                        <div class="col-md-4">
                                            <select id="product_uom" name="product_uom" class="form-select">
                                                <option value="1" {{ $product->uom == '1' ? 'selected' : '' }}>Pieces
                                                </option>
                                                <option value="2" {{ $product->uom == '2' ? 'selected' : '' }}>Sets
                                                </option>
                                                <option value="3" {{ $product->uom == '3' ? 'selected' : '' }}>Metre
                                                </option>
                                                <option value="4" {{ $product->uom == '4' ? 'selected' : '' }}>MT
                                                </option>
                                                <option value="5" {{ $product->uom == '5' ? 'selected' : '' }}>Kgs
                                                </option>
                                                <option value="6" {{ $product->uom == '6' ? 'selected' : '' }}>Litre
                                                </option>
                                                <option value="7" {{ $product->uom == '7' ? 'selected' : '' }}>
                                                    Packages</option>
                                            </select>
                                            <span class="text-danger error-text product_uom_error"></span>
                                        </div>
                                    </div>

                                    @unless ($isInternationalVendor)
                                        <!-- GST/Sales Tax Rate -->
                                        <div class="row mb-3">
                                            <div class="col-md-3 d-flex align-items-center">
                                                <label class="form-label mb-0">GST/Sales Tax Rate<span
                                                        class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-4">
                                                <select class="form-select" id="product_gst" name="product_gst">
                                                    <option value="">Select GST Class</option>
                                                    @if ($taxCollection->isNotEmpty())
                                                        @foreach ($taxCollection as $tax)
                                                            <option value="{{ $tax->id }}" {{ (string) $product->gst_id === (string) $tax->id ? 'selected' : '' }}>
                                                                {{ $formatTaxLabel($tax) }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option value="" disabled>No GST rates available</option>
                                                    @endif
                                                </select>
                                                <span class="text-danger error-text product_gst_error"></span>
                                            </div>
                                        </div>
                                    @endunless

                                    <!-- HSN Code -->
                                    <div class="row mb-3">
                                        <div class="col-md-3 d-flex align-items-center">
                                            <label class="form-label mb-0">HSN Code<span
                                                    class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" id="product_hsn_code"
                                                name="product_hsn_code" placeholder="HSN Code"
                                                value="{{ $product->hsn_code }}" maxlength="8"
                                                onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                            <span class="text-danger error-text product_hsn_code_error"></span>
                                        </div>
                                    </div>

                                    <!-- Aliases and Tags -->
                                    <div class="row mb-3">
                                        <div class="col-md-3 d-flex align-items-center">
                                            <label class="form-label mb-0">Aliases & Tags</strong></label>
                                        </div>
                                        <div class="col-md-9">
                                            <span><b>Master Aliases:</b>
                                                {{ get_alias_master_by_prod_id($product->product_id) }}</span>
                                            @php
                                                $vendor_alias = get_alias_vendor_by_prod_id(
                                                    $product->product_id,
                                                    $product->vendor_id,
                                                );
                                            @endphp
                                            <input type="text" data-role="tagsinput" class="form-control"
                                                name="tag" id="tags-input"
                                                value="{{ old('vendor_alias', $vendor_alias ?? '') }}">
                                            <div class="product-alias-error-msg"></div>
                                        </div>
                                    </div>

                                    <!-- Product Catalogue -->
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-md-3 d-flex align-items-center">
                                            <label class="form-label mb-0">Product Catalogue</strong></label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="file" name="product_catalogue_file"
                                                id="product_catalogue_file" class="form-control"
                                                accept=".pdf,.png,.jpg,.jpeg,.doc,.docx"
                                                onchange="validateProductFile(this, 'PDF/PNG/JPG/JPEG/DOCX/DOC')">
                                            <span class="text-danger error-text product_catalogue_file_error"></span>
                                            @if ($product->catalogue)
                                                <div class="mt-2">
                                                    <a href="{{ asset('public/uploads/product/docs/' . $product->catalogue) }}"
                                                        target="_blank">
                                                        {{ $product->catalogue }} View Catalogue File
                                                    </a>
                                                    <input type="hidden" name="existing_product_catalogue_file"
                                                        value="{{ $product->catalogue }}">
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <span class="text-danger font-size-11">(PDF/Image/Document)</span>
                                        </div>
                                    </div>


                                    <!-- Dealership -->
                                    <div class="row mb-3">
                                        <div class="col-md-3 d-flex align-items-center">
                                            <label class="form-label mb-0">Dealership</strong></label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" data-role="tagsinput" class="form-control"
                                                name="product_dealership" id="dealership"
                                                value="{{ $product->dealership }}">
                                            <span class="text-danger error-text product_dealership_error"></span>
                                        </div>
                                    </div>

                                    <!-- Dealership Attachment -->
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-md-3 d-flex align-items-center">
                                            <label class="form-label mb-0">Dealership Attachment</strong></label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="file" name="product_dealership_file"
                                                id="product_dealership_file" class="form-control"
                                                accept=".pdf,.png,.jpg,.jpeg,.doc,.docx"
                                                onchange="validateProductFile(this, 'PDF/PNG/JPG/JPEG/DOCX/DOC')">
                                            <span class="text-danger error-text product_dealership_file_error"></span>
                                            @if ($product->dealership_file)
                                                <div class="mt-2">
                                                    <a href="{{ asset('public/uploads/product/docs/' . $product->dealership_file) }}"
                                                        target="_blank">
                                                        {{ $product->dealership_file }} <b> View Dealership File </b>
                                                    </a>

                                                    <input type="hidden" name="existing_product_dealership_file"
                                                        value="{{ $product->dealership_file }}">
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <span class="text-danger font-size-11">(PDF/Image/Document)</span>
                                        </div>
                                    </div>


                                    <!-- Brand -->
                                    <div class="row mb-3">
                                        <div class="col-md-3 d-flex align-items-center">
                                            <label class="form-label mb-0">Brand</strong></label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" name="brand_name" class="form-control" id="brand_name"
                                                value="{{ $product->brand }}" maxlength="255">
                                            <span class="text-danger error-text brand_name_error"></span>
                                        </div>
                                    </div>

                                    <!-- Country of Origin -->
                                    <div class="row mb-3">
                                        <div class="col-md-3 d-flex align-items-center">
                                            <label class="form-label mb-0">Country of Origin</strong></label>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input domestic" type="radio"
                                                    name="product_country_origin" id="domestic" value="domestic"
                                                    checked>
                                                <label class="form-check-label" for="domestic">India</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input international" type="radio"
                                                    name="product_country_origin" id="international"
                                                    value="international">
                                                <label class="form-check-label" for="international">International</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="d-flex gap-1">
                                                <button type="submit" class="btn-rfq btn-rfq-primary">Verify
                                                    Product</button>
                                                <a href="{{ route('admin.verified-products.index') }}"
                                                    class="btn-rfq btn-rfq-danger">Cancel</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
    <script>
        $(document).ready(function() {
            const isInternationalVendor = @json($isInternationalVendor);
            const editorConfig = {
                toolbar: [
                    "heading", "bold", "italic", "bulletedList", "numberedList",
                    "blockQuote", "undo", "redo"
                ]
            };

            const MAX_CHARS = 500;

            function initEditor(selector, counterSelector, errorSelector = null, validateForm = false) {
                ClassicEditor
                    .create(document.querySelector(selector), editorConfig)
                    .then(editor => {
                        const counter = document.querySelector(counterSelector);
                        let isProcessing = false;

                        const updateCount = () => {
                            if (isProcessing) return;
                            isProcessing = true;

                            const content = editor.getData();
                            const text = content.replace(/<[^>]*>/g, '');
                            const count = text.length;

                            counter.textContent = `Characters: ${count}/${MAX_CHARS}`;
                            counter.className = 'char-count';

                            if (count > MAX_CHARS) {
                                counter.classList.add('error');
                                const trimmed = text.substring(0, MAX_CHARS);
                                editor.setData(trimmed);
                            } else if (count > 450) {
                                counter.classList.add('warning');
                            }

                            isProcessing = false;
                        };

                        updateCount();
                        editor.model.document.on('change:data', updateCount);

                        if (validateForm && errorSelector) {
                            document.querySelector('#editProductForm').addEventListener('submit', function(e) {
                                const content = editor.getData();
                                const text = content.replace(/<[^>]*>/g, '');
                                if (text.length > MAX_CHARS) {
                                    e.preventDefault();
                                    document.querySelector(errorSelector).textContent =
                                        'Description must be 500 characters or less';
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error(`Editor initialization error for ${selector}:`, error);
                    });
            }

            // Initialize both editors
            initEditor('#product_description', '.prod-des-count', '#product_description_error', true);

            $('#editProductForm input, #editProductForm select').on('keyup change', function() {
                const fieldName = $(this).attr('name');
                $(`span.error-text.${fieldName}_error`).text('');
            });

            $('#editProductForm').submit(function(e) {
                e.preventDefault();
                $('span.error-text').text('');

                const product_name = $('#product_name').val().trim();
                const product_hsn_code = $('#product_hsn_code').val();
                const product_gst = isInternationalVendor ? null : $('#product_gst').val();
                const product_dealer_type = $('#product_dealer_type').val();

                let hasErrors = false;

                // Product name
                if (!product_name) {
                    $('.product_name_error').text('Please enter the product name.');
                    toastr.error('Please enter the product name.');
                    hasErrors = true;
                } else if (product_name.length < 2) {
                    $('.product_name_error').text('Product name must be at least 2 characters.');
                    toastr.error('Product name must be at least 2 characters.');
                    hasErrors = true;
                }

                // HSN Code
                if (!product_hsn_code) {
                    $('.product_hsn_code_error').text('Please enter the HSN code.');
                    toastr.error('Please enter the HSN code.');
                    hasErrors = true;
                } else if (!/^\d{2,8}$/.test(product_hsn_code)) {
                    $('.product_hsn_code_error').text('HSN code must be 2 to 8 digits.');
                    toastr.error('HSN code must be 2 to 8 digits.');
                    hasErrors = true;
                }

                // GST
                if (!isInternationalVendor && !product_gst) {
                    $('.product_gst_error').text('Please enter the GST percentage.');
                    toastr.error('Please enter the GST percentage.');
                    hasErrors = true;
                }

                // Dealer Type
                if (!product_dealer_type) {
                    $('.product_dealer_type_error').text('Please select a dealer type.');
                    toastr.error('Please select a dealer type.');
                    hasErrors = true;
                }

                if (hasErrors) return;

                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('admin.product-approvals.update', $product->id) }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status == 1) {
                            toastr.success(response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('admin.product-approvals.index') }}";
                            }, 300);
                        } else {
                            if (response.alias_error_message && Array.isArray(response
                                    .alias_error_message)) {
                                let alias_error_html = '';
                                response.alias_error_message.forEach(function(alias_error) {
                                    alias_error_html +=
                                        '<li><span class="text-danger">*</span> ' +
                                        alias_error + '</li>';
                                });
                                $(".product-alias-error-msg").html('<ul>' + alias_error_html +
                                    '</ul>');
                            }
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                const errorField = key.replace('.', '_');
                                $(`span.error-text.${errorField}_error`).text(value[0]);
                            });
                        } else {
                            toastr.error('An error occurred. Please try again.');
                        }
                    }
                });
            });
        });

        /**
         * Validates a product file against allowed extensions and shows toast error if invalid
         * @param {HTMLInputElement} fileInput - The file input element to validate
         * @param {string} allowedExtensions - String of allowed extensions separated by slashes
         * @returns {boolean} - Returns true if valid, false if invalid
         */
        function validateProductFile(fileInput, allowedExtensions) {
            // Convert allowedExtensions string to array of lowercase extensions without dots
            const allowedExtArray = allowedExtensions.split('/')
                .map(ext => ext.toLowerCase().replace('.', ''));

            // Get the selected file
            const file = fileInput.files[0];
            if (!file) {
                showToastError('Please select a file');
                return false;
            }

            // Get file extension
            const fileName = file.name;
            const fileExt = fileName.split('.').pop().toLowerCase();

            // Check if extension is allowed
            if (!allowedExtArray.includes(fileExt)) {
                const allowedExtensionsString = allowedExtArray.map(ext => `.${ext}`).join(', ');
                showToastError(`Invalid file type. Allowed types: ${allowedExtensionsString}`);
                // Clear the invalid file selection
                fileInput.value = '';
                return false;
            }

            return true;
        }

        /**
         * Shows a toast error message (using Bootstrap 5 toast)
         * @param {string} message - The error message to display
         */
        function showToastError(message) {
            // Create toast element
            const toastEl = document.createElement('div');
            toastEl.className = 'toast align-items-center text-white bg-danger border-0 show';
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            toastEl.style.position = 'fixed';
            toastEl.style.bottom = '20px';
            toastEl.style.right = '20px';
            toastEl.style.zIndex = '9999';

            toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

            // Add to DOM
            document.body.appendChild(toastEl);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                toastEl.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(toastEl);
                }, 300);
            }, 5000);

            // Close button functionality
            toastEl.querySelector('.btn-close').addEventListener('click', () => {
                toastEl.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(toastEl);
                }, 300);
            });
        }
    </script>
@endsection
