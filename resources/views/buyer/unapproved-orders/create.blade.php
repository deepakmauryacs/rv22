@extends('buyer.layouts.app', ['title'=>'Unapproved Order Confirmation'])

@section('css')
<style>
    /* For WebKit-based browsers (Chrome, Safari, Edge, Opera) */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        /* Removes the default appearance */
        margin: 0;
        /* Removes any default margin */
    }

    /* For Mozilla Firefox */
    input[type="number"] {
        -moz-appearance: textfield;
        /* Sets the appearance to a standard text field */
    }
</style>
{{--
<link rel="stylesheet" href="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.css') }}" /> --}}
@endsection

@section('content')
<div class="bg-white">
    <!---Sidebar-->
    @include('buyer.layouts.sidebar-menu')
</div>

<!---Section Main-->
<main class="main flex-grow-1">
    <div class="container-fluid">
        <div class="bg-white unapproved-order-page">
            <h3 class="card-head-line">Unapproved Order Confirmation</h3>
            <div class="list-for-rfq-wrap">
                <ul class="list-for-rfq">
                    <li>RFQ No: {{$unapprovedOrder['rfq']['rfq_id']}}</li>
                    <li>PRN Number: {{$unapprovedOrder['rfq']['prn_no']}}</li>
                    <li>Branch/Unit : {{$unapprovedOrder['rfq']['buyer_branch_name']}}</li>
                    <li>Buyer Name : {{session('legal_name')}}</li>
                </ul>
                <div>
                    {{-- <a
                        href="{{ route('buyer.unapproved-orders.downloadPOPdf', ['rfq_id' => $unapprovedOrder['rfq']['rfq_id']]) }}?q={{ request('q') }}"
                        type="button"
                        class="ra-btn btn-outline-primary ra-btn-outline-primary small-btn text-uppercase text-nowrap">
                        <span class="bi bi-download" aria-hidden="true"></span> Download
                    </a> --}}

                    <a href="javascript:void(0)"
                        class="ra-btn btn-outline-primary ra-btn-outline-primary small-btn text-uppercase text-nowrap"
                        id="downloadAllPoBtn">
                        <span class="bi bi-download" aria-hidden="true"></span> Download
                    </a>

                    <a href="{{ route('buyer.rfq.cis-sheet', ['rfq_id' => $unapprovedOrder['rfq']['rfq_id']]) }}"
                        class="ra-btn small-btn ra-btn-primary small-btn">
                        <span class="bi bi-arrow-left-square" aria-hidden="true"></span>
                        Back
                    </a>
                </div>
            </div>
            <div class="table-info px-15 pb-15">
                @php
                $sr = 1;
                @endphp
                @foreach ($unapprovedOrder['vendors'] as $vendor_id => $vendor)
                @if(empty($vendor['vendor_variants']))
                @continue
                @endif
                <h2 class="accordion-header" id="vendor-{{$vendor_id}}">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseInfo-{{$vendor_id}}" aria-expanded="false"
                        aria-controls="collapseInfo-{{$vendor_id}}">
                        {{$sr++}}. {{$vendor['legal_name']}}
                    </button>
                </h2>

                @include('buyer.unapproved-orders.partials.vendor-details', ['vendor' => $vendor, 'variants' =>
                $unapprovedOrder['variants'], 'buyer_country' => $unapprovedOrder['rfq']['buyer_country'],
                'warranty_gurarantee' => $unapprovedOrder['rfq']['warranty_gurarantee'], 'uom'=>$uom,
                'taxes'=>$taxes,'vendor_id'=>$vendor_id])
                @endforeach
            </div>
        </div>
    </div>


    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content text-center p-3">
                <div class="modal-body">
                    <img src="{{ asset('public/assets/images/raprocure-fevicon.png') }}" alt="Logo" class="mb-3"
                        style="width: 50px;">
                    <h5 class="mb-3" id="successMessage">Unapproved Purchase Order Generated Successfully</h5>
                    <p id="poNumber"></p>
                    <button type="button" class="btn btn-primary ra-btn ra-btn-primary" id="okBtn">OK</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Loader -->
    <div id="pdfLoader" style="display:none;
            position:fixed;
            top:0; left:0; right:0; bottom:0;
            background:rgba(255,255,255,0.7);
            z-index:9999;
            text-align:center;
            padding-top:20%;">
        <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
            <span class="visually-hidden">Generating PDF...</span>
        </div>
        <p style="margin-top:15px; font-weight:600;">Generating PDF, please wait...</p>
    </div>

</main>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
    $(document).on('click', '.generatePoBtn', function (e) {
        e.preventDefault();
        let form = $(this).closest('form')[0]; // raw DOM element

        /***:- validation  -:***/
        if (!form.checkValidity()) {
            form.reportValidity(); // show built-in messages
            return false; // stop here
        }

        let formData = $(form).serialize();

        $.ajax({
            url: "{{ route('buyer.unapproved-orders.generatePO') }}",
            method: "POST",
            data: formData,
            success: function (response) {
                if (response.status) {
                     /*toastr.success(`${response.message} <br> PO Number: <strong>${response.po_number}</strong>`);
                     window.location.href = response.url+'?p='+encodeURIComponent(btoa(response.po_number));
                    $('#successMessage').text(response.message);
                    $('#poNumber').html("PO Number: <strong>" + response.po_number + "</strong>");*/


                    $('#successModal').modal('show');
                    $('#okBtn').off('click').on('click', function () {
                        window.location.href = response.url + '?p=' + encodeURIComponent(btoa(response.po_number));
                    });


                } else {

                    console.log(response.message);

                     toastr.error("Quantity cannot exceed available stock (" + maxQty + ")");
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert("Server error!");
            }
        });
    });
});

</script>


<script>
    $(document).on('input', '.product-quantity-field', function () {
    let maxQty = parseInt($(this).data('variant-qty')) || 0;
    let entered = parseInt(this.value) || 0;

    // force integer only
    this.value = entered;

    // check max qty
    if (entered > maxQty) {
        this.value = maxQty;
        toastr.error("Quantity cannot exceed available stock (" + maxQty + ")");
    }
});

$(document).on('input', '.product-mrp-field, .product-discount-field, .product-rate-field', function () {
    let val = $(this).val();


    /***:- allow only 2 decimal places  -:***/
    if (val.includes(".")) {
        let [intPart, decPart] = val.split(".");
        if (decPart.length > 2) {
            this.value = parseFloat(val).toFixed(2);
        }
    }
});



/***:-  Remove leading zeros on blur for all numeric fields  -:***/
$(document).on('blur', '.product-quantity-field, .product-mrp-field, .product-discount-field, .product-rate-field', function () {
    let val = $(this).val();

    if (val) {
        val = val.replace(/^0+(\d)/, '$1');
        if ($(this).hasClass('product-mrp-field') ||
            $(this).hasClass('product-discount-field') ||
            $(this).hasClass('product-rate-field')) {
            val = parseFloat(val).toFixed(2);
        }

        $(this).val(val);
    }
});




    $(document).on('input', 'input[type=number][data-variant-qty]', function () {
        let maxQty = parseFloat($(this).data('variant-qty')) || 0;
        let entered = parseFloat(this.value) || 0;

        /***:- prevent decimal values  -:***/
        if (!Number.isInteger(Number(this.value))) {
            this.value = Number(this.value).toFixed(0);
        }

        if (entered > maxQty) {
            this.value = maxQty;
            toastr.error("Quantity cannot exceed available stock (" + maxQty + ")");
        }
    });


    document.addEventListener("DOMContentLoaded", function () {
        $(document).on('input', 'input[type=number]', function () {
            if (this.value < 0) {
                this.value = 0;
            }
        });
        $(document).on('keydown', 'input[type=number]', function (e) {
            const allowedKeys = ["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete", "Home", "End"];

            if (
                e.key === '+' || e.key === '-' || e.key === 'e' || e.key === 'E'
            ) {
                e.preventDefault();
            }

            if (!allowedKeys.includes(e.key) && !/[0-9.]/.test(e.key)) {
                e.preventDefault();
            }
        });


        function sanitizeValue(el) {
            let val = parseFloat(el.value) || 0;
            if (val < 0) {
                el.value = 0;
                val = 0;
            }
            return val;
        }

    function recalcRow($row) {
        let qty = sanitizeValue($row.querySelector(".order-qty"));
        let price = sanitizeValue($row.querySelector(".order-price"));
        let discount = sanitizeValue($row.querySelector(".order-discount"));
        let gst = parseFloat($row.querySelector(".product-gst")?.value) || 0;

        // ensure discount not over 100%
        if (discount > 100) {
            discount = 100;
            $row.querySelector(".order-discount").value = 100;
        }

        let discountedPrice = price - (price * discount / 100);
        let amount = qty * discountedPrice;

        if (gst > 0) {
            amount += (amount * gst / 100);
        }

        let amountCell = $row.querySelector(".variant-amount");
        if (amountCell) {
            amountCell.textContent = "₹" + amount.toFixed(2);
        }
        let variant_amount = $row.querySelector(".variant_amount");
        if (variant_amount) {
            variant_amount.value = amount.toFixed(2);
        }

        return amount;
    }

    function recalcTable($form) {
        let total = 0;
        $form.querySelectorAll(".variant-row").forEach(function ($row) {
            total += recalcRow($row);
        });

        let grandTotalCell = $form.querySelector(".grand-total");
        if (grandTotalCell) {
            grandTotalCell.textContent = "₹" + total.toFixed(2);
        }

        let hiddenTotal = $form.querySelector("input[name='order_total_amount']");
        if (hiddenTotal) {
            hiddenTotal.value = total.toFixed(2);
        }
    }

    document.querySelectorAll(".unapprovedPoForm").forEach(function ($form) {
        $form.addEventListener("input", function (e) {
            if (e.target.classList.contains("order-qty") ||
                e.target.classList.contains("order-mrp") ||
                e.target.classList.contains("order-discount") ||
                e.target.classList.contains("order-price") ||
                e.target.name === "order_delivery_period") {

                sanitizeValue(e.target);
                recalcTable($form);
            }
        });

        recalcTable($form);
    });
});



// $(document).on('click', '.downloadPoBtn', function (e) {
//     e.preventDefault();
//     let form = $(this).closest('form'); // get vendor form
//     let formData = form.serialize();

//     $.ajax({
//         url: "{{ route('buyer.unapproved-orders.downloadPOPdf', ['rfq_id' => $unapprovedOrder['rfq']['rfq_id']]) }}?q={{ request('q') }}",
//         method: "POST",
//         data: {formData,_token: "{{ csrf_token() }}"},
//         xhrFields: { responseType: 'blob' }, // expect PDF
//         success: function (blob) {
//             let link = document.createElement('a');
//             link.href = window.URL.createObjectURL(blob);
//             link.download = "Unapproved-PO.pdf";
//             link.click();
//         },
//         error: function (xhr) {
//             console.error(xhr.responseText);
//             alert("Failed to generate PDF");
//         }
//     });
// });

$(document).on('click', '#downloadAllPoBtn', function (e) {
    e.preventDefault();

    let allFormsData = [];

    $(".unapprovedPoForm").each(function () {
        allFormsData.push($(this).serializeArray());
    });

      // Show loader
    $("#pdfLoader").show();

    $.ajax({
        url: "{{ route('buyer.unapproved-orders.downloadPOPdf', ['rfq_id' => $unapprovedOrder['rfq']['rfq_id']]) }}?q={{ request('q') }}",
        method: "POST",
        data: JSON.stringify({
            forms: allFormsData,
            _token: "{{ csrf_token() }}"
        }),
        contentType: "application/json",
        xhrFields: { responseType: 'blob' },
        success: function (blob) {
              // Hide loader
            $("#pdfLoader").hide();
            let link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = "Unapproved-PO.pdf";
            link.click();
        },
        error: function (xhr) {
              // Hide loader
            $("#pdfLoader").hide();
            console.error(xhr.responseText);
            alert("Failed to generate PDF");
        }
    });
});


</script>

@endsection
