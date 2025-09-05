@if ($indent->rfq_quantity_sum)
{{ optional($indent->getProduct)->product_name }}
@else
<form action="{{ route('buyer.apiIndent.update') }}" method="post">
    @csrf
    <input type="hidden" name="indent_id" value="{{ $indent->id }}" />
    <div class="product-block">
        @if (optional($indent->getProduct)->product_name)
        <span class="text-primary-blue text-decoration-underline cursor-pointer toggle-trigger">
            {{ optional($indent->getProduct)->product_name }}
        </span>
        @endif
        <div class="row align-items-center justify-content-center
            {{ optional($indent->getProduct)->product_name ? 'd-none' : 'd-block' }} form-container">
            <div class="col-12">
                <span class="division-category text-start"></span>
                <div class="d-flex align-items-start justify-content-center">
                    <div class="position-relative w-150">
                        <input type="text" class="form-control bg-white product-name" name="product_name"
                            autocomplete="off">
                        <input type="hidden" class="form-control bg-white product-id" name="product_id" maxlength="20">
                        <div class="list-group w-100 position-relative product-suggestions" id="productSuggestions"
                            style="display: none;"></div>
                    </div>
                    <button
                        class="ra-btn ra-btn-link width-inherit p-0 ms-2 mt-2 bg-transparent text-danger delete-trigger1 d-none">
                        <span class="bi bi-trash font-size-20"></span>
                    </button>
                </div>
            </div>
            <div class="col-12 mt-2">
                <button type="submit"
                    class="ra-btn ra-btn-primary d-inline-block1 font-size-11 d-none update-product-button">
                    <span class="bi bi-save font-size-11" aria-hidden="true"></span> Save
                </button>
            </div>
        </div>
    </div>
</form>
@endif