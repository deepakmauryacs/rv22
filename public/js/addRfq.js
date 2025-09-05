//====show rfq modal===
   window.show_rfq_modal = function () {
    let checkedItems = $("input[name='inv_checkbox[]']:checked");
    if (checkedItems.length > 0) {

        let hasZeroQty = false;
        let inventoryIds = [];

        checkedItems.each(function () {
            let inventoryId = $(this).val();
            let maxQty = $(this).data('maxqty');
            inventoryIds.push(inventoryId);
            if (!maxQty || maxQty === "0") {
                hasZeroQty = true;
            }
        });

        if (hasZeroQty) {
            toastr.error("You don’t have any pending indent for this product to add to an RFQ.");
            return;
        }

        $.ajax({
            url: fetchInventoryDetailsForAddRfqUrl,
            type: "POST",
            data: {
                inventories: inventoryIds,  // sending array of ids here
                _token: $('meta[name="csrf-token"]').attr("content")
            },
            success: function (response) {
                if (response.status === 1) {
                    // Clear existing rows
                    $('#rfqInventoryTable tbody').empty();

                    // Loop through returned inventories and create rows dynamically
                    response.data.forEach(function (item) {
                        let specification = item.specification && item.specification.length > 10
                            ? `${item.specification.substring(0, 10)}...`
                            : item.specification ?? '';

                        let size = item.size && item.size.length > 10
                            ? `${item.size.substring(0, 10)}...`
                            : item.size ?? '';

                        let row = `
                            <tr>
                                <td>${item.prod_name}</td>
                                <td>${specification} <i class="bi bi-info-circle-fill ms-1" data-bs-toggle="tooltip" title="${item.specification}"></i></td>
                                <td>${size} <i class="bi bi-info-circle-fill ms-1" data-bs-toggle="tooltip" title="${item.size}"></i></td>
                                <td>${item.uom_name}</td>
                                <td>${item.total_indent_qty}</td>
                                <td>
                                    <input type="text" min="0" max="${item.maxQty}" class="form-control bg-white smt_numeric_only rfq_qty_input" name="rfq_qty[]" value="">
                                    <input type="hidden" name="inventory_id[]" value="${item.id}">
                                </td>
                            </tr>
                        `;
                        $('#rfqInventoryTable tbody').append(row);
                    });

                    // Initialize Bootstrap tooltips
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                    $("#RfqModal").modal("show");

                } else {
                    toastr.error(response.message);
                }
            },
            error: function () {
                toastr.error("Something went wrong while fetching inventory details.");
            }
        });

    } else {
        toastr.error('At least one inventory is required');
    }
};
//====show rfq modal===


//active rfq pop up
function activeRfqPopUP(inventoryId) {
    const url = activeRfqUrl.replace('__ID__', inventoryId);
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.status === 1) {
                $('#rfqdetailsTable tbody').empty();

                data.data.forEach(function (item) {
                    let viewUrl = activeRfqDetailsbyIdUrl.replace('__RFQ_ID__', item.rfq_id);
                    let row = `
                        <tr>
                            <td>${item.rfq_no}</td>
                            <td>${item.rfq_date}</td>
                            <td>${item.rfq_closed}</td>
                            <td>${item.rfq_qty}</td>
                            <td>
                                <a href="${viewUrl}" target="_blank">
                                    <i class="bi bi-eye-fill"></i> View Details
                                </a>
                            </td>
                        </tr>
                    `;
                    $('#rfqdetailsTable tbody').append(row);
                });

                $("#ActiveRfqDetailsModal").modal("show");
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching RFQ details:', error);
            toastr.error('Something went wrong.');
        });
}

