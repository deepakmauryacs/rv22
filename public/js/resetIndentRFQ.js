$(document).ready(function () {
    $('.resetIndentRFQ').on('click', function (e) {
        e.preventDefault();

        if (selectedIds.length > 0) {
            if (confirm('Are you sure you want to Reset the selected inventory?')) {
                $.ajax({
                    url: resetInventoryUrl,
                    method: 'POST',
                    data: {
                        inventory_ids: selectedIds,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.status === 1) {
                            toastr.success(response.message);
                                $('#inventory-table').DataTable().ajax.reload();

                            selectedIds = [];
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function () {
                        toastr.error('Something went wrong. Please try again later.');
                    }
                });
            }
        } else {
            toastr.error('Please select at least one Inventory.');
        }
    });
});

