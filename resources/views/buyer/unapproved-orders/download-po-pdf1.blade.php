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
        @foreach($unapprovedOrder['vendors'] as $vendor_id => $vendor)
        <div class="title">UNAPPROVED ORDER CONFIRMATION</div>

        <table>
            <tr>
                <td width="100%">
                    <strong>Buyer Name:</strong> {{ session('legal_name') }}<br>
                    <strong>Buyer Address:</strong> {{ $unapprovedOrder['rfq']['buyer_branch_address'] }}<br>


                    <ul style="list-style-type: none;   margin-top: 10px; margin-bottom: 30px;">
                        <li><strong>RFQ No:</strong> {{ $unapprovedOrder['rfq']['rfq_id'] }}</li>
                        <li><strong>Buyer Order Number:</strong> {{ $unapprovedOrder['rfq']['prn_no'] }}</li>
                        <li><strong>PRN No:</strong> {{ $unapprovedOrder['rfq']['prn_no'] }}</li>
                        <li> <strong>Branch/Unit:</strong> {{ $unapprovedOrder['rfq']['buyer_branch_name'] }}<br></li>
                    </ul>


                </td>
            </tr>

            <tr>
                <td width="100%">
                    <strong>Vendor Name:</strong> {{ $vendor['legal_name'] }}<br>
                    <strong>Address:</strong> {{ $vendor['address'] ?? '-' }}<br>
                    <strong>Vendor Phone Number:</strong> <br>
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">S.No</th>
                    <th style="width: 15%;">Product Description</th>
                    <th style="width: 5%;">Specification</th>
                    <th style="width: 5%;">Size</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 10%;">UOM</th>
                    <th style="width: 10%;">MRP</th>
                    <th style="width: 10%;">Disc. %</th>
                    <th style="width: 10%;">Rate</th>
                    <th style="width: 10%;">GST %</th>
                    <th style="width: 10%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $i=1; $grandTotal=0; @endphp
                @foreach($vendor['vendor_variants'] as $variant_id)
                @php
                $variant = $unapprovedOrder['variants'][$variant_id];
                $quote = $vendor['vendorQuotes'][$variant_id];
                $qty = $quote['left_qty'];
                $mrp = $quote['mrp'];
                $discount= $quote['discount'];
                $rate = $quote['price'];
                $gst = $taxes[$vendor['vendor_product_gsts'][$variant['product_id']]] ?? 0;

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
                    <td>{{ $uom[$variant['uom']] ?? '' }}</td>
                    <td>{{ number_format($mrp,2) }}</td>
                    <td>{{ $discount }}</td>
                    <td>{{ number_format($rate,2) }}</td>
                    <td>{{ $gst }}%</td>
                    <td>{{ number_format($total,2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="10" align="right"><strong>Grand Total ({{
                            $vendor['vendor_latest_quote']['vendor_currency'] }}) </strong></td>
                    <td><strong>{{ number_format($grandTotal, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <table>
            <tr>
                <td>
                    <strong>Amount in Words({{ $vendor['vendor_latest_quote']['vendor_currency'] }}):</strong>
                    {{ \App\Helpers\CurrencyConvertHelper::numberToWordsWithCurrency($grandTotal) }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Price Basis:</strong> {{ $vendor['vendor_latest_quote']['vendor_price_basis'] }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Payment Term:</strong> {{ $vendor['vendor_latest_quote']['vendor_payment_terms'] }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Delivery Period (Days):</strong> {{ $vendor['vendor_latest_quote']['vendor_delivery_period']
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
                    <strong>Remarks:</strong> {{ $vendor['vendor_latest_quote']['vendor_remarks'] ?? '-' }}<br>
                    <strong>Additional Remarks:</strong> {{ $vendor['vendor_latest_quote']['vendor_additional_remarks']
                    ?? '-'
                    }}
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