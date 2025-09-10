<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <title>Unapproved PO</title>
        <style>
            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 12px;
                line-height: 1.4;
            }

            .page-break {
                page-break-after: always;
            }

            .title {
                text-align: center;
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 15px;
                text-decoration: underline;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 6px;
                font-size: 11px;
            }

            th {
                background: #f2f2f2;
            }

            /* .no-border td {
                border: none;
            } */

            .section-title {
                font-weight: bold;
                /* margin-top: 15px; */
            }

            .footer {
                margin-top: 40px;
                width: 100%;
            }

            .footer td {
                border: none;
                text-align: center;
                font-weight: bold;
                padding-top: 40px;
            }

            ul li {
                float: left;
                /* padding-right: 30px; */
                width: 25%
            }
        </style>
    </head>

    <body>
        @foreach($vendors as $key => $item)
        <div class="title">UNAPPROVED ORDER CONFIRMATION</div>

        <table>
            <tr>
                <td width="100%">
                    <strong>Buyer Name:</strong> {{ $item['buyer_name'] }}<br>
                    <strong>Buyer Address:</strong> {{ $item['buyer_branch_address'] }}<br>


                    <ul style="list-style-type: none;   margin-top: 10px; margin-bottom: 30px;">
                        <li><strong>RFQ No:</strong> {{ $item['rfq_id'] }}</li>
                        <li><strong>Buyer Order Number:</strong> {{ $item['buyer_order_number'] }}</li>
                        <li><strong>PRN No:</strong> {{ $item['buyer_prn_no'] }}</li>
                        <li> <strong>Branch/Unit:</strong><br> {{ $item['buyer_branch_name'] }}<br></li>
                    </ul>


                </td>
            </tr>

            <tr>
                <td width="100%">
                    <strong>Vendor Name:</strong> {{ $item['vendor_name'] }}<br>
                    <strong>Address:</strong> {{ $item['vendor_address'] ?? '-' }}<br>
                    {{-- <strong>Vendor Phone Number:</strong> <br> --}}
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr style="background-color: #aebfd3;">
                    <th style="width: 5%;background-color: #aebfd3;">S.No</th>
                    <th style="width: 15%;background-color: #aebfd3;">Product Description</th>
                    <th style="width: 5%;background-color: #aebfd3;">Specification</th>
                    <th style="width: 5%;background-color: #aebfd3;">Size</th>
                    <th style="width: 10%;background-color: #aebfd3;">Qty</th>
                    <th style="width: 10%;background-color: #aebfd3;">UOM</th>
                    <th style="width: 10%;background-color: #aebfd3;">MRP</th>
                    <th style="width: 10%;background-color: #aebfd3;">Disc. %</th>
                    <th style="width: 10%;background-color: #aebfd3;">Rate</th>
                    <th style="width: 10%;background-color: #aebfd3;">GST %</th>
                    <th style="width: 10%;background-color: #aebfd3;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $i=1; $grandTotal=0; @endphp
                @foreach($item['variants'] as $variant)
                @php
                //$variant = $unapprovedOrder['variants'][$variant_id];
                // $quote = $vendor['vendorQuotes'][$variant_id];
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
                    <td>{{ $i++ }}</td>
                    <td>{{ $variant['product_name'] }}</td>
                    <td>{{ $variant['specification'] }}</td>
                    <td>{{ $variant['size'] }}</td>
                    <td>{{ $qty }}</td>
                    <td>{{ $variant['uom'] ?? '' }}</td>
                    <td>{{ number_format($mrp,2) }}</td>
                    <td>{{ $discount }}</td>
                    <td>{{ number_format($rate,2) }}</td>
                    <td>{{ $gst }}%</td>
                    <td>{{ number_format($total,2) }}</td>
                </tr>
                @endforeach
                <tr style="background-color:#f2dcdb; ">
                    <td colspan="10" align="right"><strong>Total ({{
                            $item['vendor_currency'] }}) </strong></td>
                    <td><strong>{{ number_format($grandTotal, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <table>
            <tr>
                <td>
                    <strong>Amount in Words({{ $item['vendor_currency'] }}):</strong>
                    {{ \App\Helpers\CurrencyConvertHelper::numberToWordsWithCurrency($grandTotal) }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Price Basis:</strong> {{ $item['order_price_basis'] }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Payment Term:</strong> {{ $item['order_payment_term'] }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Delivery Period (Days):</strong> {{ $item['order_delivery_period']
                    }}
                </td>
            </tr>

            <tr>
                <td width="100%" style="text-align: right;">
                    <strong>ORDER GENERATED THROUGH</strong><br>
                    <img src="{{ public_path('assets/images/rfq-logo.png') }}" alt="" height="50px">
                </td>
            </tr>
            <tr>
                <td width="100%">
                    <strong>Remarks:</strong> {{ $item['vendor_remarks'] ?? '-' }}<br>
                    <strong>Additional Remarks:</strong> {{ $item['vendor_additional_remarks']?? '-' }}
                </td>
            </tr>
        </table>

        <table class="footer">
            <tr>
                <td>Prepared By<br>{{ $preparedBy }}</td>
                <td>Approved By<br>{{ $approvedBy }}</td>
                <td>For {{ session('legal_name') }}</td>
            </tr>
        </table>

        <div class="page-break"></div>
        @endforeach

    </body>

</html>