
function rewiesSerialNumber(_this) {
    let new_sr = 1;
    $(_this).parents(".rfq-product-row").find(".variants-record").find(".table-tr").each(function() {
        $(this).find(".row-count-number").html(new_sr);
        $(this).find(".variant-order").val(new_sr);
        new_sr++;
    });
}

function rewiseProductSerialNumber() {
    let new_sr = 1;
    $(".product-order").each(function() {
        $(this).html(new_sr + ".");
        $(this).parent().find('.product-order-hidden').val(new_sr);
        new_sr++;
    });
}

function removeVariant(_this) {

    let rowCount = $(_this).parents('.variants-record').find('tr').length;
    // console.log("rowCount", rowCount);

    if (rowCount > 1) {
        if (confirm("Are you sure want to remove this Product Variant?")) {
            deleteRFQProductVariant(_this);
        }
    } else {
        alert("You Can't Delete Variant, at least One Variant is Compulsory.");
        $(_this).parents("tr").find(".remove-product-variant-file").trigger('click');
        $(_this).parents("tr").find(".old-attachment").val('');
        $(_this).parents("tr").find(".specification").val('');
        $(_this).parents("tr").find(".size").val('');
        $(_this).parents("tr").find(".quantity").val('');
        $(_this).parents("tr").find(".uom").selectOption('');

        $(_this).parents("tr").find(".specification").trigger('change');
        updateRFQProduct(_this);
    }
}

// function updateCheckedUncheckedForAllVendors1(){
//     $('.card-vendor-list-search-panel').each(function() {
//         const rfq_form_box_section = $(this);
//         const checkListing = rfq_form_box_section.find('.card-vendor-list-search-list');
//         const rfq_form_box_selectAll = rfq_form_box_section.find('.select-product-all-vendor');
//         const vendorCheckboxes = rfq_form_box_section.find('.vendor-input-checkbox');

//         // code to reorder vendors in the current rfq_form_box_section
//         const checkedVendors = vendorCheckboxes.filter(':checked').closest('div.vendor-checkbox');
//         const uncheckedVendors = vendorCheckboxes.not(':checked').closest('div.vendor-checkbox');

//         // Clear the list and re-append items in the current rfq_form_box_section
//         checkListing.empty();
//         checkListing.append(rfq_form_box_selectAll.closest('div.vendor-checkbox')); // Add "Select All" at the top
//         checkListing.append(checkedVendors); // Add checked vendors next
//         checkListing.append(uncheckedVendors); // Add unchecked vendors at the end
//     });
// }
function updateCheckedUncheckedForAllVendors() {
    $('.card-vendor-list-search-panel').each(function () {
        const $panel = $(this);
        const $filterList = $panel.find('.filter-list');

        // Save search input block and Select All block
        const $selectAllBlock = $filterList.children().has('.select-product-all-vendor');
        const $vendorBlocks = $filterList.children('.vendor-checkbox');

        const $checkedVendors = $vendorBlocks.has('input.vendor-input-checkbox:checked');
        const $uncheckedVendors = $vendorBlocks.has('input.vendor-input-checkbox:not(:checked)');

        // Clear the vendor list section (not the entire panel)
        $filterList.empty();

        // Rebuild: keep Select All on top, then checked, then unchecked
        if ($selectAllBlock.length) $filterList.append($selectAllBlock);
        $filterList.append($checkedVendors);
        $filterList.append($uncheckedVendors);
    });
}



$(document).on("change keyup paste", ".search-product-vendor", function() {
    let input = $(this).val();
    let list_items = $(this).parents(".card-vendor-list-search-panel").find(".vendor-input-checkbox");
    list_items.each(function(idx, li) {
        let text = $(this).data('vendor-name');
        text = text.toLowerCase();
        input = input.toLowerCase();
        if (text.indexOf(input) > -1) {
            $(this).parents("div.vendor-checkbox").show();
        } else if (input == '') {
            $(this).parents("div.vendor-checkbox").show();
        } else {
            $(this).parents("div.vendor-checkbox").hide();
        }
    });
    markSelectedSelectAllCheckbox();
    // markSelectedCurrentSelectAllCheckbox(this);
});
$(document).on("change", ".vendor-input-checkbox", function() {
    let _this = this;
    let vendor_id = $(this).val();
    let is_vendor_checked = $(this).prop("checked");
    $('.vendor-has-' + vendor_id + '-id').each(function() {
        if($(this).data("product-id")!=$(_this).data("product-id")){
            $(this).prop("checked", is_vendor_checked);
        }
    });
    markSelectedSelectAllCheckbox();
    setTimeout(updateCheckedUncheckedForAllVendors, 300);
});
$(document).on("change", ".select-product-all-vendor", function() {
    // $(this).parents(".vendor-list-div").find(".vendor-input-checkbox").prop("checked", $(this).prop("checked"));
    $(this)
        .parents(".card-vendor-list-search-list")
        .find(".vendor-input-checkbox")
        .filter(function() {
            return $(this).parents(".vendor-checkbox").css('display') !== 'none';
        })
        .prop("checked", $(this).prop("checked"));
});
function markSelectedSelectAllCheckbox() {
    let all_selected_vendor = [];
    $(".vendor-input-checkbox:checked").each(function () {
        const val = $(this).val();
        if (!all_selected_vendor.includes(val)) {
            all_selected_vendor.push(val);
        }
    });

    all_selected_vendor.forEach(function (vendorId) {
        $(".vendor-has-" + vendorId + "-id").prop("checked", true);
    });
    // Loop through each product form section to toggle "select all vendor" checkbox
    // $(".product-form-section").each(function () {
    $(".card-vendor-list-search-list").each(function () {
        const $section = $(this);
        const $checkboxes = $section.find(".vendor-input-checkbox");
        const $visibleCheckboxes = $checkboxes.filter(function () {
            return $(this).closest("div").is(":visible");
        });
        const $checkedVisible = $visibleCheckboxes.filter(":checked");

        $section.find("input.select-product-all-vendor").prop(
            "checked",
            $visibleCheckboxes.length > 0 && $checkedVisible.length === $visibleCheckboxes.length
        );
    });
}
// function markSelectedSelectAllCheckboxOLD() {
//     let all_selected_vendor = new Array();
//     $(".vendor-input-checkbox:checked").each(function(){
//         if(!all_selected_vendor.includes($(this).val())){
//             all_selected_vendor.push($(this).val());
//         }
//     });
//     for (var i = 0; i < all_selected_vendor.length; i++) {
//         $(".vendor-has-"+all_selected_vendor[i]+"-id").prop('checked', true);
//     }
//     $(".product-form-section").each(function() {
//         if ($(this).find(".vendor-input-checkbox:checked").length == $(this).find(".vendor-input-checkbox").length) {
//             $(this).find("input.select-product-all-vendor").prop("checked", true);
//         } else {
//             var all_vendor=true;
//             $(this).find('.vendor-input-checkbox').each(function(){
//                 if($(this).closest("div").css('display')!='none'){
//                     if ($(this).prop('checked')==true){
//                     } else{
//                         all_vendor=false;
//                         return false; // Break out of the loop
//                     }
//                 }
//             });
//             if(all_vendor){
//                 $(this).find("input.select-product-all-vendor").prop("checked", true);
//             }else{
//                 $(this).find("input.select-product-all-vendor").prop("checked", false);
//             }
//         }
//     });
// }
function markSelectedCurrentSelectAllCheckbox(_this) {
    let select_all_vendor = true;
    $(_this).closest(".vendor-list").find(".vendor-input-checkbox").each(function () {
        if ($(this).is(":visible") && !$(this).prop("checked")) {
            select_all_vendor = false;
            return false; // Exit loop early
        }
    });
    $(_this).closest(".vendor-list").find("input.select-product-all-vendor").prop("checked", select_all_vendor);
}
// function markSelectedCurrentSelectAllCheckboxOLD(_this) {
//     let select_all_vendor=true;
//     $(_this).parents(".vendor-list").find(".vendor-input-checkbox").each(function(){
//         if($(this).closest("div").css('display')!='none'){
//             if ($(this).prop('checked')==false){
//                 select_all_vendor=false;
//                 return false; // Break out of the loop
//             }
//         }
//     });

//     if(select_all_vendor){
//         $(_this).parents(".vendor-list").find("input.select-product-all-vendor").prop("checked", true);
//     }else{
//         $(_this).parents(".vendor-list").find("input.select-product-all-vendor").prop("checked", false);
//     }
// }

function printVendorLocation(responce, vendor_location, int_vendor_location){
    let all_vendor_state = responce.all_states;
    let all_international_vendor_country = responce.all_country;

    let vendor_location_html = '';
    if (all_vendor_state.length > 0) {
        for (var i = 0; i < all_vendor_state.length; i++) {
            let state = all_vendor_state[i];
            let selected = '';
            if (vendor_location.map(Number).includes(state.id)) {
                selected = 'selected';
            }
            vendor_location_html += '<option value="' + state.id + '" '+selected+' class="domestic-vendor">' + state.name + '</option>';
        }
    }
    if (all_international_vendor_country.length > 0) {
        for (var i = 0; i < all_international_vendor_country.length; i++) {
            let country = all_international_vendor_country[i];
            let selected = '';
            if (int_vendor_location.map(Number).includes(country.id)) {
                selected = 'selected';
            }
            vendor_location_html += '<option value="' + country.id + '" '+selected+' class="domestic-vendor">' + country.name + '</option>';
        }
    }

    $('.location-sumo-select').html(vendor_location_html);
    $('.location-sumo-select')[0].sumo.reload();
}

$(document).on("change", "#dealer-type, #location-type", function() {
    showSelectedProduct();
});
$(document).on("click", ".rfq-search-back-button, .close-product-search-popup", function() {
    $("#rfq-product-search, #rfq-searched-product-id, #dealer-type").val('');
    $(".searched-product-card, #location-type").html('');
    $("#location-type").SumoSelect().sumo.reload();
    $("#rfq-product-search-list").html('').css("display", "none");
    $(".search-product-filter, .product-listing, .rfq-search-back-button").addClass('d-none');
});
$(document).on("click", ".show-searched-product", function() {
    let p_id = $(this).data('id');
    let p_name = $(this).data('name');

    if($(".product-form-section").length<=0){
        window.location.href = "{{ route('buyer.vendor.product', ['id'=>0]) }}".slice(0, -1)+p_id;
    }else{
        $("#dealer-type").val('');
        $('#location-type').html('');
        $("#location-type").SumoSelect().sumo.reload();

        $("#rfq-product-search").val(p_name);
        $("#rfq-searched-product-id").val(p_id);
        $("#rfq-product-search-list").html('').css("display", "none");

        showSelectedProduct();
    }
});
// $(document).on("click", "#search-product-for-rfq", function() {
//     let p_name = $("#rfq-search-product-name").val();
//     let p_id = $("#rfq-searched-product-id").val();

//     if($(".product-form-section").length<=0){
//         window.location.href = "{{ route('buyer.vendor.product', ['id'=>0]) }}".slice(0, -1)+p_id;
//     }else{
//         $("#dealer-type").val('');
//         $('#location-type').html('');
//         $("#location-type").SumoSelect().sumo.reload();

//         $("#rfq-search-product-name").val(p_name);
//         $("#rfq-searched-product-id").val(p_id);

//         showSelectedProduct();
//     }
// });
$(document).on("change", ".vendor-product-checkbox", function () {
    if($(".vendor-product-checkbox:checked").length>0){
        $(".add-this-vendor-product-to-draft").addClass("disabled");
    }else{
        $(".add-this-vendor-product-to-draft").removeClass("disabled");
    }
});
$(document).on("change", ".select-all-searched-product-vendor", function() {
    if ($(this).prop("checked") == true) {
        $(".vendor-product-checkbox").prop("checked", true);
    } else {
        $(".vendor-product-checkbox").prop("checked", false);
    }
});
$(document).on("click", ".add-this-vendor-product-to-draft", function () {
    let vendors_id = new Array();
    vendors_id.push($(this).parents(".vendor-product-card").find(".vendor-product-checkbox").val());
    addToDraftRFQ(vendors_id);
});
$(document).on("click", "#add-selected-vendor-product-to-draft", function () {
    let vendors_id = new Array();
    if($(".vendor-product-checkbox:checked").length<=0){
        alert("Please Select at least one Vendor.");
        return false;
    }
    $(".vendor-product-checkbox:checked").each(function(){
        vendors_id.push($(this).val());
    });
    if(vendors_id.length==0){
        alert("Please Select at least one Vendor.");
        return false;
    }
    addToDraftRFQ(vendors_id);
});

function clearAddProductModal(){
    $('.location-sumo-select').html('<option value="">Select Location</option>');
    $('.location-sumo-select')[0].sumo.reload();
    $(".searched-product-card").html("");
    $("#rfq-searched-product-id, #rfq-search-product-name").val("");
    $("#add-product-to-rfq").modal("hide");
}

$(document).on("change", ".select-all-vendor", function() {
    if ($(this).prop("checked") == true) {
        $(this).parents('ul').find(".supplier-id").each(function(){
            if($(this).closest("li").css('display')!='none'){
                $(this).prop("checked", true);
            }
        });
    } else {
        $(this).parents('ul').find(".supplier-id").each(function(){
            if($(this).closest("li").css('display')!='none'){
                $(this).prop("checked", false);
            }
        });
        let this_prod_vendor = new Array();
        $(this).parents('ul').find(".supplier-input-checkbox").each(function(){
            if(!this_prod_vendor.includes($(this).val())){
                this_prod_vendor.push($(this).val());
            }
        });

        for (var i = 0; i < this_prod_vendor.length; i++) {
            $(".supplier-has-"+this_prod_vendor[i]+"-id").prop('checked', false);
        }
    }
    $(this).parents('ul').find(".supplier-id").first().trigger('change');
});

$(document).on("blur, change", ".sync-field-changes", function() {
    // add/update loader after original html intgreation
    // if(global_loder>0){
    //     updateRFQProduct(this);//update current product data
    //     return false;
    // }
    updateRFQProduct(this);//update current product data

    markSelectedSelectAllCheckbox();
});
$(document).on("blur, change", ".sync-draft-rfq-changes", function() {
    updateDraftRFQ();
});

$(document).on("click", ".remove-product-variant-file", function() {
    let brand_field = $(this).parents(".rfq-product-row").find('input[name="brand"]');
    let old_attachment = $(this).parents(".table-tr").find('.old-attachment').val();
    $(this).parents(".table-tr").find('.delete-attachment').val(old_attachment).attr("data-file", old_attachment);
    $(this).parents(".table-tr").find('.old-attachment').val('');
    $(this).parents(".table-tr").find(".file-upload-wrapper").css('display', 'block');
    $(this).parents(".table-tr").find(".file-info").css('display', 'none').html('');
    // $(this).parents(".table-tr").find(".attachment-link").html('');
    brand_field.trigger('change');
});

function validateRFQFile(obj) {
    let file = $(obj).get(0).files[0]; // Get the first file
    let extension = file.name.split('.').pop().toUpperCase();
    let fileSize = file.size;
    // console.log(extension, fileSize);
    // Validate file extension
    if (extension != "PDF" && extension != "DOC" && extension != "DOCX" && extension != "JPEG" && extension != "JPG" &&
        extension != "PNG" && extension != "XLSX" && extension != "DWG" && extension != "CDR") {
        $(obj).val('');
        $(obj).attr('src', '');
        appendFileError(obj, "Invalid file extension.");
    }
    // Validate file size (maximum 1 MB)
    else if (fileSize > 1048576) {
        $(obj).val('');
        $(obj).attr('src', '');
        alert("File size exceeds the maximum limit of 1 MB."); // Display an alert
    }
    // If valid, clear any previous error messages
    else {
        appendFileError(obj);
    }
}
function appendFileError(obj, msg = '') {
    $(obj).parents('.file-browse').parent().find('.error-message').remove();
    if (msg) {
        alert(msg);
        $(obj).parents('.file-browse').parent().append('<span class="text-danger error-message view-btn-error">' + msg + '</span>');
    }
}

$('#openLocationFilter').on('click', function() {
    $('#locationSidebar').addClass('active');
});

$('#closeLocationSidebar').on('click', function() {
    $('#locationSidebar').removeClass('active');
});
function showVendorLocation(responce){
    let states = responce.all_states;
    let countries = responce.all_country;
    let location = '', checked = '';

    states.forEach((e) => {
        checked = '';
        if($(".domestic-vendor.vendor-location-has-"+ e.id+"-id").length){
            $(".domestic-vendor.vendor-location-has-"+ e.id+"-id").each(function(){
                if($(this).prop("checked")){
                    checked = 'checked';
                }
            });
        }

        location +=`
            <div class="mt-1">
                <label class="ra-custom-checkbox mb-0">
                    <input type="checkbox" class="vendor-location domestic-vendor-state" onclick="locationFilter(this)" value="${e.name}" ${checked} data-location-id="${e.id}">
                    <span class="font-size-11">${e.name}</span>
                    <span class="checkmark "></span>
                </label>
            </div>
            `;
    });
    countries.forEach((e) => {
        checked = '';
        if($(".international-vendor.vendor-location-has-"+ e.id+"-id").length){
            $(".international-vendor.vendor-location-has-"+ e.id+"-id").each(function(){
                if($(this).prop("checked")){
                    checked = 'checked';
                }
            });
        }

        location +=`
            <div class="mt-1">
                <label class="ra-custom-checkbox mb-0">
                    <input type="checkbox" class="vendor-location international-vendor-country" onclick="locationFilter(this)" value="${e.name}" ${checked} data-location-id="${e.id}">
                    <span class="font-size-11">${e.name}</span>
                    <span class="checkmark "></span>
                </label>
            </div>
            `;
    });
    $('#location-list-div').html(location);
}

function locationFilter(_this) {
    if($(".vendor-location:checked").length<=0){
        $(_this).prop("checked", true);
        alert("At least one location is required");
        return false;
    }

    $(".vendor-location-has-"+$(_this).data("location-id")+"-id").prop("checked", $(_this).prop("checked"));
    updateCheckedUncheckedForAllVendors();
    markSelectedSelectAllCheckbox();
}

$('#select-all-vendor').on('click', function() {
    $('.vendor-input-checkbox').prop('checked', true);
    // $(".product-form-section").each(function(){
    //     $(this).find('.vendor-input-checkbox').prop('checked', true);
    // });
    markSelectedSelectAllCheckbox();
});

function saveformData(hreflocation = null) {
    if (hreflocation != null && hreflocation != '') {
        setTimeout(function() {
            window.location.href = hreflocation;
        }, 1000);
    } else {
        window.location.reload();
    }
}

$(document).on("blur", 'input[name="quantity[]"]', function() {
    let qty = $(this).val();
    if (parseFloat(qty) < 0.1) {
        $(this).val('');
        toastr.error('Product Quantity can not 0.');
    }
});

$(document).on("click", 'input[name="specification[]"]', function() {
    let specification = $(this).val();
    $("#specifications-textarea").val(specification);
    $(this).addClass("active-specification");
});
$(document).on("click", '#submit-specification', function() {
    let specification = $("#specifications-textarea").val();
    $(".active-specification").val(specification).trigger('change').removeClass("active-specification");
});
$(document).on("click", '#reset-specification', function() {
    $("#specifications-textarea").val('');
    let specification = $("#specifications-textarea").val();
    $(".active-specification").val(specification).trigger('change').removeClass("active-specification");
});


function validateRFQForm() {
    let status = true;
    // $('#buyer-price-basis').trigger("change");

    let error_msg = '';
    if (!validateRFQFields('.product-form-section', '.uom')) {
        status = false;
    }
    if (!validateRFQFields('.product-form-section', 'input[name="quantity[]"]')) {
        status = false;
    }
    let branch_unit = $('#buyer-branch').val();
    if (branch_unit == '') {
        error_msg = 'Branch/Unit is required';
        status = false;
    }
    if (status == false) {
        if(error_msg==''){
            error_msg = 'Please Enter all Manadatory Fields';
        }
        toastr.error(error_msg);
        return status;
    }

    if (!isSelectedVendors()) {
        status = false;
    }
    if (status == false) {
        toastr.error('Please select vendor for each product.');
    }
    return status;
}

function validateRFQFields(form_selector, for_class) {
    let error_flags = true;
    $('' + form_selector + ' ' + for_class).each(function() {
        appendError(this);
        if ($(this).val() == '') {
            error_flags = false;
            appendError(this, "Required*");
        }

    });

    return error_flags;
}

function isSelectedVendors() {
    let is_all_selected = true;
    $(".vendor-input-checkbox").parents(".card-vendor-list-search-list").find('.vendor-error').remove();
    if ($(".vendor-input-checkbox:checked").length <= 0) {
        is_all_selected = false;
        $(".vendor-input-checkbox").parents(".card-vendor-list-search-list").prepend('<div class="form-check text-danger"> Vendor is Required </div>');
    }
    return is_all_selected;
}
// function isSelectedVendorsOLD() {
//     let is_all_selected = true;
//     $(".product-form-section").each(function() {
//         $(this).find(".vendor-input-checkbox").parents(".card-vendor-list-search-list").find('.vendor-error').remove();
//         if ($(this).find(".vendor-input-checkbox:checked").length <= 0) {
//             is_all_selected = false;
//             $(this).find(".vendor-input-checkbox").parents(".card-vendor-list-search-list").prepend('<div class="form-check text-danger"> Vendor is Required </div>');
//         }
//     });
//     return is_all_selected;
// }

$(document).on("click", "#schedule-rfq-btn", function() {
    if (validateRFQForm() == true) {
        $("#schedule-rfq-modal").modal({
            backdrop: 'static',
            keyboard: false
        });
        $("#schedule-rfq-modal").modal('show');
    }
});

function appendError(obj, msg = '') {
    $(obj).parent().find('.error-message').remove();
    if (msg) {
        $(obj).parent().append('<span class="text-danger error-message view-btn-error">' + msg + '</span>');
        setTimeout(removeErrorMessage, 3000);
    }
}
function removeErrorMessage() {
    $(".view-btn-error").remove();
}

function openNav() {
    const sidebar = document.getElementById("mySidebar");
    sidebar.style.transform = "translateX(0)";
    sidebar.classList.add("onClickMenuSidebar"); // Add 'open' class
}

function closeNav() {
    const sidebar = document.getElementById("mySidebar");
    sidebar.style.transform = "translateX(-115%)";
    sidebar.classList.remove("onClickMenuSidebar"); // Remove 'open' class

    let wasMobileView = window.innerWidth <= 768;
    window.addEventListener('resize', function () {
        const isMobileView = window.innerWidth <= 768;
        if (wasMobileView && !isMobileView) {
            closeNav();
        }
        wasMobileView = isMobileView;
    });
}

// Start of Custom file input button
function syncFileInput() {
    $('.file-upload-block').each(function () {
        let $block = $(this);
        let $fileInput = $block.find('.file-upload');
        let $customFileTrigger = $block.find('.custom-file-trigger');

        $customFileTrigger.on('click', function () {
            $fileInput.trigger('click');
        });
    });
}

// End of Custom file input button

// Start of Toggle filter section
function openOffcanvasFilter() {
    document.getElementById('filterPanel').classList.add('active');
}

function closeOffcanvasFilter() {
    document.getElementById('filterPanel').classList.remove('active');
}
// End of Toggle filter section

// Start of Card vendor list scroll
// function matchAllScrollHeights_old_design() {
//     const scrollSections = document.querySelectorAll('.scroll-list');
//     const mainContents = document.querySelectorAll('.table-product');

//     if (window.innerWidth < 768) {
//         // Remove inline height on mobile view
//         scrollSections.forEach(section => {
//             section.style.removeProperty('height');
//         });
//     } else {
//         // Match heights on larger screens
//         for (let i = 0; i < scrollSections.length; i++) {
//             if (mainContents[i]) {
//                 const extraHeight = 20;
//                 scrollSections[i].style.height = (mainContents[i].offsetHeight + extraHeight) + 'px';
//             }
//         }
//     }
// }
function matchAllScrollHeights() {
    const scrollSections = document.querySelectorAll('.card-vendor-list-search-panel');
    const mainContents = document.querySelectorAll('.card-vendor-list-right-panel');

    if (window.innerWidth < 768) {
        // Remove inline height on mobile view
        scrollSections.forEach(section => {
            section.style.removeProperty('height');
        });
    } else {
        // Match heights on larger screens
        for (let i = 0; i < scrollSections.length; i++) {
            if (mainContents[i]) {
                const extraHeight = 20;
                const removeExtraHeight = 98;
                scrollSections[i].style.height = (mainContents[i].offsetHeight + extraHeight - removeExtraHeight) + 'px';
            }
        }
    }
}

window.addEventListener('load', matchAllScrollHeights);
window.addEventListener('resize', matchAllScrollHeights);



function removeVendor(el, user_id) {
    // Convert user_id to number to match how it's stored in vendor_array
    user_id = parseInt(user_id);

    // Remove from vendor_array
    vendor_array = vendor_array.filter(id => id !== user_id);

    // Remove the chip from the UI
    $(el).closest('.vendor-chip-item').remove();

    // Remove the 'vendor-added' class from the dropdown item
    $(`.vendor-search-${user_id}`).removeClass('vendor-added');

    // Hide container if no vendors left
    $(".selected-vendor-list, .selected-vendor-submit-row").toggleClass('d-none', vendor_array.length === 0);
}


$('#search-vendor').on('input', function() {
    currentQuery = $(this).val().trim();
    currentPage = 1;
    hasMore = true;
    $('#search-vendor-list').empty();
    if (currentQuery.length > 4) {
        fetchVendors(currentQuery, currentPage);
    } else {
        $('#search-vendor-list').html('<li>Enter at least 5 characters to search</li>').removeClass('d-none');
    }
});


function selectVendor(_this, user_id, vendor_name, name, mobile, address) {
    user_id = parseInt(user_id);
    if (!$(_this).hasClass("vendor-added") && $(".alias-container").find('.alias-tag').length > 9) {
        alert("Maximum of 10 vendors can be added at once.");
        return false;
    }

    if ($(_this).hasClass("vendor-added")) {
        // Vendor is already selected, so remove them
        vendor_array = vendor_array.filter(id => id !== user_id);
        $(`#aliasContainer .vendor-${user_id}`).remove();
        $(_this).removeClass("vendor-added");
    } else {
        // Add new vendor
        vendor_array.push(user_id);
        let html = `<span class="vendor-chip-item vendor-${user_id}">
                       <strong>${vendor_name}</strong>, ${name}, Mob: ${mobile}, Loc: ${address}
                       <span role="button" class="ra-btn ra-btn-link bi bi-x-lg p-0 ms-2 width-inherit font-size-11 remove-alias" onclick="removeVendor(this, ${user_id})"></span>
                   </span>`;
        //
        $("#aliasContainer").append(html).parents('.selected-vendor-list').removeClass('d-none');
        $(_this).addClass("vendor-added");
    }
    // Show/hide container based on whether there are any vendors selected
    $(".selected-vendor-list, .selected-vendor-submit-row").toggleClass('d-none', vendor_array.length === 0);
}

$('#search-vendor-list').on('scroll', function() {
    const $this = $(this);
    if ($this[0].scrollTop + $this[0].clientHeight >= $this[0].scrollHeight - 5) {
        if (hasMore && !isLoading) {
            currentPage++;
            fetchVendors(currentQuery, currentPage);
        }
    }
});

// Optional: hide dropdown on outside click
$(document).on('click', function(e) {
    if (
        !$(e.target).closest('.position-relative').length &&
        !$(e.target).closest('.vendor-row').length &&
        !$(e.target).closest('#search-vendor').length
    ) {
        $('#search-vendor-list').addClass('d-none');
        $('#search-vendor').val('');
    }
});

function toggleVendor(arr, item) {
    item = parseInt(item);
    const index = arr.indexOf(item);
    if (index > -1) {
        // If exists, remove it
        arr.splice(index, 1);
    } else {
        // If not exists, add it
        arr.push(item);
    }
    return arr;
}

$('#add-vendor-btn').on('click', function () {
    // Your condition here
    var condition = true; // Replace with your actual condition

    if (condition) {
        vendor_array = [];
        $("#aliasContainer").empty();
        $(".selected-vendor-list, .selected-vendor-submit-row").toggleClass('d-none', vendor_array.length === 0);
    } else {
        // Optionally handle the case when condition is false
    }
});
