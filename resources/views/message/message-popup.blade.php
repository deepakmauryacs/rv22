{{--
<!-- Modal Compose Mail -->
<div class="modal fade" id="composeMailModal" tabindex="-1" aria-labelledby="composeMailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content"> --}}
            <div class="modal-header bg-graident text-white justify-content-between">
                <h2 class="modal-title font-size-12" id="composeMailModalLabel"><span class="bi bi-pencil"
                        aria-hidden="true"></span> New Message</h2>
                <button type="button" class="modal-close-btn width-inherit bg-transparent border-0"
                    data-bs-dismiss="modal" aria-label="Close">
                    <span class="bi bi-x-circle font-size-20 text-shadow-light"></span>
                </button>
            </div>
            <div class="modal-body">

                <!-- <div class="message pt-3">
                            <select class="form-select" name="">
                                <option value="" selected="">Select User</option>
                                <option value="756">RONIT ROY</option>
                                <option value="763">4R131312313</option>
                            </select>
                        </div> -->
                {{-- {{ dd($data['receiverId'], optional($data['receiverIdInfo']->vendor)->legal_name) }} --}}


                <input type="hidden" name="senderId" value="{{ $data['senderId'] }}" />
                <input type="hidden" name="receiverId" value="{{ $data['receiverId'] }}" />


                @if ($data['productId'])

                <input type="hidden" name="productId" value="{{ $data['productId'] }}" />

                <div class="message pt-3">

                    <!--:- buyer -:-->
                    @if ($data['receiverIdInfo']->user_type==1)
                    <input type="text" readonly value="{{optional($data['receiverIdInfo']->buyer)->legal_name  }}"
                        class="form-control" placeholder="vendor name" />
                    @elseif ($data['receiverIdInfo']->user_type==2)
                    <!--:- vendor -:-->
                    <input type="text" readonly value="{{optional($data['receiverIdInfo']->vendor)->legal_name  }}"
                        class="form-control" placeholder="vendor name" />
                    @endif

                </div>
                @endif



                <div class="message pt-3">
                    <input type="text" readonly value="{{ $data['subject'] }}" class="form-control"
                        placeholder="Subject">
                </div>

                <section class="ck-editor-section pt-3 pb-2">
                    <textarea name="" id="msg" rows="6" class="form-control height-inherit"
                        placeholder="Type your message here...">{{ $data['message'] }}</textarea>
                </section>



                <section class="upload-file py-2">
                    <div class="file-upload-block justify-content-start">
                        <div class="file-upload-wrapper">
                            <input type="file" class="file-upload" style="display: none;">
                            <button type="button" title="No file chosen"
                                class="custom-file-trigger form-control text-start text-dark font-size-13">Upload
                                file</button>
                        </div>
                        <div class="file-info" style="display: none;"></div>
                    </div>
                    <div class="text-danger-orange" style="display:none;">
                        Invalid file extension. Please upload a valid file (PDF, PNG, JPG, JPEG, DOCX, DOC, XLS,
                        CSV).
                    </div>
                </section>

            </div>
            <div class="modal-footer">
                <button type="button" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11"
                    id="sendMessageBtn" data-url="/message/store-message-data">Send</button>
            </div>
            {{--
        </div>
    </div>
</div> --}}

<style>
    .ck-editor__editable_inline {
        min-height: 180px !important;
    }
</style>