<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation PDF - RFQ #{{$rfq->rfq_id}}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .section { margin-bottom: 20px; }
        h2, h4 { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table th, table td { border: 1px solid #333; padding: 5px; }
        table th { background: #f0f0f0; }
        .label { font-weight: bold; }
        .border-box { border: 1px solid #aaa; padding: 10px; margin-top: 10px; }
    </style>
</head>
<body onload="window.print()">

<!-- Company Logo -->
<div style="text-align: left; margin-bottom: 10px;">
    <img src="{{ asset('public/assets/images/rfq-logo.png') }}" alt="Company Logo" style="height: 60px;">
</div>

<!-- Title and Vendor Name -->
<table width="100%" style="margin-bottom: 1px; border: none; border-collapse: collapse;">
    <tr>
        <td style="text-align: left; font-size: 18px; font-weight: bold; border: none;padding: 0px;">Quotation Received</td>
        <td style="text-align: right; font-size: 18px; font-weight: bold; border: none;">{{$rfq_vendor->legal_name}}</td>
    </tr>
</table>

<p>
  RFQ No: {{$rfq->rfq_id}} |
  Vendor Address: {{$rfq_vendor->registered_address}}
</p>


<div class="section">
    <h4>RFQ Summary</h4>
    <table>
        <tr>
            <th>RFQ Date</th>
            <td>{{!empty($rfq->created_at)?date('d/m/Y',strtotime($rfq->created_at)):''}}</td>
            <th>PRN Number</th>
            <td>{{$rfq->prn_no}}</td>
        </tr>
        <tr>
            @php $branch=getbuyerBranchById($rfq->buyer_branch);@endphp
            <th>Branch</th>
            <td colspan="3">{{$branch->name}}</td>
        </tr>
        <tr>
            <th>Branch Address</th>
            <td colspan="3">
                {{$branch->address}}
            </td>
        </tr>
        <tr>
            <th>Last Date to Response</th>
            <td>{{ $rfq->last_response_date ? \Carbon\Carbon::parse($rfq->last_response_date)->format('d/m/Y') : '-' }}</td>
            <th>Delivery Period</th>
            <td>{{$rfq->buyer_delivery_period}} Days</td>
        </tr>
        <tr>
            <th>Price Basis</th>
            <td>{{$rfq->buyer_price_basis}}</td>
            <th>Payment Terms</th>
            <td>{{$rfq->buyer_pay_term}}</td>
        </tr>
    </table>
</div>

@foreach($rfq->rfqProducts as $key=> $product)

    <div class="section">
        <h4>
            {{++$key}}. {{$product->masterProduct->division->division_name}} - {{$product->masterProduct->category->category_name}} - {{$product->masterProduct->product_name}}
        </h4>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Specification</th>
                <th>Size</th>
                <th>Qty/UOM</th>
                <th>Price (₹)</th>
                <th>MRP</th>
                <th>Disc. %</th>
                <th>Total</th>
                <th>Specs</th>
            </tr>
            </thead>
            <tbody>
            @foreach($product->productVariants as $keyj => $variant)
                <tr>
                    <td>{{++$keyj}}</td>
                    <td>{!!$variant->specification!!}</td>
                    <td>{!!$variant->size!!}</td>
                    <td>{{$variant->quantity}} {{getUOMName($variant->uom)}}</td>
                    <td>{{$variant->latestVendorQuotation($vendor_id)?->price}}</td>
                    <td>{{$variant->latestVendorQuotation($vendor_id)?->mrp}}</td>
                    <td>{{$variant->latestVendorQuotation($vendor_id)?->discount}}</td>
                    <td>{{$variant->latestVendorQuotation($vendor_id)?->vendor_currency}}
                                                {{IND_money_format($variant->quantity*$variant->latestVendorQuotation($vendor_id)?->price)}}</td>
                    <td>{{ substr($variant->latestVendorQuotation($vendor_id)?->specification, 0, 25) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <p><strong>Brand:</strong>{{ $product->brand }}</p>
        <p><strong>Buyer Remarks:</strong> {{ $product->remarks}}</p>
        <p><strong>Seller Brand:</strong> {{$rfq->getLastRfqVendorQuotation->vendor_brand}}</p>

    </div>
@endforeach

<div class="section">
    <h4>Vendor Quotation Terms</h4>
    <table>
        <tr>
            <th> Remarks</th>
            <td>{{$rfq_vendor_quotation->vendor_remarks??''}}</td>
        </tr>
        <tr>
            <th>Additional Remarks</th>
            <td>{{$rfq_vendor_quotation->vendor_additional_remarks??''}}</td>
        </tr>
        <tr>
            <th>Price Basis</th>
            <td>{{$rfq_vendor_quotation->vendor_price_basis??''}}</td>
        </tr>
        <tr>
            <th>Payment Terms</th>
            <td>{{$rfq_vendor_quotation->vendor_payment_terms??''}}</td>
        </tr>
        <tr>
            <th>Delivery Period</th>
            <td>{{$rfq_vendor_quotation->vendor_delivery_period??''}} Days</td>
        </tr>
        <tr>
            <th>Price Validity</th>
            <td>{{$rfq_vendor_quotation->vendor_price_validity??''}}</td>
        </tr>
        <tr>
            <th>Dispatch Branch</th>
            <td>{{!empty($rfq_vendor_quotation->vendor_dispatch_branch)? getVendorBranchById($rfq_vendor_quotation->vendor_dispatch_branch)->name:''}}</td>
        </tr>
        <tr>
            <th>Currency</th>
            <td>{{$rfq_vendor_quotation->vendor_currency??''}}</td>
        </tr>
    </table>
</div>

<p style="text-align:right;"><small>Generated on {{date("d/m/Y")}}</small></p>

</body>
</html>
