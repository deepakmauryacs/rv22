@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightslider/1.1.6/css/lightslider.min.css" />
<link href="https://cdn.jsdelivr.net/npm/ez-plus@1.2.1/css/jquery.ez-plus.min.css" rel="stylesheet">

<style>
    .main-img {
        border: 1px solid #ddd;
        padding: 5px;
        background: #f9f9f9;
    }

    /* Change navigation arrow color */
    .lSPrev,
    .lSNext {
        background-color: rgb(1 82 148 / 46%) !important;
        color: #fffff !important;
        border-radius: 50%;
    }

    /* Change hover color */
    .lSPrev:hover,
    .lSNext:hover {
        background-color: var(--primary-color) !important;
        color: #fffff !important;
    }

    /* If arrows use pseudo-elements for icons */
    .lSPrev::before,
    .lSNext::before {
        color: yellow !important;
        font-size: 20px;
    }
</style>
@endsection



@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightslider/1.1.6/js/lightslider.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/ez-plus@1.2.1/src/jquery.ez-plus.min.js"></script>


<script>
    function initSlider() {
    // Destroy existing lightSlider instance if exists
    const existingSlider = $('#vertical').data('lightSlider');
    if (existingSlider) {
        existingSlider.destroy(true);
        $('#vertical').removeAttr('style').removeClass('lightSlider');
    }

    // Determine slider options
    let options;
    if ($(window).width() > 768) {
        // Desktop - Vertical layout
        options = {
            gallery: false,
            vertical: true,
            item: 4,
            verticalHeight: 400,
            vThumbWidth: 50,
            thumbMargin: 50,
            slideMargin: 50,
            slideMargin: 10,
            loop: false,
            centerSlide: true,
            currentPagerPosition: 'middle',
            responsive: [
                {
                    breakpoint: 768,
                    settings: {
                        item: 1
                    }
                }
            ],
            pager: false
        };
    } else {
        // Mobile - Horizontal layout
        options = {
            loop: true,
            gallery: false,
            vertical: false,
            item: 3,
            thumbMargin: 5,
            slideMargin: 5,
            pager: false
        };
    }

    // Initialize lightSlider
    const slider = $('#vertical').lightSlider(options);

    // Add mouse scroll support
    let debounce = false;
    $('#vertical').off('wheel').on('wheel', function (e) {
        e.preventDefault();
        if (debounce) return;
        debounce = true;

        const delta = e.originalEvent.deltaY;
        if (delta > 0) {
            slider.goToNextSlide();
        } else {
            slider.goToPrevSlide();
        }

        setTimeout(() => debounce = false, 400); // Adjust delay for scroll sensitivity
    });
}

// Initialize on document ready
$(document).ready(function () {
    initSlider();
});

// Re-initialize on window resize
$(window).on('resize', function () {
    initSlider();
});

function initZoom() {
    let zoomImage = $("#zoomImage");

    // Remove old zoom instance before re-initializing
    $.removeData(zoomImage, 'elevateZoom');
    $('.zoomContainer').remove();

    if ($(window).width() > 768) {
        // Desktop: standard zoom window
        zoomImage.ezPlus({
            zoomType: "window",
            cursor: "crosshair",
            zoomWindowFadeIn: 300,
            zoomWindowFadeOut: 400,
            lensFadeIn: 100,
            lensFadeOut: 100,
            responsive: true
        });
    } else {
        // Mobile: use inner zoom for better UX
        zoomImage.ezPlus({
            zoomType: "inner",
            cursor: "crosshair",
            zoomWindowFadeIn: 100,
            zoomWindowFadeOut: 100,
            responsive: true
        });
    }
}


$(document).ready(function(){
    initSlider();
     initZoom();
    $(window).resize(function(){
        $('#vertical').destroy();
        initSlider();
        initZoom();
    });
});

  // Click thumbnail to change main image
  $("#vertical").on("click", "li", function(){
    var largeImg = $(this).data("src");
    $('.zoomContainer').remove();
    $("#zoomImage").removeData('elevateZoom');
    $("#zoomImage").attr("src", $(this).find("img").attr("src"))
                   .data("zoom-image", largeImg);
    initZoom()
  });


   /***:- generate rfq  -:***/
        $('.generateRfqBtn').on('click', function() {
            // Ajax submit
            $.ajax({
                url: '{{ route("buyer.rfq.add-to-draft") }}',
                type: 'POST',
                data: {
                  product_id: $(this).data('product_id'),
                  vendors_id: [$(this).data('vendor_id')],
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        window.location = `${response.redirectUrl}`
                    } else {
                        toastr.error(response.message);
                    }

                },
                error: function() {
                    toastr.error('Error generating RFQ!');
                }
            });
        });

</script>

@endsection
