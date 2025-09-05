@extends('buyer.layouts.app', ['title'=>'Unapproved Order Confirmation'])

@section('css')
    {{-- <link rel="stylesheet" href="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.css') }}" /> --}}
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
                        <button type="button"
                            class="ra-btn btn-outline-primary ra-btn-outline-primary small-btn text-uppercase text-nowrap">
                        <span class="bi bi-download" aria-hidden="true"></span> Download
                        </button>
                        <a href="{{ route('buyer.rfq.cis-sheet', ['rfq_id' => $unapprovedOrder['rfq']['rfq_id']]) }}" class="ra-btn small-btn ra-btn-primary small-btn">
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
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInfo-{{$vendor_id}}"
                                aria-expanded="false" aria-controls="collapseInfo-{{$vendor_id}}">
                            {{$sr++}}. {{$vendor['legal_name']}}
                            </button>
                        </h2>
                        @include('buyer.unapproved-orders.partials.vendor-details', ['vendor' => $vendor, 'variants' => $unapprovedOrder['variants'], 'buyer_country' => $unapprovedOrder['rfq']['buyer_country'], 'warranty_gurarantee' => $unapprovedOrder['rfq']['warranty_gurarantee'], 'uom'=>$uom, 'taxes'=>$taxes])
                    @endforeach
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')

@endsection