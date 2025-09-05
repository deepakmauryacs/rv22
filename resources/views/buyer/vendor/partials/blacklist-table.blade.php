<table class="product-listing-table w-100">
    <thead>
        <tr>
            <th>Sr.No.</th>
            <th>Vendor Name</th>
            <th>Product Name</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @php
            $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp
        @forelse ($results as $key => $result)
            <tr>
                <td>{{ ++$key}}</td>
                <td>{{ $result->user->vendor->legal_name??''}}</td>
                <td>
                    @php
                    $productNames = optional($result->user->vendor)
                            ->vendor_products
                            ->pluck('product.product_name')
                            ->filter()
                            ->join(', ');
                    @endphp
                    {{ $productNames ?: '-' }}
                </td>
                <td>
                    <button onclick="deleteFavourite(`{{ route('buyer.vendor.deleted', $result->id) }}`);" class="ra-btn ra-btn-outline-danger">DELETE</button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4"> 
                    <div class="alert alert-warning text-center font-size-13 border-0">
                       You haven't Blacklisted any Vendor.
                    </div>
                </td>
            </tr>
        @endforelse

    </tbody>
</table>
<x-paginationwithlength :paginator="$results" />
 