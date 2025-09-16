{{-- <div class="d-flex justify-content-between align-items-center mb-1">
    <input class="form-control w-100" type="text" wire:model.live.debounce.300ms="search"
        placeholder="Search by name or subject ...">
</div> --}}


<div class="d-flex justify-content-between align-items-center border-bottom mb-2 position-relative">
    <input class="form-control px-3 border-0 rounded-0 shadow-none  bg-transparent w-100 font-size-13" type="text"
        wire:model.live.debounce.300ms="search" placeholder="Search By name or subject ..." id="searchInput">

    @if ($search)
    <button type="button" class="btn btn-sm btn-link text-danger position-absolute end-0 top-0 mt-1 me-0 z-1"
        wire:click="$set('search', '')">
        <span class="bi bi-x-circle font-size-14"></span>
    </button>
    @endif
</div>

<div class="card-body-message p-0">
    <div class="message-list-container bg-white rounded">
        <ul id="inboxTable" class="position-relative" wire:poll.7s>
            <li class="chat-list d-flex justify-content-between align-items-center bg-white sticky-top z-1">
                <label class="ra-custom-checkbox">
                    <input type="checkbox" id="selectAll" wire:model="selectAll">
                    <span class="font-size-13">All</span>
                    <span class="checkmark check-input"></span>
                </label>
                <div class="d-flex gap-2 drp-icon">
                    <i class="bi bi-star font-size-16" aria-hidden="true"
                        style="cursor:pointer; color: {{ $selectAllFavourite ? 'yellow' : 'gray' }};"
                        wire:click="toggleAllFavourite"></i>


                    <div class="dropdown dropstart dropbottom ">
                        <div class="dropdown dropstart dropbottom d-inline-block">
                            <button class="ra-btn ra-btn-link p-0 width-inherit bg-white" href="#" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="bi bi-three-dots-vertical font-size-18" aria-hidden="true"></span>
                            </button>
                            <ul class="dropdown-menu message-settings">
                                <li><a class="dropdown-item" wire:click="markAsRead('read')"><span
                                            class="bi bi-book me-2"></span>Mark
                                        as
                                        Read</a></li>
                                <li><a class="dropdown-item" wire:click="markAsRead('unread')"><span
                                            class="bi-journal-richtext me-2"></span>
                                        Mark as
                                        unread</a>
                                </li>
                                <li><a class="dropdown-item"
                                        onclick="if(confirm('Are you sure you want to delete this item?')) { @this.deleteSelected() }"><span
                                            class="bi bi-trash me-2"></span>
                                        Delete</a>
                                </li>
                            </ul>
                        </div>
                        {{-- <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" wire:click="markAsRead('read')">
                                    <span class="fa fa-book-open-reader text-info"></span>
                                    Mark Selected as
                                    Read</a>
                            </li>
                            <li>

                                <a class="dropdown-item" wire:click="markAsRead('unread')">
                                    <span class="fa fa-book text-warning"></span>
                                    Mark Selected as
                                    Unread</a>
                            </li>
                            <li>

                                <a class="dropdown-item"
                                    onclick="if(confirm('Are you sure you want to delete this item?')) { @this.deleteSelected() }">
                                    <span class="fa
                                        fa-trash text-danger"></span>
                                    Delete
                                    Selected</a>
                            </li>
                        </ul> --}}
                    </div>

                </div>
            </li>
            @forelse ($listingData as $key=>$data)
            <li class="chat-list " @if (Auth::user()->id == $data->sender_id && $data->sender_send_status == '2')
                style=" font-weight: 600;"
                @elseif (Auth::user()->id == $data->receiver_id && $data->receiver_inbox_status == '2')
                style="font-weight: 600;" @else @endif>
                <div class="chat-list-container">
                    <div class="row gx-2 align-items-center position-relative">
                        {{-- Section Checkbox --}}
                        <div class="col-auto d-flex align-self-start">
                            <div class="">
                                <label class="ra-custom-checkbox" style="display: flex">
                                    <input type="checkbox" class="row-checkbox" value='@json($data)'>
                                    <span class="checkmark check-input"></span>
                                </label>
                            </div>
                        </div>

                        {{-- Section Favorite and Sender Name --}}
                        <div class="col-auto col-sm-3 col-lg-4 col-xl-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="message-fav">
                                    @if ($data->is_fav)
                                    {{-- <button type="button"
                                        class="btn btn-link align-items-center text-dark px-0 py-0"
                                        wire:click="toggleFavourite({{ $data->parent_id }})">
                                        <span class="bi bi-star favourite" aria-hidden="true"></span>
                                    </button> --}}
                                    <div class="message-fav">
                                        <button type="button"
                                            class="btn btn-link align-items-center text-dark px-0 py-0"
                                            wire:click="toggleFavourite({{ $data->parent_id }}, {{ $data->is_fav ? 0 : 1 }})">
                                            <span class="bi bi-star{{ $data->is_fav ? ' favourite' : '' }}"
                                                aria-hidden="true"></span>
                                        </button>
                                    </div>
                                    @else
                                    <button type="button" class="btn btn-link align-items-center text-dark px-0 py-0"
                                        wire:click="toggleFavourite({{ $data->parent_id }},{{ $data->is_fav ? 0 : 1 }})">
                                        <span class="bi bi-star" aria-hidden="true"></span>
                                    </button>
                                    @endif

                                </div>

                                <div class="message-sender font-size-13 text-truncate"
                                    wire:click="replyListStatusType({{ $data->id }})">
                                    @if ($listing_type == 'inbox')
                                    {{ $data->sender_name }}
                                    @elseif ($listing_type == 'send' || $listing_type == 'draft')
                                    {{ $data->receiver_name }}
                                    @elseif ($listing_type == 'trash')
                                    @if (Auth::user()->id == $data->sender_id)
                                    {{ $data->sender_name }}
                                    @else
                                    {{ $data->receiver_name }}
                                    @endif
                                    @endif

                                </div>


                            </div>
                        </div>

                        {{-- Section Subject --}}
                        <div class="col-12 col-sm-3 col-lg-3 col-xl-3">
                            <div class="message-subject font-size-13 text-truncate"
                                wire:click="replyListStatusType({{ $data->id }})">
                                {{ $data->subject }}
                            </div>
                        </div>

                        {{-- Section Date and Time --}}
                        <div class="col-12 col-sm-5 col-lg-4 col-xl-4 ms-auto">

                            <div class="message-time-edit d-flex justify-content-md-end text-nowrap">
                                <span class="message-time" wire:click="replyListStatusType({{ $data->id }})">
                                    @if ($listing_type == 'trash')
                                    <span class="dotted bg-success me-2"></span>
                                    <span class="font-size-12">
                                        {{ \Carbon\Carbon::parse($data->updated_at)->format('d M Y \a\t g:i A') }}
                                    </span>
                                    @else
                                    <span class="dotted bg-danger me-2"></span>
                                    <span class="font-size-12">
                                        {{ \Carbon\Carbon::parse($data->created_at)->format('d M Y \a\t g:i A') }}
                                    </span>
                                    @endif

                                </span>

                                {{-- Section Message dropdown --}}
                                <div class="message-dropdown dropdown dropstart dropbottom drp-icon">
                                    <button class="ra-btn ra-btn-link ms-1 p-0 width-inherit bg-white" href="#"
                                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="bi bi-three-dots-vertical font-size-18 ms-3"
                                            aria-hidden="true"></span>
                                    </button>


                                    <ul class="dropdown-menu message-settings">

                                        <li>
                                            @if (Auth::user()->id == $data->sender_id && $data->sender_draft_status ==
                                            null)
                                            @if ($data->sender_send_status == 1)
                                            <a class="dropdown-item"
                                                wire:click="readSelectedOne('unread',{{ $data->message_statuses_id }},{{ $data->message_id }}, {{ $data->sender_id }}, {{ $data->receiver_id }})">
                                                <span class="bi-journal-richtext me-2"></span> Mark
                                                Unread</a>
                                            @elseif($data->sender_send_status == 2)
                                            <a class="dropdown-item"
                                                wire:click="readSelectedOne('read',{{ $data->message_statuses_id }},{{ $data->message_id }}, {{ $data->sender_id }}, {{ $data->receiver_id }})">
                                                <span class="bi bi-book me-2"></span> Mark
                                                Read</a>
                                            @endif
                                            @elseif (Auth::user()->id == $data->receiver_id)
                                            @if ($data->receiver_inbox_status == 1)
                                            <a class="dropdown-item"
                                                wire:click="readSelectedOne('unread',{{ $data->message_statuses_id }},{{ $data->message_id }}, {{ $data->sender_id }}, {{ $data->receiver_id }})">
                                                <span class="bi bi-book me-2"></span> Mark
                                                Unread</a>
                                            @elseif($data->receiver_inbox_status == 2)
                                            <a class="dropdown-item"
                                                wire:click="readSelectedOne('read',{{ $data->message_statuses_id }},{{ $data->message_id }}, {{ $data->sender_id }}, {{ $data->receiver_id }})">
                                                <span class="bi-journal-richtext me-2"></span> Mark
                                                Read</a>
                                            @endif
                                            @endif


                                        </li>

                                        <li>
                                            <a class="dropdown-item"
                                                onclick="if(confirm('Are you sure you want to delete this message.?')) { @this.deleteSelectedOne({{ $data->parent_id }},{{ $data->sender_id }}, {{ $data->receiver_id }}) }"
                                                {{--
                                                wire:click="deleteSelectedOne({{ $data->parent_id }},{{ $data->sender_id }}, {{ $data->receiver_id }})"
                                                --}}>
                                                <span class="bi bi-trash me-2"></span> Delete Selected
                                            </a>
                                        </li>
                                        @if ($data->sender_draft_status > 0)
                                        <li><a class="dropdown-item" wire:click="editDraftData({{ $data->id }})"><i
                                                    class="text-success fa fa-pencil-alt" aria-hidden="true"></i>
                                                Edit
                                            </a></li>
                                        @endif

                                    </ul>
                                </div>
                            </div>
                        </div>




                    </div>
                </div>
            </li>
            @empty
            <li class="chat-list d-flex justify-content-center align-items-center fw-bold font-size-13">
                No message found.
            </li>
            @endforelse
        </ul>

    </div>
</div>

{{-- <div class="px-3 py-2 bg-white sticky-bottom">
    {{ $listingData->links('livewire::bootstrap') }}
</div> --}}
<script>
    window.addEventListener('reset-row-checkboxes', function () {
        document.querySelectorAll('.row-checkbox').forEach(function (checkbox) {
            checkbox.checked = false;
        });
        document.getElementById('selectAll').checked = false;
    });
</script>
