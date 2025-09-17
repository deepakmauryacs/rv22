@php
    $status = [1 => 'Approved', 2 => 'Query Raised', 3 => 'Re-submitted', 4 => 'Product for Approval'];
@endphp
<div class="table-responsive">
    <table class="product_listing_table">
    <thead>
        <tr>
            <th>#</th>
            <th>Vendor Name</th>
            <th>Received From</th>
            <th>Product Name</th>
            <th>Request Received Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @php
            $i = ($products->currentPage() - 1) * $products->perPage() + 1;
        @endphp

        @forelse ($products as $product)
            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ optional($product->vendor_profile)->legal_name ?? '-' }}</td>
                <td>{{ $product->receivedfrom->name ?? '-' }}</td>
                <td>{{ $product->product->product_name ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($product->created_at)->format('d/m/Y') }}</td>
                <td>{{ $status[$product->approval_status] ?? '-' }}</td>
                <td>
                    <a href="{{ route('admin.product-approvals.approval', $product->id) }}" class="btn-rfq btn-rfq-secondary btn-sm">Approval</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No data available in table</td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>

<x-paginationwithlength :paginator="$products" />
