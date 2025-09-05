<div class="message-detail-container p-3 p-sm-4 py-0 py-sm-0">
    <div class="row align-items-center justify-content-between mb-3 pt-3 sticky-top1 bg-white">
        <div class="col-auto col-sm-6">
            <h2 class="font-size-16">{{ $replyMessageText }}</h2>
        </div>
        <div class="col-auto col-sm-6">
            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="ra-btn ra-btn-primary"  wire:click='replyMessageModal()'>Reply</button>
                <button type="button" class="ra-btn ra-btn-outline-primary back-btn"
                    wire:click="replyBack()">Back</button>
            </div>

        </div>
    </div>

    {{-- <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand">
                {{ $subject }}
            </a>

            <button class="btn btn-outline-danger" type="submit" wire:click="replyBack()">Back
            </button>
        </div>
    </nav> --}}



    {{-- <div class="d-flex justify-content-between align-items-center mb-0 mt-1 position-relative">
        <input class="form-control w-100 pe-5" type="text" wire:model.live.debounce.300ms="search"
            placeholder="Search message..." id="searchInput">

        @if ($search)
            <button type="button" class="btn btn-sm btn-outline-danger position-absolute end-0 top-0 mt-0 me-0"
                style="z-index: 10; height: 33px;" wire:click="$set('search', '')">
                &times;
            </button>
        @endif
    </div> --}}

    <div wire:loading wire:target="loadMore" class="text-center py-2">
        Loading more...
    </div>
    <div wire:loading wire:target="replyListing" class="text-center py-2 text-muted">
        Checking for new messages...
    </div>


    <div class="message-details-scroll-container chat-container1" wire:poll.keep-alive id="chatMessages">
        @forelse ($listingData as $data)
            {{-- Section sender message --}}
            <div
                class="mb-3 {{ $data->sender_id == Auth::user()->id ? 'message-detail-sender' : 'message-detail-user' }}">
                <div
                    class="message-details-title align-items-center gap-3 position-relative {{ $data->sender_id == Auth::user()->id ? '' : 'justify-content-end' }}">
                    <h6
                        class="mb-0 font-size-14 fw-bold {{ $data->sender_id == Auth::user()->id ? 'ps-3' : 'pe-3 order-2' }}">
                        {{ $data->sender_name }}(@if ($data->sender_id == $data->buyer_sender_user_id)
                            {{ $data->buyer_sender_legal_name }}
                        @elseif ($data->sender_id == $data->vender_sender_user_id)
                            {{ $data->vender_sender_legal_name }}
                        @elseif ($data->sender_id == $data->buyer_receiver_user_id)
                            {{ $data->sender_id }},{{ $data->buyer_receiver_user_id }}
                        @elseif ($data->sender_id == $data->vender_receiver_user_id)
                            {{ $data->vender_receiver_legal_name }}
                        @else
                            {{ Auth::user()->name }}
                        @endif)
                    </h6>
                    <small class="font-size-11 {{ $data->sender_id == Auth::user()->id ? '' : 'order-1' }}">
                        {{ \Carbon\Carbon::parse($data->created_at)->diffForHumans() }}
                    </small>
                </div>
                <div class="message-details-content">
                    {!! html_entity_decode($data->message, ENT_QUOTES, 'UTF-8') !!}
                </div>
                <div class="message-details-attachment d-flex mt-2">
                    @if ($data->file_path)
                        <a href="{{ url('public/' . $data->file_path) }}"
                            download="{{ url('public/' . $data->file_path) }}" class="ra-btn ra-btn-outline-primary"
                            class="ra-btn ra-btn-outline-primary">
                            <span
                                class="bi font-size-22 bi-filetype-{{ pathinfo(parse_url($data->file_path, PHP_URL_PATH), PATHINFO_EXTENSION) }}""
                                aria-hidden="true"></span>
                            <span class="attachment-file-name text-truncate">
                                {{ $data->file_name }}
                            </span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Section receiver message
            <div
                class="mb-3 message {{ $data->sender_id == Auth::user()->id ? 'message-detail-user' : 'message-detail-sender' }}">
                <div class="message-details-title align-items-center justify-content-end gap-3 position-relative">
                    <h6 class="mb-0 font-size-14 fw-bold pe-3">
                        {{ $data->sender_name }}(@if ($data->sender_id == $data->buyer_sender_user_id)
                            {{ $data->buyer_sender_legal_name }}
                        @elseif ($data->sender_id == $data->vender_sender_user_id)
                            {{ $data->vender_sender_legal_name }}
                        @elseif ($data->sender_id == $data->buyer_receiver_user_id)
                            {{ $data->sender_id }},{{ $data->buyer_receiver_user_id }}
                        @elseif ($data->sender_id == $data->vender_receiver_user_id)
                            {{ $data->vender_receiver_legal_name }}
                        @else
                            {{ Auth::user()->name }}
                        @endif)
                    </h6>
                    <small class="font-size-11">
                        {{ \Carbon\Carbon::parse($data->created_at)->diffForHumans() }}
                    </small>
                </div>
                <div class="message-details-content">
                    {!! html_entity_decode($data->message, ENT_QUOTES, 'UTF-8') !!}
                </div>
                <div class="message-details-attachment d-flex mt-2">
                    @if ($data->file_path)
                        <a href="{{ url('public/' . $data->file_path) }}"
                            download="{{ url('public/' . $data->file_path) }}" class="ra-btn ra-btn-outline-primary"
                            class="ra-btn ra-btn-outline-primary">
                            <span
                                class="bi font-size-22 bi-filetype-{{ pathinfo(parse_url($data->file_path, PHP_URL_PATH), PATHINFO_EXTENSION) }}""
                                aria-hidden="true"></span>
                            <span class="attachment-file-name text-truncate">
                                {{ $data->file_name }}
                            </span>
                        </a>
                    @endif
                </div>
            </div> --}}
        @empty
            <div class="text-center text-muted py-4">
                <b> No messages found.</b>
            </div>
        @endforelse



        {{-- @if ((count($listingData) > 0 || $search) && $listing_type != 'draft')
            @if (empty($listingData[0]->sender_draft_status))
                <div class="chat-input p-3 border-top">
                    <form id="chatForm" wire:submit.prevent="replyMessage">
                        <!-- Hidden Inputs -->
                        <input type="hidden" wire:model.defer="type">
                        <input type="hidden" wire:model.defer="subject">
                        <input type="hidden" wire:model.defer="receiver_id">

                        <!-- Input Group -->
                        <div class="input-group">
                            <!-- Attachment Button -->
                            <label for="attachment" class="input-group-text bg-light" style="cursor: pointer;"
                                data-bs-toggle="tooltip" title="Attach a file">
                                <i class="fas fa-paperclip"></i>
                            </label>
                            <input wire:model="attachment" type="file" id="attachment" name="attachment"
                                class="d-none"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.docx,.pptx,.rar,.gif,.txt,.xlsx,.png" />

                            <!-- Message Textarea -->
                            <textarea wire:model.defer="message" class="form-control" id="messageInput" rows="2"
                                placeholder="Type your message..."></textarea>


                        </div>
                        <!-- Send Button -->
                        <button type="submit" class="btn btn-primary" @disabled($errors->isNotEmpty())>
                            Reply
                        </button>

                    </form>
                    <!-- Errors -->
                    @error('message')
                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                    @enderror

                    @error('attachment')
                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                    @enderror
                </div>
            @endif
        @endif --}}

    </div>


</div>
