<script>
    function manageVendor(e, types) {
        let vendorId = $(e).parent().attr('data-id');
        $.ajax({
            url: "{{route('buyer.search-vendor.favourite-blacklist')}}",
            type: "POST",
            dataType: "json",
            data: {
                vendor_id: vendorId,
                types: types,
                _token: "{{ csrf_token() }}"
            },
            sendBefore: function() {
            },
            success: function(response) {
                $(e).parent().html(response);
            },
            error: function(error) {
                console.log(error);
            },
            complete: function() {
            }
        });
    }

</script>
