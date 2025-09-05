<h3 class="font-size-18 mb-3">Vendor List</h3>
<div class="card-vendor-list-search-panel mb-3">
    <div class="card-vendor-list-search">
        <label class="visually-hidden-focusable">Search & Select more Vendors</label>
        <input type="text" name="keyword" placeholder="Search &amp; Select more Vendors" class="form-control bg-white search-product-vendor">
    </div>
    <div class="card-vendor-list-search-list mt-2 scrollSection">
        <div class="filter-list scroll-list">
            <div>
                <label class="ra-custom-checkbox">
                    <input type="checkbox" class="select-product-all-vendor" id="select-all-vendor-------------">
                    <span class="font-size-11">Select All</span>
                    <span class="checkmark "></span>
                </label>
            </div>
            @php
                $rfq_unique_vendors = [];
            @endphp
            @foreach($draft_rfq->rfqProducts as $k => $row)
                @foreach ($row['productVendors'] as $vendor)
                    @php
                        if(!in_array($vendor->vendor_id, $rfq_unique_vendors)){
                            array_push($rfq_unique_vendors, $vendor->vendor_id);
                        }else{
                            continue;
                        }
                        $location_type_class = $vendor->vendor_profile->vendor_country->id==101 ? "domestic-vendor" : "international-vendor";
                        $location_id = $vendor->vendor_profile->vendor_country->id==101 ? $vendor->vendor_profile->vendor_state->id : $vendor->vendor_profile->vendor_country->id;
                    @endphp
                    <div class="vendor-checkbox">
                        <label class="ra-custom-checkbox">
                            <input type="checkbox" class="vendor-input-checkbox vendor-has-{{ $vendor->vendor_id }}-id vendor-location-has-{{ $location_id }}-id {{$location_type_class}}" data-vendor-name="{{ $vendor->vendor_profile['legal_name'] }}"
                            {{ in_array($vendor->vendor_id, $rfq_vendors) ? "checked" : "" }} value="{{ $vendor->vendor_id }}" data-product-id="{{$row['masterProduct']->id}}" name="vendor_id[]">
                            <span class="font-size-11">{{ $vendor->vendor_profile['legal_name'] }}</span>
                            <span class="checkmark "></span>
                        </label>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>
</div>