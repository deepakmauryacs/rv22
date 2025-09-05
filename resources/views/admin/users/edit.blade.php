@extends('admin.layouts.app_second', [
    'title' => 'Edit User',
    'sub_title' => 'Update'
])

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management </a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit User</li>
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
                            <form id="updateUserForm" class="form-horizontal form-material">
                                @csrf
                                @method('PUT')
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label"><strong>Name</strong> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" oninput="limitCharacters(this, 255)">
                                        <span class="text-danger error-text name_error"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="designation" class="form-label"><strong>Designation</strong> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="designation" name="designation" value="{{ old('designation', $user->designation) }}" oninput="limitCharacters(this, 255)">
                                        <span class="text-danger error-text designation_error"></span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="mobile" class="form-label"><strong>Mobile No.</strong> <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select class="form-control" id="country_code" name="country_code" style="max-width: 120px;">
                                                @foreach($countries as $country)
                                                    <option value="+{{ $country->phonecode }}" {{ old('country_code', $user->country_code) == "+{$country->phonecode}" ? 'selected' : '' }}>
                                                        {{ $country->name }} (+{{ $country->phonecode }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="text" class="form-control" id="mobile" name="mobile" value="{{ old('mobile', $user->mobile) }}" oninput="limitCharacters(this, 15)">
                                        </div>
                                        <span class="text-danger error-text mobile_error"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label"><strong>Email Address</strong> <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" oninput="limitCharacters(this, 255)">
                                        <span class="text-danger error-text email_error"></span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="role_id" class="form-label"><strong>User Role</strong> <span class="text-danger">*</span></label>
                                        <select class="form-control" id="role_id" name="role_id">
                                            <option value="">Select User Role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ old('role_id', $user->role_mapping->user_role_id ?? '') == $role->id ? 'selected' : '' }}>
                                                    {{ $role->role_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger error-text role_id_error"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="profile_image" class="form-label"><strong>Profile Image</strong></label>
                                        <input type="file" class="form-control" id="profile_image" name="profile_image">
                                        @if($user->profile_image)
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/' . $user->profile_image) }}" alt="Current Profile Image" class="current-image">
                                                <p class="mt-1 small">Current Image</p>
                                            </div>
                                        @endif
                                        <span class="text-danger error-text profile_image_error"></span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label d-block"><strong>Status</strong> <span class="text-danger">*</span></label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="active" value="1" {{ old('status', $user->status) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="active">Active</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="inactive" value="2" {{ old('status', $user->status) == 2 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="inactive">Inactive</label>
                                        </div>
                                        <span class="text-danger error-text status_error"></span>
                                    </div>
                                </div>
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="submit" class="btn-rfq btn-rfq-primary">Update</button>
                                    <a href="{{ route('admin.users.index') }}" class="btn-rfq btn-rfq-danger">Cancel</a>
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
    // Clear error messages on input change
    $('#updateUserForm input, #updateUserForm select').on('keyup change', function() {
        const fieldName = $(this).attr('name');
        $(`span.error-text.${fieldName}_error`).text('');
    });

    // Form submission
    $('#updateUserForm').submit(function (e) {
        e.preventDefault();

        // Clear previous errors
        $('span.error-text').text('');

        // Get form values
        const name = $('#name').val().trim();
        const designation = $('#designation').val().trim();
        const country_code = $('#country_code').val().trim();
        const mobile = $('#mobile').val().trim();
        const email = $('#email').val().trim();
        const role_id = $('#role_id').val();
        const profile_image = $('#profile_image').val();
        const status = $('input[name="status"]:checked').val();

        let hasErrors = false;

        // Client-side validation
        if (!name) {
            $('span.error-text.name_error').text('Please enter the name.');
            hasErrors = true;
        } else if (name.length < 2) {
            $('span.error-text.name_error').text('Name must be at least 2 characters.');
            hasErrors = true;
        }

        if (!designation) {
            $('span.error-text.designation_error').text('Please enter the designation.');
            hasErrors = true;
        } else if (designation.length < 2) {
            $('span.error-text.design  designation_error').text('Designation must be at least 2 characters.');
            hasErrors = true;
        }

        if (!country_code) {
            $('span.error-text.mobile_error').text('Please select a country code.');
            hasErrors = true;
        }

        if (!mobile) {
            $('span.error-text.mobile_error').text('Please enter the mobile number.');
            hasErrors = true;
        } else if (!/^\d{7,15}$/.test(mobile)) {
            $('span.error-text.mobile_error').text('Mobile number must be between 7 and 15 digits.');
            hasErrors = true;
        }

        if (!email) {
            $('span.error-text.email_error').text('Please enter the email address.');
            hasErrors = true;
        } else if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
            $('span.error-text.email_error').text('Please enter a valid email address.');
            hasErrors = true;
        }

        if (!role_id) {
            $('span.error-text.role_id_error').text('Please select a user role.');
            hasErrors = true;
        }

        if (!status) {
            $('span.error-text.status_error').text('Please select a status.');
            hasErrors = true;
        }

        if (hasErrors) return;

        // AJAX submission
        $.ajax({
            url: "{{ route('admin.users.update', $user->id) }}",
            type: "POST",
            data: new FormData(this),
            contentType: false,
            processData: false,
            dataType: "json",
            beforeSend: function() {
                $('#updateUserForm').find('button[type="submit"]').prop('disabled', true);
            },
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => {
                        window.location.href = "{{ route('admin.users.index') }}";
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
            },
            complete: function() {
                $('#updateUserForm').find('button[type="submit"]').prop('disabled', false);
            }
        });
    });

    // Limit characters function
    window.limitCharacters = function(element, maxChars) {
        if (element.value.length > maxChars) {
            element.value = element.value.substr(0, maxChars);
        }
    };
});
</script>
@endsection