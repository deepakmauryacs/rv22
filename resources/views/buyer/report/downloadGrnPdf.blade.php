
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>GRN PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { white-space: nowrap; } 
    </style>
</head>
<body>
    <h2>{{$grn['buyer_name']}}</h2>
    <h2>GRN No: {{ $grn['grn_no'] }}</h2>
    <table>
        <tr>
            <th>Order No :</th>
            <td>{{ $grn['order_no'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Order Date :</th>
            <td>{{ $grn['order_date'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Vendor Name :</th>
            <td>{{ $grn['vendor'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Grn Number :</th>
            <td>{{ $grn['grn_no'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Product Name :</th>
            <td>{{ $grn['product'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Size :</th>
            <td>{!! !empty($grn['size']) ? $grn['size'] : '-' !!}</td>

        </tr>
        <tr>
            <th>Specification :</th>
            <td>{!! !empty($grn['specification']) ? $grn['specification'] : '-' !!}</td>
        </tr>
        <tr>
            <th>Product Order Quantity :</th>
            <td>{{ $grn['product_order_qty'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>GRN Qty :</th>
            <td>{{ $grn['grn_qty'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Bill Date :</th>
            <td>{{ $grn['bill_date'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Vendor Invoice Number :</th>
            <td>{{ $grn['vendor_invoice_no'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Transporter Name :</th>
            <td>{{ $grn['transporter_name'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Vehicle No / LR No / Date :</th>
            <td>{{ $grn['vehicle_no_lr_no'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Gross Weight :</th>
            <td>{{ $grn['gross_wt'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>GST :</th>
            <td>{{ $grn['gst_no'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Freight & Other Charges :</th>
            <td>{{ $grn['frieght_other_charges'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Approved By :</th>
            <td>{{ $grn['approved_by'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>GRN Rate :</th>
            <td>{{ $grn['rate'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>GRN Amount :</th>
            <td>{{ $grn['amount'] ?? '-' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: right;">
                <p style="font-size:10px; font-weight:bold; display:inline-block; margin:0; vertical-align:middle;">
                    GENERATED THROUGH
                </p>
                <img alt="raProcure" 
                    src="{{ public_path('assets/images/rfq-logo.png') }}" 
                    style="max-width:20%; margin-left:5px; vertical-align:middle;">

            </td>
        </tr>

    </table>
</body>
</html>
