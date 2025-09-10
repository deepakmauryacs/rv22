<table border="0" cellpadding="0" cellspacing="0" id="sheet0" class="sheet0 gridlines"
    style="border-collapse: collapse;page-break-after: always;">
    <tbody>
        <tr class="row0" style="height: 76.8pt;">
            <td class="column0 style3 s style3" colspan="12"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #27405E;font-family: 'Calibri';font-size: 20pt;background-color: white;">
                <div style="position: relative;">
                    <img style="position: absolute; z-index: 1; left: 2px; width:200px; height:53px;" width="200"
                        height="53" src="{{ url('/') }}/public/assets/images/rfq-logo.png" border="0">
                    Exclusive Automated CIS
                </div>
            </td>
        </tr>
        <tr class="row1" style="height: 31.2pt;">
            <td class="column0 style9 s"
                style="text-align: left;border: 1px dotted black;vertical-align: middle;padding-left: 0px;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                &nbsp; RFQ No. : {{ $rfq['rfq_id'] }}</td>
            <td class="column1 style10 s"
                style="text-align: left;border: 1px dotted black;vertical-align: middle;padding-left: 0px;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
                PRN Number: {{ $rfq['prn_no'] }}</td>
            <td class="column2 style10 s"
                style="text-align: left;border: 1px dotted black;vertical-align: middle;padding-left: 0px;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
                Branch/Unit Details: {{ $rfq['buyer_branch_name'] }}</td>
            <td class="column3 style10 s"
                style="text-align: left;border: 1px dotted black;vertical-align: middle;padding-left: 0px;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
                Last Date to Response: {{ $rfq['last_response_date'] ? date('d/m/Y',
                strtotime($rfq['last_response_date'])) : '' }}</td>
            @if(!empty($rfq['edit_by']))
            <td class="column4 style10 s"
                style="text-align: left;border: 1px dotted black;vertical-align: middle;padding-left: 0px;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">

                Last Edited Date: {{ $rfq['updated_at'] ? date('d/m/Y', strtotime($rfq['updated_at'])) :
                '' }}</td>

            @endif
            <td class="column5 style10 s"
                style="text-align: left;border: 1px dotted black;vertical-align: middle;padding-left: 0px;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
                RFQ Date: {{ $rfq['created_at'] ? date('d/m/Y', strtotime($rfq['created_at'])) : '' }}</td>

            <!--  -->
            <td class="column6 style10 null"
                style="border: 1px dotted black;vertical-align: middle;text-align: left;padding-left: 0px;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column7 style10 null"
                style="border: 1px dotted black;vertical-align: middle;text-align: left;padding-left: 0px;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column8 style10 null"
                style="border: 1px dotted black;vertical-align: middle;text-align: left;padding-left: 0px;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column9 style10 null"
                style="border: 1px dotted black;vertical-align: middle;text-align: left;padding-left: 0px;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column10 style10 null"
                style="border: 1px dotted black;vertical-align: middle;text-align: left;padding-left: 0px;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column11 style11 null"
                style="border: 1px dotted black;vertical-align: middle;text-align: left;padding-left: 0px;border-left: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
            </td>

        </tr>



        <!-- Vendor name -->
        <tr class="row2" style="height: 48.6pt;">

            @if($rfq['is_auction'] == 1)
            @if($rfq['is_rfq_price_map'] == 1)
            <td colspan="6" class="column0" style="border: 0px dotted black;text-align: center;">




                <span style="color: red;font-weight: 700;font-size: 24px;">


                    NOTE: These are updated Rates post AUCTION
                    that was held on
                    {{ date('d/m/Y', strtotime($rfq['auction_date'])) }}

                </span>

                &nbsp;
            </td>
            @endif
            @else
            <td colspan="6" class="column0" style="border: none; text-align: center;"></td>
            @endif



            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }
            @endphp

            <td class="column6 style50 s"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;font-weight: bold;color: #1F497D;font-family: 'Calibri';font-size: 12pt;background-color: #DBE5F1;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{ $vendor['legal_name'] }}
            </td>
            @endforeach

        </tr>
        <!-- END Vendor name:ROW -->

        <!-- Vendor Mobile Number:ROW -->
        <tr class="row2" style="height: 31.2pt;">
            <td colspan="6" class="column0" style="border: none;">&nbsp;</td>

            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }
            @endphp
            <td class="column6 style50 s"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;font-weight: bold;color: #1F497D;font-family: 'Calibri';font-size: 12pt;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{ $vendor['country_code'] ? '+'.$vendor['country_code'] : '' }}
                {{$vendor['mobile']}}
            </td>
            @endforeach
        </tr>


        <!-- Vendor Quoted %:ROW -->
        <tr class="row3" style="height: 31.2pt;">
            <td colspan="6" class="column0 style6 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;">
            </td>

            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }
            @endphp

            <td class="column6 style18 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                Quoted {{ $vendor['vendor_quoted_product'] }}</td>
            @endforeach


        </tr>


        <tr class="row4" style="height: 31.2pt;">
            <td class="column0 style14 s"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                {{$rfq['rfq_division']}} &gt; {{$rfq['rfq_category']}}</td>

            <td class="column1 style15 s"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column2 style15 s"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column3 style15 s"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column4 style15 s"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column5 style15 s"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-left: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;">
            </td>

            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }
            @endphp
            <td class="column6 style19 s"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                Last Offer Date {{ !empty($vendor['latest_quote']) ? date('d/m/Y',
                strtotime($vendor['latest_quote']['created_at'])) : '' }}</td>
            @endforeach


        </tr>


        <tr class="row5" style="height: 16.363636363636pt;">
            <td class="column0 style49 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #D2DAE4;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                Product</td>
            <td class="column1 style49 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #D2DAE4;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                Specifications</td>
            <td class="column2 style49 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #D2DAE4;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                Size</td>
            <td class="column3 style49 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #D2DAE4;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                Quantity</td>
            <td class="column4 style49 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #D2DAE4;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                UOM</td>
            <td class="column5 style49 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #D2DAE4;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                Counter Offer</td>

            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }
            @endphp
            <td class="column6 style49 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #D2DAE4;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                Rate (₹)</td>
            @endforeach
        </tr>

        <!-- Vendor Price:Start -->
        @foreach($cis['variants'] as $variant_id => $variants)
        <tr class="row6" style="height: 19.2pt;">

            <!----Buyer product information--->
            <td class="column0 style51 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #1F497D;font-family: 'Calibri';font-size: 12pt;background-color: #DBE5F1;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{$variants['product_name']}}</td>
            <td class="column1 style20 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{$variants['specification']}}</td>
            <td class="column2 style20 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{$variants['size']}}</td>
            <td class="column3 style20 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{ $variants['quantity'] }}</td>
            <td class="column4 style20 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{ $uom[$variants['uom']]
                }}
            </td>


            <td class="column5 style20 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                ₹ counter offer
            </td>



            <!----vendor rate information--->
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

            <td class="column6 style22 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{!empty($vendor['latest_quote']) &&
                !empty($vendor['latest_quote']['vendor_currency']) ?
                $vendor['latest_quote']['vendor_currency'] : '₹'}}
                {{IND_money_format($vendor_last_quote['price'])}}
            </td>




            <!-- This Checkbox will show when Buyer click Proceed to order Button -->
            @if(!empty($vendor['latest_quote']) &&
            $vendor['latest_quote']['left_qty'] > 0)

            <!--:-  -:-->
            @endif


            @else
            <td class="column6 style22 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                x
                @endif
                @endif

                @endforeach



        </tr>
        @endforeach
        <!-- Vendor Price:End -->

        <!--- Total --->
        <tr class="row8" style="height: 16.363636363636pt;">
            <td class="column0 style24 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Total</td>
            <td class="column1 style40 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column2 style40 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column3 style40 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column4 style40 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column5 style41 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
            </td>

            <!--- Vendor Total --->
            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }
            @endphp

            <td class="column6 style25 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important; font-weight:{{ !empty($cis['vendor_total_amount'][$vendor_id]) && $cis['vendor_total_amount'][$vendor_id] == $rfq['lowest_price_total'] ? '90000' : '' }}">
                <b> {{!empty($vendor['latest_quote']) &&
                    !empty($vendor['latest_quote']['vendor_currency']) ?
                    $vendor['latest_quote']['vendor_currency'] : '₹'}}
                    {{$cis['vendor_total_amount'][$vendor_id] ?
                    IND_money_format($cis['vendor_total_amount'][$vendor_id]) : 0}}</b>
            </td>
            @endforeach
        </tr>



        <!-- Price Basis -->
        <tr class="row11" style="height: 16.363636363636pt;">
            <td class="column0 style31 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Price Basis</td>

            <!-- buyer_price_basis -->
            <td colspan="5" class="column1 style38 s"
                style="border: 1px dotted black;vertical-align: bottom;border-bottom: 1px solid #000000;border-top: 1px solid #000000;border-right: 1px solid #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-left: 1px solid #000000 !important;">
                @php
                $buyer_price_basis = !empty($rfq['buyer_price_basis']) ?
                $rfq['buyer_price_basis'] : '';
                @endphp
                {!! $buyer_price_basis !!}

            </td>


            <!-- vendor price_basis -->

            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }

            $vendor_price_basis = !empty($vendor['latest_quote']) ?
            $vendor['latest_quote']['vendor_price_basis'] : '';

            @endphp
            <td class="column6 style29 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {!! $vendor_price_basis !!}</td>
            @endforeach
        </tr>
        <!--End Price Basis -->


        <!-- Payment Terms -->
        <tr class="row9" style="height: 16.363636363636pt;">
            <td class="column0 style12 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Payment Terms</td>
            <!----:- buyer_pay_term -:----->
            <td class="column1 style37 s"
                style="border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                @php
                $buyer_pay_term = !empty($rfq['buyer_pay_term']) ?
                $rfq['buyer_pay_term'] : '';
                @endphp
                {!! $buyer_pay_term !!}
            </td>





            <td class="column2 style32 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column3 style32 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column4 style32 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column5 style33 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
            </td>

            <!----:- vendor_pay_term -:----->
            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }
            @endphp

            @php
            $vendor_payment_terms = !empty($vendor['latest_quote']) ?
            $vendor['latest_quote']['vendor_payment_terms'] : '';
            @endphp




            <td class="column6 style29 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {!! $vendor_payment_terms !!}</td>

            @endforeach

        </tr>
        <!--End  Payment Terms -->



        <!-- Delivery Period -->
        <tr class="row10" style="height: 16.363636363636pt;">
            <td class="column0 style55 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Delivery Period</td>
            <!---- buyer_delivery_period ---->
            <td colspan="5" class="column1 style56 s"
                style="border: 1px dotted black;vertical-align: bottom;border-bottom: 1px solid #000000;border-top: 1px solid #000000;border-right: 1px solid #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-left: 1px solid #000000 !important;">
                {{ $rfq['buyer_delivery_period'] ?
                $rfq['buyer_delivery_period']. ' Days' : '' }} </td>

            <!---- vendor delivery_period ---->


            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }
            @endphp


            @php
            $vendor_delivery_period = !empty($vendor['latest_quote']) ?
            $vendor['latest_quote']['vendor_delivery_period'].' Days' : '';
            @endphp
            <td class="column6 style59 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {!! $vendor_delivery_period !!}</td>

            @endforeach

        </tr>
        <!-- End Delivery Period -->

        <!---:- seller_brand -:--->
        <tr class="row12" style="height: 16.363636363636pt;">
            <td class="column0 style55 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
            </td>

            <td colspan="5" class="column1 style56 s"
                style="border: 1px dotted black;vertical-align: bottom;border-bottom: 1px solid #000000;border-top: 1px solid #000000;border-right: 1px solid #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-left: 1px solid #000000 !important;">
            </td>

            <!-----: vend_wise_brand :------>



            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }
            @endphp
            <td class="column6 style59 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                @php
                $vendor_brand = !empty($vendor['vendor_brand']) ?
                $vendor['vendor_brand'] : '';
                @endphp
                {{ $vendor_brand }}

            </td>
            @endforeach



        </tr>
        <!---:- End seller_brand -:--->


        <!--:- Remarks -:--->
        <tr class="row12" style="height: 16.363636363636pt;">
            <td class="column0 style55 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Remarks</td>
            <td colspan="5" class="column1 style56 s"
                style="border: 1px dotted black;vertical-align: bottom;border-bottom: 1px solid #000000;border-top: 1px solid #000000;border-right: 1px solid #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-left: 1px solid #000000 !important;">
            </td>

            <!----:-seller_remarks -:--->



            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }
            @endphp
            <td class="column6 style59 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                @php
                $vendor_remarks = !empty($vendor['latest_quote']) ?
                $vendor['latest_quote']['vendor_remarks'] : '';
                @endphp

                {!! $vendor_remarks !!}
            </td>
            @endforeach

        </tr>
        <!--:- End Remarks -:--->

        <!--:- Additional Remarks -:--->
        <tr class="row13" style="height: 16.363636363636pt;">
            <td class="column0 style31 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Additional Remarks</td>
            <td class="column1 style39 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
            </td>
            <td class="column2 style35 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;">
            </td>
            <td class="column3 style35 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;">
            </td>
            <td class="column4 style35 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;">
            </td>
            <td class="column5 style36 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
            </td>

            <!---:-Vendor seller_add_remarks-:----->


            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }
            @endphp
            <td class="column6 style33 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: 1px solid #000000;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                @php
                $vendor_additional_remarks = !empty($vendor['latest_quote']) ?
                $vendor['latest_quote']['vendor_additional_remarks'] : '';
                @endphp
                {{ $vendor_additional_remarks }}
            </td>
            @endforeach
        </tr>
        <!--:-END Additional Remarks -:--->



        <!---:- Company Information -:---->
        <tr class="row14" style="height: 16.363636363636pt;">
            <td class="column0 style23 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;border-right: none #000000 !important;">
                Company Information:</td>
            <td class="column1 style27 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;">
            </td>
            <td class="column2 style27 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;">
            </td>
            <td class="column3 style27 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;">
            </td>
            <td class="column4 style27 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;">
            </td>
            <td class="column4 style27 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;">
            </td>


            <!----:-It should be dynamic -:---->
            @foreach($cis['vendors'] as $vendor_id => $vendor)
            <td class="column4 style27 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2DBDB;">
            </td>
            @endforeach

        </tr>
        <!---:-END  Company Information -:---->

        <!-----Vintage------>
        <tr class="row15" style="height: 16.363636363636pt;">
            <td class="column0 style12 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Vintage</td>
            <td class="column1 style37 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
            </td>
            <td class="column2 style32 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column3 style32 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column4 style32 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;">
            </td>
            <td class="column5 style33 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
            </td>

            <!-----Vendor vintage ----->


            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }
            @endphp

            <td class="column6 style36 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: 1px solid #000000;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{ $vendor['vintage'] }} Years
            </td>
            @endforeach

        </tr>
        <!-----END Vintage------>


        <!----Business Type---->
        <tr class="row16" style="height: 16.363636363636pt;">
            <td class="column0 style55 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Business Type</td>


            <td class="column1 style56 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-left: 1px solid #000000 !important;">
            </td>
            <td class="column2 style57 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;">
            </td>
            <td class="column3 style57 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;">
            </td>
            <td class="column4 style57 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;">
            </td>
            <td class="column5 style58 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-right: 1px solid #000000 !important;">
            </td>


            <!---- vendor Business Type ----->

            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }
            @endphp


            <td class="column6 style59 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                {{ $nature_of_business[$vendor['nature_of_business']] }}
            </td>
            @endforeach
        </tr>
        <!----END Business Type---->


        <!-----Main Products------>
        <tr class="row17" style="height: 43.8pt;">
            <td class="column0 style14 s"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Main Products</td>
            <td class="column1 style42 s"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-bottom: none #000000;border-top: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-left: 1px solid #000000 !important;">
            </td>
            <td class="column2 style43 s"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;">
            </td>
            <td class="column3 style43 s"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;">
            </td>
            <td class="column4 style43 s"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;">
            </td>
            <td class="column5 style44 s"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-right: 1px solid #000000 !important;">
            </td>

            <!----vendor main_products---->


            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }
            @endphp
            <td class="column6 style45 s"
                style="text-align: center;border: 1px dotted black;vertical-align: middle;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                @php
                $vendor_product = !empty($vendor['vendor_product']) ?
                $vendor['vendor_product'] : '';
                @endphp
                {{ $vendor_product }}

            </td>
            @endforeach
        </tr>
        <!-----END Main Products------>

        <!----Client----->
        <tr class="row18" style="height: 16.363636363636pt;">
            <td class="column0 style55 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Client</td>
            <td class="column1 style56 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-left: 1px solid #000000 !important;">
            </td>
            <td class="column2 style57 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;">
            </td>
            <td class="column3 style57 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;">
            </td>
            <td class="column4 style57 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;">
            </td>
            <td class="column5 style58 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-bottom: none #000000;border-top: none #000000;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-right: 1px solid #000000 !important;">
            </td>

            <!------Vendor client------>


            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }
            @endphp
            <td class="column6 style59 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: #F2F2F2;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                @php
                $client = !empty($vendor['client']) ? $vendor['client'] : '';
                @endphp
                {{ $client }}

            </td>
            @endforeach
        </tr>
        <!----END Client----->


        <!-----Certifications-MSME/ISO------>
        <tr class="row19" style="height: 16.363636363636pt;">
            <td class="column0 style13 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-right: none #000000;font-weight: bold;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
                Certifications-MSME/ISO</td>
            <td class="column1 style39 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-left: 1px solid #000000 !important;">
            </td>
            <td class="column2 style35 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;">
            </td>
            <td class="column3 style35 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;">
            </td>
            <td class="column4 style35 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;border-left: none #000000;border-right: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;">
            </td>
            <td class="column5 style36 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-top: none #000000;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
            </td>

            <!----vendor certifications----->

            @foreach($cis['vendors'] as $vendor_id => $vendor)
            @php
            if(!empty($cis['filter_vendors']) && !in_array($vendor_id,
            $cis['filter_vendors'])) {
            continue;
            }
            @endphp
            <td class="column6 style29 s"
                style="text-align: center;border: 1px dotted black;vertical-align: bottom;border-left: none #000000;color: #000000;font-family: 'Calibri';font-size: 12pt;background-color: white;border-bottom: 1px solid #000000 !important;border-top: 1px solid #000000 !important;border-right: 1px solid #000000 !important;">
                @php
                $certifications = !empty($vendor['certifications']) ?
                $vendor['certifications'] : '';
                @endphp
                {{ $certifications }}

            </td>
            @endforeach


        </tr>
    </tbody>
</table>