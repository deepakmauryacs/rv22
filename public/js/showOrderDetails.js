//====show order details modal===
function orderDetailsPopUP(inventoryId) {
    const url = orderDetailsUrl.replace('__ID__', inventoryId);

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.status === 1) {
                const tableBody = $('#orderdetailsTable tbody');
                tableBody.empty(); // Clear existing rows

                data.data.forEach(function (item) {
                    // Use rfq_id instead of rfq_no for the view link
                    // const viewUrl = activeRfqDetailsbyIdUrl.replace('__RFQ_ID__', item.order_no);

                    const row = `
                        <tr>
                            <td>${item.order_no}</td>
                            <td>${item.rfq_no}</td>
                            <td>${item.order_date}</td>
                            <td>${item.order_qty}</td>
                            <td>${item.vendor_name}</td>
                            <td>
                                <a href="" target="_blank" title="View RFQ Details">
                                    <i class="bi bi-eye-fill"></i> View Details
                                </a>
                            </td>
                        </tr>
                    `;

                    tableBody.append(row);
                });

                $("#OrderDetailsModal").modal("show");
            } else {
                toastr.error(data.message || "No order details found.");
            }
        })
        .catch(error => {
            console.error('Error fetching order details:', error);
            toastr.error('Something went wrong while fetching the data.');
        });
}
//====show order details modal===