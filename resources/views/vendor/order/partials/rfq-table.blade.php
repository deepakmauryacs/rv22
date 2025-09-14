<table class="table ra-table ra-table-stripped ">
    <thead>
        <tr>
            <th scope="col" class="text-nowrap">Order Date</th>
            <th scope="col" class="text-nowrap">Order No</th>
            <th scope="col" class="text-nowrap">BUYER ORDER NUMBER</th>
            <th scope="col" class="text-nowrap">RFQ No </th>
            <th scope="col" class="text-nowrap">Product</th>
            <th scope="col" class="text-nowrap">Buyer Name</th>
            <th scope="col" class="text-nowrap">Order Value</th>
            <th scope="col" class="text-nowrap">Status</th>
            <th scope="col" class="text-nowrap">Action</th>
            <th scope="col" class="text-nowrap">Upload PI</th>
        </tr>
    </thead>
    <tbody>
        @php
        $order_status=['1'=>'','2'=>'<span class="badge badge-danger text-start">Cancelled</span>','3'=>''];
        $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp

        @forelse ($results as $result)
        @php
            $redirect_url = $result->order_status == 1 ? route('vendor.rfq_order.show', $result->id) : 'javascript:void(0);';
            $cancelled_class = $result->order_status == 1 ? '' : 'cancelled-order';
        @endphp
        <tr>
            <td>{{ date('d/m/Y', strtotime($result->created_at)) }}</td>
            <td>
                <a href="{{ $redirect_url }}" class="{{$cancelled_class}}">
                {{ $result->po_number ?? '-' }}
                </a>
            </td>
            <td>{{ $result->buyer_order_number }}</td>
            <td>{{ $result->rfq_id ?? '-' }}</td>
            <td>{{ $result->order_variants->pluck('product.product_name')->filter()->unique()->join(', ') ?? '-' }}</td>
            <td>{{ $result->buyer->legal_name ?? '-' }}</td>
            <td>{{ $result->vendor_currency ?? '₹' }}{{ $result->order_total_amount }}</td>
            <td>{!! $order_status[$result->order_status] !!}</td>
            <td>
                <a class="ra-btn ra-btn-outline-primary-light height-inherit btn-sm {{$cancelled_class}}" href="{{ $redirect_url }}"><span><i class="bi bi-eye"></i></span></a>
            </td>
            <td>
                @if($result->order_status == 1)
                    @php 
                        $piData = orderPi($result->po_number, getParentUserId());
                    @endphp
                    <div class="custom-file">
                        <div class="file-browse">
                            <span class="button button-browse">
                                <input onchange="validatePIFile(this)" type="file" name="pi_attachment" value=""
                                    class="form-control pi-attachment-field" data-order-number="{{ $result->po_number ?? '-' }}">
                            </span>
                        </div>
                        <div class="pi-file-name-div">
                            @if(!empty($piData))
                            <span class="pi-file-name" title="{{$piData->pi_attachment}}">                                
                                <a class="btn-sm btn-rfq-secondary" href="{{asset('public/uploads/pi-order/'.$piData->pi_attachment)}}" download="PI Invoice for {{ $result->po_number ?? '-' }}">
                                    {!!
                                        strlen($piData->pi_attachment) > 11
                                        ? substr($piData->pi_attachment, 0, 11).'...'
                                        : $piData->pi_attachment
                                    !!}
                                </a>
                            </span>
                            <span class="remove-pi-file btn-rfq btn-rfq-sm"><i class="bi bi-trash text-danger"></i></span>
                            @else
                            <span class="pi-file-name d-none" title="">    
                            @endif
                        </div>
                    </div>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center">No Data Available in Table</td>
        </tr>
        @endforelse
    </tbody>
</table>

<x-paginationwithlength :paginator="$results" />
