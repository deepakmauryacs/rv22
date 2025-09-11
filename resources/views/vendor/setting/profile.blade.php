@extends('vendor.layouts.app_second', ['title' => 'Vendor Profile', 'sub_title' => 'Create'])

@section('css')
    <style>
        span.tmd-serial-no,
        span.branch-serial-no {
            font-size: 16px;
        }
        #submit-vendor-profile .spinner-border {
            height: 14px;
            width: 14px;
        }
    </style>
@endsection

@section('content')
    @php
        $vendor = $vendor_data->vendor;
        $branchDetails = $vendor_data->branchDetails;

        $is_profile_verified = false;
        if ($vendor_data->is_profile_verified == 1) {
            $is_profile_verified = true;
        }
    @endphp
    <section class="container-fluid">
        <div class="d-flex align-items-center flex-wrap justify-content-between mr-auto flex py-2">
            <!-- Start Breadcrumb Here -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Profile</li>
                </ol>
            </nav>
        </div>
        <section class="rfq-user-profile">
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <div class="row align-items-center">
                        <div class="col-8 col-sm-3 order-2 order-sm-1">
                            <h1 class="card-title font-size-18 mb-0">1. Company Details</h1>
                        </div>
                        <div class="col-12 col-sm-6 order-1 order-sm-2 text-center">
                            <h2 class="profile-title py-2">For Vendors who are supplying to Steel Plants</h2>
                        </div>
                        <div class="col-4 col-sm-3 order-3 order-sm-3 text-end" id="editEnableDesable">
                            <button type="button" onclick="disableEnabled();" class="ra-btn ra-btn-outline-primary-light font-size-12">
                                <span class="font-size-11">Edit</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="vendor-profile-form" action="{{ route('vendor.save-vendor-profile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <!-- Section Company Details -->
                        <div class="company-details">
                            <div class="row gy-4">
                            <div class="form-group col-md-6">
                                <label for="companyName" class="mb-1">
                                    Company Name / Legal Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control required text-upper-case mng-input"
                                    value="{{ $vendor->legal_name }}" placeholder="Enter Company Name / Legal Name"
                                    name="legal_name" id="legal_name"
                                    oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" maxlength="255">
                            </div>

                            <div class="form-group col-md-6 position-relative">
                                <label for="companyLogo" class="mb-1">
                                    Company Logo <span class="text-danger">(JPG, PNG, JPEG)
                                    </span>
                                </label>

                                <div class="simple-file-upload">
                                    <input type="hidden" name="profile_img_old" value="{{ $vendor->profile_img }}">
                                    <input type="file" id="companyLogo" class="real-file-input mng-input" style="display: none;"
                                        name="profile_img" onchange="validateFile(this, 'JPG/JPEG/PNG')">
                                    <div class="file-display-box form-control text-start font-size-12 text-dark"
                                        role="button" data-bs-toggle="tooltip" data-bs-placement="top">
                                        Upload Profile Image
                                    </div>
                                </div>
                                @if (is_file(public_path('uploads/vendor-profile/' . $vendor->profile_img)))
                                    <div class="uploaded-file-display">
                                        <div class="d-flex align-items-center">
                                            <span class="uploaded-file-info d-inline-block text-truncate">
                                                <a class="file-links text-green"
                                                    href="{{ url('public/uploads/vendor-profile/' . $vendor->profile_img) }}"
                                                    target="_blank" download="{{ $vendor->profile_img }}">
                                                    {!! strlen($vendor->profile_img) > 30 ? substr($vendor->profile_img, 0, 25): $vendor->profile_img !!}
                                                </a>
                                            </span>
                                            <button type="button"
                                                class="ra-btn ra-btn-link text-green font-size-14 height-inherit"
                                                data-bs-toggle="tooltip" data-placement="top"
                                                data-bs-original-title="{{ $vendor->profile_img }}">
                                                <span class="bi bi-info-circle-fill font-size-14"></span>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="form-group col-md-6">
                                <label for="dateOfIncorporation" class="mb-1">Date of Incorporation(DD/MM/YYYY)
                                    <span class="text-danger">*</span></label>
                                <input type="text" class="form-control required date-masking mng-input" placeholder="DD/MM/YYYY"
                                    onblur="validateDateFormat(this, true);" id="date_of_incorporation"
                                    name="date_of_incorporation"
                                    value="{{ !empty($vendor->date_of_incorporation) ? date('d/m/Y', strtotime($vendor->date_of_incorporation)) : '' }}"
                                    maxlength="10">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="nature_of_organization" class="mb-1">Nature of Organization <span
                                        class="text-danger">*</span></label>
                                <select id="nature_of_organization" name="nature_of_organization"
                                    class="form-select required mng-input">
                                    @if (!empty($nature_of_organization))
                                        @foreach ($nature_of_organization as $id => $name)
                                            <option value="{{ $id }}"
                                                {{ $vendor->nature_of_organization == $id ? 'selected' : '' }}>
                                                {{ $name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="nature_of_business" class="mb-1">Nature of Business <span
                                        class="text-danger">*</span></label>
                                <select id="nature_of_business" name="nature_of_business" class="form-select required mng-input">
                                    @if (!empty($nature_of_business))
                                        @foreach ($nature_of_business as $id => $name)
                                            <option value="{{ $id }}"
                                                {{ $vendor->nature_of_business == $id ? 'selected' : '' }}>
                                                {{ $name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="otherContactDetails" class="mb-1">
                                    Other Contact Details
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control mng-input" placeholder="Enter Other Contact Details"
                                    oninput="this.value = this.value.replace(/[^0-9,\-\/ ]/g, '')"
                                    id="other_contact_details" name="other_contact_details"
                                    value="{{ $vendor->other_contact_details }}" maxlength="255">
                            </div>
                            <div class="form-group col-md-12">
                                <label for="registeredAddress" class="mb-1">
                                    Registered Address
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea id="registered_address" maxlength="1700" name="registered_address"
                                    class="form-control registered_address required mng-input" rows="3">{{ $vendor->registered_address }}</textarea>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="country" class="mb-1">Country<span class="text-danger">*</span></label>
                                <select
                                    onchange="getState('organization-country', 'organisation-state', 'organisation-city')"
                                    name="country" class="form-select required organization-country mng-input" autocomplete="off">
                                    @php
                                        if (empty($vendor->country)) {
                                            $vendor->country = 101;
                                        }
                                    @endphp
                                    @if (!empty($countries))
                                        @foreach ($countries as $country_id => $country_name)
                                            <option value="{{ $country_id }}"
                                                {{ $vendor->country == $country_id ? 'selected' : '' }}>
                                                {{ $country_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="state" class="mb-1">State<span class="text-danger">*</span></label>
                                <select id="state" name="state" class="form-select required organisation-state mng-input">
                                    {!! getStateByCountryId($vendor->country, $vendor->state ?? 0) !!}
                                </select>
                            </div>

                            {{-- <div class="form-group col-md-6">
                            <label for="city" class="mb-1">City <span class="text-danger">*</span></label>
                            <select id="city" name="city" class="form-select required organisation-city">
                                 {!! !empty($vendor->state) ? getCityByStateId($vendor->state, $vendor->city??0) : '' !!}
                            </select>
                        </div> --}}

                            <div class="form-group col-md-6">
                                <label for="pincode" class="mb-1">Pin Code
                                    <span class="text-danger">*</span></label>
                                <input type="text" class="form-control organisation-pincode required mng-input"
                                    placeholder="Enter Pin Code" name="pincode" value="{{ $vendor->pincode }}"
                                    minlength="6" maxlength="6"
                                    oninput="this.value=this.value.replace(/[^0-9.\&\(\)\+,\- ]/,'')">
                            </div>

                            <div class="form-group col-md-6">
                                <div class="row">
                                    <div class="form-group col-md-5">
                                        <label for="gstVat"
                                            class="mb-1">{{ $vendor->country == 101 ? 'GSTIN/VAT' : 'Please enter your Tax Identification Number' }}
                                            <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control gstin-vat required mng-input" name="gstin"
                                            value="{{ $vendor->gstin }}"
                                            oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')"
                                            maxlength="15"
                                            placeholder="{{ $vendor->country == 101 ? 'Enter GSTIN/VAT' : 'Enter your Tax Identification Number' }}">
                                    </div>
                                    <div class="form-group col-md-7 position-relative">
                                        <label for="gstVatDoc" class="mb-1">GSTIN/VAT Doc.
                                            <span class="text-danger">* (PDF,JPG,JPEG,PNG)</span>
                                        </label>

                                        <div class="simple-file-upload">
                                            <input type="hidden" name="gstin_document_old" value="{{ $vendor->gstin_document }}">
                                            <input type="file" onchange="validateFile(this, 'JPG/JPEG/PDF/PNG')"
                                                class="{{ $vendor->gstin_document == '' || $vendor->gstin_document == null ? 'required-file' : '' }} real-file-input mng-input"
                                                name="gstin_document" style="display: none;">
                                            <div class="file-display-box form-control text-start font-size-12 text-dark"
                                                role="button" data-bs-toggle="tooltip" data-bs-placement="top">
                                                Upload GST/VAT Document
                                            </div>
                                        </div>
                                        @if (is_file(public_path('uploads/vendor-profile/' . $vendor->gstin_document)))
                                            <div class="uploaded-file-display">
                                                <div class="d-flex align-items-center">
                                                    <span class="uploaded-file-info d-inline-block text-truncate">
                                                        <a class="file-links text-green"
                                                            href="{{ url('public/uploads/vendor-profile/' . $vendor->gstin_document) }}"
                                                            target="_blank" download="{{ $vendor->gstin_document }}">
                                                            {!! strlen($vendor->gstin_document) > 30 ? substr($vendor->gstin_document, 0, 25) : $vendor->gstin_document !!}
                                                        </a>
                                                    </span>
                                                    <button type="button"
                                                        class="ra-btn ra-btn-link text-green font-size-14 height-inherit"
                                                        data-bs-toggle="tooltip" data-placement="top"
                                                        data-bs-original-title="{{ $vendor->gstin_document }}">
                                                        <span class="bi bi-info-circle-fill font-size-14"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="webSite" class="mb-1">Website</label>
                                <input type="text" class="form-control website-url mng-input" placeholder="Enter Website"
                                    name="website" value="{{ $vendor->website }}" maxlength="255">
                            </div>

                            <div class="col-12">
                                <div class="mt-3">- Mention the name of 2 of your customers (must be Steel
                                    Plants)</div>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="customerName1" class="mb-1">Customer Name 1
                                    <span class="text-danger">*</span></label>
                                <input type="text" class="form-control required mng-input" placeholder="Enter Customer Name 1"
                                    maxlength="255" name="company_name1" value="{{ $vendor->company_name1 }}">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="customerName2" class="mb-1">Customer Name 2
                                    <span class="text-danger">*</span></label>
                                <input type="text" class="form-control required mng-input" placeholder="Enter Customer Name 2"
                                    maxlength="255" name="company_name2" value="{{ $vendor->company_name2 }}">
                            </div>

                            <div class="col-12">
                                <div class="mt-3">
                                    <strong>- Mention the name of your TOP 3 PRODUCTS</strong> (Please Note:
                                    After your profile is
                                    verified, you will be able to add ALL your products with details).
                                </div>
                            </div>
                            @php
                                $organization_product_name = explode('@#', $vendor->registered_product_name);
                            @endphp
                            <div class="form-group col-md-6">
                                <label for="productName" class="mb-1">Product Name
                                    <span class="text-danger">*</span></label>
                                <input type="text" class="form-control required mng-input" placeholder="Enter Product Name"
                                    maxlength="350" name="registered_product_name" oninput="removeCharacters(this, '#')"
                                    onblur="removeCharacters(this, '#')"
                                    value="{{ implode(', ', $organization_product_name) }}">
                            </div>
                        </div>
                    </div>
                    @if ($is_profile_verified)
                        <hr class="mt-4">
                        <!-- Section Branch Details -->
                        <div class="branch-details">
                            <div class="row gy-3 mt-3 align-items-center">
                                <div class="col-xl-3 col-sm-12 mb-1">
                                    <h3 class="font-size-18 mb-0">2. Branch Details</h3>
                                </div>
                                <div class="col-xl-6 col-sm-12 mb-1 fw-bold">
                                    Note: Click on “ADD BRANCH” only if you have any additional branch.
                                </div>
                                <div class="col-xl-3 col-sm-12 mb-1">
                                    <button type="button" class="ra-btn ra-btn-outline-primary-light mng-input"
                                        onclick="addMoreBranchFields()"> <span class="bi bi-plus-lg"
                                            aria-hidden="true"></span> Add Branch</button>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <h3 class="py-1 px-2 my-2 border font-size-16">Branch Information</h3>
                                </div>
                            </div>
                            <div id="branch_container">
                                @if (!empty($branchDetails) && count($branchDetails) > 0)
                                    @foreach ($branchDetails as $k => $branch)
                                        <div class="row gy-4 branch-row" data-row-id="{{ $k }}">
                                            <div class="form-group col-md-6">
                                                <input type="hidden" name="edit_id_branch[]"
                                                    value="{{ $branch->id }}">
                                                <label for="branchName" class="mb-1">Branch Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control required text-upper-case mng-input"
                                                    placeholder="Enter Branch Name" name="branch_name[]"
                                                    value="{{ $branch->name }}" value="Branch one kolkata"
                                                    maxlength="255"
                                                    oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="gstinVat" class="mb-1">GSTIN/VAT <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control required branch-gstin-vat mng-input"
                                                    placeholder="Enter GSTIN/VAT" name="branch_gstin[]"
                                                    value="{{ $branch->gstin }}" maxlength="15"
                                                    oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&amp;\(\)\+,\- ]/,'')">
                                            </div>
                                            <div class="form-group col-md-12">
                                                <label for="registerAddressBranch" class="mb-1">Registered Address <span
                                                        class="text-danger">*</span></label>
                                                <textarea class="form-control required mng-input" name="branch_address[]" rows="3" maxlength="1700">{{ $branch->address }}</textarea>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="countryBranch" class="mb-1">Country <span
                                                        class="text-danger">*</span></label>
                                                <select
                                                    class="form-select branch-country disabled branch-country-{{ $branch->id }} required mng-input"
                                                    name="branch_country[]"
                                                    onchange="getState('branch-country-{{ $branch->id }}', 'branch-state-{{ $branch->id }}', 'branch-city-{{ $branch->id }}')">
                                                    @if (!empty($countries))
                                                        @foreach ($countries as $country_id => $country_name)
                                                            <option value="{{ $country_id }}"
                                                                {{ $branch->country == $country_id ? 'selected' : '' }}>
                                                                {{ $country_name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="stateBranch" class="mb-1">State <span
                                                        class="text-danger">*</span></label>
                                                <select
                                                    class="form-select branch-state branch-state-{{ $branch->id }} required mng-input"
                                                    {{-- onchange="getCity('branch-state-{{ $branch->id }}', 'branch-city-{{ $branch->id }}')" --}} name="branch_state[]">
                                                    <option value="">Select State</option>
                                                    @php
                                                        $b_country = !empty($branch->country) ? $branch->country : 101;
                                                    @endphp
                                                    {!! getStateByCountryId($b_country, $branch->state ?? 0) !!}
                                                </select>
                                            </div>

                                            {{-- <div class="form-group col-md-6">
                                    <label for="cityBranch" class="mb-1">City <span class="text-danger">*</span></label>
                                    <select class="form-select branch-city branch-city-{{ $branch->id }} required" name="branch_city[]">
                                        <option value="">Select City</option>
                                                {!! !empty($branch->state) ? getCityByStateId($branch->state, $branch->city??0) : '' !!}
                                    </select>
                                </div> --}}

                                            <div class="form-group col-md-6">
                                                <label for="pinCodeBranch" class="mb-1">Pin Code <span
                                                        class="text-danger">*</span></label>
                                                <input class="form-control branch-pincode required mng-input"
                                                    name="branch_pincode[]" value="{{ $branch->pincode }}"
                                                    onkeypress="return validatePinCode(event, this)"
                                                    placeholder="Enter Pin Code" minlength="6" maxlength="6">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="authorizedPersonName" class="mb-1">Name of Authorized Person
                                                    &amp;
                                                    Designation <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control required mng-input"
                                                    name="branch_authorized_designation[]"
                                                    value="{{ $branch->authorized_designation }}"
                                                    placeholder="Enter Name of Authorized Person & Designation"
                                                    oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="mobileAuthorisedPerson" class="mb-1">Mobile <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control validate-max-length my-mobile-number required mng-input"
                                                    name="branch_mobile[]" value="{{ $branch->mobile }}"
                                                    data-maxlength="{{ $vendor->country == 101 ? 10 : 25 }}"
                                                    data-minlength="{{ $vendor->country == 101 ? 10 : 1 }}"
                                                    placeholder="Enter Mobile">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="emailAuthorisedPerson" class="mb-1">Email <span
                                                        class="text-danger">*</span></label>
                                                <input type="email" class="form-control valid-email required mng-input"
                                                    name="branch_email[]" value="{{ $branch->email }}"
                                                    placeholder="Enter Email"
                                                    oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+\@,\- ]/,'')">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <div for="statusAuthorisedPerson{{ $k }}" class="mb-1">
                                                    Status <span class="text-danger">*</span></div>
                                                <label class="ra-switch-checkbox">
                                                    <input type="checkbox" onchange="branchStatus(this)" class="required mng-input"
                                                        name="status" id="statusChecked{{ $k }}"
                                                        value="1" {{ $branch->status == 1 ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                    <input class="branch-status-hidden" value="{{ $branch->status }}"
                                                        type="hidden" name="branch_status[]">
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endif
                    <hr class="mt-4">

                    <!-- Section Registration Details -->
                    <div class="registration-details">
                        <div class="row gy-3 my-3 align-items-center">
                            <div class="col-sm-12 mb-1">
                                <h3 class="font-size-18 mb-0">{{ $is_profile_verified ? 3 : 2 }}. Registrations</h3>
                            </div>
                        </div>

                        <div class="row gy-4">
                            <div class="form-group col-md-6">
                                <label for="msmeNo" class="mb-1">1. MSME</label>
                                <input type="text" class="form-control mng-input" name="msme" value="{{ $vendor->msme }}"
                                    placeholder="Enter MSME" maxlength="255"
                                    oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                            </div>

                            <div class="form-group col-md-6 position-relative">
                                <label for="uploadMsme" class="mb-1">MSME Certificate <span class="text-danger">(File
                                        Type: PDF, DOC,
                                        DOCX,JPG,JPEG,PNG)</span></label>

                                <div class="simple-file-upload">
                                    <input type="file" id="uploadMsme" class="real-file-input mng-input" style="display: none;"
                                        class="msme_certificate" id="registration-msme-file" name="msme_certificate"
                                        onchange="validateFile(this, 'PDF/DOC/DOCX/JPEG/JPG/PNG');reValidateRegistrationDoc(this);">
                                    <div class="file-display-box form-control text-start font-size-12 text-dark"
                                        role="button" data-bs-toggle="tooltip" data-bs-placement="top">
                                        Upload MSME Certificate Document
                                    </div>
                                </div>
                                @if (is_file(public_path('uploads/vendor-profile/' . $vendor->msme_certificate)))
                                    <div class="uploaded-file-display">
                                        <div class="d-flex align-items-center">
                                            <span class="uploaded-file-info d-inline-block text-truncate">
                                                <a class="file-links text-green"
                                                    href="{{ url('public/uploads/vendor-profile/' . $vendor->msme_certificate) }}"
                                                    target="_blank" download="{{ $vendor->msme_certificate }}">
                                                    {!! strlen($vendor->msme_certificate) > 30
                                                        ? substr($vendor->msme_certificate, 0, 25)
                                                        : $vendor->msme_certificate !!}
                                                </a>
                                            </span>
                                            <button type="button"
                                                class="ra-btn ra-btn-link text-green font-size-14 height-inherit"
                                                data-bs-toggle="tooltip" data-placement="top"
                                                data-bs-original-title="{{ $vendor->msme_certificate }}">
                                                <span class="bi bi-info-circle-fill font-size-14"></span>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @if ($is_profile_verified)
                                <div class="form-group col-md-6">
                                    <label for="isoRegistrationDetails" class="mb-1">2. ISO Registration</label>
                                    <input type="text" class="form-control registration-iso mng-input" name="iso_registration"
                                        value="{{ $vendor->iso_registration }}" placeholder="Enter ISO Registration"
                                        maxlength="255"
                                        oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                                </div>

                                <div class="form-group col-md-6 position-relative">
                                    <label for="uploadIso" class="mb-1">ISO Certificate <span class="text-danger">(File
                                            Type: PDF, DOC,
                                            DOCX,JPG,JPEG,PNG)</span>
                                    </label>
                                    <input type="hidden" name="iso_regi_certificate_old"
                                        value="{{ $vendor->iso_regi_certificate }}">
                                    <div class="simple-file-upload">
                                        <input type="file" class="real-file-input iso_regi_certificate mng-input"
                                            style="display: none;" id="registration-iso-file" name="iso_regi_certificate"
                                            onchange="validateFile(this, 'PDF/DOC/DOCX/JPEG/JPG/PNG');reValidateRegistrationDoc(this);">
                                        <div class="file-display-box form-control text-start font-size-12 text-dark"
                                            role="button" data-bs-toggle="tooltip" data-bs-placement="top">
                                            Upload MSME Certificate Document
                                        </div>
                                    </div>
                                    @if (is_file(public_path('uploads/vendor-profile/' . $vendor->iso_regi_certificate)))
                                        <div class="uploaded-file-display">
                                            <div class="d-flex align-items-center">
                                                <span class="uploaded-file-info d-inline-block text-truncate">
                                                    <a class="file-links text-green"
                                                        href="{{ url('public/uploads/vendor-profile/' . $vendor->iso_regi_certificate) }}"
                                                        target="_blank" download="{{ $vendor->iso_regi_certificate }}">
                                                        {!! strlen($vendor->iso_regi_certificate) > 30
                                                            ? substr($vendor->iso_regi_certificate, 0, 25)
                                                            : $vendor->iso_regi_certificate !!}
                                                    </a>
                                                </span>
                                                <button type="button"
                                                    class="ra-btn ra-btn-link text-green font-size-14 height-inherit"
                                                    data-bs-toggle="tooltip" data-placement="top"
                                                    data-bs-original-title="{{ $vendor->iso_regi_certificate }}">
                                                    <span class="bi bi-info-circle-fill font-size-14"></span>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr class="mt-4">

                    <!-- Section Other Details -->
                    <div class="registration-details">
                        <div class="row gy-3 my-3 align-items-center">
                            <div class="col-sm-12 mb-1">
                                <h3 class="font-size-18 mb-0">{{ $is_profile_verified ? 4 : 3 }}. Other Details</h3>
                            </div>
                        </div>

                        <div class="row gy-4">
                            <div class="form-group col-md-12">
                                <label for="otherDetails" class="mb-1">Organization Description <span
                                        class="text-danger">(Maximum 300 Words)*</span></label>
                                <textarea class="form-control required mng-input" name="description" id="other-description" rows="5"
                                    placeholder="Please enter a short description about your organization for the Buyer to view.">{{ $vendor->description }}</textarea>
                            </div>

                            <div class="col-12">
                                <p class="fw-bold">*Note:</p>
                                <p><span class="fw-bold">1.</span> Once your profile is verified by raProcure,
                                    you will be eligible to upload your products, catalog, create mini web page.</p>
                            </div>

                            <div class="col-12">
                                <label for="agree" class="radio-inline">
                                    <input type="checkbox" name="t_n_c" value="1"
                                        {{ $vendor->t_n_c == 1 ? 'checked' : 'checked' }} class="required mng-input">
                                    By creating an account, you agree to the <a
                                        href="{{ url('public/assets/raProcure/faqs/raPROCURES-TERMS-AND-CONDITIONS.pdf') }}"
                                        target="_blank">Terms of Service</a>. For
                                    more information about RaProcure's privacy practices, see the <a
                                        href="{{ url('public/assets/raProcure/faqs/raPROCURES-PRIVACY-POLICY.pdf') }}"
                                        target="_blank">RaProcure Privacy Statement</a>.
                                </label>
                            </div>

                            <div class="col-12 text-start text-sm-end mb-3">
                                <button type="submit" class="ra-btn ra-btn-primary font-size-12" id="submit-vendor-profile" disabled>
                                    <span class="bi bi-floppy font-size-11"></span>
                                    <span class="font-size-11">Save and Submit</span>
                                </button>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>
        </section>
    </section>
@endsection

@section('scripts')
    <script>
         var disabed=true;
        $(document).ready(function() {
            $('.mng-input').prop('disabled', true);
            $('#submit-vendor-profile').prop('disabled', true);
        })
        function disableEnabled() {
            if(disabed){
                $('.mng-input').prop('disabled', false);
                $('#submit-vendor-profile').prop('disabled', false);
                disabed=false;
                $('#editEnableDesable').html('<button type="button" onclick="disableEnabled();" class="ra-btn ra-btn-outline-danger font-size-12"><span class="font-size-11">Cancel</span></button>');
            }else{
                $('.mng-input').prop('disabled', true);
                $('#submit-vendor-profile').prop('disabled', true);
                disabed=true;
                $('#editEnableDesable').html('<button type="button" onclick="disableEnabled();" class="ra-btn ra-btn-outline-primary-light font-size-12"><span class="font-size-11">Edit</span></button>')
            }
        }
        let branch_country = "",
            branch_state = "";

        @if ($is_profile_verified)
            @if (!empty($countries))
                @foreach ($countries as $country_id => $country_name)
                    branch_country +=
                        '<option value="{{ $country_id }}" {{ $country_id == 101 ? 'selected' : '' }}>{{ $country_name }}</option>';
                @endforeach
            @endif

            @if (!empty($india_states))
                @foreach ($india_states as $id => $name)
                    branch_state += '<option value="{{ $id }}">{{ $name }}</option>';
                @endforeach
            @endif
        @endif

        let checkUniqueGstNumber = function(_this) {
            let vendor_gst_number = $(_this).val();
            $("#submit-vendor-profile").attr("disabled", "disabled");
            $.ajax({
                url: '{{ route('vendor.validate-vendor-gstin-vat') }}',
                type: "POST",
                dataType: "json",
                data: {
                    vendor_gst_number: vendor_gst_number,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status == false) {
                        toastr.error(response.message);
                        $(_this).val('');
                        setTimeout(function() {
                            $("#submit-vendor-profile").removeAttr("disabled");
                        }, 1000);
                    } else {
                        $("#submit-vendor-profile").removeAttr("disabled");
                    }
                }
            });
        }
    </script>

    <script src="{{ asset('public/assets/js/profile-validation.js') }}"></script>
    <script src="{{ asset('public/assets/vendor/js/vendor-profile-script.js') }}"></script>

    <script>
        $('#vendor-profile-form').on('submit', function(e) {
            e.preventDefault();
            $("#submit-vendor-profile").attr("disabled", "disabled");
            if (!validateVendorProfile()) {
                toastr.error("Please fill all the manadatory fields");
                $("#submit-vendor-profile").removeAttr("disabled");
                return false;
            }

            let formData = new FormData(document.getElementById("vendor-profile-form"));

            $.ajax({
                url: $(this).attr("action"),
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    $("#submit-vendor-profile").html(' Submitting...')
                        .attr("disabled", "disabled");
                },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        setTimeout(function() {
                            window.location.href = response.redirectUrl;
                        }, 1000); // 1000 ms = 1 second

                    } else {
                        if (response.errors) {
                            let errorMessage = '';
                            for (let field in response.errors) {
                                if (response.errors.hasOwnProperty(field)) {
                                    errorMessage += `${response.errors[field].join(', ')}\n`;
                                }
                            }
                            if (errorMessage != '') {
                                toastr.error(errorMessage);
                            }
                        } else {
                            toastr.error(response.message);
                            console.log(response.complete_message);
                        }
                    }
                    $("#submit-vendor-profile").html('Submit').removeAttr("disabled");
                },
                error: function(xhr) {
                    // Handle network errors or server errors
                    toastr.error("Something went wrong...");
                    setTimeout(function() {
                        $("#submit-vendor-profile").html('Submit').removeAttr("disabled");
                    }, 3000);
                    console.log("Error: ", e);
                    console.log(xhr.responseJSON?.message || 'An error occurred. Please try again.');

                    // alert(xhr.responseJSON?.message || 'An error occurred. Please try again.');
                }
            });
        });

        function getState(country, state, city = '') {
            let country_id = $("." + country).val();
            $.ajax({
                method: "POST",
                dataType: "json",
                url: "{{ route('vendor.get-state-by-country-id') }}",
                data: {
                    country_id: country_id,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status) {
                        $("." + state).html('');
                        $("." + state).html('<option value="">Select State</option>' + response.state_list);
                        if (city != '') {
                            $("." + city).html('<option value="">Select City</option>');
                        }
                    } else {
                        $("." + country).val('');
                        toastr.error(response.message);
                    }
                }
            });
        }

        // function getCity(state, city) {
        //     let state_id = $("." + state).val();
        //     $.ajax({
        //         method: "POST",
        //         dataType: "json",
        //         url: "{{ route('vendor.get-city-by-state-id') }}",
        //         data: {
        //             state_id: state_id,
        //             _token: $('meta[name="csrf-token"]').attr('content')
        //         },
        //         success: function(response) {
        //             if(response.status){
        //                 $("." + city).html(response.city_list);
        //             }else{
        //                 $("." + state).val('');
        //                 toastr.error(response.message);
        //             }
        //         }
        //     });
        // }
    </script>
@endsection