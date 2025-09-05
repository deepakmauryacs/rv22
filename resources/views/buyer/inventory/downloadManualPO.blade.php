@php
    use App\Helpers\NumberFormatterHelper;
    use App\Helpers\CurrencyConvertHelper;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manual Order PDF</title>
    <style>
       @font-face {
            font-family: 'Noto Sans Devanagari';
            src: url('{{ public_path("assets/font/NotoSansDevanagari-Regular.ttf") }}') format("truetype");
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'Noto Sans Devanagari', DejaVu Sans, Arial, sans-serif;
            font-size: 7pt;
            margin: 0;
            padding: 0;
        }

        .main-wrapper {
            margin: 20px;
            padding: 0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }
        table th,
        table td {
            padding: 3pt;
        }

        .top-bar-table {
            border: 1px solid #000;
            border-bottom-width: 0;
            font-weight: bold;
        }

        .top-bar-table td {
            vertical-align: middle;
        }

        .top-bar-table .left {
            text-align: left;
        }

        .top-bar-table .right {
            text-align: right;
        }

        .layout-table td {
            width: 50%;
            border: 1px solid #000;
            vertical-align: top;
        }

        .info-table,
        .info-table td  {
            border: none;
        }
        .info-table td {
            padding-left: 0pt;
        }

        .section-title {
            margin: 5px 0 5px 5px;
            text-align: left;
            font-weight: bold;
        }


        .product-table th,
        .product-table td {
            border: 1px solid #000;
            border-top: none;
            text-align: center;

            white-space: nowrap;
        }
        .product-table thead th {
            background: #afbfd3;
            padding-top: 1pt;
            padding-bottom: 1pt;
        }
        .product-table tfoot td {
            background: #f1dcdb;
            vertical-align: top;
        }

        .product-table td.description {
            text-align: left;
            white-space: normal;
        }

        .product-table .total-label {
            text-align: left;
            font-weight: bold;
        }

        .product-table .total-value {
            font-weight: bold;
            text-align: center;
        }

        .amount-words {
            padding: 4pt 3pt;
            border: 1px solid #000;
            border-top: none;
            font-weight: bold;
        }

        .footer {
            border: 1px solid #000;
            border-top: none;
            padding: 4pt 3pt;
        }
        .keep-word {
            word-break: normal;
        }
        .text-wrap {
             white-space: normal!important;
        }
    </style>
</head>
<body>
@php $currency=session('user_currency')['symbol'] ?? '₹';@endphp
<div class="main-wrapper">

    <table class="top-bar-table">
        <tr>
            <td class="left"><img src="https://a.eraprocure.co.in/assets/images/logo/rfq-logo.png" height="40">{{ strtoupper(auth()->user()->buyer->legal_name) }}
        </td>
            <td class="right">PURCHASE ORDER</td>
        </tr>
    </table>


    <table class="layout-table">
        <!-- VENDOR + ORDER SECTION -->
        <tr>
            <td>
                <table class="info-table">
                    <tr>
                        <td colspan="2">
                            <strong>Vendor Name:</strong> {{ strtoupper(optional($order->vendor)->name ?? 'UNKNOWN VENDOR') }}

                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Vendor Address:</strong> {{ optional(optional($order->vendor)->vendor)->registered_address ?? 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>City:</strong> {{ optional(optional(optional($order->vendor)->vendor)->vendor_city)->city_name ?? 'N/A' }}</td>
                        <td><strong>Pincode:</strong> {{ optional(optional($order->vendor)->vendor)->pincode ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>State:</strong> {{ optional(optional(optional($order->vendor)->vendor)->vendor_state)->name ?? 'N/A' }}</td>
                        <td><strong>State Code:</strong> {{ optional(optional(optional($order->vendor)->vendor)->vendor_state)->state_code ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>GSTIN:</strong> {{ optional(optional($order->vendor)->vendor)->gstin ?? 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Email:</strong> {{ optional($order->vendor)->email ?? 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Phone NO:</strong> +{{ optional($order->vendor)->country_code ?? 'N/A' }} {{ optional($order->vendor)->mobile ?? 'N/A' }}
                        </td>
                    </tr>
                </table>
            </td>
            <td style="background: #afbfd3;">
                <table class="info-table">
                    <tr>
                        <td>
                            <strong>Order No:</strong> {{ $order->manual_po_number ?? 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Order Date:</strong> {{ optional($order->created_at)->format('d/m/Y') ?? 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Delivery Period:</strong> {{$order->order_delivery_period}} Days
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Payment Term:</strong> {{$order->order_payment_term}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Price Basis:</strong> {{$order->order_price_basis}}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <!-- BUYER + DELIVERY SECTION -->
        <tr>
            <td>
                <table class="info-table">
                    <tr>
                        <td colspan="2">
                            <strong>Buyer Name:</strong>{{ strtoupper(auth()->user()->buyer->legal_name) }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Buyer Address:</strong> {{auth()->user()->buyer->registered_address}}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>City:</strong> {{auth()->user()->buyer->buyer_city->city_name}}</td>
                        <td><strong>Pincode:</strong> {{auth()->user()->buyer->pincode}}</td>
                    </tr>
                    <tr>
                        <td><strong>State:</strong> {{auth()->user()->buyer->buyer_state->name}}</td>
                        <td><strong>State Code:</strong> {{auth()->user()->buyer->buyer_state->state_code}}</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Country:</strong> {{auth()->user()->buyer->buyer_country->name}}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>GSTIN:</strong> {{ auth()->user()->buyer->gstin }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Contact Person:</strong> {{ strtoupper(auth()->user()->name) }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Email:</strong> {{ auth()->user()->email }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Phone NO:</strong> +{{ auth()->user()->country_code }}  {{ auth()->user()->mobile}}
                        </td>
                    </tr>
                </table>
            </td>
            <td>
                <table class="info-table">
                    <tr>
                        <td colspan="2">
                            <strong>Buyer Name:</strong> {{ auth()->user()->buyer->legal_name }}
                            @php
                                $firstProduct=$order->products->first();
                                $orderBranch = $firstProduct->inventory->branch;
                            @endphp
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Delivery Address:</strong> {{ $orderBranch->address  }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>City:</strong> {{ $orderBranch->branch_city->city_name  }}</td>
                        <td><strong>Pincode:</strong> {{ $orderBranch->pincode  }}</td>
                    </tr>
                    <tr>
                        <td><strong>State:</strong> {{ $orderBranch->branch_state->name  }}</td>
                        <td><strong>State Code:</strong> {{ $orderBranch->branch_state->state_code  }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Country:</strong> {{ $orderBranch->branch_country->name  }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>GSTIN:</strong> {{ $orderBranch->gstin  }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Branch Name:</strong> {{ $orderBranch->name  }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Phone NO:</strong> + {{ $orderBranch->mobile  }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Email:</strong> {{ $orderBranch->email  }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <h4 class="section-title">{{$firstProduct->product->division->division_name}} &gt; {{$firstProduct->product->category->category_name}}</h4>
            </td>
        </tr>
    </table>

    <table class="product-table">
        <thead>
            <tr>
                <th class="text-wrap keep-word">S.No</th>
                <th class="text-wrap keep-word" style="width: 40%;">Product Description</th>
                <th class="text-wrap keep-word">Quantity</th>
                <th class="text-wrap keep-word">UOM</th>
                <th class="text-wrap keep-word">Rate ({{$currency == 'रु' ? 'NPR' : $currency}})</th>
                <th class="text-wrap keep-word">HSN Code</th>
                <th class="text-wrap keep-word">GST %</th>
                <th class="text-wrap keep-word" style="width: 15%;">Total Amount ({{$currency == 'रु' ? 'NPR' : $currency}})</th>
            </tr>
        </thead>
        <tbody>
             @php  $totalAmount=0; @endphp
                    @foreach($order->products as $key => $product)
                        <tr class="highlight" >
                            <td>{{ $key + 1 }}</td>
                            <td style="word-wrap: break-word; white-space: normal; text-align: left;">
                                {{ $product->product->product_name }}
                                {{ $product->inventory->specification ? ' - ' . $product->inventory->specification : ' -' }}
                                {{ $product->inventory->size ? ' - ' . $product->inventory->size : ' -' }}
                            </td>
                            <td style="white-space: nowrap;">{{NumberFormatterHelper::formatQty($product->product_quantity,$currency)}}</td>
                            <td>{{ $product->inventory->uom->uom_name }}</td>
                            <td style="white-space: nowrap;">{{$currency == 'रु' ? 'NPR ' : $currency}}{{NumberFormatterHelper::formatQty($product->product_price,$currency)}}</td>
                            <td>{{ optional($product->vendorProducts->first())->hsn_code ?? '--' }}</td>
                            <td style="white-space: nowrap;">{{ $product->tax->tax ?? '0' }} %</td>
                            <td style="white-space: nowrap;">
                                @php
                                    $price = $product->product_price;
                                    $qty = $product->product_quantity;
                                    $taxPercent = floatval($product->tax->tax ?? 0);

                                    $subtotal = $price * $qty;
                                    $gstAmount = $subtotal * ($taxPercent / 100);
                                    $totalWithGst = $subtotal + $gstAmount;
                                    $totalAmount+= $totalWithGst;
                                @endphp
                                {{$currency == 'रु' ? 'NPR ' : $currency}}{{NumberFormatterHelper::formatQty($totalWithGst,$currency)}}</td>
                        </tr>
                    @endforeach

        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="total-label">Total:</td>
                <td class="total-value">{{$currency == 'रु' ? 'NPR ' : $currency}}{{NumberFormatterHelper::formatQty($totalAmount,$currency)}}</td>
            </tr>
        </tfoot>
    </table>

    <div class="amount-words">
        Amount In Words: {{ CurrencyConvertHelper::numberToWordsWithCurrency($totalAmount, $currency) }}
    </div>

    <div class="footer" >
        <p><strong>ORDER GENERATED THROUGH</strong></p>
        <p><strong>Remarks:</strong> {{$order->order_remarks}}</p>
        <p><strong>Additional Remarks:</strong> {{$order->order_add_remarks}}</p>
        <p><strong>Prepared By:</strong> {{ strtoupper($order->preparedBy->name) }} For {{ strtoupper(auth()->user()->buyer->legal_name) }}</p>
    </div>

</div>

</body>
</html>
