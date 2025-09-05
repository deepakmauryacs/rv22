{{-- <table class="table table-bordered"> 
    <thead>
        <tr>
            <th>#</th>
            <th>Sender Name</th>
            <th>Date</th>
            <th>Message</th>
            <th>Action</th>
          
        </tr>
    </thead>
    <tbody>
        @php
            $i = ($notifications->currentPage() - 1) * $notifications->perPage() + 1;
        @endphp

        @forelse ($notifications as $notification)
            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ $notification->sender_name ?? '' }}</td>
                <td>{{ $notification->created_at ?? '' }}</td>
                <td>{!! $notification->message ?? '' !!}</td>
                <td>
                    <a href="{{$notification->link}}" class="btn btn-sm btn-info">View</a> 
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">No notification found.</td>
            </tr>
        @endforelse

    </tbody>
</table>--}}
@php
            $i = ($notifications->currentPage() - 1) * $notifications->perPage() + 1;
        @endphp

        @forelse ($notifications as $notification)
            <div class="message-wrapper Nblue">
            <div class="message-detail">
                <div class="message-head-line">
                    <div class="person_name">
                        <span>{{ $notification->sender_name ?? '' }}</span>
                    </div>
                    <p class="message-body-line">{{ $notification->created_at ?? '' }}</p>
                </div>
                <div class="message-body-line">
                    <a href="{{$notification->link}}" target="_blank">{!! $notification->message ?? '' !!} <i
                                                    class="bi bi-eye"></i></a>
                </div>
            </div>
        </div>
        @empty
            <div class="text-center">
                No notification found.
            </div>
        @endforelse
        
<x-paginationwithlength :paginator="$notifications" />

    