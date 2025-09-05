@extends('buyer.layouts.app', ['title'=>'CIS Sheet'])

@section('css')
    <link rel="stylesheet" href="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.css') }}" />
    <link href="{{ asset('public/assets/library/sumoselect-v3.4.9-2/css/sumoselect.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/library/sumoselect-v3.4.9-2/css/sumo-select-style.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/buyer/css/additional-custom-style.css') }}" rel="stylesheet">
    <style>
        .no-vendor-found{
            padding: 7.6px !important;
        }
        .cis-details {
            margin-top: 20px !important;
        }
        .cis-vendor-table-heading {
            height: 150px !important;
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
            <!---CIS Statemant section-->
            <section class="card rounded">
                <div class="card-header bg-white">
                    <div class="row gy-3 justify-content-between align-items-center py-3 px-0 px-md-3 mb-30">
                        <div class="col-12 col-sm-auto order-2 order-sm-1">
                            <h1 class="text-primary-blue font-size-27">Comparative Information Statement</h1>
                        </div>
                        <div class="col-12 col-sm-auto order-1 order-sm-2">
                            <div
                                class="row gx-3 gy-2 align-items-center justify-content-center justify-content-sm-end">
                                
                                <div class="col-auto">
                                    <button type="button" class="ra-btn ra-btn-outline-primary px-2 font-size-11" onclick="window.location.reload();">
                                        <span class="bi bi-arrow-clockwise font-size-12" aria-hidden="true"></span>
                                        Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="cis-info pb-2 px-0 px-md-3  d-none d-sm-flex">
                        <div class="cis-info-left">
                            <ul>
                                <li>RFQ No. : {{ $rfq['rfq_id'] }}</li>
                                <li>PRN Number : {{ $rfq['prn_no'] }}</li>
                                <li>Branch/Unit Details : {{ $rfq['buyer_branch_name'] }}</li>
                                <li>Last Date to Response: {{ $rfq['last_response_date'] ? date('d/m/Y', strtotime($rfq['last_response_date'])) : '' }}</li>
                                @if(!empty($rfq['edit_by']))
                                <li>Last Edited Date: {{ $rfq['updated_at'] ? date('d/m/Y', strtotime($rfq['updated_at'])) : '' }}</li>
                                @endif
                            </ul>
                        </div>
                        <div class="cis-info-right">
                            RFQ Date: {{ $rfq['created_at'] ? date('d/m/Y', strtotime($rfq['created_at'])) : '' }}
                        </div>
                    </div>
                        
                   

                    <div class="cis-details py-3 px-0 px-md-3 d-none d-sm-block">
                        <div class="row g-0 gy-5">
                            <div class="col-6 col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-bordered border-dark cis-table-left">
                                        <thead>
                                            <tr>
                                                <th colspan="3" class="border-right-0 p-0 align-bottom">
                                                    <div class="p-3">
                                                        <div class="mb-4">
                                                            <h2 class="cis-table-left-heading text-primary-blue ">
                                                                Your Exclusive Automated CIS
                                                            </h2>
                                                            <h3 class="font-size-10 fw-bold text-danger-red p-0 m-0">
                                                                @if($rfq['is_auction'] == 1)
                                                                    @if($rfq['is_rfq_price_map'] == 1)
                                                                        NOTE: These are updated Rates post AUCTION that was held on {{ date('d/m/Y', strtotime($rfq['auction_date'])) }}
                                                                    @endif
                                                                @endif
                                                            </h3>
                                                        </div>
                                                        <nav aria-label="breadcrumb">
                                                            <ol class="breadcrumb breadcrumb-cis">
                                                                <li class="breadcrumb-item">{{$rfq['rfq_division']}}</li>
                                                                <li class="breadcrumb-item active" aria-current="page">{{$rfq['rfq_category']}} </li>
                                                            </ol>
                                                        </nav>
                                                    </div>
                                                </th>
                                                <th colspan="2" class="border-left-0 p-0 align-bottom">
                                                    <div class="cis-table-headings text-end text-nowrap px-2">
                                                        <p>Vendor's Name</p>
                                                        <p>Vendor's Contact</p>
                                                        <p>No. of Product Quoted</p>
                                                        <p>Latest Quote Received on</p>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col" class="p-2 cis-table-left-w-200">Product</th>
                                                <th scope="col" class="p-2 text-nowrap">Specifications</th>
                                                <th scope="col" class="p-2 text-nowrap">Size</th>
                                                <th scope="col" class="p-2 text-nowrap">Quantity/UOM</th>
                                                
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($cis['variants'] as $variant_id => $variants)
                                            <tr>
                                                <td class="align-middle p-1 text-nowrap position-relative" scope="row">
                                                    <span class="name-tooltip">
                                                        {!! 
                                                            strlen($variants['product_name']) > 20 
                                                                ? substr($variants['product_name'], 0, 20)
                                                                : $variants['product_name'] 
                                                        !!}
                                                    </span>
                                                    @if(strlen($variants['product_name']) > 20)
                                                    <span role="button" type="button" class="p-0 infoIcon" data-bs-toggle="tooltip" data-bs-placement="top" title="{!! $variants['product_name'] !!}">
                                                        <span class="bi bi-info-circle-fill text-dark font-size-14" aria-hidden="true"></span>
                                                    </span>
                                                    @endif
                                                </td>
                                                <td class="align-middle p-1">
                                                    <span class="name-tooltip">
                                                        {!! 
                                                            strlen($variants['specification']) > 20 
                                                                ? substr($variants['specification'], 0, 20)
                                                                : $variants['specification'] 
                                                        !!}
                                                    </span>
                                                    @if(strlen($variants['specification']) > 20)
                                                    <span role="button" type="button" class="p-0 infoIcon" data-bs-toggle="tooltip" data-bs-placement="top" title="{!! $variants['specification'] !!}">
                                                        <span class="bi bi-info-circle-fill text-dark font-size-14" aria-hidden="true"></span>
                                                    </span>
                                                    @endif
                                                </td>
                                                <td class="align-middle p-1">
                                                    <span class="name-tooltip">
                                                        {!! 
                                                            strlen($variants['size']) > 20 
                                                                ? substr($variants['size'], 0, 20)
                                                                : $variants['size'] 
                                                        !!}
                                                    @if(strlen($variants['size']) > 20)
                                                    </span>
                                                    <span role="button" type="button" class="p-0 infoIcon" data-bs-toggle="tooltip" data-bs-placement="top" title="{!! $variants['size'] !!}">
                                                        <span class="bi bi-info-circle-fill text-dark font-size-14" aria-hidden="true"></span>
                                                    </span>
                                                    @endif
                                                </td>
                                                <td class="align-middle p-1">
                                                    {{ $variants['quantity'] }} {{ $uom[$variants['uom']] }}
                                                    @if(!empty($variants['orders']))
                                                        @php
                                                            $variant_order_history = [];
                                                        @endphp
                                                        @foreach($variants['orders'] as $vendor_id => $orders)
                                                            @foreach($orders as $order_qty)
                                                                @php
                                                                    $variant_order_history[] = $cis['vendors'][$vendor_id]['legal_name'] .'->'.$order_qty;
                                                                @endphp
                                                            @endforeach
                                                        @endforeach
                                                        <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ implode(', ', $variant_order_history) }}">
                                                            <span class="bi bi-info-circle-fill text-dark font-size-11" aria-hidden="true"></span>
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                            <tr>
                                                <td class="align-middle p-1 bg-pink text-uppercase fw-bold"
                                                    scope="row">
                                                    Total
                                                </td>
                                                <td class="align-middle p-1" colspan="4"></td>
                                                {{-- <td class="align-middle p-1"></td> --}}
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1" scope="row">Price Basis</td>
                                                <td class="align-middle p-1" colspan="4">{{ $rfq['buyer_price_basis'] }}</td>
                                                {{-- <td class="align-middle p-1"></td> --}}
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1" scope="row">Payment Terms</td>
                                                <td class="align-middle p-1" colspan="4">{{ $rfq['buyer_pay_term'] }}</td>
                                                {{-- <td class="align-middle p-1"></td> --}}
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1" scope="row">Delivery Period</td>
                                                <td class="align-middle p-1" colspan="4">{{ $rfq['buyer_delivery_period'] ? $rfq['buyer_delivery_period']. ' Days' : '' }}</td>
                                                {{-- <td class="align-middle p-1"></td> --}}
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1" scope="row">Seller Brand</td>
                                                <td class="align-middle p-1" colspan="4"></td>
                                                {{-- <td class="align-middle p-1"></td> --}}
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1" scope="row">Remarks</td>
                                                <td class="align-middle p-1" colspan="4"></td>
                                                {{-- <td class="align-middle p-1"></td> --}}
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1" scope="row">Additional Remarks </td>
                                                <td class="align-middle p-1" colspan="4"></td>
                                                {{-- <td class="align-middle p-1"></td> --}}
                                            </tr>
                                            
                                           
                                            <tr>
                                                <td class="align-middle p-1 bg-pink fw-bold" scope="row"
                                                    colspan="5">
                                                    Company Information
                                                </td>
                                            </tr>
                                            <tr class="toggle-row">
                                                <td class="align-middle p-1" scope="row">Vintage</td>
                                                <td class="align-middle p-1" colspan="4"></td>
                                                {{-- <td class="align-middle p-1"></td> --}}
                                            </tr>
                                            <tr class="toggle-row">
                                                <td class="align-middle p-1" scope="row">Business Type</td>
                                                <td class="align-middle p-1" colspan="4"></td>
                                                {{-- <td class="align-middle p-1"></td> --}}
                                            </tr>
                                            <tr class="toggle-row">
                                                <td class="align-middle p-1" scope="row">Main Products</td>
                                                <td class="align-middle p-1" colspan="4"></td>
                                                {{-- <td class="align-middle p-1"></td> --}}
                                            </tr>
                                            <tr class="toggle-row">
                                                <td class="align-middle p-1" scope="row">Client</td>
                                                <td class="align-middle p-1" colspan="4"></td>
                                                {{-- <td class="align-middle p-1"></td> --}}
                                            </tr>
                                            <tr class="toggle-row">
                                                <td class="align-middle p-1 text-nowrap" scope="row">
                                                    Certifications-MSME/ISO
                                                </td>
                                                <td class="align-middle p-1" colspan="4"></td>
                                                {{-- <td class="align-middle p-1"></td> --}}
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-6 col-md-6 position-relative">
                                <div class="d-flex justify-content-between prev-next-table-space d-none d-sm-flex">
                                    <button type="button" id="scrollLeft"
                                        class="btn btn-link min-width-inherit height-inherit p-0">
                                    <span class="visually-hidden-focusable">scroll left</span>
                                    <span class="bi bi-arrow-left font-size-20 text-primary-blue"
                                        aria-hidden="true"> </span>
                                    </button>
                                    <button type="button" id="scrollRight"
                                        class="btn btn-link min-width-inherit height-inherit p-0">
                                    <span class="visually-hidden-focusable">scroll right</span>
                                    <span class="bi bi-arrow-right font-size-20 text-primary-blue"
                                        aria-hidden="true"> </span>
                                    </button>
                                </div>
                                <div class="table-responsive" id="tableScrollContainer">
                                    @if($is_date_filter == true && empty($cis['filter_vendors']))
                                    <table class="table table-bordered border-dark cis-vendor-table cis-table-mobile">
                                        <tr>
                                            <th class="text-center no-vendor-found">No vendor Found...</th>
                                        </tr>
                                    </table>
                                    @else
                                    <table
                                        class="table table-bordered border-dark cis-vendor-table cis-table-mobile">
                                        <thead>
                                            <tr>
                                                @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                @php
                                                    if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                        continue;
                                                    }
                                                @endphp
                                                <th scope="col">
                                                    {{-- @if($rfq['is_auction'] == 2)
                                                        @if(in_array($vendor['vendor_rfq_status'], [1, 4]))
                                                        <div class="position-relative">
                                                            <div class="cis-vendor-notification">
                                                                <a href="javascript:void(0)" class="send-reminder-notification" title="Remind the Vendor"><span class=" ml-10 bi bi-bell"></span></a>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    @endif --}}
                                                    <div class="cis-vendor-table-heading text-center">
                                                        <p>
                                                            <a href="javascript:void(0)" target="_blank"
                                                                class="font-size-13 cursor-pointer"
                                                                title="{{ $vendor['legal_name'] }}">
                                                                {!! 
                                                                    strlen($vendor['legal_name']) > 12 
                                                                        ? substr($vendor['legal_name'], 0, 12)
                                                                        : $vendor['legal_name'] 
                                                                !!}
                                                            </a>
                                                            <span class="font-size-13">
                                                                <input type="checkbox" class="select-vendor-product" value="{{$vendor_id}}" name="vendor_id[]">
                                                            </span>
                                                        </p>
                                                        <p>{{ $vendor['country_code'] ? '+'.$vendor['country_code'] : '' }} {{$vendor['mobile']}}</p>
                                                        <p>{{ $vendor['vendor_quoted_product'] }}</p>
                                                        <p>{{ !empty($vendor['latest_quote']) ? date('d/m/Y', strtotime($vendor['latest_quote']['created_at'])) : '' }}</p>
                                                    </div>
                                                </th>
                                                @endforeach
                                            </tr>
                                            <!-- <tr>
                                                @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                @php
                                                    if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                        continue;
                                                    }
                                                @endphp
                                                <th scope="col" class="text-center p-2 bg-white">
                                                    <a target="_blank" href="{{ route('buyer.rfq.quotation-received', ['rfq_id' => $rfq['rfq_id'], 'vendor_id' => $vendor_id]) }}"
                                                        class="text-decoration-underline text-primary-blue"> View Quotation </a>
                                                </th>
                                                @endforeach
                                            </tr> -->
                                            <tr>
                                                @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                @php
                                                    if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                        continue;
                                                    }
                                                @endphp
                                                <th scope="col" class="text-center p-2 bg-white">Rate ({{!empty($vendor['latest_quote']) && !empty($vendor['latest_quote']['vendor_currency']) ? $vendor['latest_quote']['vendor_currency'] : '₹'}})</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($cis['variants'] as $variant_id => $variants)
                                                <tr>
                                                    @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                        @php
                                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                                continue;
                                                            }
                                                        @endphp
                                                        @if(isset($cis['is_vendor_product'][$vendor_id]) && isset($cis['is_vendor_product'][$vendor_id][$variants['product_id']]))
                                                        @php
                                                            $vendor_last_quote = isset($vendor['last_quote'][$variant_id]) ? $vendor['last_quote'][$variant_id] : [];
                                                        @endphp
                                                        <td class="product-price p-1 align-middle {{ !empty($vendor_last_quote) && $vendor_last_quote['price']==$variants['lowest_price'] ? 'bg-gold' : '' }} ">
                                                            @if(!empty($vendor_last_quote))
                                                            <div class="d-flex justify-content-center align-items-center gap-4">
                                                                @php
                                                                    $vendor_quote_history = isset($vendor['vendorQuotes'][$variant_id]) ? $vendor['vendorQuotes'][$variant_id] : [];
                                                                    $quote_history = [];
                                                                    foreach ($vendor_quote_history as $item) {
                                                                        $timestamp = strtotime($item['created_at']);
                                                                        $formatted_date = date('d-M', $timestamp); // e.g., 22-Jul
                                                                        $quote_history[] = "{$item['price']} ({$formatted_date})";
                                                                    }
                                                                    $final_quote_history_string = implode(', ', $quote_history);
                                                                @endphp
                                                                <div>
                                                                    <span role="button" type="button" class="p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="{{$final_quote_history_string}}">
                                                                        <span class="bi bi-info-circle-fill text-dark font-size-11" aria-hidden="true"></span>
                                                                    </span>
                                                                </div>

                                                                <div class="vendor-variant-price">
                                                                    {{IND_money_format($vendor_last_quote['price'])}}
                                                                    <!-- This Checkbox will show when Buyer click Proceed to order Button -->
                                                                    @if(!empty($vendor['latest_quote']) && $vendor['latest_quote']['left_qty'] > 0)
                                                                    <span class="font-size-13 d-none">
                                                                        <input type="checkbox" name="proceed_to_order" value="{{$vendor_id}}" data-variant-id="{{$variant_id}}" class="proceed-to-order-input proceed-to-order-{{$vendor_id}}">
                                                                    </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </td>
                                                        @else
                                                        <td class="product-price p-1 align-middle text-center">
                                                            <span class="fa fa-close" aria-hidden="true" style="font-weight: 900;">X</span>
                                                        </td>
                                                        @endif
                                                    @endforeach
                                                </tr>                                            
                                            @endforeach
                                            <tr>
                                                @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                    @php
                                                        if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                            continue;
                                                        }
                                                    @endphp
                                                <td class="product-price p-1 align-middle text-center {{ !empty($cis['vendor_total_amount'][$vendor_id]) && $cis['vendor_total_amount'][$vendor_id] == $rfq['lowest_price_total'] ? 'bg-gold' : '' }} ">
                                                    <b>
                                                    {{!empty($vendor['latest_quote']) && !empty($vendor['latest_quote']['vendor_currency']) ? $vendor['latest_quote']['vendor_currency'] : '₹'}} {{$cis['vendor_total_amount'][$vendor_id] ? IND_money_format($cis['vendor_total_amount'][$vendor_id]) : 0}}
                                                    </b>
                                                </td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                    @php
                                                        if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                            continue;
                                                        }
                                                    @endphp
                                                <td class="product-price p-1 align-middle text-center">
                                                    @php
                                                        $vendor_price_basis = !empty($vendor['latest_quote']) ? $vendor['latest_quote']['vendor_price_basis'] : '';
                                                    @endphp
                                                    <span title="{{$vendor_price_basis}}">
                                                        {!! 
                                                            strlen($vendor_price_basis) > 12 
                                                                ? substr($vendor_price_basis, 0, 12)
                                                                : $vendor_price_basis 
                                                        !!}
                                                    </span>
                                                    @if(strlen($vendor_price_basis) > 12)
                                                    <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $vendor_price_basis }}">
                                                        <span class="bi bi-info-circle-fill text-dark font-size-11" aria-hidden="true"></span>
                                                    </span>
                                                    @endif
                                                </td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                    @php
                                                        if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                            continue;
                                                        }
                                                    @endphp
                                                <td class="product-price p-1 align-middle text-center">
                                                    @php
                                                        $vendor_payment_terms = !empty($vendor['latest_quote']) ? $vendor['latest_quote']['vendor_payment_terms'] : '';
                                                    @endphp
                                                    <span title="{{$vendor_payment_terms}}">
                                                        {!! 
                                                            strlen($vendor_payment_terms) > 12 
                                                                ? substr($vendor_payment_terms, 0, 12)
                                                                : $vendor_payment_terms 
                                                        !!}
                                                    </span> 
                                                    @if(strlen($vendor_payment_terms) > 12)
                                                    <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $vendor_payment_terms }}">
                                                        <span class="bi bi-info-circle-fill text-dark font-size-11" aria-hidden="true"></span>
                                                    </span>
                                                    @endif
                                                </td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                    @php
                                                        if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                            continue;
                                                        }
                                                    @endphp
                                                <td class="product-price p-1 align-middle text-center">
                                                    @php
                                                        $vendor_delivery_period = !empty($vendor['latest_quote']) ? $vendor['latest_quote']['vendor_delivery_period'].' Days' : '';
                                                    @endphp
                                                    <span title="{{ $vendor_delivery_period }}">
                                                        {{ $vendor_delivery_period }}
                                                    </span>
                                                </td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                    @php
                                                        if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                            continue;
                                                        }
                                                    @endphp
                                                <td class="product-price p-1 align-middle text-center">
                                                    @php
                                                        $vendor_brand = !empty($vendor['vendor_brand']) ? $vendor['vendor_brand'] : '';
                                                    @endphp
                                                    <span title="{{ $vendor_brand }}">
                                                        {!! 
                                                            strlen($vendor_brand) > 12 
                                                                ? substr($vendor_brand, 0, 12)
                                                                : $vendor_brand 
                                                        !!}
                                                    </span>
                                                    @if(strlen($vendor_brand) > 12)
                                                    <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $vendor_brand }}">
                                                        <span class="bi bi-info-circle-fill text-dark font-size-11" aria-hidden="true"></span>
                                                        <span class="visually-hidden-focusable">info</span>
                                                    </span>
                                                    @endif
                                                </td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                    @php
                                                        if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                            continue;
                                                        }
                                                    @endphp
                                                <td class="product-price p-1 align-middle text-center">
                                                    @php
                                                        $vendor_remarks = !empty($vendor['latest_quote']) ? $vendor['latest_quote']['vendor_remarks'] : '';
                                                    @endphp
                                                    <span title="{{ $vendor_remarks }}">
                                                        {!! 
                                                            strlen($vendor_remarks) > 12 
                                                                ? substr($vendor_remarks, 0, 12)
                                                                : $vendor_remarks 
                                                        !!}
                                                    </span>
                                                    @if(strlen($vendor_remarks) > 12)
                                                    <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $vendor_remarks }}">
                                                        <span class="bi bi-info-circle-fill text-dark font-size-11" aria-hidden="true"></span>
                                                        <span class="visually-hidden-focusable">info</span>
                                                    </span>
                                                    @endif
                                                </td>
                                                @endforeach
                                            </tr>
                                          
                                            <tr>
                                                @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                    @php
                                                        if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                            continue;
                                                        }
                                                    @endphp
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                @endforeach
                                                {{-- <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td> --}}
                                            </tr>
                                            <tr>
                                                <td colspan="{{count($cis['vendors'])-count($cis['filter_vendors'])}}" class="bg-pink ps-2 align-middle">
                                                    <span role="button" type="button" class="toggle-row-button p-0">
                                                        <span class="bi bi-chevron-up text-dark"></span>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr class="toggle-row">
                                                @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                    @php
                                                        if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                            continue;
                                                        }
                                                    @endphp
                                                <td class="product-price p-1 align-middle text-center">
                                                    {{ $vendor['vintage'] }} Years
                                                </td>
                                                @endforeach
                                            </tr>
                                            <tr class="toggle-row">
                                                @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                    @php
                                                        if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                            continue;
                                                        }
                                                    @endphp
                                                <td class="product-price p-1 align-middle text-center">
                                                    {{ $nature_of_business[$vendor['nature_of_business']] }}
                                                </td>
                                                @endforeach
                                            </tr>
                                            <tr class="toggle-row">
                                                @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                    @php
                                                        if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                            continue;
                                                        }
                                                    @endphp
                                                <td class="product-price p-1 align-middle text-center">
                                                    @php
                                                        $vendor_product = !empty($vendor['vendor_product']) ? $vendor['vendor_product'] : '';
                                                    @endphp
                                                    <span title="{{ $vendor_product }}">
                                                        {!! 
                                                            strlen($vendor_product) > 12 
                                                                ? substr($vendor_product, 0, 12)
                                                                : $vendor_product 
                                                        !!}
                                                    </span>
                                                    @if(strlen($vendor_product) > 12)
                                                    <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $vendor_product }}">
                                                        <span class="bi bi-info-circle-fill text-dark font-size-11" aria-hidden="true"></span>
                                                    </span>
                                                    @endif
                                                </td>
                                                @endforeach
                                            </tr>
                                            <tr class="toggle-row">
                                                @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                    @php
                                                        if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                            continue;
                                                        }
                                                    @endphp
                                                <td class="product-price p-1 align-middle text-center">
                                                    @php
                                                        $client = !empty($vendor['client']) ? $vendor['client'] : '';
                                                    @endphp
                                                    <span title="{{ $client }}">
                                                        {!! 
                                                            strlen($client) > 12 
                                                                ? substr($client, 0, 12)
                                                                : $client 
                                                        !!}
                                                    </span>
                                                    @if(strlen($client) > 12)
                                                    <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $client }}">
                                                        <span class="bi bi-info-circle-fill text-dark font-size-11" aria-hidden="true"></span>
                                                    </span>
                                                    @endif
                                                    {{-- <span title="Ankit,Saurba">Ankit,Saurba </span><span
                                                        role="button" type="button" class="p-0"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Ankit,Saurba">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    </span> --}}
                                                </td>
                                                @endforeach
                                            </tr>
                                            <tr class="toggle-row">
                                                @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                    @php
                                                        if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                            continue;
                                                        }
                                                    @endphp
                                                <td class="product-price p-1 align-middle text-center">
                                                    @php
                                                        $certifications = !empty($vendor['certifications']) ? $vendor['certifications'] : '';
                                                    @endphp
                                                    <span title="{{ $certifications }}">
                                                        {!! 
                                                            strlen($certifications) > 12 
                                                                ? substr($certifications, 0, 12)
                                                                : $certifications 
                                                        !!}
                                                    </span>
                                                    @if(strlen($certifications) > 12)
                                                    <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $certifications }}">
                                                        <span class="bi bi-info-circle-fill text-dark font-size-11" aria-hidden="true"></span>
                                                    </span>
                                                    @endif
                                                </td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Start of CIS details Mobile only -->
                    <div class="cis-details cis-details-mobile d-sm-none">
                        <div class="cis-details-mobile-wrapper">
                            <div
                                class="card-header border-0 bg-white d-flex align-items-center justify-content-between py-3">
                                <button class="cis-mobile-toggle-button ra-btn btn-show-hide bg-white w-100 justify-content-start">
                                    <h2 class="font-size-14 fw-bold text-primary-blue">COPPER MOULD TUBE</h2>
                                    <span id="toggleIcon" class="toggle-icon bi bi-chevron-up"></span>
                                </button>
                            </div>
                            <div class="cis-details-mobile-wrapper-content">
                                <div>
                                    <h3 class="font-size-14 fw-bold">Specifications</h3>
                                    <div class="mb-3">Lorem Ipsum is simply dummy text of the printing and
                                        typesetting industry
                                    </div>
                                    <p><strong>Size:</strong> 12 &nbsp; &nbsp; | &nbsp; &nbsp;
                                        <strong>Quantity/UOM:</strong> 100 Pieces
                                    </p>
                                    <div class="list-of-vendors mt-4">
                                        <ul
                                            class="list-of-vendors-heading border-bottom-0 rounded-0 rounded-top rounded-right bg-light py-2">
                                            <li class="d-flex">
                                                <span class="vendor-name fw-bold">Vendor Name</span>
                                                <span class="vendor-price fw-bold">Rate (₹)</span>
                                            </li>
                                        </ul>
                                        <ul class="rounded-0 rounded-bottom rounded-left">
                                            <li class="d-flex">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="RONIT VENDOR">
                                                RONIT VENDOR
                                                </a>
                                                </span>
                                                <span class="vendor-price">600</span>
                                            </li>
                                            <li class="d-flex">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="NARSHINGH ENTERPRISE">
                                                NARSHINGH EN
                                                </a>
                                                </span>
                                                <span class="vendor-price">550</span>
                                            </li>
                                            <li class="d-flex">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="Wipro Steel">
                                                WIPRO STEEL
                                                </a>
                                                </span>
                                                <span class="vendor-price">720</span>
                                            </li>
                                            <li class="toggle-vendor-list">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="CISCO">
                                                CISCO
                                                </a>
                                                </span>
                                                <span class="vendor-price">520</span>
                                            </li>
                                            <li class="toggle-vendor-list">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="New India BE">
                                                New India BE
                                                </a>
                                                </span>
                                                <span class="vendor-price">640</span>
                                            </li>
                                            <li class="toggle-vendor-list">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="Guru RaProcure">
                                                Guru RaProcure
                                                </a>
                                                </span>
                                                <span class="vendor-price">480</span>
                                            </li>
                                        </ul>
                                        <div
                                            class="show-more d-flex align-items-center justify-content-center py-2">
                                            <span role="button" type="button"
                                                class="toggle-show-more-button d-flex align-items-center fw-bold text-primary-blue height-inherit  p-0">
                                            Show More <span
                                                class="bi bi-chevron-down text-dark ms-1 font-size-13"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="cis-details-mobile-wrapper">
                            <div
                                class="card-header border-0 bg-white d-flex align-items-center justify-content-between py-3">
                                <button class="cis-mobile-toggle-button ra-btn btn-show-hide bg-white w-100 justify-content-start">
                                    <h2 class="font-size-14 fw-bold text-primary-blue">ADAPTER SOCKET</h2>
                                    <span id="toggleIcon" class="toggle-icon bi bi-chevron-up"></span>
                                </button>
                            </div>
                            <div class="cis-details-mobile-wrapper-content">
                                <div>
                                    <h3 class="font-size-14 fw-bold">Specifications</h3>
                                    <div class="mb-3">Lorem Ipsum is simply dummy text of the printing and
                                        typesetting industry
                                    </div>
                                    <p><strong>Size:</strong> 12 &nbsp; &nbsp; | &nbsp; &nbsp;
                                        <strong>Quantity/UOM:</strong> 100 Pieces
                                    </p>
                                    <div class="list-of-vendors mt-4">
                                        <ul
                                            class="list-of-vendors-heading border-bottom-0 rounded-0 rounded-top rounded-right bg-light py-2">
                                            <li class="d-flex">
                                                <span class="vendor-name fw-bold">Vendor Name</span>
                                                <span class="vendor-price fw-bold">Rate (₹)</span>
                                            </li>
                                        </ul>
                                        <ul class="rounded-0 rounded-bottom rounded-left">
                                            <li class="d-flex">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="RONIT VENDOR">
                                                RONIT VENDOR
                                                </a>
                                                </span>
                                                <span class="vendor-price">600</span>
                                            </li>
                                            <li class="d-flex">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="NARSHINGH ENTERPRISE">
                                                NARSHINGH EN
                                                </a>
                                                </span>
                                                <span class="vendor-price">550</span>
                                            </li>
                                            <li class="d-flex">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="Wipro Steel">
                                                WIPRO STEEL
                                                </a>
                                                </span>
                                                <span class="vendor-price">720</span>
                                            </li>
                                            <li class="toggle-vendor-list">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="CISCO">
                                                CISCO
                                                </a>
                                                </span>
                                                <span class="vendor-price">520</span>
                                            </li>
                                            <li class="toggle-vendor-list">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="New India BE">
                                                New India BE
                                                </a>
                                                </span>
                                                <span class="vendor-price">640</span>
                                            </li>
                                            <li class="toggle-vendor-list">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="Guru RaProcure">
                                                Guru RaProcure
                                                </a>
                                                </span>
                                                <span class="vendor-price">480</span>
                                            </li>
                                        </ul>
                                        <div
                                            class="show-more d-flex align-items-center justify-content-center py-2">
                                            <span role="button" type="button"
                                                class="toggle-show-more-button d-flex align-items-center fw-bold text-primary-blue height-inherit  p-0">
                                            Show More <span
                                                class="bi bi-chevron-down text-dark ms-1 font-size-13"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End of CIS details Mobile only -->
                </div>
            </section>
            <!---Gap Creation-->
            <div class="fill-more-details d-none d-sm-block"></div>
            <!-- Floating CIS options-->
            <section class="floting-product-options cis-floating-button d-none d-sm-block">
                <div class="d-flex flex-wrap flex-md-nowrap align-items-center justify-content-center gap-3">
                    <button type="button"
                        class="ra-btn btn-outline-primary ra-btn-outline-primary text-uppercase text-nowrap font-size-10"
                        data-bs-toggle="modal" data-bs-target="#createAuctionModal"><span
                        class="bi bi-calendar-date font-size-12" aria-hidden="true"></span> VIEW/EDIT AUCTION
                    </button>
                    <button type="button"
                        class="ra-btn btn-outline-primary ra-btn-outline-primary text-uppercase text-nowrap font-size-10"
                        data-bs-toggle="modal" data-bs-target="#messageModal"><span class="bi bi-send font-size-12"
                        aria-hidden="true"></span> SEND MESSAGE
                    </button>
                    <a type="button" href="{{ route('buyer.rfq.counter-offer', ['rfq_id' => $rfq['rfq_id'], 'vendor_id' => 1]) }}"
                        class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10"><span
                        class="bi bi-repeat font-size-12" aria-hidden="true"></span> COUNTER OFFER
                    </a>
                    <a type="button" 
                        class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10 proceed-to-order-btn"><span
                        class="bi bi-check2-square font-size-12" aria-hidden="true"></span> PROCEED TO ORDER 
                    </a>
                    {{-- href="{{ route('buyer.unapproved-orders.create', ['rfq_id' => $rfq['rfq_id']]) }}" --}}
                </div>
            </section>
        </div>
    </main>

     
    
    <!-- Modal Create Auction -->
    @include('buyer.auction.partials.create-auction-modal', [
        'modalId'               => 'createAuctionModal',
        'action'                => route('buyer.auction.create'),
        'rfqId'                 => $rfq['rfq_id'] ?? null,
        'rfqType'               => $rfq['rfq_type'] ?? 'Scheduled',
        'currentStatus'         => $current_status ?? null,
        'editId'                => $editId ?? null,
        'vendors'               => $cis['vendors'] ?? [],     // each: ['vendor_user_id','legal_name']
        'variants'              => $cis['variants'] ?? [],
        'currencies'            => $currencies ?? [],
        // NEW:
        'selectedVendorIds'     => $selectedVendorIds ?? [],
        'prefill'               => $prefill ?? [],
        'prefillVariantPrices'  => $prefillVariantPrices ?? [],
    ])



    <!-- Modal Message -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header bg-graident text-white">
                    <h2 class="modal-title font-size-12" id="messageModalLabel"><span class="bi bi-pencil"
                            aria-hidden="true"></span> New Message</h2>
                    <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="message pt-3">
                        <div class="dropdown">
                            <button id="dropdownButtonLastVendor"
                                class="btn btn-outline-default custom-multiselect-dropdown-btn dropdown-toggle justify-content-between"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Select Vendor
                            </button>
                            <div class="dropdown-menu custom-multiselect-dropdown-menu">
                                <div class="sticky-top-option">
                                    <div class="mt-1">
                                        <label class="ra-custom-checkbox mb-0">
                                            <input type="checkbox">
                                            <span class="font-size-13 ra-custom-checkbox-label">Select
                                                All</span>
                                            <span class="checkmark "></span>
                                        </label>
                                    </div>
                                </div>
                                <ul class="filter-list scroll-list p-0">
                                    <li>
                                        <div class="mt-1">
                                            <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-13 ra-custom-checkbox-label">RONIT VENDOR
                                                    PROFILE COMPANY QWE</span>
                                                <span class="checkmark "></span>
                                            </label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="mt-1">
                                            <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-13 ra-custom-checkbox-label">GURU VENDOR
                                                    PVT AND LTD</span>
                                                <span class="checkmark "></span>
                                            </label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="mt-1">
                                            <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-13 ra-custom-checkbox-label">MY RAPROCURE
                                                    VENDOR PVT LTD</span>
                                                <span class="checkmark "></span>
                                            </label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="mt-1">
                                            <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-13 ra-custom-checkbox-label">ABC GURU
                                                    TEST VENDOR PVT LTD</span>
                                                <span class="checkmark "></span>
                                            </label>
                                        </div>
                                    </li>
                                </ul>

                            </div>

                        </div>
                    </div>
                    <div class="message pt-2">
                        <input type="text" value="RONI-25-00056" class="form-control" readonly>
                    </div>

                    <section class="ck-editor-section py-2">
                        <textarea name="" id="" rows="5" class="form-control height-inherit"
                            placeholder="This is the placeholder of the editor."></textarea>
                    </section>
                    <section class="upload-file py-2">
                        <div class="file-upload-block justify-content-start">
                            <div class="file-upload-wrapper">
                                <input type="file" class="file-upload" style="display: none;">
                                <button type="button"
                                    class="custom-file-trigger form-control text-start text-dark font-size-13">Upload
                                    file</button>
                            </div>
                            <div class="file-info" style="display: none;"></div>
                        </div>
                    </section>

                </div>
                <div class="modal-footer">
                    <button type="button"
                        class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11">Send</button>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('scripts')
    <script src="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>
    <script src="{{ asset('public/assets/library/sumoselect-v3.4.9-2/js/jquery.sumoselect.min.js') }}"></script>

    {{-- <script src="{{ asset('public/assets/login/crypto-js/crypto.js') }}"></script> --}}

    <script>
        $('.location-sumo-select').SumoSelect({selectAll: true, nativeOnDevice: [], maxHeight:100, csvDispCount: 7, placeholder: 'Select Location' });
        $('.favourite-vendor-sumo-select').SumoSelect({selectAll: true, nativeOnDevice: [], maxHeight:100, csvDispCount: 7, placeholder: 'Select Favorite' });
        $('.last-vendor-sumo-select').SumoSelect({selectAll: true, nativeOnDevice: [], maxHeight:100, csvDispCount: 7, placeholder: 'Select Last Vendor' });

        jQuery(function () {
            const today = new Date();

            function parseDate(str) {
                if (!str) return null;
                const [d, m, y] = str.split('/');
                return new Date(`${y}-${m}-${d}`);
            }

            jQuery('.dateTimePickerStart').datetimepicker({
                lang: 'en',
                timepicker: false,
                format: 'd/m/Y',
                formatDate: 'd-m-Y',
                maxDate: today,
                scrollMonth: false,
                scrollInput: false,
                onShow: function () {
                    const toDateVal = parseDate(jQuery('.dateTimePickerEnd').val());
                    this.setOptions({
                        maxDate: toDateVal && toDateVal < today ? toDateVal : today
                    });
                },
                onChangeDateTime: function () {
                    // jQuery('.dateTimePickerEnd').datetimepicker('show');  // Trigger update of restrictions
                }
            });

            jQuery('.dateTimePickerEnd').datetimepicker({
                lang: 'en',
                timepicker: false,
                format: 'd/m/Y',
                formatDate: 'd-m-Y',
                maxDate: today,
                scrollMonth: false,
                scrollInput: false,
                onShow: function () {
                    const fromDateVal = parseDate(jQuery('.dateTimePickerStart').val());
                    this.setOptions({
                        minDate: fromDateVal || false,
                        maxDate: today
                    });
                },
                onChangeDateTime: function () {
                    // jQuery('.dateTimePickerStart').datetimepicker('show');  // Trigger update of restrictions
                }
            });

            var isActiveProceedToOrder = false;

            $(document).on('click', '.proceed-to-order-btn', function() {
                if(isActiveProceedToOrder==false) {
                    if($(".proceed-to-order-input").length<=0){
                        if($(".vendor-variant-price").length>=0){
                            toastr.error("No Product quantity has been left to send order.");
                        }else{
                            toastr.error("No Price Counter found.");
                        }
                        return false;
                    }
                    $(".proceed-to-order-input").prop("checked", false).parent().removeClass('d-none');
                    toastr.success("Kindly select Vendor or Product to proceed further.");
                    isActiveProceedToOrder = true;
                    return false;
                }

                if($(".proceed-to-order-input:checked").length<=0){
                    toastr.error("Please select the vendors or products individually.");
                    return false;
                }
                
                let vendor_data = [];
                $(".proceed-to-order-input:checked").each(function() {
                    vendor_data.push($(this).val()+"-"+$(this).data("variant-id"));
                });
                
                // console.log(vendor_data);
                let vendor_str_data = vendor_data.join(",");
                let url = "{{ route('buyer.unapproved-orders.create', ['rfq_id' => $rfq['rfq_id']]) }}";
                url = url + "?q="+encodeURIComponent(btoa(vendor_str_data));
                window.location.href = url;
            });

            // function encryptString(string_data) {
            //     var CryptoJSAesJson = {
            //         stringify: function (cipherParams) {
            //                 var j = {ct: cipherParams.ciphertext.toString(CryptoJS.enc.Base64)};
            //                 if (cipherParams.iv) j.iv = cipherParams.iv.toString();
            //                 if (cipherParams.salt) j.s = cipherParams.salt.toString();
            //                 return JSON.stringify(j);
            //         },
            //         parse: function (jsonStr) {
            //                 var j = JSON.parse(jsonStr);
            //                 var cipherParams = CryptoJS.lib.CipherParams.create({ciphertext: CryptoJS.enc.Base64.parse(j.ct)});
            //                 if (j.iv) cipherParams.iv = CryptoJS.enc.Hex.parse(j.iv)
            //                 if (j.s) cipherParams.salt = CryptoJS.enc.Hex.parse(j.s)
            //                 return cipherParams;
            //         }
            //     }
            //     let key = '{{ env("AUTH_ENCRYPTION_KEY", "C7zjDVG0fnjVVwjd") }}';
            //     return CryptoJS.AES.encrypt(JSON.stringify(string_data), key, {format: CryptoJSAesJson}).toString();
            // }

            $(document).on('click', '.select-vendor-product', function() {
                if(isActiveProceedToOrder){
                    $(".proceed-to-order-"+$(this).val()).prop("checked", $(this).prop("checked"));
                }
            });
        });
        
        $(document).on('change', '.location-sumo-select', function() {
            let state_arr = new Array();
            let country_arr = new Array();
            $(".domestic-vendor-location:checked").each(function() {
                state_arr.push(parseInt($(this).val()));
            });
            $(".international-vendor-location:checked").each(function() {
                country_arr.push(parseInt($(this).val()));
            });
            $(".state-location-hidden").val(state_arr);
            $(".international-location-hidden").val(country_arr);
        });
    </script>
     <!-- <script src="{{ asset('public/assets/buyer/js/create-auction-modal.js') }}"></script> -->
@endsection