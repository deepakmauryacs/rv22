@extends('buyer.layouts.app', ['title'=>'Send Counter Offer'])

@section('css')
    <link rel="stylesheet" href="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.css') }}" />
    <link href="{{ asset('public/assets/library/sumoselect-v3.4.9-2/css/sumoselect.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/library/sumoselect-v3.4.9-2/css/sumo-select-style.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/buyer/css/additional-custom-style.css') }}" rel="stylesheet">
    <style>
        .no-vendor-found{
            padding: 7.6px !important;
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
                        <div class="col-12">
                            <h1 class="text-primary-blue font-size-27 text-center">Comparative Information Statement</h1>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="cis-info pb-2 px-0 px-md-3  d-none d-sm-flex">
                        <div class="cis-info-left">
                            <ul>
                                <li>RFQ No. : {{ $rfq['rfq_id'] }}</li>
                                <li>PRN Number: {{ $rfq['prn_no'] }}</li>
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
                    <form action="{{ route('buyer.rfq.counter-offer', ['rfq_id' => $rfq['rfq_id']]) }}" method="GET">
                        <input type="hidden" name="q" value="{{ request()->input('q') }}">
                        <div class="cis-filter gx-3 gy-2 py-2 px-2 mb-4 mx-0 mx-md-3 d-none d-sm-flex">
                            <div class="cis-filter-item flex-lg-fill">
                                <select class="form-select" aria-label="Default select example " name="sort_price" id="sortPrice">
                                    <option value="">Sort By Price</option>
                                    <option value="1" {{ !empty($filter) && $filter['sort_price'] == '1' ? 'selected' : '' }}>Lowest Price</option>
                                    <option value="2" {{ !empty($filter) && $filter['sort_price'] == '2' ? 'selected' : '' }}>Highest Price</option>
                                    <option value="3" {{ !empty($filter) && $filter['sort_price'] == '3' ? 'selected' : '' }}>Delivery Period</option>
                                </select>
                            </div>
                            <div class="cis-filter-item flex-lg-fill">
                                <input type="text" class="form-control dateTimePickerStart" id="fromDate" name="from_date" placeholder="From Date" value="{{ !empty($filter) && $filter['from_date'] ? $filter['from_date'] : '' }}" autocomplete="off">
                                <label for="fromDate" class="visually-hidden-focusable">From Date</label>
                            </div>
                            <div class="cis-filter-item flex-lg-fill">
                                <input type="text" class="form-control dateTimePickerEnd" id="toDate" name="to_date" placeholder="To Date" value="{{ !empty($filter) && $filter['to_date'] ? $filter['to_date'] : '' }}" autocomplete="off">
                                <label for="toDate" class="visually-hidden-focusable">From End</label>
                            </div>
                            <div class="cis-filter-item flex-lg-fill">
                                <select class="form-control location-sumo-select required" name="location[]" multiple>
                                    @if(!empty($cis['filter_state']))
                                        @foreach ($cis['filter_state'] as $id => $name)
                                        <option value="{{ $id }}" {{ !empty($filter) && $filter['state_location'] && in_array($id, explode(',', $filter['state_location'])) ? 'selected' : '' }} class="domestic-vendor-location">{{ $name }}</option>
                                        @endforeach
                                    @endif
                                    @if(!empty($cis['filter_country']))
                                        @foreach ($cis['filter_country'] as $id => $name)
                                        <option value="{{ $id }}" {{ !empty($filter) && $filter['country_location'] && in_array($id, explode(',', $filter['country_location'])) ? 'selected' : '' }} class="international-vendor-location">{{ $name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <input type="hidden" class="state-location-hidden" name="state_location" value="{{ !empty($filter) && $filter['state_location'] ? $filter['state_location'] : '' }}">
                                <input type="hidden" class="international-location-hidden" name="country_location" value="{{ !empty($filter) && $filter['country_location'] ? $filter['country_location'] : ''  }}">
                            </div>
                            <div class="cis-filter-item flex-lg-fill">
                                <select class="form-control favourite-vendor-sumo-select required" name="favourite_vendor[]" multiple>
                                    {{-- change this list with favourite vendor --}}
                                    @if(!empty($cis['fav_vendor']))
                                        @foreach ($cis['fav_vendor'] as $id => $name)
                                        <option value="{{ $id }}" {{ !empty($filter) && $filter['favourite_vendor'] && in_array($id, $filter['favourite_vendor']) ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="cis-filter-item flex-lg-fill">
                                <select class="form-control last-vendor-sumo-select required" name="last_vendor[]" multiple>
                                    @if(!empty($cis['last_vendor']))
                                        @foreach ($cis['last_vendor'] as $id => $name)
                                        <option value="{{ $id }}" {{ !empty($filter) && $filter['last_vendor'] && in_array($id, $filter['last_vendor']) ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="cis-filter-item">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="submit" class="ra-btn ra-btn-sm px-3 ra-btn-primary">
                                        <span class="bi bi-search font-size-14" aria-hidden="true"></span>
                                        Search
                                    </button>
                                    <a href="{{ route('buyer.rfq.cis-sheet', ['rfq_id' => $rfq['rfq_id']]) }}" class="ra-btn ra-btn-sm px-3 ra-btn-outline-danger">
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="cis-details py-3 px-0 px-md-3 d-none d-sm-block">
                        <div class="row g-0 gy-5">
                            <div class="col-6 col-md-6 col-xxl-5">
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
                                                                        NOTE: These are updated Rates post AUCTION that was held on 18/06/2025
                                                                    @endif
                                                                @endif
                                                            </h3>
                                                        </div>
                                                        <nav aria-label="breadcrumb">
                                                            <ol class="breadcrumb breadcrumb-cis">
                                                                <li class="breadcrumb-item"><a href="#">{{$rfq['rfq_division']}}</a></li>
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
                                                        <p class="quote-heading">Quotation Page</p>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col" class="p-2 cis-table-left-w-200">Product</th>
                                                <th scope="col" class="p-2 text-nowrap">Specifications</th>
                                                <th scope="col" class="p-2 text-nowrap">Size</th>
                                                <th scope="col" class="p-2 text-nowrap">Quantity/UOM</th>
                                                <th scope="col" class="p-2 text-nowrap w-80">Counter Offer</th>
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
                                                <td class="align-middle p-1">
                                                    {{-- buyer counter offer --}}
                                                    @php
                                                        $variant_quotes = isset($cis['buyer_quotes'][$variant_id]) ? $cis['buyer_quotes'][$variant_id] : [];
                                                        $last_quote = '';
                                                    @endphp
                                                    @if(!empty($variant_quotes))
                                                        @php
                                                            $last_quote = $variant_quotes[0]['buyer_price'];
                                                        @endphp
                                                    @endif
                                                    <input type="text" name="counter_offer[{{ $variant_id }}]" class="form-control form-control-counter-offer" value="{{$last_quote}}" required>
                                                </td>
                                            </tr>
                                            @endforeach
                                            <tr>
                                                <td class="align-middle p-1 bg-pink text-uppercase fw-bold" scope="row">
                                                    Total
                                                </td>
                                                <td class="align-middle p-1" colspan="4"></td>
                                                {{-- <td class="align-middle p-1"></td> --}}
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1" scope="row">Price Basis</td>
                                                <td class="align-middle p-1" colspan="4">
                                                    @php
                                                        $buyer_price_basis = !empty($rfq['buyer_price_basis']) ? $rfq['buyer_price_basis'] : '';
                                                    @endphp
                                                    <span title="{{$buyer_price_basis}}">
                                                        {!!
                                                            strlen($buyer_price_basis) > 60
                                                                ? substr($buyer_price_basis, 0, 60)
                                                                : $buyer_price_basis
                                                        !!}
                                                    </span>
                                                    @if(strlen($buyer_price_basis) > 60)
                                                    <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $buyer_price_basis }}">
                                                        <span class="bi bi-info-circle-fill text-dark font-size-11" aria-hidden="true"></span>
                                                    </span>
                                                    @endif
                                                </td>
                                                {{-- <td class="align-middle p-1"></td> --}}
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1" scope="row">Payment Terms</td>
                                                <td class="align-middle p-1" colspan="4">
                                                    @php
                                                        $buyer_pay_term = !empty($rfq['buyer_pay_term']) ? $rfq['buyer_pay_term'] : '';
                                                    @endphp
                                                    <span title="{{$buyer_pay_term}}">
                                                        {!!
                                                            strlen($buyer_pay_term) > 60
                                                                ? substr($buyer_pay_term, 0, 60)
                                                                : $buyer_pay_term
                                                        !!}
                                                    </span>
                                                    @if(strlen($buyer_pay_term) > 60)
                                                    <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $buyer_pay_term }}">
                                                        <span class="bi bi-info-circle-fill text-dark font-size-11" aria-hidden="true"></span>
                                                    </span>
                                                    @endif
                                                </td>
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
                                                <td class="align-middle p-1" scope="row">Technical Approval</td>
                                                <td class="align-middle p-1" colspan="4"></td>
                                                {{-- <td class="align-middle p-1"></td> --}}
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1 text-nowrap" scope="row">Technical Approval Remarks </td>
                                                <td class="align-middle p-1" colspan="4"></td>
                                                {{-- <td class="align-middle p-1"></td> --}}
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1 bg-pink fw-bold" scope="row" colspan="5">
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
                            <div class="col-6 col-md-6 col-xxl-7 position-relative">
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
                                    <table class="table table-bordered border-dark cis-vendor-table cis-table-mobile">
                                        <thead>
                                            <tr>
                                                @foreach($cis['vendors'] as $vendor_id => $vendor)
                                                @php
                                                    if(!empty($cis['filter_vendors']) && !in_array($vendor_id, $cis['filter_vendors'])) {
                                                        continue;
                                                    }
                                                @endphp
                                                <th scope="col">
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
                                                            {{-- <span class="font-size-13">
                                                                <input type="checkbox" class="select-vendor-product" value="{{$vendor_id}}" name="vendor_id[]">
                                                            </span> --}}
                                                        </p>
                                                        <p>{{ $vendor['country_code'] ? '+'.$vendor['country_code'] : '' }} {{$vendor['mobile']}}</p>
                                                        <p>{{ $vendor['vendor_quoted_product'] }}</p>
                                                        <p>{{ !empty($vendor['latest_quote']) ? date('d/m/Y', strtotime($vendor['latest_quote']['created_at'])) : '' }}</p>
                                                    </div>
                                                </th>
                                                @endforeach
                                            </tr>
                                            <tr>
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
                                            </tr>
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
                                                                    <span class="vendor-variant-price vendor-price-{{$variant_id}} " data-vendor-price="{{$vendor_last_quote['price']}}" data-vendor-id="{{$vendor_id}}">{{IND_money_format($vendor_last_quote['price'])}}</span>
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
                                                    @php
                                                        $vendor_additional_remarks = !empty($vendor['latest_quote']) ? $vendor['latest_quote']['vendor_additional_remarks'] : '';
                                                    @endphp
                                                    <span title="{{ $vendor_additional_remarks }}">
                                                        {!!
                                                            strlen($vendor_additional_remarks) > 12
                                                                ? substr($vendor_additional_remarks, 0, 12)
                                                                : $vendor_additional_remarks
                                                        !!}
                                                    </span>
                                                    @if(strlen($vendor_additional_remarks) > 12)
                                                    <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $vendor_additional_remarks }}">
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
                                                    <span role="button" type="button" class="p-0" data-bs-toggle="modal" data-bs-target="#viewTechApprovalModal" title="Technical Approval">
                                                        <span class="bi bi-eye-fill text-dark font-size-14" aria-hidden="true"></span>
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
                                                    <span title=""></span>
                                                </td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td colspan="{{ count($cis['vendors'])==count($cis['filter_vendors']) ? count($cis['vendors']) : count($cis['vendors'])-count($cis['filter_vendors'])}}" class="bg-pink ps-2 align-middle">
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
                        @foreach($cis['variants'] as $variant_id => $variants)
                        <div class="cis-details-mobile-wrapper">
                            <div
                                class="card-header border-0 bg-white d-flex align-items-center justify-content-between py-3">
                                <button class="cis-mobile-toggle-button ra-btn btn-show-hide bg-white w-100 justify-content-start">
                                    <h2 class="font-size-14 fw-bold text-primary-blue">{{$variants['product_name']}}</h2>
                                    <span id="toggleIcon" class="toggle-icon bi bi-chevron-up"></span>
                                </button>
                            </div>
                            <div class="cis-details-mobile-wrapper-content">
                                <div>
                                    <h3 class="font-size-14 fw-bold">Specifications</h3>
                                    <div class="mb-3"> {{$variants['specification']}} </div>
                                    <p><strong>Size:</strong> {{$variants['size']}} &nbsp; &nbsp; | &nbsp; &nbsp;
                                        <strong>Quantity/UOM:</strong> {{ $variants['quantity'] }} {{ $uom[$variants['uom']] }}
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
                                            @foreach($cis['vendors'] as $vendor_id => $vendor)
                                            <li class="d-flex">
                                                <span class="vendor-name">
                                                    <a href="javascript:void(0)" target="_blank"
                                                        class="font-size-13" title="{{ $vendor['legal_name'] }}">
                                                    {{ $vendor['legal_name'] }}
                                                    </a>
                                                </span>
                                                @php
                                                    $vendor_last_quote = isset($vendor['last_quote'][$variant_id]) ? $vendor['last_quote'][$variant_id] : [];
                                                @endphp
                                                @if(!empty($vendor_last_quote))
                                                    <span class="vendor-price">{{IND_money_format($vendor_last_quote['price'])}}</span>
                                                @else
                                                    <span class="vendor-price"></span>
                                                @endif
                                            </li>
                                            @endforeach
                                        </ul>
                                        <div
                                            class="show-more d-flex align-items-center justify-content-center py-2">
                                            <span role="button" type="button"
                                                class="toggle-show-more-button d-flex align-items-center fw-bold text-primary-blue height-inherit p-0">
                                            Show More <span
                                                class="bi bi-chevron-down text-dark ms-1 font-size-13"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <!-- End of CIS details Mobile only -->
                </div>
            </section>
            <!---Gap Creation-->
            <div class="fill-more-details d-none d-sm-block"></div>
            <!-- Floating CIS options-->
            <section class="floting-product-options cis-floating-button d-none d-sm-block">
                <div class="d-flex flex-wrap flex-md-nowrap align-items-center justify-content-center gap-3">
                    <button type="button" id="send-counter-offer" class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10">
                        <span class="bi bi-send font-size-12" aria-hidden="true"></span> Send Counter Offer
                    </button>
                    <a href="{{ route('buyer.rfq.cis-sheet', ['rfq_id' => $rfq['rfq_id']]) }}"
                        class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10"><span
                        class="bi bi-arrow-left-square font-size-12" aria-hidden="true"></span> Back
                    </a>
                </div>
            </section>
        </div>
    </main>
@endsection

@section('scripts')

    <script src="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>
    <script src="{{ asset('public/assets/library/sumoselect-v3.4.9-2/js/jquery.sumoselect.min.js') }}"></script>

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

            function initializeCounterOfferInputs() {
                $('input.form-control-counter-offer').each(function () {
                    // Extract variant ID from input name
                    var input = $(this);
                    var name = input.attr('name');
                    var match = name.match(/counter_offer\[(\d+)\]/);
                    if (!match) return;
                    var variantId = match[1];

                    // Get all vendor prices for this variant
                    var vendorPrices = [];
                    $('.vendor-price-' + variantId).each(function () {
                        var price = parseFloat($(this).data("vendor-price"));
                        if (!isNaN(price)) vendorPrices.push(price);
                    });

                    // Disable if no vendor prices
                    if (vendorPrices.length === 0) {
                        input.prop('disabled', true).val('');
                            // .attr('placeholder', 'No vendor price');
                        return;
                    }

                    input.on('input', function () {
                        $(this).sanitizeNumberField();
                        if($(this).val().length>10){
                            $(this).val($(this).val().substring(0, 10));
                        }
                    });

                    // On blur: restrict to float, 2 decimals, and validate against lowest vendor price
                    input.on('blur', function () {
                        // console.log("vendorPrices", vendorPrices);
                        var val = input.val().replace(/[^0-9.]/g, '');
                        val = val.replace(/^(\d*\.\d{0,2}).*$/, "$1"); // max 2 decimals
                        var floatVal = parseFloat(val);

                        if (!isNaN(floatVal)) {
                            var minVendorPrice = Math.min.apply(null, vendorPrices); // Use minimum vendor price
                            if (floatVal > minVendorPrice) {
                                alert("You have received a lower quote. Please quote " + minVendorPrice + " or less.");
                                input.val(''); // clear input
                            } else {
                                input.val(floatVal.toFixed(2));
                            }
                        } else {
                            input.val('');
                        }
                    });
                });
            }

            initializeCounterOfferInputs();
            setTimeout(() => {
                $('.form-control-counter-offer').trigger('blur');
            }, 1000);
            $('#send-counter-offer').on('click', function (e) {
                e.preventDefault();
                $(this).addClass("disabled", "disabled");

                $('.form-control-counter-offer').trigger('blur');

                let formData = new FormData();
                let buyerPricesFilled = false;

                $('input.form-control-counter-offer').each(function () {
                    var input = $(this);
                    var val = input.val().trim();

                    if (val !== '') {
                        buyerPricesFilled = true;

                        var name = input.attr('name');
                        var match = name.match(/counter_offer\[(\d+)\]/);
                        if (!match) return true; // continue

                        var variantId = match[1];
                        var buyerPrice = parseFloat(val);
                        // Append data
                        formData.append('counter_offer[' + variantId + ']', buyerPrice.toFixed(2));
                        $(".vendor-price-" + variantId).each(function () {
                            formData.append('variant_vendors[' + variantId + '][]', $(this).data("vendor-id"));
                        });
                    }
                });

                if (!buyerPricesFilled) {
                    alert('Please Enter Price to Revert Counter Offer.');
                    $(this).removeClass("disabled");
                    return;
                }

                if(!confirm("Do you want to send the Counter Offer ?")){
                    $(this).removeClass("disabled");
                    return;
                }
                let _this = $(this);
                // Submit ajax
                $.ajax({
                    url: '{{ route("buyer.rfq.save-counter-offer", ["rfq_id" => $rfq["rfq_id"]]) }}',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        // console.log("response", response);
                        if(response.status){
                            window.location.href = response.redirect_url;
                        }else{
                            alert(response.message);
                            _this.removeClass("disabled");
                        }
                    },
                    error: function () {
                        _this.removeClass("disabled");
                        alert('Error submitting buyer prices.');
                    }
                });
            });
        });
    </script>

@endsection