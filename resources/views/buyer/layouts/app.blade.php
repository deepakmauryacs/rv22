<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="" />
        <meta name="keywords" content="" />
        <meta name="author" content="" />
        <meta property="og:image:size" content="300" />
        <title>{{ $title??'' }} - Raprocure</title>
        <!---favicon-->
		<link rel="shortcut icon" href="{{ asset('public/assets/images/favicon/raprocure-fevicon.ico') }}" type="image/x-icon">
        <!---bootsrap-->
        <link href="{{ asset('public/assets/buyer/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
        <!---bootsrap-icon-->
        <link rel="stylesheet" href="{{ asset('public/assets/buyer/bootstrap-icons/bootstrap-icons.min.css') }}">
        <!---css-->
        <link href="{{ asset('public/assets/buyer/css/layout.css') }}" rel="stylesheet"/>
        <link href="{{ asset('public/assets/buyer/css/style.css') }}" rel="stylesheet">

		<script src="{{ asset('public/assets/login/js/jquery-3.7.1.min.js') }}"></script>

		<meta name="csrf-token" content="{{ csrf_token() }}">
        <style>
            li.suggesation-line a {
                color: #0d71bb;
            }
        </style>
		@yield('css')

    </head>
    <body>
        <!---Header part-->
        <div class="project_header sticky-top">
            @include('buyer.layouts.navigation')
        </div>
        <div class="d-flex">
            @yield('content')

            <!-- Modal Compose Mail -->
            <div class="modal fade" id="dynamicMessageModal" tabindex="-1" aria-labelledby="composeMailModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content message_html">
                        <!-- Content will be injected here -->
                    </div>
                </div>
            </div>
        </div>
        <!-- Back to top button -->
        <button onclick="scrollToTop()" id="backToTopBtn" class="ra-btn ra-btn-primary px-2 py-1 font-size-20">
            <span>
                <span class="bi bi-arrow-up-short font-size-20" aria-hidden="true"></span>
            </span>
        </button>
        <!---bootsrap-->
        <script src="{{ asset('public/assets/buyer/bootstrap/js/bootstrap.bundle.js') }}"></script>
        <!---local-js-->
        <script src="{{ asset('public/assets/buyer/js/common.js') }}"></script>
        <script src="{{ asset('public/assets/js/messagePopup.js') }}"></script>
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

        {{-- toastr --}}
        <link href="{{ asset('public/assets/library/toastr/css/toastr.min.css') }}" rel="stylesheet" />
        <script src="{{ asset('public/assets/library/toastr/js/toastr.min.js') }}"></script>

        @include('buyer.layouts.app-js')

		@yield('scripts')
        <script>
            @if(Auth::user()->is_profile_verified==1)
            setInterval(check_user_message, 15*60000); //poll every 60 second
            check_user_message();
            // Listen for the visibility change event to detect when the tab becomes active
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    // The tab just became active, so fetch notifications immediately
                    check_user_message();
                }
            });
            function check_user_message()
            {
                // Check if the document (tab) is currently visible
                if (document.hidden) {
                    return; // If the tab is not active, skip the AJAX call
                }
                $.ajax({
                    url: "{{ route('buyer.check_notification') }}",
                    type: 'POST',
                    dataType  : 'JSON',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        $('.notification-number').text(response.count);
                        $('#Allnotification_messages').html(response.html);
                    }
                });
            }

            function readNotification(notification) {
                $.ajax({
                    url: "{{ route('update-notification-status') }}",
                    type: 'POST',
                    dataType  : 'JSON',
                    data: {
                        notification,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                    }
                });
            }
            @endif
        </script>
    </body>
</html>
