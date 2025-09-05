@extends('buyer.layouts.app',['title'=>'Help and Support','sub_title'=>'Edit'])

@section('css')
<style>
.upload-info-tooltip {
    position: absolute;
    right: 10px;
    top: 6px;
}
</style>
@endsection

@section('content')
<div class="bg-white">
    <!---Sidebar-->
    @include('buyer.layouts.sidebar-menu')
</div>

<main class="main flex-grow-1">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-sm-7 pt-0 pt-sm-4 mt-0 mt-sm-4 mx-auto">
                <div class="card">
                    <div class="card-header bg-transparent feedback-card-spacing">
                        <h1 class="font-size-18 mb-0 py-3">Support</h1>
                    </div>

                    <div class="card-body feedback-card-spacing">
                        <form action="{{route('buyer.help_support.store')}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6 mt-3">
                                    <div class="input-group">
                                        <span class="input-group-text"><span class="bi bi-shop"></span></span>
                                        <div class="form-floating">
                                            <select class="form-select" id="issue_type" name="issue_type">
                                                <option selected=""> Select Issue </option>
                                                <option value="CIS Sheet Issue">CIS Sheet Issue</option>
                                                <option value="RFQ Received">Compose RFQ Issue</option>
                                                <option value="RFQ Received">Bulk RFQ Issue</option>
                                                <option value="RFQ Received">RFQ Received</option>
                                                <option value="Confirm Order">Confirm Order</option>
                                                <option value="Product Issue">Product Issue</option>
                                            </select>
                                            <label>Issue Type:</label>
                                        </div>
                                        @error('issue_type')
                                        <span class="text-danger error-text">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-3">
                                    <div class="form-group position-relative pb-2 pb-sm-0">
                                        <div class="simple-file-upload file-browse">
                                            <input type="file"
                                                onchange="validateImageFile(this, 'PDF/PNG/JPG/JPEG/DOCX/XLSX')"
                                                id="image" name="image" class="real-file-input" style="display: none;">
                                            <div class="file-display-box form-control text-start font-size-12 text-dark"
                                                role="button" data-bs-toggle="tooltip" data-bs-placement="top">
                                                Upload File
                                            </div>
                                            <div class="upload-info-tooltip">
                                                <span title="" class="custom-tooltip text-danger"
                                                    data-tooltip="Max file: size 1MB,File type: ( PDF, DOC, Excel, Image, CDR, DWG)">
                                                    <i class="bi bi-question-circle font-size-16"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <span class="text-danger error-text"></span>
                                        <!-- <div class="uploaded-file-display">
                                            <div class="d-flex align-items-center">
                                                <span class="uploaded-file-info d-inline-block text-truncate">
                                                    <a class="file-links text-green font-size-12"
                                                        href="javascript:void(0)" target="_blank" download="Download">
                                                        uploaded-file-name.pdf
                                                    </a>
                                                </span>
                                            </div>
                                        </div> -->
                                        @error('image')
                                        <span class="text-danger error-text">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <label>Description</label>
                                    <textarea id="description" name="description" class="form-control height-inherit"
                                        rows="3"></textarea>
                                    @error('description')
                                    <span class="text-danger error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-12 mt-3 text-center text-sm-end">
                                    <button type="submit" class="ra-btn small-btn ra-btn-primary my-1">
                                        <span class="bi bi-send"></span> Send</button>
                                    <a href="{{ route('buyer.help_support.index') }}"
                                        class="ra-btn small-btn ra-btn-outline-primary-light my-1">Cancel</a>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@endsection

@section('scripts')
<script>
$(document).on("click", ".remove-rfq-file", function() {
    $(".bulk-rfq-input").val('');
    $(".bulk-rfq-input").attr('src', '');
    $(".rfq-file-name").addClass('d-none').html('');
    $(".remove-rfq-file").addClass('d-none');
    $(".invalid-product-row").html('');
    $(".invalid-product-section").addClass('d-none');
});

function validateImageFile(obj, ext) {
    var avatarok = 1;
    var avatar = $(obj).val();
    var extension = avatar.split('.').pop().toUpperCase();
    checkExtension = ext.split('/');
    if (checkExtension.indexOf(extension) < 0) { // || extension != "XLS"//XLSB
        console.log("file extension: ", extension, avatar);
        $(obj).val('');
        $(obj).attr('src', '');
        $(".rfq-file-name").addClass('d-none').html('');
        $(".remove-rfq-file").addClass('d-none');
        appendFileError(obj, "Invalid file extension.");
    } else {
        $(".rfq-file-name").removeClass('d-none').html(avatar.split('\\').pop());
        $(".remove-rfq-file").removeClass('d-none');
        appendFileError(obj);
    }
}

function validateAttachment(obj, ext) {
    var avatarok = 1;
    var avatar = $(obj).val();
    var extension = avatar.split('.').pop().toUpperCase();
    checkExtension = ext.split('/');
    if (checkExtension.indexOf(extension) < 0) { // || extension != "XLS"//XLSB
        console.log("file extension: ", extension, avatar);
        $(obj).val('');
        $(obj).attr('src', '');
        $(".rfq-file-name").addClass('d-none').html('');
        $(".remove-rfq-file").addClass('d-none');
        appendFileError(obj, "Invalid file extension.");
    } else {
        $(".rfq-file-name").removeClass('d-none').html(avatar.split('\\').pop());
        $(".remove-rfq-file").removeClass('d-none');
        appendFileError(obj);
    }
}

function appendFileError(obj, msg = '') {
    console.log($(obj));
    $(obj).parents('.file-browse').parent().find('.error-message').remove();
    if (msg) {
        $(obj).parents('.file-browse').parent().append('<span class="help-block text-danger error-message">' + msg +
            '</span>');
    }
}
</script>
@endsection