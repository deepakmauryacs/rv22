<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title??'' }} {{ $sub_title??'' }} - Raprocure</title>
        <!---favicon-->
        <link rel="shortcut icon" href="{{ asset('public/assets/vendor/favicon/raprocure-fevicon.ico') }}"
            type="image/x-icon" />
        <!---Bootsrap CSS and Icons-->
        <link href="{{ asset('public/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('public/assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}" />
        <!---Custom CSS-->
        <link href="{{ asset('public/assets/vendor/css/layout.css') }}" rel="stylesheet" />
        <link href="{{ asset('public/assets/vendor/css/style.css') }}" rel="stylesheet" />
        <link href="{{ asset('public/assets/vendor/css/responsive.css') }}" rel="stylesheet" />
        <link href="{{ asset('public/assets/vendor/css/custom.css') }}" rel="stylesheet" />

        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

         {{-- toastr --}}
        <link href="{{ asset('public/assets/library/toastr/css/toastr.min.css') }}" rel="stylesheet" />
        <script src="{{ asset('public/assets/library/toastr/js/toastr.min.js') }}"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

        @yield('styles')
    </head>

    <body>
        @include('vendor.layouts.navigation_second')

        <!-- Body Section -->
        <div class="d-flex">
            @include('vendor.layouts.sidebar_second')

            <!---Section Main-->
            <main class="main main-inner-page flex-grow-1 py-2 px-md-3 px-1">
                @yield('content')
            </main>

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
        <button onclick="scrollToTop()" id="back-to-top-btn" class="ra-btn ra-btn-primary px-2 py-1 font-size-20">
            <span>
                <span class="bi bi-arrow-up-short font-size-20" aria-hidden="true"></span>
            </span>
        </button>
        <!---bootsrap-->
        <script src="{{ asset('public/assets/vendor/bootstrap/js/bootstrap.bundle.js') }}"></script>
        <!---local-js-->
        <script src="{{ asset('public/assets/vendor/js/common.js') }}"></script>

        <script src="{{ asset('public/assets/js/messagePopup.js') }}"></script>
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

        <script>
            function openNav() {
      const sidebar = document.getElementById("mySidebar");
      sidebar.style.transform = "translateX(0)";
      sidebar.classList.add("onClickMenuSidebar");
      window.addEventListener('resize', function () {
        const isMobileView = window.innerWidth <= 768;

        if (!isMobileView) {
          sidebar.classList.remove("onClickMenuSidebar");
          sidebar.removeAttribute("style");
        }
      });
    }

    function closeNav() {
      const sidebar = document.getElementById("mySidebar");
      sidebar.style.transform = "translateX(-115%)";
      sidebar.classList.remove("onClickMenuSidebar");

      let wasMobileView = window.innerWidth <= 768;
      window.addEventListener('resize', function () {
        const isMobileView = window.innerWidth <= 768;

        if (wasMobileView && !isMobileView) {
          openNav();
          sidebar.classList.remove("onClickMenuSidebar");
          sidebar.removeAttribute("style");
        }
        wasMobileView = isMobileView;
      });
    }
  </script>
  @yield('scripts')
    <script>
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
                    url: "{{ route('vendor.check_notification') }}",
                    type: 'POST',
                    dataType  : 'JSON',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        $('.notification-number').text(response.count);
                        $('#Allnotification-messages').html(response.html);
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
    </script>
</body>
</html>
