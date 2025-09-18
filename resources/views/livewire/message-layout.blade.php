<div>
    @push('livewireStyles')
    <link rel="stylesheet" href="{{ asset('public/css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/message.css') }}">
    <style>
        .active>.page-link,
        .page-link.active {
            background: var(--primary-color);
            color: var(--white-color) !important;
            border: none;
            z-index: 3;
            /* color: var(--bs-pagination-active-color); */
            background-color: var(--primary-color);
            border-color: var(--bs-pagination-active-border-color) #015294;
        }

        .page-link {
            background: var(--pagination-bg-color);
            border: none;
            color: var(--color-base);
            font-size: .875rem;
            margin: 2px;
            border-radius: .25rem;
            padding: 6px 15px;
        }

        .main {
            width: 86% !important;
            left: 15% !important;
        }

        .ck-editor__editable_inline {
            min-height: 180px !important;
        }
    </style>
    @endpush


    @if (session()->has('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @elseif (session()->has('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif


    {{-- <div class="container"> --}}
        <div class="chat-page mx-3 mx-sm-0 rounded overflow-hidden1">
            <div class="row g-0">
                {{-- Card Left Side --}}
                <div class="col-lg-3 col-md-4 col-12">
                    <div class="message-left-side">
                        @if (
                        (Auth::user()->user_type == '1' || Auth::user()->user_type == '2') &&
                        $type_message == '3' &&
                        $replyListStatus == false)
                        <div class="p-3">
                            <button class="ra-btn ra-btn-primary w-100 justify-content-center"
                                wire:click='showComposeModal()'>
                                <i class="bi bi-plus-circle" aria-hidden="true"></i> Compose Mail
                            </button>
                        </div>
                        @endif

                        @include('livewire.compose-mail')

                        <div class="mt-2">
                            <ul>
                                <li class="message-list" wire:click="listingType('inbox')">
                                    <div
                                        class="d-flex align-items-center justify-content-between active-item-link {{ $listing_type == 'inbox' ? 'active' : '' }}">
                                        <span class="inbox-text">
                                            <span class="bi bi-envelope me-1"></span>
                                            Inbox
                                        </span>
                                        <span class="message-number">
                                            @if ($message_type == 'raprocure')
                                            {{ collect($msgCount)->firstWhere('user_type', '3')?->inbox_unread_count ??
                                            0 }}
                                            @elseif ($message_type == 'vendor')
                                            {{ collect($msgCount)->firstWhere('user_type', '2')?->inbox_unread_count ??
                                            0 }}
                                            @elseif ($message_type == 'buyer')
                                            {{ collect($msgCount)->firstWhere('user_type', '1')?->inbox_unread_count ??
                                            0 }}
                                            @endif
                                        </span>
                                    </div>
                                </li>
                                <li class="message-list" wire:click="listingType('send')">
                                    <div
                                        class="d-flex align-items-center justify-content-between active-item-link {{ $listing_type == 'send' ? 'active' : '' }}">
                                        <span class="inbox-text">
                                            <span class="bi bi-send me-1"></span>
                                            Sent Messages
                                        </span>
                                    </div>
                                </li>
                                <li class="message-list" wire:click="listingType('draft')">
                                    <div
                                        class="d-flex align-items-center justify-content-between active-item-link {{ $listing_type == 'draft' ? 'active' : '' }}">
                                        <span class="inbox-text">
                                            <span class="bi bi-pencil-square me-1"></span>
                                            Draft
                                        </span>
                                    </div>
                                </li>
                                {{-- <li class="message-list" wire:click="listingType('trash')">
                                    <div
                                        class="d-flex align-items-center justify-content-between active-item-link {{ $listing_type == 'trash' ? 'active' : '' }}">
                                        <span class="inbox-text">
                                            <span class="bi bi-trash me-1"></span>
                                            Trash
                                        </span>
                                    </div>
                                </li> --}}
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Card Right Side --}}
                <div class="col-lg-9 col-md-8 col-12">
                    <div class="message-right-side position-relative">
                        @if ($replyListStatus == true)
                        @include('livewire.message-reply')
                        @else
                        @include('livewire.message-list')
                        @endif
                    </div>
                </div>

            </div>
        </div>




        {{--
    </div> --}}

    <!-- Close modal on success -->

    <!-- Show modal again on validation error -->

    @push('livewireScripts')
    @if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
                    const modal = new bootstrap.Modal(document.getElementById('composeModal'),{ focus: false });
                    modal.show();
                });
    </script>
    @endif

    {{-- <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script> --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>



    <script>
        /*let ckEditorInstance = null;

            function initCkEditor(initialData = '') {
                const editorElement = document.querySelector('#msg');

                if (!editorElement) {
                    console.warn('CKEditor target #msg not found');
                    return;
                }

                if (ckEditorInstance) {
                    ckEditorInstance.destroy()
                        .then(() => {
                            createCkEditor(editorElement);
                        })
                        .catch(error => {
                            console.error('Error destroying previous CKEditor:', error);
                            createCkEditor(editorElement);
                        });
                } else {
                    createCkEditor(editorElement);
                }
            }

            function createCkEditor(editorElement,initialData) {
                ClassicEditor
                    .create(editorElement, {
                        toolbar: ['heading','|','bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote','undo', 'redo',],
                    })
                    .then(editor => {
                        ckEditorInstance = editor;
                        editor.setData(initialData || '');
                        editor.model.document.on('change:data', () => {
                            const data = editor.getData();
                            Livewire.dispatch('ckeditor-update', {
                                content: data
                            });
                        });
                    })
                    .catch(error => {
                        console.error('CKEditor error:', error);
                    });
            }*/

           function injectCkeditorStyles() {
                if (document.getElementById('ck-custom-styles')) return;

                const style = document.createElement('style');
                style.id = 'ck-custom-styles';
                style.textContent = `
                /* Keep link panel above Bootstrap modal */
                .ck-balloon-panel,
                .ck.ck-balloon-panel,
                .ck.ck-tooltip {
                z-index: 99999999999 !important ;
                }

                /* Minimum height for editor area */
                .ck-editor__editable_inline {
                min-height: 180px !important;
                }

                /* Restore bullets/numbers inside CKEditor even if global CSS removes them */
                .ck-editor__editable_inline ul,
                .ck-editor__editable_inline ol {
                padding-left: 2rem;
                margin-left: 0;
                }
                .ck-editor__editable_inline ul li {
                list-style-type: disc !important;
                }
                .ck-editor__editable_inline ol li {
                list-style-type: decimal !important;
                }
                `;
            document.head.appendChild(style);
            }

            let ckEditorInstance = null;

            function initCkEditor(initialData = '') {
                const editorElement = document.querySelector('#msg');
                if (!editorElement) {
                console.warn('CKEditor target #msg not found');
                return;
                }

                if (ckEditorInstance) {
                ckEditorInstance.destroy()
                .then(() => {
                createCkEditor(editorElement, initialData);
                })
                .catch(error => {
                console.error('Error destroying previous CKEditor:', error);
                createCkEditor(editorElement, initialData);
                });
                } else {
                createCkEditor(editorElement, initialData);
                }
            }

            function createCkEditor(editorElement, initialData = '') {
                injectCkeditorStyles();
            ClassicEditor
            .create(editorElement, {
            toolbar: [
            'heading', '|', 'bold', 'italic',
            'link', 'bulletedList', 'numberedList',
            'blockQuote', 'undo', 'redo'
            ],
            })
            .then(editor => {
            ckEditorInstance = editor;
            editor.setData(initialData || '');
            editor.model.document.on('change:data', () => {
            const data = editor.getData();
            Livewire.dispatch('ckeditor-update', { content: data });
            });
            })
            .catch(error => {
            console.error('CKEditor error:', error);
            });
            }

            document.addEventListener('DOMContentLoaded', function() {

                const selectAll = document.getElementById('selectAll');
                Livewire.on('resetCheckboxes', () => {
                    const checkboxes = document.querySelectorAll('.row-checkbox');
                    checkboxes.forEach(cb => cb.checked = false);
                    selectAll.checked = false;
                });
                document.addEventListener('change', function(event) {
                    const checkboxes = document.querySelectorAll('.row-checkbox');
                    if (event.target.id === 'selectAll') {
                        checkboxes.forEach(cb => cb.checked = event.target.checked);

                        const selectedMessages = [];
                        checkboxes.forEach(cb => {
                            if (cb.checked) {
                                console.log(cb.value);

                                selectedMessages.push(JSON.parse(cb.value));
                            }
                        });
                        @this.set('selectedMessages', selectedMessages);
                    }
                    if (event.target.classList.contains('row-checkbox')) {
                        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                        selectAll.checked = allChecked;
                        const selectedMessages = [];
                        checkboxes.forEach(cb => {
                            if (cb.checked) {
                                console.log(cb.value);
                                selectedMessages.push(JSON.parse(cb.value));
                            }
                        });
                        @this.set('selectedMessages', selectedMessages);
                    }
                });
            });


            document.addEventListener('DOMContentLoaded', () => {
                const modalEl = document.getElementById('composeModal');
                const closeBtn = document.getElementById('composeCloseBtn');
                const cancelBtn = document.getElementById('composeCancelBtn');
                const subject = document.getElementById('subject');
                const msg = document.getElementById('msg');

                const showConfirmBeforeClose = (e) => {
                    e.preventDefault();
                    if (msg.value != '' && subject.value != '') {

                        const confirmClose = confirm("Do you want to save this as a draft before closing?");
                        if (confirmClose) {
                            Livewire.dispatch('save-draft');
                        } else {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                        /*const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();*/
                    } else {
                        Livewire.dispatch('resetInputFields');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }
                };

                closeBtn?.addEventListener('click', showConfirmBeforeClose);
                cancelBtn?.addEventListener('click', showConfirmBeforeClose);

                /***:- close form  -:***/
                window.addEventListener('hide-compose-modal', () => {
                    $('#composeModal').on('hidden.bs.modal', function() {
                        $(this).find('form').trigger('reset');
                    })
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                });


                /***:- show form  -:***/
                window.addEventListener('show-compose-modal', () => {
                    //alert(1);
                    const modal = new bootstrap.Modal(document.getElementById('composeModal'),{ focus: false });
                    modal.show();
                    setTimeout(() => {

                        console.log('msg',@this.get('message'));

                        const content = @this.get('message');
                        // initCkEditor(content || '');
                        initCkEditor(content || '', "#msg");
                        // initCkEditor(@this.get('message') || '');
                    }, 500);

                });


                /***:- notifications  -:***/
                    window.addEventListener('notify', function (e) {
                        const detail=e.detail[0];
                        const type = detail.type ?? 'info';
                        const msg  = detail.message ?? 'No message provided';

                        toastr.options.closeButton = true;
                        toastr.options.progressBar = true;
                        toastr[type](msg);
                    });

                    /*window.Livewire.onError((error, component) => {
                        if (error.status === 419) {
                            alert('Your session has expired. The page will reload.');
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000); // wait 2 seconds
                        }

                        return true;
                    });*/
            });

            document.addEventListener("livewire:update", () => {
                setTimeout(() => {


                    const el = document.getElementById('chatMessages');
                    if (el) {
                        //alert('scroll-to-bottom');
                        // Scroll to the bottom of the chat messages
                        el.scrollTop = el.scrollHeight;
                    }
                }, 50);
                let content=@this.get('message');
                // initCkEditor( @this.get('message')|| '');

                initCkEditor(content || '', "#msg");

            });
            window.addEventListener('scroll-to-bottom', () => {
                setTimeout(() => {
                    const el = document.getElementById('chatMessages');
                    if (el) {
                        // el.scrollTop = el.scrollHeight;
                        // el.scrollTop =0;
                    }
                    bindChatScroll();
                   // document.getElementById('messageInput').focus();

                }, 500);

            });


            function bindChatScroll() {

                let chatScrollBound = false;

                const container = document.getElementById('chatMessages');

                if (container && !container.dataset.scrollBound) {
                    container.dataset.scrollBound = "true"; // Mark as bound

                    container.addEventListener('scroll', function() {
                        const scrollTop = container.scrollTop;


                        const scrollHeight = container.scrollHeight;
                        const clientHeight = container.clientHeight;



                        if (scrollTop + clientHeight >= scrollHeight - 10) {
                            const componentEl = container.closest('[wire\\:id]');
                            if (!componentEl) return;

                            const component = Livewire.find(componentEl.getAttribute('wire:id'));

                            if (component.get('hasMoreMessages')) {
                                console.log('Loading more messages...');
                                component.call('loadMore');
                                //container.scrollTop = container.scrollHeight - 400;
                            }else{
                                console.log(component.get('hasMoreMessages'));
                            }
                        }
                    });

                    console.log('Scroll event bound.');
                } else {
                    console.log('Scroll event already bound or container missing.');
                }
            }


            function bindChatScroll5() {
                    const container = document.getElementById('chatMessages');

                    if (container && !container.dataset.scrollBound) {
                        container.dataset.scrollBound = "true"; // Mark as bound

                        container.addEventListener('scroll', function () {
                            const scrollTop = container.scrollTop;
                            const scrollHeight = container.scrollHeight;
                            const clientHeight = container.clientHeight;


                            // When user scrolls near the bottom (or exactly to bottom)
                            if (scrollTop + clientHeight >= scrollHeight - 10) {
                                const componentEl = container.closest('[wire\\:id]');

                                // console.log('componentEl',componentEl,'getAttribute',componentEl.getAttribute('wire:id'));

                                if (!componentEl) return;

                                const component = Livewire.find(componentEl.getAttribute('wire:id'));

                                // console.log('scrollTop',scrollTop,'scrollHeight',scrollHeight,'clientHeight',clientHeight,'hasMoreMessages',component.get('hasMoreMessages'));


                                if (component.get('hasMoreMessages')) {
                                    console.log('Reached bottom - Loading more messages...');
                                    component.call('loadMore');
                                }
                            }
                        });

                        console.log('Scroll event bound.');
                    } else {
                        console.log('Scroll event already bound or container missing.');
                    }
                }



            document.addEventListener('DOMContentLoaded', function() {
                bindChatScroll();
                let content=@this.get('message') || '';
                // initCkEditor(@this.get('message') || '');
                initCkEditor(content || '', "#msg");

            });

            Livewire.hook('message.processed', (message, component) => {
                bindChatScroll();

                const modal = document.getElementById('composeModal');
                if (modal?.classList.contains('show')) {
                    setTimeout(() => {
                        // initCkEditor(@this.get('message') || '');
                        let content=@this.get('message') || '';
                        // initCkEditor(@this.get('message') || '');
                        initCkEditor(content || '', "#msg");
                    }, 300);
                }

            });


            // Update Livewire model on custom event
            Livewire.on('ckeditor-update', ({
                content
            }) => {
                Livewire.dispatch('set-message', {
                    content
                });
            });
    </script>

    @if ($replyListStatus == true)
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
                    alert(1);

                    setTimeout(() => {
                        let chatScrollBound = false;
                        const container = document.getElementById('chatMessages');
                        if (container && !chatScrollBound) {
                            chatScrollBound = true;

                            container.addEventListener('scroll', function() {
                                const scrollTop = container.scrollTop;
                                if (scrollTop === 0) {

                                    const component = Livewire.find(container.closest('[wire\\:id]')
                                        .getAttribute(
                                            'wire:id'));

                                    console.log('Component:', component.get('hasMoreMessages'));
                                    if (component.get('hasMoreMessages') == true) {
                                        console.log('Loading more messages...');
                                        component.call('loadMore');
                                        container.scrollTop = container.scrollHeight - 400;
                                    }
                                }
                            });
                            console.log('Scroll event bound.');
                        } else {
                            console.log('Scroll event not bound.');
                        }
                    }, 7000);

                });
    </script> --}}
    @endif
    @endpush

</div>
