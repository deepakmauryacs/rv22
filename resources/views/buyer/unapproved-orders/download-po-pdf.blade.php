<!doctype html>
<html>

    <head>
        <meta charset="utf-8">
        <title>Unapproved Order
            5px //echo $result[0]->rfq_id; 5px
        </title>
        <style>
            * {

                font-family: sans-serif;

                font-size: 0.96em;

            }

            .page-break {
                page-break-after: always;
            }

            .table3 {
                float: left;
                width: 33.33%;
            }

            #datatable_wrapper {
                margin-top: 15px;
            }

            table {
                border-collapse: collapse;
            }

            thead tr th {
                color: #1b1a1a !important;
                text-align: center;
            }

            .fc {
                color: black;
                line-height: 0.9;
            }

            .fc1 {
                color: black;
                line-height: 0.7;
                font-size: 1.2em !important;
                /* padding-bottom: 2px; */
            }

            .form-control {
                height: 30px;
            }

            .fcolor1 {
                background-color: rgb(54, 96, 146, 0.4);
                /* opacity: 0.5; */
            }
        </style>
    </head>

    <body>

        @foreach($vendors as $key => $item)


        <table style="table-layout: fixed; width: 100%;">
            <tr>
                <td style="border: 1px solid #0a0909 !important;border-bottom-style: none !important;">
                    <div class="row" style="margin-bottom: -22px; ">

                        <div class="col-md-12" style="position: relative;height:80px;">
                            <table style="table-layout: fixed; width: 100%;">
                                <tr>
                                    <td> </td>
                                    <td>
                                        <h4 style="text-align: center;padding-left: 12px;">
                                            {{ $item['buyer_name'] }}
                                        </h4>
                                    </td>
                                    <td>
                                        <h4 style="text-align: right;padding-right: 12px;">Unapproved Order</h4>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table style="table-layout: fixed; width: 100%;">
            <tbody>
                <tr>
                    <td style="padding-left: 5px;border: 1px solid #0a0909 !important;width: 50%;border-bottom-style: none !important;"
                        colspan="5">
                        <p class="fc"><b>Buyer Name : </b>
                            {{ $item['buyer_name'] }}
                        </p>
                        <p class="fc"><b>Buyer Address :</b>
                            {{ $item['buyer_branch_address'] }}
                        </p>
                        <table style="table-layout: fixed; width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>RFQ No : </b>
                                            {{ $item['rfq_id'] }}
                                        </p>
                                    </td>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>Buyer Order Number : </b>
                                            {{ $item['buyer_order_number'] }}
                                        </p>
                                    </td>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>PRN Number : </b>
                                            {{ $item['buyer_prn_no'] }}
                                        </p>
                                    </td>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>Branch/Unit : </b>
                                            {{ $item['buyer_branch_name'] }}
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>

                </tr>
            </tbody>
        </table>
        <div class="card-body pt-0">
            <div class="accordion" id="accordionExample">

            </div>
        </div>

        <table style="table-layout: fixed; width: 100%;">
            <tr>
                <td style="border: 1px solid #0a0909 !important;border-bottom-style: none !important;">
                    <div class="row">
                        <div class="col-md-12">
                            <h4 style="text-align: left; margin-left:5px;">
                                <b>Vendor Name :</b>
                                {{ $item['vendor_name'] }} <br>
                                <b>Vendor Address :</b>
                                {{ $item['vendor_address'] ?? '-' }}
                                <br>
                                <b>Vendor Phone Number :</b>
                                {{ $item['vendor_mobile'] }}
                            </h4>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table style="table-layout: fixed; width: 100%;" class="display dataTable">
            <thead>
                <tr class='fcolor1'>
                    <th style="border: 1px solid #0a0909 !important;text-align:center;width:5%;">S.No</th>
                    <th
                        style="border: 1px solid #0a0909 !important;text-align:center;width:5px echo $int_bv==2 ? '20' : '20' 5px%;">
                        Product Description</th>
                    <th style="border: 1px solid #0a0909 !important;text-align:center;width:7%;">Quantity</th>
                    <th style="border: 1px solid #0a0909 !important;text-align:center;width:9%;">UOM</th>
                    <th style="border: 1px solid #0a0909 !important;text-align:center;width:8%;white-space: nowrap;">
                        MRP(<span style="font-family: DejaVu Sans; sans-serif;">{{$item['vendor_currency'] }}
                        </span>)
                    </th>
                    <th style="border: 1px solid #0a0909 !important;text-align:center;width:9%;white-space: nowrap;">
                        DISC. (%)</th>
                    <th style="border: 1px solid #0a0909 !important;text-align:center;width:9%;white-space: nowrap;">
                        Rate (<span style="font-family: DejaVu Sans; sans-serif;">{{$item['vendor_currency'] }}</span>)
                    </th>
                    <th style="border: 1px solid #0a0909 !important;text-align:center;width:9%;">HSN Code</th>

                    <th style="border: 1px solid #0a0909 !important;text-align:center;width:6%;">GST %</th>

                    <th style="border: 1px solid #0a0909 !important;text-align:center;">Total Amount(<span
                            style="font-family: DejaVu Sans; sans-serif;">{{$item['vendor_currency'] }}</span>)
                    </th>
                </tr>
            </thead>
            <tbody>
                @php $i=1; $grandTotal=0; @endphp
                @foreach($item['variants'] as $variant)
                @php
                $qty = $variant['order_quantity'];
                $mrp = $variant['order_mrp'];
                $discount= $variant['order_discount'];
                $rate = $variant['order_price'];
                $gst = $variant['product_gst'] ?? 0;

                $total = $qty * $rate;
                $total -= ($total * $discount / 100);
                $total += ($total * $gst / 100);
                $grandTotal += $total;
                @endphp
                <tr>
                    <td style="border: 1px solid #0a0909 !important;text-align:center;">
                        {{ $i++ }}
                    </td>
                    <td style="border: 1px solid #0a0909 !important;">
                        {{ $variant['product_name'] }}
                        {{ isset($variant['specification']) && $variant['specification']!='' ? ' -
                        '.$variant['specification']: '' }}
                        {{ isset($variant['size']) && $variant['size']!='' ? ' -
                        '.$variant['size']: '' }}
                    </td>
                    <td style="border: 1px solid #0a0909 !important;text-align:center;">
                        {{ $qty??0 }}
                    </td>
                    <td style="border: 1px solid #0a0909 !important;text-align:center;">
                        {{ $variant['uom'] ?? '' }}
                    </td>
                    <td style="border: 1px solid #0a0909 !important;text-align:center;">
                        <span style="font-family: DejaVu Sans; sans-serif;"></span>
                        {{ number_format($mrp,2) }}
                    </td>
                    <td style="border: 1px solid #0a0909 !important;text-align:center;">
                        {{ $discount??'' }}
                    </td>
                    <td style="border: 1px solid #0a0909 !important;text-align:center;">
                        <span style="font-family: DejaVu Sans; sans-serif;"></span>
                        {{ IND_money_format($rate) }}
                    </td>
                    <td style="border: 1px solid #0a0909 !important;text-align:center;">
                        {{ $variant['hsn_code'] ?? '' }}
                    </td>

                    <td style="border: 1px solid #0a0909 !important;text-align:center;">
                        {{ $gst }}%
                    </td>

                    <td style="border: 1px solid #0a0909 !important;text-align:right;padding-right:5px;"><span
                            style="font-family: DejaVu Sans; sans-serif;"></span>
                        {{ IND_money_format($total) }}
                    </td>
                </tr>

                <tr>
                    <td colspan="9"
                        style="background-color:rgb(242,220,219);border-left: 1px solid #0a0909 !important;
                    padding-left:5px;border-top: 1px solid #0a0909 !important;border-bottom: 1px solid #0a0909 !important;">
                        Total </td>
                    <td
                        style="padding:5px; background-color:rgb(242,220,219);border-right: 1px solid #0a0909 !important;border-top: 1px solid #0a0909 !important;border-bottom: 1px solid #0a0909 !important;text-align:right;">
                        <span style="font-family: DejaVu Sans; sans-serif;">
                            {{$item['vendor_currency'] }}
                        </span>
                        {{ IND_money_format($grandTotal) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table style="table-layout: fixed; width: 100%;  ">
            <tbody>
                <tr>
                    <td
                        style=" padding:5px; font-size: 12px; border-left: 1px solid #0a0909;border-right: 1px solid #0a0909;">
                        <b>Amount In Words :</b>
                        {{ amounts_number_to_words_with_currency($grandTotal, $item['vendor_currency']); }}
                    </td>
                </tr>
            </tbody>
        </table>
        <table style="table-layout: fixed; width: 100%;">
            <tr>
                <th colspan="8" style="padding: 5px;border:1px solid; text-align:left;">Price Basis :
                    {{ $item['order_price_basis'] }}
                    </td>
            </tr>
            <tr>
                <th colspan="8" style="padding: 5px;border:1px solid;text-align:left;">Payment Terms:
                    {{ $item['order_payment_term'] }}
                    </td>
            </tr>
            <tr>
                <th colspan="8" style="padding: 5px;border:1px solid;text-align:left;">Delivery Period (In Days) :
                    {{ $item['order_delivery_period']
                    }}
                </th>
            </tr>
        </table>

        <table style="table-layout: fixed; width: 100%;">
            <tr>
                <td style="border: 1px solid #0a0909 !important;border-bottom-style: none !important;">
                    <div class="row">
                        <div class="col-md-12">
                            <h3 style="text-align: right; padding-right:5px;">ORDER GENERATED THROUGH<br>
                                <img height="30" width="150" src="{{ public_path('assets/images/rfq-logo.png') }}" />
                            </h3>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <table style="border-bottom: 1px solid #0a0909 !important;table-layout: fixed; width: 100%;">
            <tbody>
                <tr>
                    <td style="padding: 5px;border: 1px solid #0a0909 !important;width: 50%;border-bottom-style: none !important;"
                        colspan="2">

                        <p class="fc"><b>Remarks : </b>
                            {{ $item['vendor_remarks'] ?? '-' }}
                        </p>
                        <p class="fc"><b>Additional Remarks : </b>
                            {{ $item['vendor_additional_remarks']?? '-' }}
                        </p>
                    </td>
                </tr>
                <tr>
                    <td
                        style="padding: 5px;border: 1px solid #0a0909 !important;width: 50%;border-bottom-style: none !important;text-align:left;border-right: none !important;">
                        <p class="fc"><b>Prepared By : </b>
                            {{ $preparedBy }}
                        </p>
                        <p class="fc"><b>Approved By : </b>
                            {{ $approvedBy }}
                        </p>
                    </td>
                    <td
                        style="padding: 5px;border: 1px solid #0a0909 !important;width: 50%;border-bottom-style: none !important;border-left: none !important;text-align:right;">
                        <b>For
                            {{ session('legal_name') }}
                        </b>
                    </td>
                </tr>
            </tbody>
        </table>



        @if (!$loop->last)
        <div class="page-break"></div>
        @endif
        @endforeach
    </body>


</html>
