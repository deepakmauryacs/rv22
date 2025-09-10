@extends('admin.layouts.app_second',['title' => 'Vendor','sub_title' => 'Primary Contact'])
@section('breadcrumb')
<style type="text/css">
    /* Form group styling */
    .form-group.col-md-6.mb-3 {
        margin-bottom: 1rem !important;
    }
    /* Text label styling */
    .form-group .text-dark {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #212529;
    }
    /* File browse container */
    .file-browse {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    /* Browse button styling */
    .button.button-browse {
        position: relative;
        display: inline-block;
        padding: 0.375rem 0.75rem;
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        color: #495057;
        font-size: 1rem;
        line-height: 1.5;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        margin-right: 5px;
    }
    .button.button-browse:hover {
        background-color: #e2e6ea;
        border-color: #dae0e5;
    }
    /* Hide the actual file input */
    .button.button-browse input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    /* Readonly input field */
    .file-browse .form-control {
        flex: 1;
        background-color: #fff;
    }
    /* File link styling */
    .file-links {
        display: inline-flex;
        align-items: center;
        color: #0d6efd;
        text-decoration: none;
        margin-top: 0.25rem;
    }
    .file-links:hover {
        text-decoration: underline;
    }
    /* Info icon styling */
    .bi.bi-info-circle-fill {
        margin-left: 0.25rem;
        color: #6c757d;
        font-size: 0.875rem;
    }
    /* Responsive adjustments */
    @media (max-width: 767.98px) {
        .form-group.col-md-6.mb-3 {
            width: 100%;
        }
    }
    #verify-buyer-profile .spinner-border {
        height: 14px;
        width: 14px;
    }
</style>
<div class="breadcrumb-header">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page">
                    <a href="{{ route('admin.vendor.index') }}"> Vendor </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Primary Contact</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
  <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8 col-md-9 col-sm-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h4 class="card-title mb-0">Primary Contact</h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if($success = Session::get('success'))
                        <div class="alert alert-success">
                            <p>{{ $success }}</p>
                        </div>
                    @endif
                    <form id="vendor-profile-form" action="{{ route('admin.vendor.primaryContactDetailsUpdate') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-12  mb-3">
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <label for="name" class="form-label"><strong>Name</strong> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" oninput="limitCharacters(this, 255)">
                                <span class="text-danger error-text name_error"></span>
                            </div>
                            <div class="col-md-12  mb-3">
                                <label for="email" class="form-label"><strong>Email Address</strong> <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" oninput="limitCharacters(this, 255)">
                                <span class="text-danger error-text email_error"></span>
                            </div>
                            <div class="col-md-12  mb-3">
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
                            <div class="col-md-12  mb-3 text-end">
                                <input type="submit" class="btn btn-primary" value="Save">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
     window.limitCharacters = function(element, maxChars) {
        if (element.value.length > maxChars) {
            element.value = element.value.substr(0, maxChars);
        }
    };
</script>
@endsection
