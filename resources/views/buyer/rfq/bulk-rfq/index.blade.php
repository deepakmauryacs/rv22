@extends('buyer.layouts.app', ['title'=>'Bulk RFQs'])

@section('css')
    <link rel="stylesheet" href="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.css') }}" />
    <style>
        .clickable-td{
            cursor: pointer;
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
                <!---RFQ Filter section-->
                <form id='bulkRFQForm' method="post">
                @csrf
                <section class="mt-2 mb-2 mx-0 mx-md-2 pt-2">
                    <div class="row align-items-center">
                        <div class="col-md-3 mb-md-4">
                            <h1 class="font-size-14 py-2">Generate Bulk RFQ</h1>
                        </div>
                        <div class="col-md-9">
                            <div class="row align-items-center flex-wrap flex-wrap gx-3">
                                <div class="col-6 col-md-auto mb-4">
                                    <div class="input-group generate-rfq-input-group">
                                        <span class="input-group-text">
                                            <span class="bi bi-file-earmark-text"></span>
                                        </span>
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="prnNumber" name="prnNumber"
                                                placeholder="PRN Number">
                                            <label for="prnNumber">PRN Number</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-auto mb-4">
                                    <div class="input-group generate-rfq-input-group">
                                        <span class="input-group-text">
                                            <span class="bi bi-shop"></span>
                                        </span>
                                        <div class="form-floating">
                                            <select name="branch_id" id="branch_id" class="form-select">
                                                <option value="">Select</option>
                                                @foreach($branches as $branch)
                                                    <option value="{{ $branch->branch_id }}" {{ session('branch_id') == $branch->branch_id ? 'selected' : '' }}>
                                                        {{ $branch->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <label for="selectBranch">Branch/Unit: <sup
                                                    class="text-danger">*</sup></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-8 col-md-auto mb-4">
                                    <div class="input-group generate-rfq-input-group" id="datepicker">
                                        <span class="input-group-text">
                                            <span class="bi bi-calendar2-date"></span>
                                        </span>
                                        <div class="form-floating">
                                            <input type="text" class="form-control dateTimepicker" id="last_date_to_response" name="last_date_to_response"
                                                placeholder="Last Response Date">
                                            <label for="lastResponseDate">Last Response Date</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>
                <!---RFQ Vendor list section-->
                <section class="card shadow-sm mx-0 mx-md-2 fill-more-details">
                    <div class="card-header bg-transparent">
                        <h1 class="font-size-20">Generate Bulk RFQ</h1>
                        <p class="font-size-11">Note: You can upload maximum 100 product at a time.</p>
                    </div>
                    <div class="card-body">
                        <!-- Upload section -->
                        <div class="upload-bulk-rfq-csv row gx-3 mb-4">
                            <div class="col-auto flex-grow-1 flex-sm-grow-0">
                                <div class="form-group position-relative pb-2 pb-sm-0 bulk-rfq-upload">
                                    <div class="simple-file-upload file-browse">
                                        <input type="file" onchange="validateRFQFile(this)" name="bulk_rfq_excel" id="bulkRfq" class="real-file-input" style="display: none;">
                                        <div class="file-display-box form-control text-start font-size-12 text-dark" role="button" data-bs-toggle="tooltip" data-bs-placement="top">
                                            Upload Bulk RFQ12
                                        </div>
                                    </div>
                                    <input type="hidden" class="form-control" placeholder="Upload Bulk RFQ" readonly="">
                                    <input type="hidden" name="submit_type" id="submit_type" class="upload-type" value="1">
                                    <div class="uploaded-file-display">
                                        <div class="d-flex align-items-center">
                                            <span class="uploaded-file-info d-inline-block text-truncate rfq-file-name d-none">
                                                <a class="file-links text-green font-size-12" href="javascript:void(0)"
                                                    target="_blank" download="Download">
                                                    
                                                </a>
                                            </span>
                                            <span class="uploaded-file-info d-inline-block text-danger remove-rfq-file d-none">
                                                <a class="file-links text-green font-size-12" href="javascript:void(0)"
                                                    target="_blank" download="Download">
                                                   
                                                </a>
                                            </span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-auto ps-0">
                                <button type="button"
                                    class="ra-btn ra-btn-link text-danger font-size-16 height-inherit width-inherit bg-transparent p-0  mt-2"
                                    data-bs-toggle="tooltip" data-placement="top"
                                    data-bs-original-title="Upload Bulk RFQ(File Type: XLSX)">
                                    <span class="bi bi-question-circle font-size-16"></span>
                                </button>
                            </div>
                            <div class="col-12 col-sm-auto">
                                <a href="{{asset('public/assets/buyer/web/sample_csv/bulk-rfq.xlsx')}}"
                                    class="ra-btn btn-outline-primary ra-btn-outline-primary text-uppercase text-nowrap d-inline-flex font-size-11"><i
                                        class="bi bi-download"></i> Download Template </a>
                            </div>
                            <div class="invalid-product-section d-none">
                                <h2 class="fs-5 mb-2" >Uploaded Products Details</h2>
                                <div class="table-responsive">
                                    <table class="table-primary-light">
                                        <thead>
                                            <tr>
                                                <th class="text-center" scope="col">Sr. No</th>
                                                <th class="text-center" scope="col">Product Name</th>
                                                <th class="text-center" scope="col">Brand</th>
                                                <th class="text-center" scope="col">Remarks</th>
                                                <th class="text-center" scope="col">Specification</th>
                                                <th class="text-center" scope="col">Size</th>
                                                <th class="text-center" scope="col">Quantity</th>
                                                <th class="text-center" scope="col">UOM</th>
                                                <th class="text-center" scope="col">Message</th>
                                                <th class="text-center" scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="invalid-product-row"></tbody>
                                    </table>
                                </div>

                                <div class="text-end mt-3">
                                    <button type="button" class="ra-btn btn-primary ra-btn-primary d-inline-flex text-uppercase text-nowrap font-size-10 add-product"><span class="bi bi-plus-square font-size-12"></span> Add Product</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>
                <section class="floting-product-options">
                    <div class="col-md-8">
                        <p class="error-found-msg"></p>
                    </div>
                    <div class="d-flex flex-wrap flex-md-nowrap align-items-center justify-content-center gap-3">
                        <button type="submit"
                            class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10 draft-rfq-btn" id="draft-rfq-btn"><span
                                class="bi bi-check2-square font-size-12"></span> Proceed to RFQ</button>
                    </div>

                </section>
                <!-- Floating product options-->
                </form>
            </div>
            <input type="hidden" name="dsa" id="bulk_rfq_per_p_n">
            <input type="hidden" name="dsa1" id="bulk_rfq_total_r">
        </main>

  
@endsection

@section('scripts')
    <!-- jQuery UI -->
    <script src="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script>
    var num_input="this.value=this.value.replace(/[^0-9.]/,'')";
    $(document).ready(function () {
       
        $('#last_date_to_response').datetimepicker({
             lang: 'en',
             timepicker: false,
             minDate: last_date_to_response,
             format: 'd/m/Y',
             formatDate: 'd/m/Y',
        });
        
    });
    function validateRFQFile(obj) {
       // console.log(obj);
      
       var avatar = $(obj).val();
       var extension = avatar.split('.').pop().toUpperCase();
       
       if (extension != "XLSX") {// || extension != "XLS"//XLSB
            $(obj).val('');
            $(obj).attr('src', '');
            $(".rfq-file-name").addClass('d-none').html('');
            $(".remove-rfq-file").addClass('d-none');
            appendFileError(obj, "Invalid file extension.");
       }else{
            $(".rfq-file-name").removeClass('d-none').html(avatar.split('\\').pop());
            $(".remove-rfq-file").removeClass('d-none');
            appendFileError(obj);
            uploadExcel();
       }
       
   }
   $("#bulkRFQForm").validate({
            // setTimeout(removeFileError, 3000);
        submitHandler: function(form) {
            event.preventDefault();
            if($("#bulkRfq").val()==''){
               alert("Please Select Excel file to import Bulk RFQ.");
               return false;
            }
            if($("#submit_type").val()=='2'){
               if($(".validate-product").length<=0 ){
                  alert("No Product found to upload bulk RFQ.");
                  return false;
               }
               var p_name_status = true;
               $(".bulk-rfq-product-field").each(function(){
                  if($(this).val()=='' ){
                     p_name_status = false;
                  }
               });
               if(p_name_status == false){
                  alert("All Product Name field is required to upload bulk RFQ.");
                  return false;
               }              
               var errorStatus = false;
               $(".validate-qty-uom").each(function(){
                  if($(this).val()==2 && $(this).parents('td').find('.validate-product').val()==1){
                     errorStatus = true;
                  }
               });
               if(errorStatus == true ){
                  alert("Quantity and UOM field is required to upload bulk RFQ.");
                  return false;
               }
               var errorStatus1 = false;
               $(".validate-product").each(function(){
                  if($(this).val()==2){
                     errorStatus1 = true;
                  }
               });
              
            }
            var form_data = new FormData(document.getElementById("bulkRFQForm"));
            $('#data_list').html('<div id="loading" style="text-align:center;width: 100%;" ></div>');

            // console.log("form data", form_data);
            $.ajax({
                url: "{{ route("buyer.rfq.bulk-rfq.bulkDraftRFQ") }}",
                type: "POST",
                dataType: 'json',
                data: form_data,
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function() {},
                success: function(responce) {

                //console.log('responce',responce);

                    if (responce.status == 2) {//invalid products
                        printInvalidProduct(responce.all_product_data.all_product_data);
                    } else if (responce.status == 3) {//'No Product Found....';, Uploaded Product Verified successfully
                        $(".remove-rfq-file").click();
                        alert(responce.message);
                    } else if (responce.status == 1) {
                        alert(responce.message);
                        setTimeout(function(){
                           window.location.href=responce.url;
                        },1000);
                    }
                },
                error: function() {
                    alert('Something Went Wrong..');
                },
                complete: function() {}
            });
        }
   });
   function appendFileError(obj, msg='') {
      $(obj).parents('.file-browse').parent().find('.error-message').remove();
      if (msg) {
         $(obj).parents('.file-browse').parent().append('<span class="help-block text-danger error-message">'+msg+'</span>');
      }
   }
   function uploadExcel(){
        $(".upload-type").val('1');
        var formData = new FormData(document.getElementById("bulkRFQForm"));
        
        $.ajax({
            url: '{{ route("buyer.rfq.bulk-rfq.uploadBulkExcel") }}',
            type: 'POST',
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {},
            success: function (response) {
                if (response.status == 2) {
                    printInvalidProduct(response.all_product_data.all_product_data);
                } else if (response.status == 3) {//'No Product Found....';, Uploaded Product Verified successfully
                    $(".remove-rfq-file").click();
                    alert(response.message);
                } else if (response.status == 1) {
                    if(response.message!='Bulk RFQ imported successfully.'){
                        alert(response.message);
                    }
                   
                }
            },
            error: function (xhr) {
                alert('Error uploading file');
            }
        });
   }
   
   function printInvalidProduct(other_category_product_data){
      // invalid-product-row
      
      var html = '';
      var row_color = '';
      var status = 1;
      
      for (var i = 0; i < other_category_product_data.length; i++) {
         var p_data = other_category_product_data[i];
         console.log(p_data.status);
         if(p_data.status==1){
            row_color = '';
            status = 1;
         }else{
            row_color = 'text-danger';
            status = 2;
         }
         html+='<tr class="bulk-product-row">';
            html+='<td class="row-count-number '+row_color+'">'+(i+1)+'</td>';
            html+='<td class="align-top">';
               html+='<div class="bulk-rfq-product-row">';
                  html+='<input type="text" title="'+(p_data.product_name ? p_data.product_name : '')+'" readonly name="product_name[]" class="form-control readonly-text-field bulk-rfq-product-field '+row_color+'" value="'+(p_data.product_name ? p_data.product_name : '')+'" autocomplete="off">';
                     html+='<ul class="suggest-str-p d-none">';
                     html+='</ul>';
               html+='</div>';
               html+='<input type="hidden" name="status[]" class="validate-product" value="'+status+'">';
               html+='<input type="hidden" name="status2[]" class="validate-qty-uom" value="'+status+'">';
               // html+='<input type="text" readonly name="product_name[]" class="form-control readonly-text-field bulk-rfq-product-field" value="'+p_data.product_name+'">';
            html+='</td>';
            html+='<td class="align-top">';
               html+='<input type="text" readonly name="brand[]" class="form-control readonly-text-field '+row_color+'" value="'+(p_data.brand ? p_data.brand : '')+'">';
            html+='</td>';
            html+='<td class="align-top">';
               html+='<input type="text" readonly name="remarks[]" class="form-control readonly-text-field '+row_color+'" value="'+(p_data.remarks ? p_data.remarks : '')+'">';
            html+='</td>';
            html+='<td class="align-top">';
               // console.log("p_data.specification", p_data.specification);
               html+='<input type="text" title="'+(p_data.specification ? p_data.specification : '')+'" readonly name="specification[]" maxlength="255" class="form-control readonly-text-field '+row_color+'" value="'+(p_data.specification ? p_data.specification : '')+'">';
            html+='</td>';
            html+='<td class="align-top">';
               html+='<input type="text" readonly name="size[]" class="form-control readonly-text-field '+row_color+'" value="'+(p_data.size ? p_data.size : '')+'">';
            html+='</td>';
            html+='<td class="align-top">';
               html+='<input type="text" readonly name="quantity[]" class="form-control readonly-text-field product-qty smt_numeric_only '+row_color+'" value="'+(p_data.quantity ? p_data.quantity : '')+'" >';//oninput="'+num_input+'"
            html+='</td>';
            html+='<td class="align-top">';
                html +=  '<select readonly class="form-control product-uom '+row_color+'" name="uom[]" data-uom-name="'+p_data.uom_id+'">';
                html += `<option value="">Select</option>`;
                    p_data.uom_list.list.forEach(uom => {
                        const selected = (uom.id === p_data.uom_id) ? ' selected' : '';
                        html += `<option value="${uom.id}">${uom.name}</option>`;
                    });

               html +=  '</select>';
                //html+='<input type="text" readonly name="uom[]" class="form-control readonly-text-field '+row_color+'" value="'+p_data.uom+'">';
            html+='</td>';

               // html+='<input type="text" readonly name="uom[]" class="form-control readonly-text-field '+row_color+'" value="'+p_data.uom+'">';
            html+='</td>';
            html+='<td class="product-message '+row_color+'">'+p_data.message+'</td>';
            html+='<td>';
               html+='<span class="align-top remove-rfq-record btn-rfq '+row_color+'"><i class="bi bi-trash3 text-danger remove-bulk-rfq-btn" ></i></span>';
            html+='</td>';
         html+='</tr>';
      }
      $(".invalid-product-row").html(html);
      $(".invalid-product-section").removeClass('d-none');
      rewiesSerialNumber();
      makeSelectedUOM();
      updateSubmitBtn();
      $('.suggest-str-p').on('scroll', function() {
         var page = $('#bulk_rfq_per_p_n').val(), _this = this;
         npage = parseInt(page) + 1;
         var tot_page = $('#bulk_rfq_total_r').val();

         if (npage <= tot_page && is_search_p_loading) {
            var scrollTop = $(this).scrollTop();
            if ((scrollTop + $(this).innerHeight()) >= (this.scrollHeight - 100)) {        
               $('#bulk_rfq_per_p_n').val(npage);
               is_search_p_loading = false;
               bulkProductSearch(_this);
            }
         } else {
         }
      });
   }
   $(document).on("click", ".remove-rfq-file", function(){
      $(".bulk-rfq-input").val('');
      $(".bulk-rfq-input").attr('src', '');
      $(".rfq-file-name").addClass('d-none').html('');
      $(".remove-rfq-file").addClass('d-none');
      $(".invalid-product-row").html('');
      $(".invalid-product-section").addClass('d-none');
      // $('.ignore-status-section').addClass('d-none');

   });
   $(document).on("dblclick", ".readonly-text-field", function(){
      $(".suggest-str-p").html('').addClass("d-none");
      $(".readonly-text-field").attr('readonly', 'readonly');
      $(this).parents('tr').find(".readonly-text-field").removeAttr('readonly');
   });
   var search_bulk_p_request;
    $(document).on("input", ".bulk-rfq-product-field", function(){
      var p_name = $(this).val(), _this = this;
      if(!p_name || p_name==''){
         $(_this).parent().find(".suggest-str-p").html('').addClass("d-none");
         return false;
      }
      if(search_bulk_p_request && p_name.length >= 3){
         search_bulk_p_request.abort();
      }
      document.body.addEventListener('click', suggestCloser, false);

      
      if(p_name.length>2){
         $(_this).parents('tr').find(".product-message").html("Select a<br> Product");
      }
      $(_this).parents('tr').find(".validate-product").val(2);
      if(p_name.length < 3){
          $(_this).parent().find(".suggest-str-p").html('<li><font style="color:#6aa510;">Please enter more than 3 characters.</font><li>').removeClass("d-none");
      }else{
        
         search_bulk_p_request = $.ajax({
             url: "{{ route("buyer.rfq.bulk-rfq.validateProductName") }}",
             type: "POST",
             dataType: 'json',
             data: {
                _token: "{{ csrf_token() }}",
                product_name: p_name,
                page_no: 1,
                filtered_total_count: '0',
            },
             beforeSend: function() {},
             success: function(response) {
               is_search_p_loading = true;
               if(response.status){
                  $('#bulk_rfq_total_r').val(response.num_rows);
                  $('#bulk_rfq_per_p_n').val(1);
                  var products = response.products;
                  if(products.length>0){
                     var html = '';
                     for (var i = 0; i < products.length; i++) {
                        html += '<li value="'+products[i].product_id+'" class="select-product" data-product-name="'+products[i].product_name+'" >'+products[i].product_name+'</li>';
                     }
                     $(_this).parent().find(".suggest-str-p").html(html).removeClass("d-none");
                  }else{
                     $(_this).parent().find(".suggest-str-p").html('<li>No Product Found...<li>').removeClass("d-none");
                  }
               }else{
                  is_search_p_loading = false;
                  $(_this).parent().find(".suggest-str-p").html('<li>No Product Found...<li>').removeClass("d-none");
                  $(_this).parents('tr').find(".product-message").html(response.message);
                  $(_this).parents('tr').find(".validate-product").val(2);
               }
               updateSubmitBtn();  
             },
             error: function(e, textStatus) {
                 if(textStatus!='abort'){
                     alert('Something Went Wrong..');
                 }
             },
             complete: function() {}
         });
      }
   });
   
   function bulkProductSearch(_this){
      let p_name = $(_this).parent().find(".bulk-rfq-product-field").val();
      let page = $('#bulk_rfq_per_p_n').val();
      let tot_page = $('#bulk_rfq_total_r').val();
      search_bulk_p_request = $.ajax({
          url: '',
          type: "POST",
          dataType: 'json',
          data: {
            p_name:p_name,
            page_no: page,
            filtered_total_count: tot_page,
         },//, division:division, category:category
          beforeSend: function() {},
          success: function(response) {
            is_search_p_loading = true;
            if(response.status){
               var products = response.products;
               if(products.length>0){
                  var html = '';
                  for (var i = 0; i < products.length; i++) {
                     html += '<li value="'+products[i].product_id+'" class="select-product" data-product-name="'+products[i].product_name+'" ><span class="fw-bold">'+products[i].product_name+'</li>';
                  }
                  $(_this).parent().find(".suggest-str-p").append(html).removeClass("d-none");
               }else{
                  is_search_p_loading = false;
                  // $(_this).parent().find(".suggest-str-p").html('<li>No Product Found...<li>').removeClass("d-none");
               }
            }else{
               is_search_p_loading = false;
               // $(_this).parent().find(".suggest-str-p").html('<li>No Product Found...<li>').removeClass("d-none");
               $(_this).parents('tr').find(".product-message").html(response.message);
               $(_this).parents('tr').find(".validate-product").val(2);
            }
            updateSubmitBtn();  
          },
          error: function(e, textStatus) {
              if(textStatus!='abort'){
                  alert('Something Went Wrong..');
              }
          },
          complete: function() {}
      });
   }
   $(document).on("click", ".select-product", function(){
      var selected_product_name = $(this).data('product-name');

      $(this).parents('div.bulk-rfq-product-row').find('.bulk-rfq-product-field').val(selected_product_name);
      $(this).parents('div.bulk-rfq-product-row').find('.bulk-rfq-product-field').attr('title',selected_product_name);


      $(this).parents('tr').find(".product-message").html("Product Verified");
      $(this).parents('tr').find(".validate-product").val(1);
      $(".readonly-text-field").attr('readonly', 'readonly');
      $(this).parents('tr').find(".suggest-str-p").html('').addClass("d-none");
      
      makeSelectedUOM();
      updateSubmitBtn();
      updateQtyUom();
   });
   $(document).on("change", ".product-uom", function(){//.product-qty, 
      updateQtyUom(this);
   });
   $(document).on("blur", ".product-qty", function(){//.product-qty, 
      updateQtyUom(this);
   });

   function updateQtyUom(_this, is_first=false){
      // $(this).parents('div.bulk-rfq-product-row').find('.bulk-rfq-product-field').val($(this).html());
      // console.log("_this is ", _this);

      var uom = $(_this).parents('tr.bulk-product-row').find('.product-uom').val();
      var uom_data = $(_this).parents('tr.bulk-product-row').find('.product-uom').data('uom-name');
      // console.log("click on product",parseInt(uom_data) > 0, uom, uom_data);
      if(is_first && uom=='' && parseInt(uom_data) > 0){
         uom = uom_data;
      }
      var qty = $(_this).parents('tr.bulk-product-row').find('.product-qty').val();
      var old_status_p = $(_this).parents('tr.bulk-product-row').find('.validate-product').val();
      var old_status = $(_this).parents('tr.bulk-product-row').find('.validate-qty-uom').val();
      // console.log("click on qty uom", qty=='', parseInt(qty)<=0, uom=='', old_status);
      if(old_status_p==1){
         var row_color = '', status = 1;
         var msg = '';
         if(qty=='' || parseInt(qty)<=0 || uom=='' || parseInt(uom)<=0 ){
            if(qty=='' || parseInt(qty)<=0){
               msg += "Quantity";
            }
            
            if(uom=='' || parseInt(uom)<=0 ){
               if(msg!=''){
                  msg += " &";
               }
               msg += " UOM";
            }
            msg +=" is required";
            status = 2;
            
            $(_this).closest('tr.bulk-product-row').find('td input').addClass('text-danger');
            $(_this).closest('tr.bulk-product-row').find('td').addClass('text-danger');
            $(_this).closest('tr.bulk-product-row').find('td select').addClass('text-danger');
         }else{
            msg ="Product Verified";
            $(_this).closest('tr.bulk-product-row').find('td input').removeClass('text-danger');
            $(_this).closest('tr.bulk-product-row').find('td').removeClass('text-danger');
            $(_this).closest('tr.bulk-product-row').find('td select').removeClass('text-danger');
         }         
         updateSubmitBtn();
      }
   }
   $(document).on("change", ".change-field", function(){
      // $(".remove-rfq-file").click();
      if($("#category").val()!=''){
         $(".bulk-rfq-input").removeAttr("disabled");
      }
      if($(".bulk-rfq-input").attr('src')!='' && $(".bulk-rfq-input").val()!=''){
         $("#form").submit();
      }
   });
   $(document).on("click", ".add-product", function(){

      var html = '', row_color = '';  
      $.ajax({
            url: '{{ route("buyer.rfq.bulk-rfq.getAllUOMLists") }}',
            type: 'GET',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {},
            success: function (response) {
                //console.log(response);
                var p_data = response.all_uom_list.list;;
                if(response.status==1){
                    row_color = '';
                    status = 1;
                }else{
                    row_color = 'text-danger';
                    status = 2;
                }
                html+='<tr class="bulk-product-row">';
                html+='<td class="row-count-number"> </td>';
                html+='<td >';
                    html+='<div class="bulk-rfq-product-row">';
                    html+='<input type="text" name="product_name[]" class="form-control readonly-text-field bulk-rfq-product-field" value="" autocomplete="off">';
                        html+='<ul class="suggest-str-p d-none">';
                        html+='</ul>';
                    html+='</div>';
                    html+='<input type="hidden" name="status[]" class="validate-product" value="">';
                    html+='<input type="hidden" name="status2[]" class="validate-qty-uom" value="2">';
                html+='</td>';
                html+='<td>';
                    html+='<input type="text" name="brand[]" class="form-control readonly-text-field" value="">';
                html+='</td>';
                html+='<td>';
                    html+='<input type="text" name="remarks[]" class="form-control readonly-text-field" value="">';
                html+='</td>';
                html+='<td>';
                    html+='<input type="text" name="specification[]" maxlength="255" class="form-control readonly-text-field" value="">';
                html+='</td>';
                html+='<td>';
                    html+='<input type="text" name="size[]" class="form-control readonly-text-field" value="">';
                html+='</td>';
                html+='<td>';
                    html+='<input type="text" name="quantity[]" class="form-control readonly-text-field product-qty" value="" oninput="">';
                html+='</td>';
                html+='<td>';
                  html += '<select class="form-control product-uom ' + row_color + '" name="uom[]">';
                   html += `<option value="">Select</option>`;
                    p_data.forEach(uom => {
                        html += `<option value="${uom.id}">${uom.name}</option>`;
                    });
                  html += '</select>';
                html+='</td>';
                html+='<td class="product-message"></td>';
                html+='<td>';
                    html+='<span class="remove-rfq-record btn-rfq"><i class="bi bi-trash3 text-danger remove-bulk-rfq-btn" ></i></span>';
                html+='</td>';
                html+='</tr>';
                $(".readonly-text-field").attr('readonly', 'readonly');
                $(".invalid-product-row").append(html);
                rewiesSerialNumber();
            }            
        });
       
      
   });
   $(document).on("click", ".remove-rfq-record", function(){
      if(confirm("Do you want to remove Product from Bulk RFQ?")){
         $(this).parents('tr').remove();
         rewiesSerialNumber();
         updateSubmitBtn();
      }
   });
   
   function rewiesSerialNumber(){
      var new_sr = 1;
      $(".invalid-product-row").find("tr").each(function(){
         $(this).find(".row-count-number").html(new_sr);
         new_sr++;
      });
   }
   function suggestCloser(event) {
      var className = event.target.className;
      var classNameArr = className.split(" ");
      if(!classNameArr.includes('select-product')){
         document.body.removeEventListener('click', suggestCloser, false);
         $(".suggest-str-p").addClass("d-none");
      }
   }
   function _selectOption(selecter, vals, multiple=false){
      $(selecter).find('option').each(function(){
         var row = $(this);
         if(multiple==false){
            if(row.attr('value')==vals)
                row.attr('selected', 'selected');
            else
                row.removeAttr('selected');
         }else{
            var row_val = $(this).attr('value'), find_val = vals.split(",");
            if(find_val.includes(row_val))
                row.attr('selected', 'selected');
            else
                row.removeAttr('selected');
         }
      });
   }
   function updateSubmitBtn(){
      var isInvalidAnyProduct = false;
      var isValidAnyProduct = false;
      var sr_no_arr = new Array();
      $(".validate-product").each(function(){
         var qty = $(this).parent().find(".validate-qty-uom").val();
         if($(this).val()=='2' || $(this).val()==2 || qty=='2' || qty==2){
            isInvalidAnyProduct = true;
            sr_no_arr.push(parseInt($(this).parents("tr").find(".row-count-number").html()));
            //console.log("sr no is: ", $(this).parents("tr").find(".row-count-number").html());
         }
         if($(this).val()=='1' || $(this).val()==1 || qty=='1' || qty==1){
            isValidAnyProduct = true;
         }
      });

      if(isInvalidAnyProduct==true){
         $("#draft-rfq-btn").html('<i class="bi bi-skip-end"></i> Ignore & Proceed to RFQ');
         if(isValidAnyProduct==false){
            $("#draft-rfq-btn").html('<i class="bi bi-save"></i> No Valid Product found');
         }
         $(".error-found-msg").html("Error has been found on Sr. No. " + sr_no_arr.toString() + " Kindly rectify the same or for further processing <i><a target='_blank' href='{{url('buyer/help-support/create')}}'><u>Submit to RaProcure</u></a></i>");

      }else{
         $("#draft-rfq-btn").html('<i class="bi bi-check2-square"></i> Proceed to RFQ');
         $(".error-found-msg").html('');
      }
      if(isValidAnyProduct==true){
         $("#draft-rfq-btn").removeAttr("disabled");
      }else{
         $("#draft-rfq-btn").attr("disabled", "disabled");
      }
   }

   $(document).on("change", "input[name='specification[]']", function(){
      $(this).attr("title", $(this).val());
   });

   function makeSelectedUOM(){
      $(".invalid-product-row").find(".product-uom").each(function(){
         updateQtyUom(this, true);
         _selectOption($(this), $(this).data('uom-name'));
         $(this).attr('data-uom-name', 0);
      });
   }
</script>
@endsection