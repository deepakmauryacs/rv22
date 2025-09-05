@extends('admin.layouts.app_second', [
    'title' => 'Division',
    'sub_title' => 'Add Division',
])

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.divisions.index') }}">Division List </a></li>
                <li class="breadcrumb-item active" aria-current="page">Add Division</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
<div class="page-start-section">
<div class="container-fluid">
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="basic-form pro_edit">
                    <form id="createDivisionForm" class="form-horizontal form-material">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="division_name" class="form-label"><strong>Division Name</strong> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control capital" id="division_name" name="division_name" oninput="limitCharacters(this,255)">
                            <span class="text-danger error-text division_name_error"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block"><strong>Status</strong></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="active" value="1" checked>
                                <label class="form-check-label" for="active">Active</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="inactive" value="2">
                                <label class="form-check-label" for="inactive">Inactive</label>
                            </div>
                            <span class="text-danger error-text status_error"></span>
                        </div>
                    </div>

                    <div class="d-flex gap-1 justify-content-center">
                        <button type="submit" class="btn-rfq btn-rfq-primary">Save</button>
                        <a href="{{ route('admin.divisions.index') }}" class="btn-rfq btn-rfq-danger">Cancel</a>
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
<script>
$(document).ready(function () {
    $('#createDivisionForm input').on('keyup change', function() {
        const fieldName = $(this).attr('name');
        $(`span.error-text.${fieldName}_error`).text('');
    });

    $('#createDivisionForm').submit(function (e) {
        e.preventDefault();

        $('span.error-text').text('');
        const division_name = $('#division_name').val().trim();
        const status = $('input[name="status"]:checked').val();
        let hasErrors = false;

        if (!division_name) {
            $('span.error-text.division_name_error').text('Please enter the division name.');
            hasErrors = true;
        } else if (division_name.length < 2) {
            $('span.error-text.division_name_error').text('Division name must be at least 2 characters.');
            hasErrors = true;
        }

        if (!status) {
            $('span.error-text.status_error').text('Please select a status.');
            hasErrors = true;
        }

        if (hasErrors) return;

        $.ajax({
            url: "{{ route('admin.divisions.store') }}",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => {
                        window.location.href = "{{ route('admin.divisions.index') }}";
                    }, 300);
                } else {
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
});
</script>
@endsection
