@extends('admin.layouts.app')
@section('title', 'View Product')
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
</style>
<div class="container-fluid">
    <div class="card shadow mb-4 mt-3">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary">Edit Verified Product</h5>
            <a href="{{ route('admin.verified-products.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="card-body">
            <form id="editVerifiedProductForm" enctype="multipart/form-data">
                
                @csrf
                @method('PUT')

                <input type="hidden" name="vendor_id" value="{{ $product->vendor_id }}">
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="prod_id" value="{{ $product->prod_id }}">
                <input type="hidden" name="vend_id" value="{{ $product->vend_id }}">

                <div class="pt-4">
                    <div class="basic-form">
                        {{-- Product Name --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">Product Name <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="product_name" id="product_name" value="{{ $product->product->product_name }}" readonly>
                                <span class="error_class text-danger" id="product_name_error"></span>
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
                                <span class="help-block text-danger" id="error_msg_product_image"></span>
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
                                <span class="error_class" id="product_description_error"></span>
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
                                <span class="error_class text-danger" id="product_dealer_type_error"></span>
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
                                <span class="error_class" id="product_uom_error"></span>
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
                                <span class="error_class text-danger" id="product_gst_error"></span>
                            </div>
                        </div>

                        {{-- HSN Code --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">HSN Code <span class="text-danger">*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="product_hsn_code" name="product_hsn_code" placeholder="HSN Code" value="{{ $product->hsn_code }}" maxlength="8" onkeypress="return event.charCode >= 48 && event.charCode <= 57" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                <span class="error_class text-danger" id="product_hsn_code_error"></span>
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
                                       name="product_catalog" 
                                       id="product_catalog" 
                                       class="form-control" 
                                       accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" 
                                       onchange="validateProductFile(this, 'PDF/PNG/JPG/JPEG/DOCX/DOC')">
                                <span class="help-block text-danger" id="error_msg_upload_catalog"></span>
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
                                       name="product_specifications_attachment" 
                                       id="product_specifications_attachment" 
                                       class="form-control" 
                                       accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" 
                                       onchange="validateProductFile(this, 'PDF/PNG/JPG/JPEG/DOCX/DOC')">
                                <span class="help-block text-danger" id="error_msg_upload_catalog1"></span>
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
                                       name="product_certification_attachment" 
                                       id="file" 
                                       class="form-control" 
                                       accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" 
                                       onchange="validateProductFile(this, 'PDF/PNG/JPG/JPEG/DOCX/DOC')">
                                <span class="error_class" id="product_attachment_error"></span>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-danger">(PDF/Image/Document)</span>
                            </div>
                        </div>

                        {{-- Dealership --}}
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-3 col-form-label">Dealership</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="product_dealership" id="dealership" data-role="tagsinput">
                            </div>
                        </div>
                        
                        {{-- Dealership Attachment --}}
                        <div class="mb-4 row align-items-center">
                            <label for="product_dealership_attachment" class="col-sm-3 col-form-label">Dealership Attachment</label>
                            <div class="col-sm-4">
                                <input type="file" 
                                       name="product_dealership_attachment" 
                                       id="product_dealership_attachment" 
                                       class="form-control" 
                                       accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" 
                                       onchange="validateProductFile(this, 'PDF/PNG/JPG/JPEG/DOCX/DOC')">
                                <span class="error_class" id="product_dealership_attachment_error"></span>
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
                                <select name="product_gorw" id="waranty_guarantee_type" class="form-select">
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
@endsection