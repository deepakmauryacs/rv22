<div class="table-responsive">
    <table class="product_listing_table">
    <thead>
        <tr>
            <th style="width: 2%;"><input type="checkbox" id="select-all-products"></th>
            <th style="width: 2%;">#</th>
            <th>Vendor Name</th>
            <th>Product Name</th>
            <th>Delete</th>
            <th>Status</th>
            <th>Added By Vendor</th>
            <th>Action</th>
          
        </tr>
    </thead>
    <tbody>
        @php
            $i = ($products->currentPage() - 1) * $products->perPage() + 1;
        @endphp

        @forelse ($products as $product)
            <tr>
                <td>
                    <input type="checkbox" class="product-checkbox" value="{{ $product->id }}" />
                </td>
                <td>{{ $i++ }}</td> {{-- S.No. --}}
                <td>{{ $product->vendor_legal_name ?? '-' }}</td>
                <td>{{ $product->product_name ?? '' }}</td>
                <td>
                    <button class="btn-style btn-style-danger btn-delete-product" data-id="{{ $product->id }}">Delete</button>
                </td>
                <td>
                    <span>
                        <label class="switch">
                            <input type="checkbox" class="product-status-toggle"
                                   data-id="{{ $product->id }}"
                                   {{ $product->vendor_status == '1' ? 'checked' : '' }} />
                            <span class="slider round"></span>
                        </label>
                    </span>
               </td>

                <td>{{ $product->created_by_vendor ? 'Yes' : 'No' }}</td>
                <td>
                    <a href="{{ route('admin.verified-products.view', $product->id) }}" class="btn-style btn-style-secondary">View</a>
                    <a href="{{ route('admin.verified-products.edit', $product->id) }}" class="btn-style btn-style-secondary">Edit</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">No data available in table</td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>
<x-paginationwithlength :paginator="$products" />

    