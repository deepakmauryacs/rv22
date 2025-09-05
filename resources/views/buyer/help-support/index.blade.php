@extends('buyer.layouts.app',['title'=>'Help and Support','sub_title'=>''])
@section('css')

@endsection

@section('content')
<div class="bg-white">
    <!---Sidebar-->
    @include('buyer.layouts.sidebar-menu')
</div>

<main class="main flex-grow-1">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h1 class="font-size-18 mb-0">Support</h1>
                <div>
                    <a href="{{ route('buyer.help_support.create') }}"
                        class="ra-btn small-btn ra-btn-primary d-inline-flex width-inherit my-1">Write to Us</a>
                    <a href="{{ asset('uploads/FAQ-Vendors.pdf') }}" target="_blank"
                        class="ra-btn small-btn ra-btn-outline-primary d-inline-flex width-inherit my-1">FAQ</a>
                </div>
            </div>

            <div class="card-body">
                <form id="searchForm" action="{{ route('buyer.help_support.index') }}" method="GET"></form>
                <div class="table-responsive p-2">
                    @include('buyer.help-support.partials.table', ['results' => $results])
                </div>
            </div>
        </div>
    </div>
</main>


<!-- Modal View Request -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" aria-labelledby="viewRequestModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-14" id="viewRequestModalLabel">View Request</h2>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="request-details">
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>REQUEST ID:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="request_id">RAP00061</span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>ISSUE TYPE:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="issue_type">Bulk RFQ Issue</span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>STATUS:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="status">Working</span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>UPLOAD FILE:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="uploadfile"><a download="" href="">1745388438759bulk-rfq.xlsx</a></span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>DESCRIPTION:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="discription">i am getting an issue in this products
                            </span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>RAPROCURE ATTACHMENT:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="attachment"></span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>RAPROCURE RESPONSE:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="raprocure_response"></span>
                        </div>
                    </div>
                </div>


                <div class="inline-chat-details p-3 p-sm-4 py-0 py-sm-0 position-relative">
                    <div class="text-end bg-white pb-3 sticky-top">
                        <button type="button"
                            class="ra-btn btn-primary ra-btn-primary d-inline-block text-uppercase text-nowrap font-size-11"
                            data-bs-toggle="modal" data-bs-target="#internalChatModal">Send New Message</button>
                    </div>

                    <div class="mb-3 message-detail-sender">
                        <div class="message-details-title align-items-center gap-3 position-relative ">
                            <h6 class="mb-0 font-size-14 fw-bold ps-3">
                                Raprocure Support ( Raprocure )
                            </h6>
                            <small class="font-size-11">
                                Apr 23 at 12:04 PM
                            </small>
                        </div>
                        <div class="message-details-content">
                            <p>698498498494948<a href=""> Reports-> RFQ Received RFQ Received> Dashboard ->
                                    Reports-> RFQ Received RFQ Received</a></p>
                            <p><a href="www.google.com">www.google.com</a></p>
                            <p>wcfwe</p>
                            <p>we</p>

                        </div>
                        <div class="message-details-attachment d-flex mt-2">
                            <a href="" download="" class="ra-btn ra-btn-outline-primary">
                                <span class="bi font-size-22 bi-filetype-xlsx" "="" aria-hidden=" true"></span>
                                <span class="attachment-file-name text-truncate">
                                    1752736817.xlsx
                                </span>
                            </a>
                        </div>
                    </div>

                    <div class="mb-3 message-detail-user">
                        <div
                            class="message-details-title align-items-center gap-3 position-relative justify-content-end">
                            <h6 class="mb-0 font-size-14 fw-bold pe-3 order-2">
                                Raprocure Support(AMIT BUYER TEST)
                            </h6>
                            <small class="font-size-11 order-1">
                                1 week ago
                            </small>
                        </div>
                        <div class="message-details-content">
                            <ol>
                                <li>solve my issue</li>
                                <li>issue solve karo</li>
                                <li>solve ni horha hai issue</li>
                                <li>d</li>
                                <li>e</li>
                                <li>e</li>
                                <li>e</li>
                                <li>ee</li>
                                <li>e</li>
                                <li>e</li>
                                <li>eee</li>
                                <li>e</li>
                            </ol>
                            <p>e</p>
                            <p>e</p>
                            <p>ee</p>
                            <p>e</p>
                            <p>e</p>
                            <p>e</p>
                            <p>e</p>
                            <p>e</p>
                            <p>e</p>
                            <p>e</p>
                            <p>e</p>
                            <p>ee</p>
                            <p>&nbsp;</p>
                            <p>ee</p>
                            <p>e</p>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                        </div>
                        <div class="message-details-attachment d-flex mt-2">
                            <a href="" download="" class="ra-btn ra-btn-outline-primary">
                                <span class="bi font-size-22 bi-filetype-xlsx" "="" aria-hidden=" true"></span>
                                <span class="attachment-file-name text-truncate">
                                    1752736817.xlsx
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button"
                    class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11">Submit</button>
                <button type="button"
                    class="ra-btn btn-outline-primary ra-btn-outline-danger text-uppercase text-nowrap font-size-11"
                    data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Internal Message -->
<div class="modal fade" id="internalChatModal" tabindex="-1" aria-labelledby="internalChatModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h1 class="modal-title font-size-12" id="internalChatModalLabel"><span class="bi bi-pencil"
                        aria-hidden="true"></span> New Message</h1>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="message pt-3">
                    <span class="form-control" readonly>Subject : RAP00061/Bulk RFQ Issue</span>
                </div>
                <div class="ck-editor-section py-2">
                    This is the placeholder of the editor.
                    <textarea name="" id="" rows="5" class="form-control height-inherit"></textarea>

                </div>
                <section class="upload-file py-2">
                    <div class="file-upload-block justify-content-start">
                        <div class="file-upload-wrapper">
                            <input type="file" class="file-upload" style="display: none;">
                            <button type="button"
                                class="custom-file-trigger form-control text-start text-dark font-size-11">Upload
                                file</button>
                        </div>
                        <div class="file-info" style="display: none;"></div>
                    </div>
                </section>

            </div>
            <div class="modal-footer justify-content-center">
                <button type="button"
                    class="ra-btn btn-primary ra-btn-primary d-inline-flex text-uppercase text-nowrap font-size-11">Send</button>
            </div>
        </div>
    </div>
</div>

@endsection


@section('scripts')
<script>
$(document).ready(function() {
    $(document).on('submit', '#searchForm', function(e) {
        e.preventDefault();
        loadTable($(this).attr('action') + '?' + $(this).serialize());
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        loadTable($(this).attr('href'));
    });

    $(document).on('change', '#perPage', function() {
        const form = $('#searchForm');
        const formData = form.serialize();
        const perPage = $(this).val();
        const url = form.attr('action') + '?' + formData + '&per_page=' + perPage;
        loadTable(url);
    });

    function loadTable(url) {
        $.ajax({
            url: url,
            type: 'GET',
            beforeSend: function() {
                $('#table-container').html('<div class="text-center py-4">Loading...</div>');
            },
            success: function(response) {
                $('#table-container').html(response);
                if (history.pushState) {
                    history.pushState(null, null, url);
                }
            }
        });
    }
});


function viewrequest(id, userid) {
    $.ajax({
        url: "{{ route('buyer.help_support.view') }}",
        type: 'POST',
        data: {
            id: id,
            userid: userid,
            _token: "{{ csrf_token() }}"
        },
        dataType: 'JSON',
        success: function(response) {
            if (response.status == true) {
                var responseData = response.data;
                var userData = response.user;
                var btn_disable = 'disabled';
                if (responseData.status == 3) {
                    $('.sendNewMessage').attr(btn_disable, true);
                } else {
                    $('.sendNewMessage').removeAttr(btn_disable, true);
                }
                var subject = 'Subject : ' + responseData.request_id + '/' + responseData.issue_type;

                $('.all_reply_message').html('');
                $('.requestId').val(responseData.request_id);
                $('.request_id').html(responseData.request_id);
                $('.issue_type').html(responseData.issue_type);
                $('.discription').html(responseData.description);
                $('.attachment').html(responseData.raprocure_attachment);
                $('.raprocure_response').html(responseData.raprocure_response);
                if (responseData.status == 1) {
                    $('.status').html('Pending');
                } else if (responseData.status == 2) {
                    $('.status').html('Working');
                } else if (responseData.status == 3) {
                    $('.status').html('Close');
                }
                let asset_url = "{{asset('uploads/ticket_document')}}";
                if (responseData.document != '') {
                    $('.uploadfile').html('<a download href="' + asset_url + '/' +
                        responseData.document + '">' + responseData.document + '</a>');
                }
                if (responseData.raprocure_attachment != '' && responseData.raprocure_attachment != null) {
                    $('.attachment').html('<a download href="' + asset_url + '/' +
                        responseData.raprocure_attachment + '">' + responseData.raprocure_attachment +
                        '</a>');
                }

                $('#viewRequestModal').modal('show');
            } else if (response.status == false) {
                alert(response.message);
            }
        }

    })
}
</script>
@endsection