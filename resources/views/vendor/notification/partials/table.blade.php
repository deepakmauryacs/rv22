@php
    $i = ($notifications->currentPage() - 1) * $notifications->perPage() + 1;
    $status = [
            '1' => 'notification-bg-blue',
            '2' => 'notification-bg-pink',
            '3' => 'notification-bg-yellow',
            '4' => 'notification-bg-green',
        ];
    $key = 0;
@endphp

@forelse ($notifications as $notification)
    <div class="message-wrapper {{ $status[++$key] ?? '' }}">
        <div class="message-detail">
            <div class="message-head-line">
                <div class="person_name">
                    <span>{{ $notification->sender_name ?? '' }}</span>
                </div>
                <p class="message-body-line">{{ $notification->created_at ?? '' }}</p>
            </div>
            <div class="message-body-line">
                <a href="{{ $notification->link }}" target="_blank">{!! $notification->message ?? '' !!}
                    <i class="bi bi-eye"></i>
                </a>
                @php $key =$key==4 ? 0 : $key @endphp
            </div>
        </div>
    </div>
@empty
    <div class="text-center">
        No notification found.
    </div>
@endforelse

<x-paginationwithlength :paginator="$notifications" />
