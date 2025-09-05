@extends('buyer.layouts.app', ['title'=>'Add Your Vendor'])

@section('css')
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar-menu')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1">
        <div class="container-fluid">
            <ul class="buyer-breadcrumb">
                <li class="active"> Add Your Vendor </li>
            </ul>
            <div class="card mb-90">
                <div class="card-header py-3 bg-white add-your-vendor">
                    <h1 class="card-head-line mb-0 border-0 pb-0">Add Your Vendor</h1>
                </div>
                <div class="card-body add-your-vendor pt-md-4">
                    <div class="add-vendor-details">
                        <h4>Add Vendor Details</h4>
                        <div class="table-responsive">
                            <form id="addVendorForm" method="POST" action="{{ route('buyer.add-vendor.store') }}">
                                @csrf
                            <table class="product-listing-table w-100 mt-2">
                                <thead>
                                    <tr>
                                        <th class="text-left">Row No</th>
                                        <th class="text-center">Vendor Company <span class="text-danger">*</span> </th>
                                        <th class="text-center">Contact Person</th>
                                        <th class="text-center">Email Id <span class="text-danger">*</span></th>
                                        <th class="text-center">Phone Number <span class="text-danger">*</span></th>
                                        <th class="text-center">Product Name <span class="text-danger">*</span></th>
                                        <th class="text-center">Product Category</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="addVendorTablebody">
                                    <tr>
                                        <td>1</td>
                                        <td class="text-center">
                                            <input type="text" name="vendor_company[]" class="vendor-details-field form-control bg-white mx-auto">
                                        </td>
                                        <td class="text-center"> 
                                            <input type="text" name="contact_person[]" class="vendor-details-field form-control bg-white mx-auto">
                                        </td>
                                        <td>
                                            <input type="email" name="email_id[]" class="vendor-details-field form-control bg-white mx-auto">
                                        </td>
                                        <td>
                                            <input type="text" name="phone_number[]" class="vendor-details-field form-control bg-white mx-auto">
                                        </td>
                                        <td class="text-center"> 
                                            <input type="text" name="product_name[]" class="vendor-details-field form-control bg-white mx-auto">
                                        </td>
                                        <td>
                                            <input type="text" name="product_category[]" class="vendor-details-field form-control bg-white mx-auto">
                                        </td>
                                        <td class="text-blue result-text"></td>
                                        <td>
                                            <button class="bg-transparent border-0" onclick="this.closest('tr').remove()">
                                                <span class="bi bi-trash3 text-danger"></span>
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- <tr class="area-red">
                                        <td>2</td>
                                        <td class="text-center">
                                            <input type="text" name="vendor_company[]" class="vendor-details-field form-control bg-white mx-auto">
                                        </td>
                                        <td class="text-center"> 
                                            <input type="text" name="contact_person[]" class="vendor-details-field form-control bg-white mx-auto">
                                        </td>
                                        <td>
                                            <input type="email" name="email_id[]" class="vendor-details-field form-control bg-white mx-auto">
                                        </td>
                                        <td>
                                            <input type="text" name="phone_number[]" class="vendor-details-field form-control  bg-white mx-auto">
                                        </td>
                                        <td class="text-center"> 
                                            <input type="text" name="product_name[]" class="vendor-details-field form-control bg-white mx-auto">
                                        </td>
                                        <td>
                                            <input type="text" name="product_category[]" class="vendor-details-field form-control bg-white mx-auto">
                                        </td>
                                        <td class="text-red result-text" >
                                            Vendor<br>Company,Email<br>Id,Phone<br>Number,Product<br>Name is<br>required 
                                        </td>
                                        <td>
                                            <button class="bg-transparent border-0" onclick="this.closest('tr').remove()">
                                                <span class="bi bi-trash3 text-danger"></span>
                                            </button>
                                        </td>
                                    </tr> -->
                                </tbody>
                            </table>
                            </form>
                        </div>
                        <div class="col-md-12 mt-3">
                            <button type="button"
                                class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10 ms-auto d-table"
                                onclick="addVendoer()">
                                <span class="bi bi-plus-square font-size-12"></span> Add Vendor
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="floting-product-options">
                <div class="d-flex flex-wrap flex-md-nowrap align-items-center justify-content-center gap-3">
                    <button type="button" onclick="saveVendoer()" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10">
                        Submit Vendors
                    </button>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <script>
        // Start of Add your Vendor Table
        function addVendoer() {
            var addVendorTablebody = document.getElementById('addVendorTablebody');
            var addVendorTableRow = document.createElement('tr');
            var rowNumber = addVendorTablebody.rows.length + 1;
            addVendorTableRow.innerHTML = `
                            <td>${rowNumber}</td>
                            <td class="text-center">
                                <input type="text" name="vendor_company[]" class="vendor-details-field form-control text-center bg-white mx-auto">
                            </td>
                            <td class="text-center"> 
                                <input type="text" name="contact_person[]" class="vendor-details-field form-control text-center bg-white mx-auto">
                            </td>
                            <td>
                                <input type="email" name="email_id[]" class="vendor-details-field form-control text-center bg-white mx-auto">
                            </td>
                            <td>
                                <input type="text" name="phone_number[]" class="vendor-details-field form-control text-center bg-white mx-auto">
                            </td>
                            <td class="text-center"> 
                                <input type="text" name="product_name[]" class="vendor-details-field form-control text-center bg-white mx-auto">
                            </td>
                            <td>
                                <input type="text" name="product_category[]" class="vendor-details-field form-control text-center bg-white mx-auto">
                            </td>
                            <td class="text-blue result-text"></td>
                            <td>
                                <button class="bg-transparent border-0" onclick="this.closest('tr').remove()"><span class="bi bi-trash3 text-danger"></span></button
                            </td>
                        `
            addVendorTablebody.appendChild(addVendorTableRow);
        }
        // End of Add your Vendor Table

        function saveVendoer() {
            $("#addVendorForm").submit();
        }
        $('#addVendorForm').submit(function(e) {
            e.preventDefault();
            var status=true;
            $('#addVendorTablebody tr').each(function() {
                var row = $(this);
                var resultText = row.find('.result-text');
                var vendorCompany = row.find('input[name="vendor_company[]"]').val();
                var contactPerson = row.find('input[name="contact_person[]"]').val();
                var emailId = row.find('input[name="email_id[]"]').val();
                var phoneNumber = row.find('input[name="phone_number[]"]').val();
                var productName = row.find('input[name="product_name[]"]').val();
                var productCategory = row.find('input[name="product_category[]"]').val();
                if (vendorCompany == '' || contactPerson == '' || emailId == '' || phoneNumber == '' || productName == '' || productCategory == '') {
                    resultText.removeClass('text-blue').addClass('text-red').html('Vendor<br>Company,Email<br>Id,Phone<br>Number,Product<br>Name is<br>required');
                    status=false;
                    row.addClass("area-red");
                } else {
                    resultText.removeClass('text-red').addClass('text-blue').html('Ready to Submit');
                    row.removeClass("area-red");
                }
            });
            if(status){
                $.ajax({
                    url: $(this).attr("action"),
                    method: $(this).attr("method"),
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    dataType: "json",
                    success: function(response) {
                        console.log(response);
                        if (response.status) {
                            alert(response.message);
                            window.location.reload();
                        }else{
                            alert(response.message);
                        }
                    }
                });
            }
        });
    </script>
@endsection