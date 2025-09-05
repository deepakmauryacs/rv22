@extends('vendor.layouts.app_second',['title'=>'Change Password','sub_title'=>''])
@section('css')
<style>
.relative-input {
    position: relative;
}

.ids {
    position: absolute;
    top: 10px;
    right: 15px;
    cursor: pointer;
    color: black;
}
</style>
@endsection
@section('content')
<section class="container-fluid">
    <div class="d-flex align-items-center flex-wrap justify-content-between mr-auto flex py-2">
        <!-- Start Breadcrumb Here -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Change Password</li>
            </ol>
        </nav>
    </div>

    <!-- Start Profile Section Here -->
    <section class="rfq-user-profile">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 col-md-9 col-sm-12">
                <div class="card mb-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="row align-items-center">
                            <div class="col-12">
                                <h1 class="card-title font-size-18 mb-0">Change Password</h1>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="changePasswordForm" action="{{ route('vendor.password.change') }}" method="POST">
                        <!-- Section Change Password Form -->
                         @csrf
                        <div class="row gy-3">
                            <div class="form-group col-md-12">
                                <label for="password" class="mb-1">
                                    Current Password <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="password" oninput="this.value=this.value.replace(/[ ]/,'')" class="form-control password-input" id="password" name="password" placeholder="Enter current password">
                                    <button type="button" class="ra-btn ra-btn-link pw-icon-show-hide" aria-label="Toggle password visibility">
                                        <span class="bi bi-eye-slash-fill font-size-16" aria-hidden="true"></span>
                                    </button>
                                </div>
                                <div class="error text-danger-red password_error pt-2">
                                    
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <label for="new_password" class="mb-1">
                                    Change Password <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="password" onblur="password_check_new()" oninput="this.value=this.value.replace(/[ ]/,'')" class="form-control password-input" id="new_password" name="new_password" placeholder="Change password">
                                    <button type="button" class="ra-btn ra-btn-link pw-icon-show-hide" aria-label="Toggle password visibility">
                                        <span class="bi bi-eye-slash-fill font-size-16" aria-hidden="true"></span>
                                    </button>
                                </div>
                                <small id="passwordHelp" class="font-size-14">
                                    Password must be minimum 8 characters
                                </small>
                                <div class="error text-danger-red new_password_error pt-2">
                                     
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <label for="confirm_password" class="mb-1">
                                    Confirm Password <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="password" onblur="password_check_confirm()" oninput="this.value=this.value.replace(/[ ]/,'')" class="form-control password-input" id="confirm_password" name="confirm_password" placeholder="Confirm password">
                                    <button type="button" class="ra-btn ra-btn-link pw-icon-show-hide" aria-label="Toggle password visibility">
                                        <span class="bi bi-eye-slash-fill font-size-16" aria-hidden="true"></span>
                                    </button>
                                </div>
                                <div class="error text-danger-red confirm_password_error pt-2">
                                     
                                </div>
                            </div>
                            <div class="form-group col-md-12">
                                <div class="d-flex align-item-center justify-content-center gap-3 py-2">
                                    <button type="submit" class="ra-btn ra-btn-primary">
                                        <span class="bi bi-journal-bookmark font-size-12"></span>
                                        <span class="font-size-11">Save</span>
                                    </button>
                                    <a href="{{ route('vendor.dashboard') }}" class="ra-btn ra-btn-outline-danger">
                                        <span class="font-size-11">Cancel</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>
@endsection

@section('scripts')
<script>
// Start of Show Hide Icon
document.querySelectorAll('.pw-icon-show-hide').forEach(button => {
    button.addEventListener('click', () => {
      const input = button.parentElement.querySelector('.password-input');
      const icon = button.querySelector('span');

      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye-slash-fill');
        icon.classList.add('bi-eye');
      } else {
        input.type = 'password';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash-fill');
      }
    });
  });
// End of Show Hide Icon
$(document).ready(function() {
    // Clear error messages on input change
    $('#changePasswordForm input, #changePasswordForm select').on('keyup change', function() {
        const fieldName = $(this).attr('name');
        $(`div.text-danger-red.${fieldName}_error`).text('');
    });

    $('#changePasswordForm').submit(function(e) {
        e.preventDefault();

        // Clear previous errors
        $('div.text-danger-red').text('');
        // Get form values
        const password = $('#password').val().trim();
        const new_password = $('#new_password').val().trim();
        const confirm_password = $('#confirm_password').val().trim();

        let hasErrors = false;

        // Client-side validation
        if (!password) {
            $('div.text-danger-red.password_error').text('Current Password Required!');
            hasErrors = true;
        }
        if (password.length < 6) {
            $('div.text-danger-red.password_error').text(
                'Old password must be 6 digit or greater then 6 digit!');
            hasErrors = true;
        }
        if (!new_password) {
            $('div.text-danger-red.new_password_error').text(
                'Change Password Required! Password must be minimum 8 characters');
            hasErrors = true;
            $('#password-msg').hide();
        }
        if (new_password.length < 6) {
            $('div.text-danger-red.new_password_error').text('Password Must Be Greater Than 6 Digits.');
            hasErrors = true;
            $('#password-msg').hide();
        }

        if (!confirm_password) {
            $('div.text-danger-red.confirm_password_error').text('Confirm Password Required!');
            hasErrors = true;
        }

        if (!confirm_password != new_password) {
            $('div.text-danger-red.confirm_password_error').text(
                'Change Password And Confirm Password Must Be Match!');
            hasErrors = true;
        }
        // If there are errors, stop form submission
        if (hasErrors) {
            return false;
        }

        // Proceed with AJAX submission
        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: new FormData(this),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    window.location.href = response.redirectUrl;
                } else {
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
</script>
@endsection