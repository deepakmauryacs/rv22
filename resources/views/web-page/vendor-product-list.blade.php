@forelse ($collection as $item)
<div class="col-lg-4 col-md-4 col-sm-12">
    <div class="product-items bg-white position-relative">

        <div class="product-items-thumb">

            <img style="cursor:pointer;"
                onclick="window.location='{{ route('webPage.productDetail',['companyName'=>session()->get('company_slug'),'productId'=>createSlug($item->product->product_name)]) }}?p={{ base64_encode($item->id) }}'"
                @if ($item->image)
            src="{{ url('public/uploads/product',$item->image) }}"
            @else
            src="https://a.eraprocure.co.in/assets/images/product-placeholder.png"
            @endif
            alt="">


        </div>
        <div class="product-items-detail">
            <p class="font-size-12 mb-0">
                {{ optional($item->product->division)->division_name }}> {{
                optional($item->product->category)->category_name}}</p>
            <h5 class="product-title pt-2 font-size-14 fw-bold">
                <a href="{{ route('webPage.productDetail',['companyName'=>session()->get('company_slug'),'productId'=>createSlug($item->product->product_name)]) }}?p={{ base64_encode($item->id) }}"
                    target="_blank" class="truncate-2-lines" title="{{
                    $item->product->product_name }}">{{
                    $item->product->product_name }}</a>
            </h5>

            <h4 class="font-size-12 mb-3">
                {!! htmlEntityDecodeWithLimit($item->description,10) !!}
            </h4>


            @if (Auth::user()->user_type==1)


            <p><button type="button" data-vendor_id="{{ session()->get('vendorId') }}"
                    data-product_id="{{ $item->product_id }}" class="btn btn-primary ra-btn-sm mt-1 generateRfqBtn"
                    id="generateRfqBtn">Add
                    RFQ</button>
            </p>

            @endif
        </div>


    </div>
</div>
@empty
<div class="col-lg-4 col-md-4 col-sm-12">
    <div class="product-items bg-white position-relative">
        <h5>Product not found.</h5>
    </div>
</div>
@endforelse


@include('web-page.product-details-js')