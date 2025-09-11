 <div class="message_wrap height-inherit">
    @php
        $i = ($notifications->currentPage() - 1) * $notifications->perPage() + 1;
        $status = [
            '1' => 'notification-bg-blue',
            '2' => 'notification-bg-pink',
            '3' => 'notification-bg-yellow',
            '4' => 'notification-bg-green',
        ];
        $key=0;
    @endphp
    @forelse ($notifications as  $notification)
    <div class="message-wrapper {{ $status[++$key] ?? '' }}">
        <div class="message-detail">
            <div class="message-head-line">
                <div class="person_name">
                    <span>{{ $notification->sender_name ?? '' }}</span>
                </div>
                <p class="message-body-line">
                    {{ $notification->created_at ?? '' }}
                </p>
            </div>
            <p class="message-body-line">
                <a href="{{$notification->link}}" target="_blank" rel="noopener noreferrer">
                   {!! $notification->message ?? '' !!}
                   @php $key =$key==4 ? 0 : $key @endphp
                   <i class="bi bi-eye"></i>
                </a>
            </p>
        </div>
    </div>
    @empty
    <div class="message-wrapper notification-bg-blue">
        <div class="message-detail">
            <div class="message-head-line">
                <div class="person_name">
                    <span>Notification</span>
                </div>
                <p class="message-body-line">
                    No Notification
                </p>
            </div>
        </div>
    </div>
    @endforelse
    <!-- <div class="message-wrapper notification-bg-pink">
        <div class="message-detail">
            <div class="message-head-line">
                <div class="person_name">
                    <span>A KUMAR</span>
                </div>
                <p class="message-body-line">
                    26 Mar, 2025 05:12 PM
                </p>
            </div>
            <p class="message-body-line">
                <a href="http://" target="_blank" rel="noopener noreferrer">
                    'A KUMAR' has responded to your RFQ No.
                    RATB-25-00046. You can check their quote here <i class="bi bi-eye"></i>
                </a>
            </p>
        </div>
    </div>
    <div class="message-wrapper notification-bg-yellow">
        <div class="message-detail">
            <div class="message-head-line">
                <div class="person_name">
                    <span>TEST AMIT VENDOR</span>
                </div>
                <p class="message-body-line">
                    26 Mar, 2025 04:35 PM
                </p>
            </div>
            <p class="message-body-line">
                <a href="http://" target="_blank" rel="noopener noreferrer">
                    'TEST AMIT VENDOR' has responded to your RFQ No.
                    RATB-25-00046. You can check their quote here <i class="bi bi-eye"></i>
                </a>
            </p>
        </div>
    </div>
    <div class="message-wrapper notification-bg-green">
        <div class="message-detail">
            <div class="message-head-line">
                <div class="person_name">
                    <span>A KUMAR</span>
                </div>
                <p class="message-body-line">
                    26 Mar, 2025 04:35 PM
                </p>
            </div>
            <p class="message-body-line">
                <a href="http://" target="_blank">
                    'A KUMAR' has responded to your RFQ No.
                    RATB-25-00046. You can check their quote here <i
                        class="bi bi-eye"></i></a>
            </p>
        </div>
    </div> -->
</div>

<x-paginationwithlength :paginator="$notifications" />

