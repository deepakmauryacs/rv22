@extends('buyer.layouts.app', ['title'=>'CIS Sheet'])

@section('css')
<link rel="stylesheet" href="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.css') }}" />
<link href="{{ asset('public/assets/library/sumoselect-v3.4.9-2/css/sumoselect.min.css') }}" rel="stylesheet">
<link href="{{ asset('public/assets/library/sumoselect-v3.4.9-2/css/sumo-select-style.css') }}" rel="stylesheet">
<link href="{{ asset('public/assets/buyer/css/additional-custom-style.css') }}" rel="stylesheet">
<style>
    .no-vendor-found {
        padding: 7.6px !important;
    }

    .ck-editor__editable_inline {
        min-height: 180px !important;
    }
</style>
@endsection

@section('content')
<div class="bg-white">
    <!---Sidebar-->
    @include('buyer.layouts.sidebar-menu')
</div>

@php
$is_auction_btn_disabled = false;
$is_message_btn_disabled = false;
$is_counter_offer_disabled = false;
$is_order_btn_disabled = false;
$mg_products=[];
@endphp

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
                        <div class="row gx-3 gy-2 align-items-center justify-content-center justify-content-sm-end">
                            <div class="col-auto">
                                <button type="button" class="ra-btn ra-btn-outline-primary px-2 font-size-11"
                                    data-bs-toggle="modal" data-bs-target="#checkLastCisPoModal">
                                    Check Last CIS/PO
                                </button>
                            </div>
                            <div class="col-auto">
                                <a target="_blank"
                                    href="{{ route('buyer.rfq.cis-sheet', ['rfq_id'=>$rfq['rfq_id']]) }}?export=true&{{ http_build_query(request()->all()) }}"
                                    class="ra-btn ra-btn-outline-primary px-2 font-size-11">
                                    <span class="bi bi-download font-size-12" aria-hidden="true"></span>
                                    Download CIS
                                </a>
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
                            <li>Last Date to Response: {{ $rfq['last_response_date'] ? date('d/m/Y',
                                strtotime($rfq['last_response_date'])) : '' }}</li>
                            @if(!empty($rfq['edit_by']))
                            <li>Last Edited Date: {{ $rfq['updated_at'] ? date('d/m/Y', strtotime($rfq['updated_at'])) :
                                '' }}</li>
                            @endif
                        </ul>
                    </div>
                    <div class="cis-info-right">
                        RFQ Date: {{ $rfq['created_at'] ? date('d/m/Y', strtotime($rfq['created_at'])) : '' }}
                    </div>
                </div>

                <form action="{{ route('buyer.rfq.cis-sheet', ['rfq_id' => $rfq['rfq_id']]) }}" method="GET">
                    <div class="cis-filter gx-3 gy-2 py-2 px-2 mb-4 mx-0 mx-md-3 d-none d-sm-flex">
                        <div class="cis-filter-item flex-lg-fill">
                            <select class="form-select" aria-label="Default select example " name="sort_price"
                                id="sortPrice">
                                <option value="">Sort By Price</option>
                                <option value="1" {{ !empty($filter) && $filter['sort_price']=='1' ? 'selected' : '' }}>
                                    Lowest Price</option>
                                <option value="2" {{ !empty($filter) && $filter['sort_price']=='2' ? 'selected' : '' }}>
                                    Highest Price</option>
                                <option value="3" {{ !empty($filter) && $filter['sort_price']=='3' ? 'selected' : '' }}>
                                    Delivery Period</option>
                            </select>
                        </div>
                        <div class="cis-filter-item flex-lg-fill">
                            <input type="text" class="form-control dateTimePickerStart" id="fromDate" name="from_date"
                                placeholder="From Date"
                                value="{{ !empty($filter) && $filter['from_date'] ? $filter['from_date'] : '' }}"
                                autocomplete="off">
                            <label for="fromDate" class="visually-hidden-focusable">From Date</label>
                        </div>
                        <div class="cis-filter-item flex-lg-fill">
                            <input type="text" class="form-control dateTimePickerEnd" id="toDate" name="to_date"
                                placeholder="To Date"
                                value="{{ !empty($filter) && $filter['to_date'] ? $filter['to_date'] : '' }}"
                                autocomplete="off">
                            <label for="toDate" class="visually-hidden-focusable">From End</label>
                        </div>
                        <div class="cis-filter-item flex-lg-fill">
                            <select class="form-control location-sumo-select required" name="location[]" multiple>
                                @if(!empty($cis['filter_state']))
                                @foreach ($cis['filter_state'] as $id => $name)
                                <option value="{{ $id }}" {{ !empty($filter) && $filter['state_location'] &&
                                    in_array($id, explode(',', $filter['state_location'])) ? 'selected' : '' }}
                                    class="domestic-vendor-location">{{ $name }}</option>
                                @endforeach
                                @endif
                                @if(!empty($cis['filter_country']))
                                @foreach ($cis['filter_country'] as $id => $name)
                                <option value="{{ $id }}" {{ !empty($filter) && $filter['country_location'] &&
                                    in_array($id, explode(',', $filter['country_location'])) ? 'selected' : '' }}
                                    class="international-vendor-location">{{ $name }}</option>
                                @endforeach
                                @endif
                            </select>
                            <input type="hidden" class="state-location-hidden" name="state_location"
                                value="{{ !empty($filter) && $filter['state_location'] ? $filter['state_location'] : '' }}">
                            <input type="hidden" class="international-location-hidden" name="country_location"
                                value="{{ !empty($filter) && $filter['country_location'] ? $filter['country_location'] : ''  }}">
                        </div>
                        <div class="cis-filter-item flex-lg-fill">
                            <select class="form-control favourite-vendor-sumo-select required" name="favourite_vendor[]"
                                multiple>
                                {{-- change this list with favourite vendor --}}
                                @if(!empty($cis['fav_vendor']))
                                @foreach ($cis['fav_vendor'] as $id => $name)
                                <option value="{{ $id }}" {{ !empty($filter) && $filter['favourite_vendor'] &&
                                    in_array($id, $filter['favourite_vendor']) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="cis-filter-item flex-lg-fill">
                            <select class="form-control last-vendor-sumo-select required" name="last_vendor[]" multiple>
                                @if(!empty($cis['last_vendor']))
                                @foreach ($cis['last_vendor'] as $id => $name)
                                <option value="{{ $id }}" {{ !empty($filter) && $filter['last_vendor'] && in_array($id,
                                    $filter['last_vendor']) ? 'selected' : '' }}>{{ $name }}</option>
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
                                <a href="{{ route('buyer.rfq.cis-sheet', ['rfq_id' => $rfq['rfq_id']]) }}"
                                    class="ra-btn ra-btn-sm px-3 ra-btn-outline-danger">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

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
                                                            NOTE: These are updated Rates post AUCTION that was held on
                                                            {{ date('d/m/Y', strtotime($rfq['auction_date'])) }}
                                                            @endif
                                                            @endif
                                                        </h3>
                                                    </div>
                                                    <nav aria-label="breadcrumb">
                                                        <ol class="breadcrumb breadcrumb-cis">
                                                            <li class="breadcrumb-item">{{$rfq['rfq_division']}}</li>
                                                            <li class="breadcrumb-item active" aria-current="page">
                                                                {{$rfq['rfq_category']}} </li>
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
                                            <th scope="col" class="p-2 text-nowrap">Counter Offer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cis['variants'] as $variant_id => $variants)
                                        @php $mg_products[$variants['product_id']]=$variants['product_name']; @endphp
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
                                                <span role="button" type="button" class="p-0 infoIcon"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{!! $variants['product_name'] !!}">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-14"
                                                        aria-hidden="true"></span>
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
                                                <span role="button" type="button" class="p-0 infoIcon"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{!! $variants['specification'] !!}">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-14"
                                                        aria-hidden="true"></span>
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
                                                <span role="button" type="button" class="p-0 infoIcon"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{!! $variants['size'] !!}">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-14"
                                                        aria-hidden="true"></span>
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
                                                $variant_order_history[] = $cis['vendors'][$vendor_id]['legal_name']
                                                .'->'.$order_qty;
                                                @endphp
                                                @endforeach
                                                @endforeach
                                                <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="{{ implode(', ', $variant_order_history) }}">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                </span>
                                                @endif
                                            </td>
                                            <td class="align-middle p-1">
                                                {{-- buyer counter offer --}}
                                                @php
                                                $variant_quotes = isset($cis['buyer_quotes'][$variant_id]) ?
                                                $cis['buyer_quotes'][$variant_id] : [];
                                                @endphp
                                                @if(!empty($variant_quotes))
                                                @php
                                                $last_quote = $variant_quotes[0];
                                                $variant_quote_history = [];
                                                @endphp

                                                @foreach ($variant_quotes as $item)
                                                @php
                                                $variant_quote_history[] = $item['buyer_price'] ."(". date('d-M',
                                                strtotime($item['updated_at'])).")";
                                                @endphp
                                                @endforeach
                                                <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="{{ implode(', ', array_unique($variant_quote_history)) }}">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                </span>
                                                {{ IND_money_format($last_quote['buyer_price']) }}
                                                @endif
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
                                                $buyer_price_basis = !empty($rfq['buyer_price_basis']) ?
                                                $rfq['buyer_price_basis'] : '';
                                                @endphp
                                                <span title="{{$buyer_price_basis}}">
                                                    {!!
                                                    strlen($buyer_price_basis) > 60
                                                    ? substr($buyer_price_basis, 0, 60)
                                                    : $buyer_price_basis
                                                    !!}
                                                </span>
                                                @if(strlen($buyer_price_basis) > 60)
                                                <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="{{ $buyer_price_basis }}">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                </span>
                                                @endif
                                            </td>
                                            {{-- <td class="align-middle p-1"></td> --}}
                                        </tr>
                                        <tr>
                                            <td class="align-middle p-1" scope="row">Payment Terms</td>
                                            <td class="align-middle p-1" colspan="4">
                                                @php
                                                $buyer_pay_term = !empty($rfq['buyer_pay_term']) ?
                                                $rfq['buyer_pay_term'] : '';
                                                @endphp
                                                <span title="{{$buyer_pay_term}}">
                                                    {!!
                                                    strlen($buyer_pay_term) > 60
                                                    ? substr($buyer_pay_term, 0, 60)
                                                    : $buyer_pay_term
                                                    !!}
                                                </span>
                                                @if(strlen($buyer_pay_term) > 60)
                                                <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="{{ $buyer_pay_term }}">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                </span>
                                                @endif
                                            </td>
                                            {{-- <td class="align-middle p-1"></td> --}}
                                        </tr>
                                        <tr>
                                            <td class="align-middle p-1" scope="row">Delivery Period</td>
                                            <td class="align-middle p-1" colspan="4">{{ $rfq['buyer_delivery_period'] ?
                                                $rfq['buyer_delivery_period']. ' Days' : '' }}</td>
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
                                            <td class="align-middle p-1 text-nowrap" scope="row">Technical Approval
                                                Remarks</td>
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
                        <div class="col-6 col-md-6 position-relative">
                            <div class="d-flex justify-content-between prev-next-table-space d-none d-sm-flex">
                                <button type="button" id="scrollLeft"
                                    class="btn btn-link min-width-inherit height-inherit p-0">
                                    <span class="visually-hidden-focusable">scroll left</span>
                                    <span class="bi bi-arrow-left font-size-20 text-primary-blue" aria-hidden="true">
                                    </span>
                                </button>
                                <button type="button" id="scrollRight"
                                    class="btn btn-link min-width-inherit height-inherit p-0">
                                    <span class="visually-hidden-focusable">scroll right</span>
                                    <span class="bi bi-arrow-right font-size-20 text-primary-blue" aria-hidden="true">
                                    </span>
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
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
                                            continue;
                                            }
                                            @endphp
                                            <th scope="col">
                                                {{-- @if($rfq['is_auction'] == 2)
                                                @if(in_array($vendor['vendor_rfq_status'], [1, 4]))
                                                <div class="position-relative">
                                                    <div class="cis-vendor-notification">
                                                        <a href="javascript:void(0)" class="send-reminder-notification"
                                                            title="Remind the Vendor"><span
                                                                class=" ml-10 bi bi-bell"></span></a>
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
                                                            <input type="checkbox" class="select-vendor-product"
                                                                value="{{$vendor_id}}" name="vendor_id[]">
                                                        </span>
                                                    </p>
                                                    <p>{{ $vendor['country_code'] ? '+'.$vendor['country_code'] : '' }}
                                                        {{$vendor['mobile']}}</p>
                                                    <p>{{ $vendor['vendor_quoted_product'] }}</p>
                                                    <p>{{ !empty($vendor['latest_quote']) ? date('d/m/Y',
                                                        strtotime($vendor['latest_quote']['created_at'])) : '' }}</p>
                                                </div>
                                            </th>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach($cis['vendors'] as $vendor_id => $vendor)
                                            @php
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
                                            continue;
                                            }
                                            @endphp
                                            <th scope="col" class="text-center p-2 bg-white">
                                                <a target="_blank"
                                                    href="{{ route('buyer.rfq.quotation-received', ['rfq_id' => $rfq['rfq_id'], 'vendor_id' => $vendor_id]) }}"
                                                    class="text-decoration-underline text-primary-blue"> View Quotation
                                                </a>
                                            </th>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach($cis['vendors'] as $vendor_id => $vendor)
                                            @php
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
                                            continue;
                                            }
                                            @endphp
                                            <th scope="col" class="text-center p-2 bg-white">Rate
                                                ({{!empty($vendor['latest_quote']) &&
                                                !empty($vendor['latest_quote']['vendor_currency']) ?
                                                $vendor['latest_quote']['vendor_currency'] : '₹'}})</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cis['variants'] as $variant_id => $variants)
                                        <tr>
                                            @foreach($cis['vendors'] as $vendor_id => $vendor)
                                            @php
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
                                            continue;
                                            }
                                            @endphp
                                            @if(isset($cis['is_vendor_product'][$vendor_id]) &&
                                            isset($cis['is_vendor_product'][$vendor_id][$variants['product_id']]))
                                            @php
                                            $vendor_last_quote = isset($vendor['last_quote'][$variant_id]) ?
                                            $vendor['last_quote'][$variant_id] : [];
                                            @endphp
                                            <td
                                                class="product-price p-1 align-middle {{ !empty($vendor_last_quote) && $vendor_last_quote['price']==$variants['lowest_price'] ? 'bg-gold' : '' }} ">
                                                @if(!empty($vendor_last_quote))
                                                <div class="d-flex justify-content-center align-items-center gap-4">
                                                    @php
                                                    $vendor_quote_history = isset($vendor['vendorQuotes'][$variant_id])
                                                    ? $vendor['vendorQuotes'][$variant_id] : [];
                                                    $quote_history = [];
                                                    foreach ($vendor_quote_history as $item) {
                                                    $timestamp = strtotime($item['created_at']);
                                                    $formatted_date = date('d-M', $timestamp); // e.g., 22-Jul
                                                    $quote_history[] = "{$item['price']} ({$formatted_date})";
                                                    }
                                                    $final_quote_history_string = implode(', ', $quote_history);
                                                    @endphp
                                                    <div>
                                                        <span role="button" type="button" class="p-0"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{$final_quote_history_string}}">
                                                            <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                                aria-hidden="true"></span>
                                                        </span>
                                                    </div>

                                                    <div class="vendor-variant-price">
                                                        {{IND_money_format($vendor_last_quote['price'])}}
                                                        <!-- This Checkbox will show when Buyer click Proceed to order Button -->
                                                        @if(!empty($vendor['latest_quote']) &&
                                                        $vendor['latest_quote']['left_qty'] > 0)
                                                        <span class="font-size-13 d-none">
                                                            <input type="checkbox" name="proceed_to_order"
                                                                value="{{$vendor_id}}" data-variant-id="{{$variant_id}}"
                                                                class="proceed-to-order-input proceed-to-order-{{$vendor_id}}">
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                @endif
                                            </td>
                                            @else
                                            <td class="product-price p-1 align-middle text-center">
                                                <span class="fa fa-close" aria-hidden="true"
                                                    style="font-weight: 900;">X</span>
                                            </td>
                                            @endif
                                            @endforeach
                                        </tr>
                                        @endforeach
                                        <tr>
                                            @foreach($cis['vendors'] as $vendor_id => $vendor)
                                            @php
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
                                            continue;
                                            }
                                            @endphp
                                            <td
                                                class="product-price p-1 align-middle text-center {{ !empty($cis['vendor_total_amount'][$vendor_id]) && $cis['vendor_total_amount'][$vendor_id] == $rfq['lowest_price_total'] ? 'bg-gold' : '' }} ">
                                                <b>
                                                    {{!empty($vendor['latest_quote']) &&
                                                    !empty($vendor['latest_quote']['vendor_currency']) ?
                                                    $vendor['latest_quote']['vendor_currency'] : '₹'}}
                                                    {{$cis['vendor_total_amount'][$vendor_id] ?
                                                    IND_money_format($cis['vendor_total_amount'][$vendor_id]) : 0}}
                                                </b>
                                            </td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach($cis['vendors'] as $vendor_id => $vendor)
                                            @php
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
                                            continue;
                                            }
                                            @endphp
                                            <td class="product-price p-1 align-middle text-center">
                                                @php
                                                $vendor_price_basis = !empty($vendor['latest_quote']) ?
                                                $vendor['latest_quote']['vendor_price_basis'] : '';
                                                @endphp
                                                <span title="{{$vendor_price_basis}}">
                                                    {!!
                                                    strlen($vendor_price_basis) > 12
                                                    ? substr($vendor_price_basis, 0, 12)
                                                    : $vendor_price_basis
                                                    !!}
                                                </span>
                                                @if(strlen($vendor_price_basis) > 12)
                                                <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="{{ $vendor_price_basis }}">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                </span>
                                                @endif
                                            </td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach($cis['vendors'] as $vendor_id => $vendor)
                                            @php
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
                                            continue;
                                            }
                                            @endphp
                                            <td class="product-price p-1 align-middle text-center">
                                                @php
                                                $vendor_payment_terms = !empty($vendor['latest_quote']) ?
                                                $vendor['latest_quote']['vendor_payment_terms'] : '';
                                                @endphp
                                                <span title="{{$vendor_payment_terms}}">
                                                    {!!
                                                    strlen($vendor_payment_terms) > 12
                                                    ? substr($vendor_payment_terms, 0, 12)
                                                    : $vendor_payment_terms
                                                    !!}
                                                </span>
                                                @if(strlen($vendor_payment_terms) > 12)
                                                <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="{{ $vendor_payment_terms }}">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                </span>
                                                @endif
                                            </td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach($cis['vendors'] as $vendor_id => $vendor)
                                            @php
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
                                            continue;
                                            }
                                            @endphp
                                            <td class="product-price p-1 align-middle text-center">
                                                @php
                                                $vendor_delivery_period = !empty($vendor['latest_quote']) ?
                                                $vendor['latest_quote']['vendor_delivery_period'].' Days' : '';
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
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
                                            continue;
                                            }
                                            @endphp
                                            <td class="product-price p-1 align-middle text-center">
                                                @php
                                                $vendor_brand = !empty($vendor['vendor_brand']) ?
                                                $vendor['vendor_brand'] : '';
                                                @endphp
                                                <span title="{{ $vendor_brand }}">
                                                    {!!
                                                    strlen($vendor_brand) > 12
                                                    ? substr($vendor_brand, 0, 12)
                                                    : $vendor_brand
                                                    !!}
                                                </span>
                                                @if(strlen($vendor_brand) > 12)
                                                <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="{{ $vendor_brand }}">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    <span class="visually-hidden-focusable">info</span>
                                                </span>
                                                @endif
                                            </td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach($cis['vendors'] as $vendor_id => $vendor)
                                            @php
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
                                            continue;
                                            }
                                            @endphp
                                            <td class="product-price p-1 align-middle text-center">
                                                @php
                                                $vendor_remarks = !empty($vendor['latest_quote']) ?
                                                $vendor['latest_quote']['vendor_remarks'] : '';
                                                @endphp
                                                <span title="{{ $vendor_remarks }}">
                                                    {!!
                                                    strlen($vendor_remarks) > 12
                                                    ? substr($vendor_remarks, 0, 12)
                                                    : $vendor_remarks
                                                    !!}
                                                </span>
                                                @if(strlen($vendor_remarks) > 12)
                                                <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="{{ $vendor_remarks }}">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    <span class="visually-hidden-focusable">info</span>
                                                </span>
                                                @endif
                                            </td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach($cis['vendors'] as $vendor_id => $vendor)
                                            @php
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
                                            continue;
                                            }
                                            @endphp
                                            <td class="product-price p-1 align-middle text-center">
                                                @php
                                                $vendor_additional_remarks = !empty($vendor['latest_quote']) ?
                                                $vendor['latest_quote']['vendor_additional_remarks'] : '';
                                                @endphp
                                                <span title="{{ $vendor_additional_remarks }}">
                                                    {!!
                                                    strlen($vendor_additional_remarks) > 12
                                                    ? substr($vendor_additional_remarks, 0, 12)
                                                    : $vendor_additional_remarks
                                                    !!}
                                                </span>
                                                @if(strlen($vendor_additional_remarks) > 12)
                                                <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="{{ $vendor_additional_remarks }}">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                </span>
                                                @endif
                                            </td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach($cis['vendors'] as $vendor_id => $vendor)
                                            @php
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
                                            continue;
                                            }
                                            $technical_approval = !empty($vendor['technical_approval']) ?
                                            $vendor['technical_approval'] : [];
                                            @endphp
                                            <td class="product-price p-1 align-middle text-center"
                                                title="Technical Approval">
                                                <span role="button" type="button" class="p-0 technical-approval"
                                                    data-vendor-id="{{ $vendor_id }}"
                                                    data-vendor-name="{{ $vendor['legal_name'] }}"
                                                    data-technical-approval-description="{{ !empty($technical_approval) ? $technical_approval['description'] : '' }}"
                                                    data-technical-approval="{{ !empty($technical_approval) ? $technical_approval['technical_approval'] : '' }}"
                                                    title="Technical Approval">
                                                    @if(!empty($technical_approval))
                                                    {{ $technical_approval['technical_approval'] }}
                                                    @else
                                                    <span class="bi bi-eye-fill text-dark font-size-14"
                                                        aria-hidden="true"></span>
                                                    @endif
                                                </span>
                                                {{-- data-bs-toggle="modal" data-bs-target="#technical-approval-modal"
                                                --}}
                                            </td>
                                            @endforeach
                                            {{-- <td class="product-price p-1 align-middle text-center">
                                                <span title=""></span>
                                            </td> --}}
                                        </tr>
                                        <tr>
                                            @foreach($cis['vendors'] as $vendor_id => $vendor)
                                            @php
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
                                            continue;
                                            }
                                            $technical_approval = !empty($vendor['technical_approval']) ?
                                            $vendor['technical_approval'] : [];
                                            @endphp
                                            <td class="product-price p-1 align-middle text-center"
                                                title="Technical Approval Remarks">
                                                @if(!empty($technical_approval))
                                                <span title="{{ $technical_approval['description'] }}">
                                                    {!!
                                                    strlen($technical_approval['description']) > 12
                                                    ? substr($technical_approval['description'], 0, 12)
                                                    : $technical_approval['description']
                                                    !!}
                                                </span>
                                                @if(strlen($technical_approval['description']) > 12)
                                                <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="{{ $technical_approval['description'] }}">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    <span class="visually-hidden-focusable">info</span>
                                                </span>
                                                @endif
                                                @else
                                                <span title=""></span>
                                                @endif

                                            </td>
                                            @endforeach
                                            {{-- <td class="product-price p-1 align-middle text-center">
                                                <span title=""></span>
                                            </td> --}}
                                        </tr>
                                        <tr>
                                            <td colspan="{{count($cis['vendors'])-count($cis['filter_vendors'])}}"
                                                class="bg-pink ps-2 align-middle">
                                                <span role="button" type="button" class="toggle-row-button p-0">
                                                    <span class="bi bi-chevron-up text-dark"></span>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr class="toggle-row">
                                            @foreach($cis['vendors'] as $vendor_id => $vendor)
                                            @php
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
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
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
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
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
                                            continue;
                                            }
                                            @endphp
                                            <td class="product-price p-1 align-middle text-center">
                                                @php
                                                $vendor_product = !empty($vendor['vendor_product']) ?
                                                $vendor['vendor_product'] : '';
                                                @endphp
                                                <span title="{{ $vendor_product }}">
                                                    {!!
                                                    strlen($vendor_product) > 12
                                                    ? substr($vendor_product, 0, 12)
                                                    : $vendor_product
                                                    !!}
                                                </span>
                                                @if(strlen($vendor_product) > 12)
                                                <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="{{ $vendor_product }}">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                </span>
                                                @endif
                                            </td>
                                            @endforeach
                                        </tr>
                                        <tr class="toggle-row">
                                            @foreach($cis['vendors'] as $vendor_id => $vendor)
                                            @php
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
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
                                                <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="{{ $client }}">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                </span>
                                                @endif
                                                {{-- <span title="Ankit,Saurba">Ankit,Saurba </span><span role="button"
                                                    type="button" class="p-0" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="Ankit,Saurba">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                </span> --}}
                                            </td>
                                            @endforeach
                                        </tr>
                                        <tr class="toggle-row">
                                            @foreach($cis['vendors'] as $vendor_id => $vendor)
                                            @php
                                            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
                                            $cis['filter_vendors'])) {
                                            continue;
                                            }
                                            @endphp
                                            <td class="product-price p-1 align-middle text-center">
                                                @php
                                                $certifications = !empty($vendor['certifications']) ?
                                                $vendor['certifications'] : '';
                                                @endphp
                                                <span title="{{ $certifications }}">
                                                    {!!
                                                    strlen($certifications) > 12
                                                    ? substr($certifications, 0, 12)
                                                    : $certifications
                                                    !!}
                                                </span>
                                                @if(strlen($certifications) > 12)
                                                <span role="botton" type="button" class="p-0" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="{{ $certifications }}">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
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
                            <button
                                class="cis-mobile-toggle-button ra-btn btn-show-hide bg-white w-100 justify-content-start">
                                <h2 class="font-size-14 fw-bold text-primary-blue">{{$variants['product_name']}}</h2>
                                <span id="toggleIcon" class="toggle-icon bi bi-chevron-up"></span>
                            </button>
                        </div>
                        <div class="cis-details-mobile-wrapper-content">
                            <div>
                                <h3 class="font-size-14 fw-bold">Specifications</h3>
                                <div class="mb-3"> {{$variants['specification']}} </div>
                                <p><strong>Size:</strong> {{$variants['size']}} &nbsp; &nbsp; | &nbsp; &nbsp;
                                    <strong>Quantity/UOM:</strong> {{ $variants['quantity'] }} {{ $uom[$variants['uom']]
                                    }}
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
                                                <a href="javascript:void(0)" target="_blank" class="font-size-13"
                                                    title="{{ $vendor['legal_name'] }}">
                                                    {{ $vendor['legal_name'] }}
                                                </a>
                                            </span>
                                            @php
                                            $vendor_last_quote = isset($vendor['last_quote'][$variant_id]) ?
                                            $vendor['last_quote'][$variant_id] : [];
                                            @endphp
                                            @if(!empty($vendor_last_quote))
                                            <span
                                                class="vendor-price">{{IND_money_format($vendor_last_quote['price'])}}</span>
                                            @else
                                            <span class="vendor-price"></span>
                                            @endif
                                        </li>
                                        @endforeach
                                    </ul>
                                    <div class="show-more d-flex align-items-center justify-content-center py-2">
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
        @php
        if($rfq['is_auction'] == 1){
        $is_counter_offer_disabled = true;

        if($rfq['auction_status'] != 3){
        $is_order_btn_disabled = true;
        }
        }
        if(in_array($rfq['buyer_rfq_status'], [1, 5, 8, 10])){
        $is_auction_btn_disabled = true;
        $is_message_btn_disabled = true;
        $is_counter_offer_disabled = true;
        $is_order_btn_disabled = true;
        }
        if(in_array($rfq['buyer_rfq_status'], [9])){
        $is_auction_btn_disabled = true;
        }

        // if does not have technical approval permission then
        // $is_counter_offer_disabled = true;
        // $is_order_btn_disabled = true;
        @endphp
        <section class="floting-product-options cis-floating-button d-none d-sm-block">
            <div class="d-flex flex-wrap flex-md-nowrap align-items-center justify-content-center gap-3">
                <button type="button"
                    class="ra-btn btn-outline-primary ra-btn-outline-primary text-uppercase text-nowrap font-size-10 {{ $is_auction_btn_disabled ? 'disabled' : '' }}"
                    data-bs-toggle="modal" data-bs-target="#createAuctionModal">
                    <span class="bi bi-calendar-date font-size-12" aria-hidden="true"></span> {{$rfq['is_auction'] == 1
                    ? 'VIEW/EDIT AUCTION' : 'CREATE AUCTION' }}
                </button>
                <button type="button"
                    class="ra-btn btn-outline-primary ra-btn-outline-primary text-uppercase text-nowrap font-size-10 {{ $is_message_btn_disabled ? 'disabled' : '' }}"
                    data-bs-toggle="modal" data-bs-target="#messageModal">
                    <span class="bi bi-send font-size-12" aria-hidden="true"></span> Send Message
                </button>
                <button type="button" href="javascript:void(0)"
                    class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10 counter-offer-btn {{ $is_counter_offer_disabled ? 'disabled' : '' }}">
                    <span class="bi bi-repeat font-size-12" aria-hidden="true"></span> Counter Offer
                </button>
                <button type="button"
                    class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10 proceed-to-order-btn {{ $is_order_btn_disabled ? 'disabled' : '' }}">
                    <span class="bi bi-check2-square font-size-12" aria-hidden="true"></span> Proceed to Order
                </button>
            </div>
        </section>
    </div>
</main>





<!-- Modal Check Last CIS/PO -->
<div class="modal fade" id="checkLastCisPoModal" tabindex="-1" aria-labelledby="checkLastCisPoModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md create-auction-modal">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="checkLastCisPoModalLabel">
                    <span class="bi bi-eye" aria-hidden="true"></span>
                    Check Last CIS/PO
                </h2>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="pt-3">
                    <div class="form-group">
                        <input type="radio" name="check-cis-po" id="checkLastCis" value="cis" checked
                            onchange="checkCisPo();">
                        <label for="checkLastCis" class="me-4">Check Last CIS</label>

                        <input type="radio" name="check-cis-po" id="checkLastPo" value="po" onchange="checkCisPo();">
                        <label for="checkLastPo">Check Last PO</label>
                    </div>
                </div>
                <div class="pt-3">
                    <label for="checkCisProduct" class="font-size-13">Products</label>
                    <select name="checkCisProduct" id="checkCisProduct" class="form-select" onchange="checkCisPo();">
                        <option value="">Select Product</option>
                        @foreach($mg_products as $product_id => $product_name)
                        <option value="{{$product_id}}">{{$product_name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-3 check-result-table">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th id="cis_po_label">RFQ No.</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="checkCisPoResult">

                            </tbody>
                        </table>
                    </div>
                </div>




            </div>
        </div>
    </div>
</div>


<!-- Modal Create Auction -->
@include('buyer.auction.partials.create-auction-modal', [
'modalId' => 'createAuctionModal',
'action' => route('buyer.auction.create'),
'rfqId' => $rfq['rfq_id'] ?? null,
'rfqType' => $rfq['rfq_type'] ?? 'Scheduled',
'currentStatus' => $current_status ?? null,
'editId' => $editId ?? null,
'vendors' => $cis['vendors'] ?? [], // each: ['vendor_user_id','legal_name']
'variants' => $cis['variants'] ?? [],
'currencies' => $currencies ?? [],
// NEW:
'selectedVendorIds' => $selectedVendorIds ?? [],
'prefill' => $prefill ?? [],
'prefillVariantPrices' => $prefillVariantPrices ?? [],
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
            <form id="message-form" action="{{ route('message.storeMessageData') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="message pt-3">
                        <select class="form-control last-vendor-sumo-select required" name="vendors[]" multiple
                            required>
                            @if(!empty($cis['last_vendor']))
                            @foreach ($cis['last_vendor'] as $id => $name)
                            <option value="{{ $id }}" {{ !empty($filter) && $filter['last_vendor'] && in_array($id,
                                $filter['last_vendor']) ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="message pt-2">
                        <input type="text" id="subject" name="subject" value="{{ $rfq['rfq_id'] }}" class="form-control"
                            readonly required>
                    </div>

                    <section class="ck-editor-section py-2">
                        <textarea required name="message" id="msg1" rows="5" class="form-control height-inherit"
                            placeholder="This is the placeholder of the editor."></textarea>
                    </section>
                    <section class="upload-file py-2">
                        <div class="file-upload-block justify-content-start">
                            <div class="file-upload-wrapper">
                                <input type="file" name="attachment" class="file-upload" style="display: none;">
                                <button type="button" title="No file chosen"
                                    class="custom-file-trigger form-control text-start text-dark font-size-13">Upload
                                    file</button>
                            </div>
                            <div class="file-info" style="display: none;"></div>
                        </div>
                        <div class="text-danger-orange" style="display:none;">
                            Invalid file extension. Please upload a valid file (PDF, PNG, JPG, JPEG, DOCX, DOC, XLS,
                            CSV).
                        </div>
                    </section>

                </div>
                <div class="modal-footer">
                    <button type="submit"
                        class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Technical Approval -->
<div class="modal fade" id="technical-approval-modal" tabindex="-1" aria-labelledby="viewTechApprovalModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-16" id="viewTechApprovalModalLabel">Technical Approval</h2>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="technical-approval-form" action="javascript:void(0)" method="POST">
                    <div class="message pt-2">
                        <input type="hidden" value="" name="vendor_id" class="form-control"
                            id="technical-approval-vendor-id">
                        <input type="text" value="" class="form-control" id="technical-approval-vendor-name" readonly>
                    </div>
                    <div class="message pt-2">
                        <input type="text" value="{{ $rfq['rfq_id'] }}" name="rfq_no" class="form-control" readonly>
                    </div>

                    <div class="ck-editor-section py-2">
                        <label for="technicalApprovalDesc">Technical Approval Description</label>
                        <textarea id="technicalApprovalDesc" rows="5" name="technical_approval_description"
                            class="form-control height-inherit"></textarea>
                    </div>
                    <div class="pt-2 form-group">
                        <label class="font-size-13">Technical Approval</label>
                        <div class="mt-2">
                            <label class="radio-inline font-size-13 me-3">
                                <input type="radio" name="technical_approval" value="Yes" checked> Yes
                            </label>
                            <label class="radio-inline font-size-13">
                                <input type="radio" name="technical_approval" value="No"> No
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="technical-approval-form"
                    class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11">Submit</button>
                <button type="button"
                    class="ra-btn btn-outline-primary ra-btn-outline-danger text-uppercase text-nowrap font-size-11"
                    data-bs-dismiss="modal">Cancel</button>
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
    function checkCisPo() {
            let cis_po = $('input[name="check-cis-po"]:checked').val();
            let product_id = $('#checkCisProduct').val();
            if(cis_po=='po') {
                $('#cis_po_label').html('PO No.');
            }else{
                $('#cis_po_label').html('RFQ No.');
            }
            if(cis_po!='') {
                $.ajax({
                    url: "{{ route('buyer.rfq.cis.last-cis-po') }}",
                    type: "POST",
                    data: {
                        rfq_id: "{{ $rfq['rfq_id'] }}",
                        cis_po: cis_po,
                        product_id: product_id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        $('#checkCisPoResult').html(response);
                        console.log(response);
                    }
                });
            }
        }

        $('.location-sumo-select').SumoSelect({selectAll: true, nativeOnDevice: [], maxHeight:100, csvDispCount: 7, placeholder: 'Select Location' });
        $('.favourite-vendor-sumo-select').SumoSelect({selectAll: true, nativeOnDevice: [], maxHeight:100, csvDispCount: 7, placeholder: 'Select Favorite' });
        $('.last-vendor-sumo-select').SumoSelect({selectAll: true, nativeOnDevice: [], maxHeight:100, csvDispCount: 7, placeholder: 'Select Last Vendor' });

        jQuery(function () {
            initCkEditor('', "#msg1");
            initFileUpload();

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

            $(document).on("click", ".technical-approval", function() {
                $(".technical-approval").removeClass("active-technical-approval");
                $(this).addClass("active-technical-approval");

                $("#technical-approval-modal").modal({
                    backdrop: 'static',
                    keyboard: false
                });

                $("#technical-approval-vendor-id").val($(this).data("vendor-id"));
                $("#technical-approval-vendor-name").val($(this).data("vendor-name"));
                $("#technicalApprovalDesc").val($(this).data("technical-approval-description"));
                $('input[name="technical_approval"][value="' + ($(this).data("technical-approval") !='' ? $(this).data("technical-approval") : "Yes") + '"]').prop('checked', true);

                $("#technical-approval-modal").modal('show');
            });

            $(document).on("submit", "#technical-approval-form", function(e) {
                e.preventDefault();

                let _this = $(this);
                let formData = new FormData(this);

                $.ajax({
                    url: '{{ route("buyer.cis.technical-approval.save") }}',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        // console.log("response", response);
                        if(response.status){
                            window.location.reload();
                        }else{
                            let errors = response.errors;
                            $.each(errors, function(field, messages) {
                                // messages is an array
                                messages.forEach(function(msg) {
                                    toastr.error(msg);
                                });
                            });
                            // alert(response.message);
                            _this.removeClass("disabled");
                        }
                    },
                    error: function () {
                        _this.removeClass("disabled");
                        alert('Error submitting technical approval.');
                    }
                });

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

            $(document).on('click', '.select-vendor-product', function() {
                if(isActiveProceedToOrder){
                    $(".proceed-to-order-"+$(this).val()).prop("checked", $(this).prop("checked"));
                }
            });

            $(document).on('click', '.counter-offer-btn', function() {
                let vendor_data = [];
                if($(".select-vendor-product:checked").length>0){
                    $(".select-vendor-product:checked").each(function() {
                        vendor_data.push($(this).val());
                    });
                }else{
                    $(".select-vendor-product").each(function() {
                        vendor_data.push($(this).val());
                    });
                }
                let vendor_str_data = vendor_data.join(",");
                let url = "{{ route('buyer.rfq.counter-offer', ['rfq_id' => $rfq['rfq_id']]) }}";
                url = url + "?q="+encodeURIComponent(btoa(vendor_str_data));
                window.location.href = url;
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
