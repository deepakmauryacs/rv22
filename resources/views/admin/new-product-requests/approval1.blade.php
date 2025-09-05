@extends('admin.layouts.app', ['title' => 'Edit Product Request', 'sub_title' => 'Update Product'])
@section('content')
<!-- Bootstrap Tags Input CSS -->
<link href="https://cdn.jsdelivr.net/bootstrap.tagsinput/0.8.0/bootstrap-tagsinput.css" rel="stylesheet" />
<!-- Bootstrap Tags Input JS -->
<script src="https://cdn.jsdelivr.net/bootstrap.tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
<!-- Optional: Basic styling fix -->
<style>
    .bootstrap-tagsinput {
        width: 100%;
        min-height: 32px;
        padding: 0.25rem 0.75rem;
        line-height: 1.5;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        background-color: #fff;
    }

    .bootstrap-tagsinput .tag {
        margin-right: 2px;
        color: white;
        background-color: #0d6efd;
        padding: 0.25em 0.5em;
        border-radius: 0.25rem;
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
    .error-class {
        color: #ff0000 !important;
    } 
     ul {
         list-style: outside none none;
         margin: 0;
         padding: 0;
     }
</style>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<div class="container-fluid">
    <div class="card shadow mb-4 mt-3">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary">Edit Products For Approvel</h5>
        </div>
        <div class="card-body">
            <form id="editVerifiedProductForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $product->id }}">
                <div class="pt-4">
                    <div class="basic-form">
                        {{-- Product Name --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">Product Name <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <div class="position-relative">
                                     <input type="text" class="form-control" name="product_name" id="product_name" value="{{ $product->product_name }}" autocomplete="off">
                                     <input type="hidden" name="product_id" id="product_id">
                                     <!-- <div id="product_suggestions" class="dropdown-menu" style="width: 100%; display: none;"></div> -->

                                    <div id="product_suggestions" class="dropdown-menu w-100 shadow" style="display: none; max-height: 300px; overflow-y: auto; position: absolute; z-index: 1000;"></div>
                                </div>
                                <span class="error-class text-danger" id="product-name-error"></span>
                            </div>
                        </div>

                        {{-- Division Dropdown --}}
                        <div class="mb-4 row align-items-center">
                            <label  class="col-sm-3 col-form-label">Division <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                            <select name="division_id" id="division_id" class="form-control">
                                <option value="">Select Division</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ (old('division_id', $product->division_id) == $division->id) ? 'selected' : '' }}>
                                        {{ $division->division_name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="error-class" id="product-division-error"></span>
                          </div>
                        </div>

                        {{-- Category Dropdown --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">Category<span class="text-danger">*</span></label>
                            <div class="col-sm-9" >
                            <select name="category_id" id="category_id" class="form-control">
                                <option value="">Select Category</option>
                            </select>
                            <span class="error-class" id="product-category-error"></span>
                          </div>
                        </div>


                       {{-- Upload Picture --}}
                       <div class="mb-4 row align-items-center">
                            <label for="Product_image" class="col-sm-3 col-form-label">Upload Picture</label>
                            <div class="col-sm-4">
                                <input type="file" 
                                       name="product_image" 
                                       id="product_image" 
                                       class="form-control" 
                                       accept=".jpeg,.jpg,.png,.gif"
                                       onchange="validateProductFile(this, 'JPEG/JPG/PNG/GIF')">
                                <span class="help-block text-danger" id="error-msg-product-image"></span>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-danger">(JPEG/JPG/PNG/GIF)</span>
                            </div>
                        </div>


                        {{-- Product Description --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">Product Description <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <span class="pro-char-count text-muted prod-des-count">Characters: {{ strlen($product->description) }}/500</span>
                                <textarea class="form-control" id="product_description" name="product_description">{{ $product->description }}</textarea>
                                <span class="error-class" id="product-description-error"></span>
                            </div>
                        </div>

                        {{-- Dealer Type --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">Dealer Type <span class="text-danger">*</span></label>
                            <div class="col-sm-4">
                                <select class="form-select" id="product_dealer_type" name="product_dealer_type">
                                    <option value="">Select Dealer Type</option>
                                    <option value="1" {{ $product->dealer_type_id == '1' ? 'selected' : '' }}>Manufacturer</option>
                                    <option value="2" {{ $product->dealer_type_id == '2' ? 'selected' : '' }}>Trader</option>
                                </select>
                                <span class="error-class text-danger" id="product-dealer-type-error"></span>
                            </div>
                        </div>

                        {{-- UOM --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">UOM</label>
                            <div class="col-sm-4">
                                <select id="product_uom" name="product_uom" class="form-select">
                                    <option value="1" {{ $product->uom == '1' ? 'selected' : '' }}>Pieces</option>
                                    <option value="2" {{ $product->uom == '2' ? 'selected' : '' }}>Sets</option>
                                    <option value="3" {{ $product->uom == '3' ? 'selected' : '' }}>Metre</option>
                                    <option value="4" {{ $product->uom == '4' ? 'selected' : '' }}>MT</option>
                                    <option value="5" {{ $product->uom == '5' ? 'selected' : '' }}>Kgs</option>
                                    <option value="6" {{ $product->uom == '6' ? 'selected' : '' }}>Litre</option>
                                    <option value="7" {{ $product->uom == '7' ? 'selected' : '' }}>Packages</option>
                                </select>
                                <span class="error-class" id="product-uom-error"></span>
                            </div>
                        </div>

                        {{-- GST/Sales Tax Rate --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">GST/Sales Tax Rate <span class="text-danger">*</span></label>
                            <div class="col-sm-4">
                                <select class="form-select" id="product_gst" name="product_gst">
                                    <option value="">Select GST Class</option>
                                    <option value="1" {{ $product->gst_id == '1' ? 'selected' : '' }}>0%</option>
                                    <option value="2" {{ $product->gst_id == '2' ? 'selected' : '' }}>5%</option>
                                    <option value="3" {{ $product->gst_id == '3' ? 'selected' : '' }}>12%</option>
                                    <option value="4" {{ $product->gst_id == '4' ? 'selected' : '' }}>18%</option>
                                    <option value="5" {{ $product->gst_id == '5' ? 'selected' : '' }}>28%</option>
                                </select>
                                <span class="error-class text-danger" id="product-gst-error"></span>
                            </div>
                        </div>

                        {{-- HSN Code --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">HSN Code <span class="text-danger">*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="product_hsn_code" name="product_hsn_code" placeholder="HSN Code" value="{{ $product->hsn_code }}" maxlength="8" onkeypress="return event.charCode >= 48 && event.charCode <= 57" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                <span class="error-class text-danger" id="product-hsn-code-error"></span>
                            </div>
                        </div>

                        {{-- Aliases and Tags --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">Aliases &amp; Tags</label>
                            <div class="col-md-9">
                                <span><b>Master Aliases:</b> {{ $product->master_aliases ?? 'N/A' }}</span>
                                <input type="text" data-role="tagsinput" class="form-control" name="tag" id="tags-input" value="">
                                <div class="product-alias-error-msg"></div>
                            </div>
                        </div>
                    
                       {{-- Product Catalogue --}}
                       <div class="mb-4 row align-items-center">
                            <label for="product_catalog" class="col-sm-3 col-form-label">Product Catalogue</label>
                            <div class="col-sm-4">
                                <input type="file" 
                                       name="product_catalogue_file" 
                                       id="product_catalogue_file" 
                                       class="form-control" 
                                       accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" 
                                       onchange="validateProductFile(this, 'PDF/PNG/JPG/JPEG/DOCX/DOC')">
                                <span class="help-block text-danger" id="error-msg-upload-catalog"></span>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-danger">(PDF/Image/Document)</span>
                            </div>
                        </div>

                        {{-- Product Specifications --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">Specifications<span class="text-danger"></span></label>
                            <div class="col-sm-9">
                                <span class="spec-char-count">Characters: 0/500</span>
                                <textarea class="form-control" id="product_specifications" name="product_specifications" >{{ $product->specification }}</textarea>
                            </div>
                        </div>
                        
                        {{-- Specifications Attachment --}}
                        <div class="mb-4 row align-items-center">
                            <label for="product_specifications_attachment" class="col-sm-3 col-form-label">Specifications Attachment</label>
                            <div class="col-sm-4">
                                <input type="file" 
                                       name="product_specification_file" 
                                       id="product_specification_file" 
                                       class="form-control" 
                                       accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" 
                                       onchange="validateProductFile(this, 'PDF/PNG/JPG/JPEG/DOCX/DOC')">
                                <span class="help-block text-danger" id="error-msg-upload-catalog1"></span>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-danger">(PDF/Image/Document)</span>
                            </div>
                        </div>

                        {{-- Size --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">Size<span class="text-danger"></span></label>
                            <div class="col-sm-4 mt-2">
                                <input type="text" data-role="tagsinput" class="form-control" name="product_size" id="size-input" value="{{ $product->size }}">
                            </div>
                        </div>
                        
                        {{-- Certification --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">Certification<span class="text-danger"></span></label>
                            <div class="col-sm-4">
                                <input type="text" data-role="tagsinput" class="form-control" name="product_certification" id="product_certification"  value="{{ $product->certificates }}">
                            </div>
                        </div>
                        
                        {{-- Certification Attachment --}}
                        <div class="mb-4 row align-items-center">
                            <label for="file" class="col-sm-3 col-form-label">Certification Attachment</label>
                            <div class="col-sm-4">
                                <input type="file" 
                                       name="product_certificates_file" 
                                       id="product_certificates_file" 
                                       class="form-control" 
                                       accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" 
                                       onchange="validateProductFile(this, 'PDF/PNG/JPG/JPEG/DOCX/DOC')">
                                <span class="error-class" id="product-attachment-error"></span>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-danger">(PDF/Image/Document)</span>
                            </div>
                        </div>

                        {{-- Dealership --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">Dealership</label>
                            <div class="col-sm-4">
                                <input type="text" data-role="tagsinput"  class="form-control" name="product_dealership" id="dealership" value="{{ $product->dealership }}">
                            </div>
                        </div>
                        
                        {{-- Dealership Attachment --}}
                        <div class="mb-4 row align-items-center">
                            <label for="product_dealership_attachment" class="col-sm-3 col-form-label">Dealership Attachment</label>
                            <div class="col-sm-4">
                                <input type="file" 
                                       name="product_dealership_file" 
                                       id="product_dealership_file" 
                                       class="form-control" 
                                       accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" 
                                       onchange="validateProductFile(this, 'PDF/PNG/JPG/JPEG/DOCX/DOC')">
                                <span class="error-class" id="product_dealership_attachment_error"></span>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-danger">(PDF/Image/Document)</span>
                            </div>
                        </div>

                        
                        {{-- Packaging --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">Packaging<span class="text-danger"></span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="product_packaging" id="product_packaging"  value="{{ $product->packaging }}" maxlength="1700">
                            </div>
                        </div>
                        
                        {{-- Model No --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">Model No.<span class="text-danger"></span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="product_model_no"  id="Model No." value="{{ $product->model_no }}" maxlength="255">
                            </div>
                        </div>
                        
                        {{-- Guarantee/Warranty --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">Guarantee/Warranty<span class="text-danger"></span></label>
                            <div class="col-md-4">
                                <select name="prod_gorw" id="waranty_guarantee_type" class="form-select">
                                    <option value="Guarantee" {{ $product->gorw == 'Guarantee' ? 'selected' : '' }}>Guarantee</option>
                                    <option value="Waranty" {{ $product->gorw == 'Waranty' ? 'selected' : '' }}>Warranty</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex">
                                <label style="margin-right: 10px;margin-top: 10px;">Year</label>
                                <input type="text" class="form-control" name="product_gorw_year" id="product_gorw_year" value="{{ $product->gorw_year }}" placeholder="Ex. 1 Year" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,4)">
                            </div>
                            <div class="col-md-2 d-flex">
                                <label style="margin-right: 10px;margin-top: 10px;">Month</label>
                                <input type="number" class="form-control waranty_guarantee_value" name="product_gorw_month" id="waranty_guarantee_value" min="0" max="12" value="{{ $product->gorw_month }}"  placeholder="Ex. 1 Month" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,2); if(parseInt(this.value) > 12) this.value = '12';">
                            </div>
                        </div>
                        
                        {{-- Brand --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">Brand</label>
                            <div class="col-sm-4">
                                <input type="text" name="brand_name" class="form-control" id="brand_name"  value="{{ $product->brand }}" maxlength="255">
                            </div>
                        </div>
                        
                        {{-- Country of Origin --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">Country of Origin</label>
                            <div class="col-sm-4">
                                <div class="custom-file">
                                    <label class="radio-inline mr-3">
                                        <input type="radio" class="domestic" name="product_country_origin" value="domestic" checked> India 
                                    </label>
                                    <label class="radio-inline mr-3">
                                        <input type="radio" class="international" name="product_country_origin" value="international"> International
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.edit-products.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Verify Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
<script>
$(document).ready(function () {
    const editorConfig = {
        toolbar: [
            "heading", "bold", "italic", "bulletedList", "numberedList",
            "blockQuote", "insertTable", "mediaEmbed", "undo", "redo"
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

                    counter.classList.remove('text-muted', 'text-warning', 'text-danger');
                    if (count > MAX_CHARS) {
                        counter.classList.add('text-danger');
                        const trimmed = text.substring(0, MAX_CHARS);
                        editor.setData(trimmed);
                    } else if (count > 450) {
                        counter.classList.add('text-warning');
                    } else {
                        counter.classList.add('text-muted');
                    }

                    isProcessing = false;
                };

                updateCount();
                editor.model.document.on('change:data', updateCount);

                if (validateForm && errorSelector) {
                    document.querySelector('#general_information_form').addEventListener('submit', function (e) {
                        const content = editor.getData();
                        const text = content.replace(/<[^>]*>/g, '');
                        if (text.length > MAX_CHARS) {
                            e.preventDefault();
                            document.querySelector(errorSelector).textContent = 'Description must be 500 characters or less';
                        }
                    });
                }
            })
            .catch(error => {
                console.error(`Editor initialization error for ${selector}:`, error);
            });
    }

    // Initialize both editors
    initEditor('#product_description', '.pro-char-count', '#p_desc_err', true);
    initEditor('#product_specifications', '.spec-char-count');
});
</script>
<script>
$(document).ready(function () {
    $('#editVerifiedProductForm input').on('keyup change', function () {
        const fieldName = $(this).attr('name');
        $(`span.error-text.${fieldName}_error`).text('');
    });

    $('#editVerifiedProductForm').submit(function (e) {
        e.preventDefault();

        $('span.error-text').text('');

        const product_name = $('#product_name').val();
        const productId = $('#product_id').val().trim();
        const division = $('#division_id').val();
        const category = $('#category_id').val();  
        const product_hsn_code = $('#product_hsn_code').val();
        const product_gst = $('#product_gst').val();
        const product_dealer_type = $('#product_dealer_type').val();
        

        let hasErrors = false;
        
        // Product name
        if (!product_name) {
            $('#product-name-error').text('Please enter the product name.');
            hasErrors = true;
        } else if (product_name.length < 2) {
            $('#product-name-error').text('Product name must be at least 2 characters.');
            hasErrors = true;
        }

        if (!productId) {
            if (!division) {
                $('#product-division-error').text('Please select a division.');
                toastr.error('Division is Required.');
                hasErrors = true;
            } else {
                $('#product-division-error').text('');
            }

            if (!category) {
                $('#product-category-error').text('Please select a category.');
                toastr.error('Category is Required.');
                hasErrors = true;
            } else {
                $('#product-category-error').text('');
            }
        } else {
            // Clear any previous errors if product is selected
            $('#product-division-error').text('');
            $('#product-category-error').text('');
        }

        // HSN Code
        if (!product_hsn_code) {
            $('#product-hsn-code-error').text('Please enter the HSN code.');
            hasErrors = true;
        } else if (!/^\d{2,8}$/.test(product_hsn_code)) {
            $('#product-hsn-code-error').text('HSN code must be 2 to 8 digits.');
            hasErrors = true;
        }

        // GST
        if (!product_gst) {
            $('#product-gst-error').text('Please enter the GST percentage.');
            hasErrors = true;
        } else if (!/^\d{1,2}(\.\d{1,2})?$/.test(product_gst) || parseFloat(product_gst) > 100) {
            $('#product-gst-error').text('Enter a valid GST percentage (0-100).');
            hasErrors = true;
        }

        // Dealer Type
        if (!product_dealer_type) {
            $('#product-dealer-type-error').text('Please select a dealer type.');
            hasErrors = true;
        }


        if (hasErrors) return;

        $.ajax({
            url: "{{ route('admin.new-products.update', $product->id) }}",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                if (response.status == 1) {
                    toastr.success(response.message);
                    setTimeout(function () {
                        window.location.href = "{{ route('admin.new-products.index') }}";
                    }, 300);
                } else {
                    // Check if alias_error_msg exists in the response
                    if (response.alias_error_message && Array.isArray(response.alias_error_message)) {
                        let alias_error_html = '';

                        // Iterate over the error messages and create HTML to display them
                        response.alias_error_message.forEach(function(alias_error) {
                            alias_error_html += '<li><span class="text-danger">*</span> ' + alias_error + '</li>';
                        });
                        
                        // Display the error messages with HTML content
                        $(".product-alias-error-msg").html('<ul>' + alias_error_html + '</ul>');
                    }
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (key, value) {
                        const errorField = key.replace('.', '_');
                        $(`span.error-text.${errorField}_error`).text(value[0]);
                    });
                } else {

                    toastr.error('An error occurred. Please try again.');
                }
            }
        });
    });

    // Optional: Load categories dynamically when division changes
    $('#division_id').on('change', function () {
        var divisionId = $(this).val();
        $('#category_id').html('<option value="">Loading...</option>');
        $.get('{{ route("admin.getCategoriesByDivision") }}', { division_id: divisionId }, function (data) {
            let options = '<option value="">Select</option>';
            $.each(data.categories, function (index, cat) {
                options += `<option value="${cat.id}">${cat.category_name}</option>`;
            });
            $('#category_id').html(options);
        });
    });
});

$(document).ready(function () {
    const $input = $('#product_name');
    const $hiddenInput = $('#product_id');
    const $suggestionBox = $('#product_suggestions');
    let debounceTimer;

    $input.on('input', function () {
        const query = $(this).val().trim();
        clearTimeout(debounceTimer);
        $hiddenInput.val(''); // reset hidden input

        // Show dropdowns again if user is typing
        $('#division_id').closest('.row').show();
        $('#category_id').closest('.row').show();

        if (query.length < 2) {
            $suggestionBox.hide();
            return;
        }

        debounceTimer = setTimeout(function () {
            $.get('{{ route("admin.product.autocomplete") }}', { term: query }, function (data) {
                $suggestionBox.empty();

                if (data.length === 0) {
                    $suggestionBox.append('<div class="dropdown-item disabled">No products found</div>');
                } else {
                    $suggestionBox.append(
                        `<div class="dropdown-item disabled text-muted">Showing result for "<strong>${query}</strong>" — ${data.length} found</div>`
                    );
                    $.each(data, function (i, item) {
                        const $option = $('<div class="dropdown-item" style="cursor: pointer;"></div>').text(item.label);
                        $option.on('click', function () {
                            $input.val(item.label);
                            $hiddenInput.val(item.id);
                            $suggestionBox.hide();

                            // Hide Division and Category dropdowns
                            $('#division_id').closest('.row').hide();
                            $('#category_id').closest('.row').hide();

                        });
                        $suggestionBox.append($option);
                    });
                }

                $suggestionBox.show();
            });
        }, 300);
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#product_name, #product_suggestions').length) {
            $suggestionBox.hide();
        }
    });
});
</script>
@endsection