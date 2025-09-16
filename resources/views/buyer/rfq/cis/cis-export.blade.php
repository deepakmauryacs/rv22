@php
$count=6;
@endphp

<table border="0" cellpadding="0" cellspacing="0"
    style="border-collapse: collapse;page-break-after: always; width: 100%;">

    @foreach($cis['vendors'] as $vendor_id => $vendor)
    @if ($cis['vendor_total_amount'][$vendor_id]>0)
    @php $count++; @endphp
    @endif
    @endforeach

    <tbody>
        <tr style="height: 76.8pt;">
            <td colspan="{{ $count }}"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-bottom: 1px solid #000000;border-top: none #000000;border-left: none #000000;border-right: 1px solid #000000;font-weight: bold;color: #27405E;font-family: 'Calibri';font-size: 20pt;background-color: white;">
                <div style="position: relative;">
                    <img style="position: absolute; z-index: 1; left: 2px; width:200px; height:53px;" width="200"
                        height="53" src="{{ url('/') }}/public/assets/images/rfq-logo.png" border="0">
                    Exclusive Automated CIS
                </div>
            </td>
        </tr>
        <tr style="height: 31.2pt;">
            <td
                style="text-align: left;border: 1px dotted black;vertical-align: middle;padding-left: 0px;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                &nbsp; RFQ No. : {{ $rfq['rfq_id'] }}</td>
            <td
                style="text-align: left;border: 1px dotted black;vertical-align: middle;padding-left: 0px;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
                PRN Number: {{ $rfq['prn_no'] }}</td>
            <td
                style="text-align: left;border: 1px dotted black;vertical-align: middle;padding-left: 0px;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
                Branch/Unit Details: {{ $rfq['buyer_branch_name'] }}</td>
            <td
                style="text-align: left;border: 1px dotted black;vertical-align: middle;padding-left: 0px;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
                Last Date to Response: {{ $rfq['last_response_date'] ? date('d/m/Y',
                strtotime($rfq['last_response_date'])) : '' }}</td>
            @if(!empty($rfq['edit_by']))
            <td
                style="text-align: left;border: 1px dotted black;vertical-align: middle;padding-left: 0px;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">

                Last Edited Date: {{ $rfq['updated_at'] ? date('d/m/Y', strtotime($rfq['updated_at'])) :
                '' }}</td>

            @endif
            <td
                style="text-align: left;border: 1px dotted black;vertical-align: middle;padding-left: 0px;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
                RFQ Date: {{ $rfq['created_at'] ? date('d/m/Y', strtotime($rfq['created_at'])) : '' }}</td>

            <!--  -->
            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)

            <td
                style="border: 1px dotted black;vertical-align: middle;text-align: left;padding-left: 0px;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
            </td>

            @if ($loop->last)
            <td
                style="border: 1px solid black;vertical-align: middle;text-align: left;padding-left: 0px;border-left: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
            </td>
            @endif
            @endif
            @endforeach

        </tr>



        <!-- Vendor name -->
        <tr style="height: 48.6pt;">
            <td colspan="6" style="border: none;">&nbsp;</td>

            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: middle;font-weight: bold;color: #1F497D;font-family: 'Calibri';font-size: 12pt;background-color: #DBE5F1;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{ $vendor['legal_name'] }}
            </td>
            @endif
            @endforeach

        </tr>
        <!-- END Vendor name:ROW -->

        <!-- Vendor Mobile Number:ROW -->
        <tr style="height: 31.2pt;">
            @if($rfq['is_auction'] == 1)
            @if($rfq['is_rfq_price_map'] == 1)
            <td colspan="6"
                style="border: 0px dotted red;text-align: center; color: red;font-weight: 700;font-size: 14px;">
                NOTE: These are updated Rates post AUCTION
                that was held on
                {{ date('d/m/Y', strtotime($rfq['auction_date'])) }}
                &nbsp;
            </td>
            @else
            <td colspan="6" style="border: none; text-align: center;">&nbsp;</td>
            @endif
            @else
            <td colspan="6" style="border: none; text-align: center;">&nbsp;</td>
            @endif

            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)

            <td
                style="text-align: center;border: 1px dotted black;vertical-align: middle;font-weight: bold;color: #1F497D;font-family: 'Calibri';font-size: 12pt;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{ $vendor['country_code'] ? '+'.$vendor['country_code'] : '' }}
                {{$vendor['mobile']}}
            </td>
            @endif
            @endforeach
        </tr>


        <!-- Vendor Quoted %:ROW -->
        <tr style="height: 31.2pt;">
            <td colspan="6"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;">
            </td>

            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)

            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                Quoted {{ $vendor['vendor_quoted_product'] }}</td>
            @endif
            @endforeach
        </tr>


        <tr style="height: 31.2pt;">
            <td colspan="6"
                style="text-align: left;border: 1px dotted black;vertical-align: middle;border-right: 1px solid #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                {{$rfq['rfq_division']}} &gt; {{$rfq['rfq_category']}}</td>

            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: middle;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                Last Offer Date {{ !empty($vendor['latest_quote']) ? date('d/m/Y',
                strtotime($vendor['latest_quote']['created_at'])) : '' }}</td>
            @endif
            @endforeach
        </tr>


        <tr style="height: 16.363636363636pt;">
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #D2DAE4;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                Product</td>
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #D2DAE4;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                Specifications</td>
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #D2DAE4;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                Size</td>
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #D2DAE4;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                Quantity</td>
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #D2DAE4;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                UOM</td>
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #D2DAE4;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                Counter Offer</td>

            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #D2DAE4;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                Rate (@if (!empty($vendor['latest_quote']) &&
                !empty($vendor['latest_quote']['vendor_currency']))
                @if ($vendor['latest_quote']['vendor_currency'] =='₹')
                &#8377;
                @elseif ($vendor['latest_quote']['vendor_currency'] =='$')
                &#36;
                @elseif ($vendor['latest_quote']['vendor_currency'] =='NPR')
                &#x930;&#x941;
                @endif

                @endif)</td>
            @endif
            @endforeach
        </tr>

        <!-- Vendor Price:Start -->
        @foreach($cis['variants'] as $variant_id => $variants)
        <tr style="height: 19.2pt;">

            <!----Buyer product information--->
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #1F497D;font-family: 'Calibri';font-size: 12pt;background-color: #DBE5F1;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{$variants['product_name']}}</td>
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{$variants['specification']}}</td>
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{$variants['size']}}</td>
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{ $variants['quantity'] }}</td>
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{ $uom[$variants['uom']]
                }}
            </td>


            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                @php
                $variant_quotes = isset($cis['buyer_quotes'][$variant_id]) ? $cis['buyer_quotes'][$variant_id] : [];
                @endphp
                @if(!empty($variant_quotes))
                @php
                $last_quote = $variant_quotes[0];
                $variant_quote_history = [];
                @endphp

                @foreach ($variant_quotes as $item)
                @php
                $variant_quote_history[] = $item['buyer_price'] ."(". date('d-M', strtotime($item['updated_at'])).")";
                @endphp
                @endforeach
                {{-- {{!empty($vendor['latest_quote']) &&
                !empty($vendor['latest_quote']['vendor_currency']) ?
                $vendor['latest_quote']['vendor_currency'] : '₹'}} --}}

                @if (!empty($vendor['latest_quote']) &&
                !empty($vendor['latest_quote']['vendor_currency']))
                @if ($vendor['latest_quote']['vendor_currency'] =='₹')
                &#8377;
                @elseif ($vendor['latest_quote']['vendor_currency'] =='$')
                &#36;
                @elseif ($vendor['latest_quote']['vendor_currency'] =='NPR')
                &#x930;&#x941;
                @endif

                @endif

                {{ IND_money_format($last_quote['buyer_price']) }}
                @endif
            </td>



            <!----vendor rate information--->
            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)

            @if(isset($cis['is_vendor_product'][$vendor_id]) &&
            isset($cis['is_vendor_product'][$vendor_id][$variants['product_id']]))
            @php
            $vendor_last_quote = isset($vendor['last_quote'][$variant_id]) ?
            $vendor['last_quote'][$variant_id] : [];
            @endphp

            @if(!empty($vendor_last_quote))

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

            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{-- {{!empty($vendor['latest_quote']) &&
                !empty($vendor['latest_quote']['vendor_currency']) ?
                $vendor['latest_quote']['vendor_currency'] : '₹'}} --}}

                @if (!empty($vendor['latest_quote']) &&
                !empty($vendor['latest_quote']['vendor_currency']))
                @if ($vendor['latest_quote']['vendor_currency'] =='₹')
                &#8377;
                @elseif ($vendor['latest_quote']['vendor_currency'] =='$')
                &#36;
                @elseif ($vendor['latest_quote']['vendor_currency'] =='NPR')
                &#x930;&#x941;
                @endif

                @endif


                {{IND_money_format($vendor_last_quote['price'])}}
            </td>




            <!-- This Checkbox will show when Buyer click Proceed to order Button -->
            @if(!empty($vendor['latest_quote']) &&
            $vendor['latest_quote']['left_qty'] > 0)

            <!--:-  -:-->
            @endif


            @else
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                x</td>
            @endif
            @endif
            @endif

            @endforeach



        </tr>
        @endforeach
        <!-- Vendor Price:End -->

        <!--- Total --->
        <tr style="height: 16.363636363636pt;">
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Total</td>
            <td colspan="5"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;border-top: 1px solid #000000 !important;">
            </td>


            <!--- Vendor Total --->
            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)

            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                <b>
                    {{-- {{!empty($vendor['latest_quote']) &&
                    !empty($vendor['latest_quote']['vendor_currency']) ?
                    $vendor['latest_quote']['vendor_currency'] : '₹'}} --}}
                    @if (!empty($vendor['latest_quote']) &&
                    !empty($vendor['latest_quote']['vendor_currency']))
                    @if ($vendor['latest_quote']['vendor_currency'] =='₹')
                    &#8377;
                    @elseif ($vendor['latest_quote']['vendor_currency'] =='$')
                    &#36;
                    @elseif ($vendor['latest_quote']['vendor_currency'] =='NPR')
                    &#x930;&#x941;
                    @endif

                    @endif

                    {{$cis['vendor_total_amount'][$vendor_id] ?
                    IND_money_format($cis['vendor_total_amount'][$vendor_id]) : 0}}</b>
            </td>
            @endif
            @endforeach
        </tr>



        <!-- Price Basis -->
        <tr style="height: 16.363636363636pt;">
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Price Basis</td>

            <!-- buyer_price_basis -->
            <td colspan="5"
                style="border: 1px dotted black;vertical-align: bottom;border-bottom: 1px solid #000000;border-top: 1px solid #000000;border-right: 1px solid #000000;color: #000000; text-align:left; font-family: 'Calibri';font-size: 12pt;background-color: white;border-left: 1px solid #000000 !important;">
                @php
                $buyer_price_basis = !empty($rfq['buyer_price_basis']) ?
                $rfq['buyer_price_basis'] : '';
                @endphp
                {!! $buyer_price_basis !!}

            </td>


            <!-- vendor price_basis -->

            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }

            $vendor_price_basis = !empty($vendor['latest_quote']) ?
            $vendor['latest_quote']['vendor_price_basis'] : '';

            @endphp
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {!! $vendor_price_basis !!}</td>
            @endif
            @endforeach
        </tr>
        <!--End Price Basis -->


        <!-- Payment Terms -->
        <tr style="height: 16.363636363636pt;">
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Payment Terms</td>
            <!----:- buyer_pay_term -:----->
            <td colspan="5"
                style="text-align: left;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-right: 1px solid #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                @php
                $buyer_pay_term = !empty($rfq['buyer_pay_term']) ?
                $rfq['buyer_pay_term'] : '';
                @endphp
                {!! $buyer_pay_term !!}
            </td>

            <!----:- vendor_pay_term -:----->
            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)

            @php
            $vendor_payment_terms = !empty($vendor['latest_quote']) ?
            $vendor['latest_quote']['vendor_payment_terms'] : '';
            @endphp

            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {!! $vendor_payment_terms !!}</td>
            @endif
            @endforeach

        </tr>
        <!--End  Payment Terms -->



        <!-- Delivery Period -->
        <tr style="height: 16.363636363636pt;">
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Delivery Period</td>
            <!---- buyer_delivery_period ---->
            <td colspan="5"
                style="border: 1px dotted black;vertical-align: bottom;border-bottom: 1px solid #000000;border-top: 1px solid #000000;border-right: 1px solid #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-left: 1px solid #000000 !important;">
                {{ $rfq['buyer_delivery_period'] ?
                $rfq['buyer_delivery_period']. ' Days' : '' }} </td>

            <!---- vendor delivery_period ---->


            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)



            @php
            $vendor_delivery_period = !empty($vendor['latest_quote']) ?
            $vendor['latest_quote']['vendor_delivery_period'].' Days' : '';
            @endphp
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {!! $vendor_delivery_period !!}</td>
            @endif
            @endforeach
        </tr>
        <!-- End Delivery Period -->

        <!---:- seller_brand -:--->
        <tr style="height: 16.363636363636pt;">
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
            </td>

            <td colspan="5"
                style="border: 1px dotted black;vertical-align: bottom;border-bottom: 1px solid #000000;border-top: 1px solid #000000;border-right: 1px solid #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-left: 1px solid #000000 !important;">
            </td>

            <!-----: vend_wise_brand :------>
            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)

            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                @php
                $vendor_brand = !empty($vendor['vendor_brand']) ?
                $vendor['vendor_brand'] : '';
                @endphp
                {{ $vendor_brand }}

            </td>
            @endif
            @endforeach
        </tr>
        <!---:- End seller_brand -:--->


        <!--:- Remarks -:--->
        <tr style="height: 16.363636363636pt;">
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Remarks</td>
            <td colspan="5"
                style="text-align: left;border: 1px dotted black;vertical-align: bottom;border-bottom: 1px solid #000000;border-top: 1px solid #000000;border-right: 1px solid #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-left: 1px solid #000000 !important;">
            </td>

            <!----:-seller_remarks -:--->



            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)

            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                @php
                $vendor_remarks = !empty($vendor['latest_quote']) ?
                $vendor['latest_quote']['vendor_remarks'] : '';
                @endphp

                {!! $vendor_remarks !!}
            </td>
            @endif
            @endforeach

        </tr>
        <!--:- End Remarks -:--->

        <!--:- Additional Remarks -:--->
        <tr style="height: 16.363636363636pt;">
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Additional Remarks</td>
            <td colspan="5"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;border-right: 1px solid #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
            </td>


            <!---:-Vendor seller_add_remarks-:----->


            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)

            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: 1px solid #000000;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                @php
                $vendor_additional_remarks = !empty($vendor['latest_quote']) ?
                $vendor['latest_quote']['vendor_additional_remarks'] : '';
                @endphp
                {{ $vendor_additional_remarks }}
            </td>
            @endif
            @endforeach
        </tr>
        <!--:-END Additional Remarks -:--->



        <!---:- Company Information -:---->
        <tr style="height: 16.363636363636pt;">
            <td
                style="text-align: center;border: 1px solid black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: none #000000 !important;">
                Company Information:</td>
            <td colspan="5"
                style="text-align: center;border: 1px solid black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: 1px solid #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;">
            </td>


            <!----:-It should be dynamic -:---->
            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)




            @if ($loop->last)
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: 1px solid #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;">
            </td>
            @else
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;">
            </td>
            @endif
            @endif
            @endforeach

        </tr>
        <!---:-END  Company Information -:---->

        <!-----Vintage------>
        <tr style="height: 16.363636363636pt;">
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Vintage</td>
            <td colspan="5"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-right: 1px solid #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
            </td>


            <!-----Vendor vintage ----->


            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)


            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: 1px solid #000000;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{ $vendor['vintage'] }} Years
            </td>
            @endif
            @endforeach
        </tr>
        <!-----END Vintage------>


        <!----Business Type---->
        <tr style="height: 16.363636363636pt;">
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Business Type</td>
            <td colspan="5"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-right: 1px solid #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-left: 1px solid #000000 !important;">
            </td>

            <!---- vendor Business Type ----->
            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{ $nature_of_business[$vendor['nature_of_business']] }}
            </td>
            @endif
            @endforeach
        </tr>
        <!----END Business Type---->


        <!-----Main Products------>
        <tr style="height: 43.8pt;">
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Main Products</td>
            <td colspan="5"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-bottom: none #000000;border-top: none #000000;border-right: 1px solid #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-left: 1px solid #000000 !important;">
            </td>


            <!----vendor main_products---->
            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)

            <td
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                @php
                $vendor_product = !empty($vendor['vendor_product']) ?
                $vendor['vendor_product'] : '';
                @endphp
                {{ $vendor_product }}

            </td>
            @endif
            @endforeach
        </tr>
        <!-----END Main Products------>

        <!----Client----->
        <tr style="height: 16.363636363636pt;">
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Client</td>
            <td colspan="5"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-right: 1px solid #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-left: 1px solid #000000 !important;">
            </td>


            <!------Vendor client------>
            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)

            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                @php
                $client = !empty($vendor['client']) ? $vendor['client'] : '';
                @endphp
                {{ $client }}
            </td>
            @endif
            @endforeach
        </tr>
        <!----END Client----->


        <!-----Certifications-MSME/ISO------>
        <tr style="height: 16.363636363636pt;">
            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Certifications-MSME/ISO</td>
            <td colspan="5"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;border-right: 1px solid  #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
            </td>


            <!----vendor certifications----->

            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @if ($cis['vendor_total_amount'][$vendor_id]>0)

            <td
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                @php
                $certifications = !empty($vendor['certifications']) ?
                $vendor['certifications'] : '';
                @endphp
                {{ $certifications }}

            </td>
            @endif
            @endforeach
        </tr>
    </tbody>
</table>