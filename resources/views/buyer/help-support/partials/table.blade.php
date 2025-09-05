<table class="product-listing-table w-100">
    <thead>
        <tr>
           <th>S.No</th>
            <th>REQUEST ID</th>
            <th>DATE</th>
            <th>ISSUE TYPE</th>
            <th>DESCRIPTION</th>
            <th>STATUS</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @php
        $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp

        @forelse ($results as $result)
        <tr>
            <td>{{ $i++ }}</td>
            <td>
                <span class="cursor-pointer" onclick="viewrequest({{$result->id}},{{$result->created_by}});" >
                    {{ $result->request_id ?? '-' }}
                </span>
            </td>
            <td>{{ date('d/m/Y', strtotime($result->created_at)) }}</td>
            <td>{{ $result->issue_type}}</td>
            @php 
            if(strlen($result->description) > 20) {
                $description= substr($result->description, 0, 20).'<i title="'.$result->description.'" data-bs-toggle="tooltip" class="bi bi-info-circle font-size-12" aria-hidden="true"></i>';
            } else {
                $description=  $result->description;
            }
            @endphp
            <td>{!! $description !!}</td>
            @php
            $status=$result->getStatus($result->status);
            @endphp
            <td>
                <span class="badge {{$status['class']}}">{{$status['status']}}</span>
            </td>
            <td>
                <a href="javascript:void(0);" onclick="viewrequest({{$result->id}},{{$result->created_by}});"  class="ra-btn ra-btn-outline-primary-light width-inherit d-inline-block py-1 px-2">
                    <span><i class="bi bi-eye font-size-12"></i></span>
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center">No Data Available in Table</td>
        </tr>
        @endforelse
    </tbody>
</table>

<x-paginationwithlength :paginator="$results" />