<!-- Compose Modal -->
<div wire:ignore.self class="modal fade" id="composeModal" tabindex="-1" aria-labelledby="composeModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" class="mt-6">
    <div class="modal-dialog modal-dialog-centered modal-md ">
        <form @if ($listing_type=='draft' ) wire:submit.prevent="updateDraftData" @else
            wire:submit.prevent="composeMessage" @endif class="modal-content">
            <div class="modal-header bg-graident text-white justify-content-between">
                <h2 class="modal-title font-size-12" id="composeModalLabel">
                    <span class="bi bi-pencil" aria-hidden="true"></span>
                    Send New Message
                </h2>
                <button type="button" class="modal-close-btn width-inherit bg-transparent border-0"
                    data-bs-dismiss="modal" aria-label="Close" id="composeCloseBtn">
                    <span class="bi bi-x-circle font-size-20 text-shadow-light"></span>
                </button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" wire:model.live="subject" id="subject" class="form-control" placeholder="Subject"
                        @disabled($replyMessageText)>
                    @error('subject')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3" wire:ignore>
                    <textarea wire:model.live="message" id="msg" class="form-control height-inherit"
                        placeholder="Type your message here..." rows="4"></textarea>
                    @error('message')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <input type="file" wire:model="attachment" class="form-control"
                        accept=".pdf, .doc, .docx, .jpg, .jpeg, .pptx, .rar, .xlsx, .png,.txt, .gif" />
                    @error('attachment')
                    <small class="text-danger-orange">{{ $message }}</small>
                    @enderror

                    @if ($uploadedFilePath != '')
                    <div class="mt-2">
                        <a href="{{ url('public/' . $uploadedFilePath) }}"
                            download="{{ url('public/' . $uploadedFilePath) }}" class="download-link">
                            <i
                                class="fa fa-2x bi bi-filetype-{{ pathinfo(parse_url($uploadedFilePath, PHP_URL_PATH), PATHINFO_EXTENSION) }}"></i>
                            {{ $uploadedFilePath }}
                        </a>

                    </div>
                    @endif
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" @disabled($errors->isNotEmpty())
                    class="ra-btn ra-btn-primary text-uppercase text-nowrap font-size-11">{{ $draftEditMode ? 'Send' :
                    'Send' }}</button>
                <button type="button" class="ra-btn ra-btn-outline-danger text-uppercase text-nowrap font-size-11"
                    id="composeCancelBtn">Cancel</button>
            </div>
        </form>
    </div>
</div>
