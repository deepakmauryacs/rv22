@extends('buyer.layouts.app', ['title'=>'Change Password'])

@section('css')
<style>
.error-msg {
    color: red;
}
</style>
@endsection

@section('content')
<div class="bg-white">
    <!---Sidebar-->
    @include('buyer.layouts.sidebar-menu')
</div>

<!---Section Main-->
<main class="main flex-grow-1">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-sm-7 col-md-6 col-lg-5 col-xl-4 mx-auto">
                <div class="card change-password rounded-4 overflow-hidden mt-md-5 mb-0">
                    <div class="card-header bg-white py-3 py-md-4 px-md-4 px-lg-5">
                        <h1 class="font-size-16 mb-0 text-uppercase text-deep-blue">Change Password</h1>
                    </div>
                    <div class="card-body py-4 px-md-4 px-lg-5">
                        <form action="{{ route('buyer.setting.update-password') }}" method="POST"
                            id="change-password-form">
                            @csrf
                            @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                            @endif
                            @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                            @endif
                            <div class="mb-3">
                                <label>Current Passwsord<span class="text-danger"> * </span></label>
                                <div class="password-type-input">
                                    <input type="password" class="form-control pe-5" name="currentpassword"
                                        id="currentpassword" placeholder="Current Passwsord" value="">
                                    <span class="bi bi-eye-slash" id="passwordEye1"
                                        onclick="passwordHideShow('currentpassword','passwordEye1')"></span>
                                </div>
                                @if($errors->has('currentpassword'))
                                <span class="error-msg error_msg_current_password">{{ $errors->first('currentpassword') }}</span>
                                @endif
                                <span class="error-msg error_currentpassword"></span>
                            </div>
                            <div class="mb-3">
                                <label>Change Password<span class="text-danger"> * </span></label>
                                <div class="password-type-input">
                                    <input type="password" class="form-control pe-5" name="changepassword"
                                        id="changepassword" placeholder="Change Password" value="">
                                    <span class="bi bi-eye-slash" id="passwordEye2"
                                        onclick="passwordHideShow('changepassword','passwordEye2')"></span>
                                </div>
                                @if($errors->has('changepassword'))
                                <span class="error-msg error_msg_change_password">{{ $errors->first('changepassword') }}</span>
                                @endif
                                 
                                <span class="error-msg error_changepassword"></span>
                            </div>
                            <div class="mb-3">
                                <label>Confirm Passwsord<span class="text-danger"> * </span></label>
                                <div class="password-type-input">
                                    <input type="password" class="form-control pe-5" name="confirmpassword"
                                        id="confirmpassword" placeholder="Confirm Passwsord" value="">
                                    <span class="bi bi-eye-slash" id="passwordEye3"
                                        onclick="passwordHideShow('confirmpassword','passwordEye3')"></span>
                                </div>
                                @if($errors->has('confirmpassword'))
                                <span class="error-msg error_msg_confirm_password">{{ $errors->first('confirmpassword') }}</span>
                                @endif
                                <span class="error-msg error_confirmpassword"></span>
                            </div>
                            <div class="ms-auto d-table">
                                <button type="button" onclick="validateForm()" class="ra-btn small-btn ra-btn-primary">
                                    Submit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('scripts')

<script>
 
function validateForm() {
    var status = 0;

    var currentpassword = $('#currentpassword').val().trim();
    var changepassword = $('#changepassword').val().trim();
    var confirmpassword = $('#confirmpassword').val().trim();

    // Clear all error messages initially
    $(".error-msg").text("");
    // Validate Current Password
    if (currentpassword === '') {
        status++;
        $(".error_currentpassword").text("Current Password Required!");
    } else if (currentpassword.length < 6) {
        status++;
        $(".error_currentpassword").text("Current password must be 6 characters or more!");
    }

    // Validate Change Password
    if (changepassword === '') {
        status++;
        $(".error_changepassword").text("Change Password Required!");
    } else if (changepassword.length < 8) {
        status++;
        $(".error_changepassword").text("Change password must be 8 characters or more!");
    }

    // Validate Confirm Password
    if (confirmpassword === '') {
        status++;
        $(".error_confirmpassword").text("Confirm Password Required!");
    } else if (confirmpassword.length < 8) {
        status++;
        $(".error_confirmpassword").text("Confirm password must be 8 characters or more!");
    } else if (confirmpassword !== changepassword) {
        status++;
        $(".error_msg_confirm_password").text("Change Password and Confirm Password do not match!");
    }

    // Prevent form submission if there are validation errors
    if (status > 0) {
        return false;
    }

    // All validations passed, submit the form
    $('#change-password-form').submit();
    return false; // Prevent default form submission
}
</script>
@endsection