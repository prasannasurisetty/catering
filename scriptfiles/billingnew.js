function showToast(message, type = "success") {
    let bgColor = "#4CAF50"; // success

    if (type === "error") bgColor = "#f44336";
    if (type === "warning") bgColor = "#ff9800";

    Toastify({
        text: message,
        duration: 2000,
        gravity: "top",
        position: "right",
        backgroundColor: bgColor,
        close: false,
    }).showToast();
}



let selectedAddressId = null;
let checkedAddressIds = [];

function loadAddress() {
    var payload = {
        load: "get_address",
        cid: document.querySelector(".customer_id").dataset.cid,
    };

    $.ajax({
        type: "POST",
        url: "./webservices/addressapi.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (response) {
            if (response.success) {
                let container = document.getElementById("addresses_container");
                container.style.display = "flex";
                container.innerHTML = `<div class="add_address_block"></div>`;

                response.data.forEach((address) => {
                    const isDefault = address.isDefault == 1;
                    if (isDefault) selectedAddressId = address.aid;
                    // displayMonthlySub();
                    let block = `
                   <div class="address_block ${isDefault ? "highlight-address" : ""}" 
                 data-block-aid="${address.aid}" 
                 onclick="highlightAddress(${address.aid});">
              <div class="address_text">
                ${isDefault ? `<span class="default-label">Default</span>` : ""}
                <p>${address.flatno}</p>
                <p>${address.street}</p>
                <p>${address.area}</p>
                <p>${address.landmark}</p>
                <p>${address.address_ph_number}</p>
                <p>${address.pincode}</p>
                <p>${address.addresslink}</p>

               
              </div>
                  </div>
                  `;

                    container.insertAdjacentHTML("beforeend", block);
                });

                if (selectedAddressId) {
                    highlightAddress(selectedAddressId);
                }


                checkedAddressIds = [];
                $(".address-checkbox:checked").each(function () {
                    checkedAddressIds.push(parseInt($(this).data("aid")));
                });

                $(".address-checkbox").on("change", function () {
                    let aid = parseInt($(this).data("aid"));

                    if ($(this).is(":checked")) {
                        if (!checkedAddressIds.includes(aid)) checkedAddressIds.push(aid);
                    } else {
                        checkedAddressIds = checkedAddressIds.filter((x) => x !== aid);
                    }
                });


            } else {
                showToast("No address found", "error");
            }
        },
    });
}

function highlightAddress(aid) {

    selectedAddressId = aid;

    document.querySelectorAll(".address_block").forEach(el =>
        el.classList.remove("highlight-address")
    );
    document.querySelector(`.address_block[data-block-aid="${aid}"]`)
        .classList.add("highlight-address");

    const customerId = document.querySelector(".customer_id").dataset.cid;

    if (customerId) {
        fetchAllOrders(customerId, aid);

    }

    // resetOrderContext();

}




// place order
let searchinput = document.getElementById("search_input");

searchinput.addEventListener("input", () => {
    var payload = {
        load: "search",
        searchvalue: searchinput.value,
    };
    $.ajax({
        type: "POST",
        url: "./webservices/register.php",
        data: JSON.stringify(payload),
        dataType: "json",
        success: function (response) {
            let recmd = document.querySelector(".recmd");
            recmd.innerHTML = "";
            if (response.data.length > 0) {
                recmd.style.display = "flex";
                response.data.forEach((itm) => {
                    let para = document.createElement("p");
                    para.setAttribute("class", "multiple_list_item");
                    para.setAttribute("onclick", `fetchbyid(${itm.CustomerID})`);
                    para.innerHTML = `<span>${itm.CustomerID} -</span>
                    <span>${itm.CustomerName} (</span><span>${itm.Phone1})</span>`;
                    recmd.appendChild(para);
                });
            } else {
                recmd.style.display = "none";
            }
        },
    });
});

function fetchbyid(sinput) {
    customerid = sinput;

    var load = "fetchbyid";

    var payload = {
        load: load,
        customerid: sinput,
    };
    $.ajax({
        type: "POST",
        url: "./webservices/register.php",
        data: JSON.stringify(payload),
        dataType: "json",
        success: function (response) {
            if (response.status === "Success") {
                document.querySelector(".recmd").style.display = "none";
                document.querySelector(".search_input").value = "";
                document.querySelector(".customer_id").textContent = `ID :${response.data[0].CustomerID}`;
                document.querySelector(".customer_id").dataset.cid = response.data[0].CustomerID;
                document.querySelector(".customer_name").textContent = `Name :${response.data[0].CustomerName}`;
                document.querySelector(".customer_name").setAttribute("title", response.data[0].CustomerName);
                document.querySelector(".customer_ph").textContent = `PH Number :${response.data[0].Phone1}`;
                loadAddress();
                document.querySelector('.customer-billdata').style.display = "flex";
                document.querySelector('.customer-orderdata').style.display = "flex";
                document.querySelector('.foodTypeTable thead').style.display = "table";

            } else {
                showToast("NO DATA FOUND", "error");
            }
            return;
        },
    });
}



document.getElementById("recovery_amount").addEventListener("input", recalcTotal);
document.getElementById("paid_amount").addEventListener("input", recalcTotal);



function recalcTotal() {
    const pending = parseFloat(
        document.getElementById("amounttobe_paid").value
    ) || 0;

    const recovery = parseFloat(
        document.getElementById("recovery_amount").value
    ) || 0;

    document.getElementById("totalamount_paid").value =
        (pending + recovery).toFixed(2);
}


function savepayment() {
    const customerId = document.querySelector(".customer_id").dataset.cid;
    const addressId = selectedAddressId;
    const paidAmount = Number($('#paid_amount').val());
    const totalPayable = Number($('#totalamount_paid').val());

    if (
        paidAmount <= 0 ||
        !$('#pay_mode').val() ||
        !$('#pay_date').val()
    ) {
        showToast("Please fill all payment fields", "warning");
        return;
    }

    if (paidAmount > totalPayable) {
        showToast("Paid amount cannot exceed total payable", "warning");
        return;
    }

    const payload = {
        load: "savepayment",
        customerid: customerId,
        addressid: addressId,
        orderid: localStorage.getItem("order_id"),
        orderdate: $('#order_date').val(),
        ordertime: $('#order_time').val(),
        recoveryamt: Number($('#recovery_amount').val()),
        paidamount: paidAmount,
        paymode: $('#pay_mode').val(),
        paydate: $('#pay_date').val()
    };
    $.ajax({
        type: "POST",
        url: "./webservices/cateringservicespayments.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (response) {
            if (response?.status === "success") {
                showToast("Payment successful", "success");

                // Clear form
                // $('#paid_amount').val("");
                // $('#recovery_amount').val(0);
                // $('#pay_date').val("");

                // Refresh UI
                prepareAndPrintInvoice();


            } else {
                showToast(response.message || "Payment failed", "error");
            }
            fetchAllOrders(customerId, addressId)
        },
    });
}




function loadpaymode() {

    var payload = {
        load: "loadpaymode",
        sno: "",
        type: "type"
    };

    $.ajax({
        type: "POST",
        url: "./webservices/billingnew.php",
        data: JSON.stringify(payload),
        dataType: "json",
        contentType: "application/json",

        success: function (response) {

            const dropdown = document.getElementById("pay_mode");
            const dropdown1 = document.getElementById("refund_pay_mode");

            dropdown.innerHTML = "";
            dropdown1.innerHTML = "";

            // Default option
            dropdown.appendChild(new Option("Select Paymode", ""));
            dropdown1.appendChild(new Option("Select Paymode", ""));

            if (response.status === "success" && response.data.length > 0) {

                response.data.forEach(x => {

                    const opt1 = new Option(x.type, x.sno);
                    const opt2 = new Option(x.type, x.sno);

                    dropdown.appendChild(opt1);
                    dropdown1.appendChild(opt2);
                });

            } else {

                dropdown.appendChild(new Option("No types available", ""));
                dropdown1.appendChild(new Option("No types available", ""));
            }
        },
    });
}

loadpaymode();


const paymentMode = document.getElementById('pay_mode');
const dynamicInputDiv = document.getElementById('dynamicInput');


paymentMode.addEventListener('change', function () {
    dynamicInputDiv.innerHTML = '';

    let inputField;

    switch (this.options[this.selectedIndex].text.toLowerCase()) {
        case 'cash':
            inputField = `
                <label><b>Delivery Person:</b></label>
                <select id="delivery-drop" class="form-select"></select>`;

            dynamicInputDiv.innerHTML = inputField;

            // Call AFTER element is created
            load_deliveryboys();
            break;

        case 'credit card':
        case 'debit card':
            inputField = `
                <label><b>Card Number:</b></label>
                <input type="text" id="card-num" placeholder="Enter card number">`;
            dynamicInputDiv.innerHTML = inputField;
            break;

        case 'cheque':
            inputField = `
                <label><b>Cheque Number:</b></label>
                <input type="text" id="cheque-num" placeholder="Enter cheque number">`;
            dynamicInputDiv.innerHTML = inputField;
            break;

        case 'upi':
            inputField = `
                <label><b>UPI Number:</b></label>
                <input type="text" id="upi-num" placeholder="Enter UPI number">`;
            dynamicInputDiv.innerHTML = inputField;
            break;

        default:
            inputField = '';
            dynamicInputDiv.innerHTML = inputField;
    }
});




const refundMode = document.getElementById('refund_pay_mode');
const dynamicInputDiv1 = document.getElementById('dynamicInput1');

refundMode.addEventListener('change', function () {
    dynamicInputDiv1.innerHTML = '';
    let inputField;
    switch (this.options[this.selectedIndex].text.toLowerCase()) {

        case 'cash':
            inputField = `
                <label><b>Delivery Person:</b></label>
                <select id="refund_delivery-drop" class="form-select"></select>`;
            dynamicInputDiv1.innerHTML = inputField;
            // Call AFTER element is created
            load_deliveryboys();
            break;
        case 'credit card':
        case 'debit card':
            inputField = `


                <label><b>Card Number:</b></label>
                <input type="text" id="refund_card-num" placeholder="Enter card number">`;
            dynamicInputDiv1.innerHTML = inputField;
            break;
        case 'cheque':
            inputField = `
                <label><b>Cheque Number:</b></label>
                <input type="text" id="refund_cheque-num" placeholder="Enter cheque number">`;
            dynamicInputDiv1.innerHTML = inputField;
            break;
        case 'upi':
            inputField = `
                <label><b>UPI Number:</b></label>
                <input type="text" id="refund_upi-num" placeholder="Enter UPI number">`;
            dynamicInputDiv1.innerHTML = inputField;
            break;
        default:
            inputField = '';
            dynamicInputDiv1.innerHTML = inputField;
    }
});








// loading delivery
function load_deliveryboys() {

    var payload = { load: "load_deliveryboys" };

    $.ajax({
        type: "POST",
        url: "./webservices/billingnew.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (response) {

            if (response.status !== "Success") return;

            const deliveryBoy = document.querySelector("#delivery-drop");
            const deliveryBoy1 = document.querySelector("#refund_delivery-drop");

            if (deliveryBoy) {
                deliveryBoy.innerHTML =
                    '<option value="" selected>Select Delivery Boy</option>';
            }

            if (deliveryBoy1) {
                deliveryBoy1.innerHTML =
                    '<option value="" selected>Select Delivery Boy</option>';
            }

            response.data.forEach(fd => {

                if (deliveryBoy) {
                    deliveryBoy.appendChild(
                        new Option(fd.Name, fd.ID)
                    );
                }

                if (deliveryBoy1) {
                    deliveryBoy1.appendChild(
                        new Option(fd.Name, fd.ID)
                    );
                }
            });
        },
    });
}


$(document).ready(function () {
    load_deliveryboys();
});


function togglePaymentOrRefundUI(order) {
    const status = Number(order.order_status);

    if (status === 1) {
        // 💰 PAYMENT
        $('#payment_section').show();
        $('#refund_section').hide();

    } else if (status === 0) {
        // 🔁 REFUND
        $('#payment_section').hide();
        $('#refund_section').show();

        $('#refund_grand_total').val(order.grand_total);
        $('#refund_advance_amount').val(order.paid_amount);
        $('#refund_amount').val(order.paid_amount);
    }
}



$(document).ready(function () {

    if (!hasOrderContext()) {

        // ❌ Hide order-dependent sections
        $('.order-utensils').hide();
        $('#payment_section').hide();
        $('#refund_section').hide();

        // ✅ Show helper message
        if (!$('#select-order-msg').length) {
            $('.payment-container').prepend(`
                <div id="select-order-msg" style="
                    margin:15px;
                    padding:12px;
                    background:#e7f1ff;
                    border:1px solid #b6d4fe;
                    color:#084298;
                    border-radius:6px;
                    font-weight:600;
                    text-align:center;">
                    ℹ️ Please select an order from the right panel to view billing details.
                </div>
            `);
        }

        return; // ⛔ STOP here
    }

    // ✅ ONLY runs when order exists
    fetchTotalAmount();
    paymenthistory();
    fetchbyid(customerid);
    autoLoadUtensils();
    applyOrderStatusLocks();
    togglePaymentOrRefundUI();
});

function closeRefundForm() {

    // Disable refund inputs
    $('#refund_amount').prop('readonly', true);
    $('#refund_pay_date').prop('disabled', true);
    $('#refund_pay_mode').prop('disabled', true);

    // Disable refund button
    $('button[onclick="refund()"]').prop('disabled', true).hide();

    // Remove old message if any
    $('#refund-closed-msg').remove();

    // Show ORANGE refund closed message
    $('#refund_section').append(`
           <div id="refund-closed-msg" style="
            margin-top:15px;
            padding:12px;
            background:#fff3cd;
            border:1px solid #ffb020;
            color:#8a4b00;
            border-radius:6px;
            font-weight:600;
            text-align:center;
         ">
            Refund closed. No further actions allowed.
              </div>
            `);
}

function openPaymentForm() {
    $('.form-row').show();

    $('#payment_amount_row').hide();

    $('#recovery_amount').prop('readonly', false);
    $('button[onclick="savepayment()"]').prop('disabled', false).show();

    $('#payment-closed-msg').remove();
}

function refund() {
    const customerId = document.querySelector(".customer_id").dataset.cid;
    const addressId = selectedAddressId;

    const refundorderdate = $('#refund_order_date').val();   // ✅ FIX
    const refundordertime = $('#refund_order_time').val();   // ✅ FIX

    const refundamount = Number($('#refund_amount').val());
    const refunddate = $('#refund_pay_date').val();
    const refundpaymode = $('#refund_pay_mode').val();

    if (!refundamount || refundamount <= 0) {
        showToast("Enter valid refund amount", "warning");
        return;
    }

    var payload = {
        load: "refund",
        customerId: customerId,
        addressId: addressId,
        refunddate: refunddate,
        refundamount: refundamount,
        refundpaymode: refundpaymode,

        // 🔥 THESE MUST MATCH PHP GLOBALS
        refundorderdate: refundorderdate,
        refundordertime: refundordertime
    };

    $.ajax({
        type: "POST",
        url: "./webservices/billingnew.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (response) {
            if (response && response.status === "success") {
                showToast("Refund Successful", "success");

                // Optional UI refresh
                fetchAllOrders(customerId, addressId);
            } else {
                showToast(response.message || "Refund Failed", "warning");
            }
        },
    });
}

function fetchTotalAmount() {

    const payload = {
        load: "fetchtotalamount",
        customerid: customerid,
        addressid: addressid,
        orderdate: orderdate,
        ordertime: ordertime
    };
    $.ajax({
        type: "POST",
        url: "./webservices/cateringservicespayments.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (response) {

            if (response.status !== "success") return;
            let orderdate = response.orderdate;
            let ordertime = response.ordertime;


            let grandTotal = Number(response.grand_total) || 0;
            let paidAmount = Number(response.paid_amount) || 0;
            let recovery = Number(response.recovery_amount) || 0;
            let balance = Number(response.amounttobe_paid) || 0;
            let refundamount = Number(response.refund_amount) || 0;

            $('#grand_total').val(grandTotal);
            $('#advance_amount').val(paidAmount);
            $('#amounttobe_paid').val(balance);
            $('#recovery_amount').val(recovery);
            $('#payment_amount').val(paidAmount);

            $('#refund_grand_total').val(grandTotal);
            $('#refund_advance_amount').val(paidAmount);
            $('#refund_amount').val(refundamount);
            $('#refund_order_date').val(orderdate);
            $('#refund_order_time').val(ordertime);



            // 🔒 PAYMENT CLOSED
            if (response.payment_status == 1) {
                closePaymentForm();
            }
            // 🔓 PAYMENT OPEN
            else {
                openPaymentForm();
                recalculatePayment();
            }

            if (Number(response.refund_status) === 1) {
                closeRefundForm();
            }



        },
    });
}

$(document).ready(function () {
    // Hide payment amount until payment is closed
    $('#payment_amount_row').hide();
});

function closePaymentForm() {

    $('.form-row').show();

    // Always hide advance amount
    $('#advance_amount').closest('.form-row').hide();

    // Hide payment-related inputs
    $('#amounttobe_paid').closest('.form-row').hide();
    $('#paid_amount').closest('.form-row').hide();
    $('#balance_amount').closest('.form-row').hide();
    $('#pay_mode').closest('.form-row').hide();
    $('#pay_date').closest('.form-row').hide();
    $('#totalamount_paid').closest('.form-row').hide();

    $('#recovery_amount').prop('readonly', true);

    // ❌ Disable & hide Pay button
    $('button[onclick="savepayment()"]').prop('disabled', true).hide();

    // ✅ SHOW payment amount ONLY when closed
    $('#payment_amount_row').show();

    if (!$('#payment-closed-msg').length) {
        $('.payment').append(`
            <div id="payment-closed-msg" style="
                margin-top:15px;
                padding:12px;
                background:#e6fffa;
                border:1px solid #20c997;
                color:#0f5132;
                border-radius:6px;
                font-weight:600;
                text-align:center;
            ">
                ✅ Order payment completed. Payment closed.
            </div>
        `);
    }
}


function fetchAllOrders(customerId, addressId) {

    var payload = {
        load: "allorders",
        customerid: customerId,
        addressid: addressId
    };

    $.ajax({
        type: "POST",
        url: "./webservices/billingnew.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        //     success: function (response) {

        //         const tbody = document.querySelector("#foodTypeTable tbody");
        //         tbody.innerHTML = "";

        //         if (response.code !== 200 || response.data.length === 0) {

        //             tbody.innerHTML = `
        //     <tr>
        //         <td colspan="5" style="text-align:center;">
        //             No orders found for this address
        //         </td>
        //     </tr>
        // `;

        //             // 🔥 No orders → reset UI
        //             resetOrderContext();
        //             return;
        //         }


        //         response.data.forEach(order => {

        //             const pending =
        //                 parseFloat(order.grand_total) - parseFloat(order.paid_amount);

        //             const tr = document.createElement("tr");
        //             tr.style.cursor = "pointer";

        //             tr.onclick = function () {
        //                 populatePaymentDetails(order);
        //                 togglePaymentOrRefundUI(order);


        //             };



        //             tr.innerHTML = `
        //                 <td>${order.order_date}</td>
        //                 <td>${order.order_time}</td>
        //                 <td>₹${order.grand_total}</td>
        //                 <td>₹${order.paid_amount}</td>
        //                 <td>₹${pending.toFixed(2)}</td>
        //             `;

        //             tbody.appendChild(tr);
        //         });
        //     },

        success: function (response) {

            const tbody = document.querySelector("#foodTypeTable tbody");
            tbody.innerHTML = "";

            // if (response.code !== 200 || response.data.length === 0) {
            //     resetOrderContext();
            //     return;
            // }

            if (response.code !== 200 || response.data.length === 0) {

                // 🟡 Clear ONLY orders table
                tbody.innerHTML = `
        <tr>
            <td colspan="5" style="text-align:center;color:#888;">
                No orders found for this address
            </td>
        </tr>
    `;

                // ✅ STILL SHOW PAYMENT DETAILS
                fetchTotalAmount();   // <-- THIS IS KEY
                $('#payment_section').show();
                $('#refund_section').hide();

                return;
            }


            response.data.forEach((order, index) => {

                const pending =
                    parseFloat(order.grand_total) - parseFloat(order.paid_amount);

                const tr = document.createElement("tr");
                tr.style.cursor = "pointer";

                tr.innerHTML = `
            <td>${order.order_date}</td>
            <td>${order.order_time}</td>
            <td>₹${order.grand_total}</td>
            <td>₹${order.paid_amount}</td>
            <td>₹${pending.toFixed(2)}</td>
        `;

                tr.onclick = function () {
                    populatePaymentDetails(order);
                    togglePaymentOrRefundUI(order);

                    if (Number(order.order_status) === 1) {
                        $('#payment_section').show();
                        $('#refund_section').hide();
                    } else {
                        $('#payment_section').hide();
                        $('#refund_section').show();
                    }
                    $('#payment-button').prop('disabled', false);
                    $('#refund_button').prop('disabled', false);
                };

                tbody.appendChild(tr);

                // 🔥 AUTO SELECT FIRST ORDER
                // if (index === 0) {
                // populatePaymentDetails(order);
                togglePaymentOrRefundUI(order);

                if (Number(order.order_status) === 1) {
                    $('#payment_section').show();
                    $('#refund_section').hide();
                } else {
                    $('#payment_section').hide();
                    $('#refund_section').show();
                }
                // }
            });

        },


        error: function () {
            showToast("Error loading orders", "error");
        }
    });
}

function populatePaymentDetails(order) {

    const grandTotal = Number(order.grand_total) || 0;
    const advancePaid = Number(order.paid_amount) || 0;
    const pending = grandTotal - advancePaid;

    $('#grand_total').val(grandTotal);
    $('#advance_amount').val(advancePaid);
    $('#amounttobe_paid').val(pending);
    $('#recovery_amount').val(0);
    $('#totalamount_paid').val(pending);
    $('#paid_amount').val("");

    $('#order_date').val(order.order_date);
    $('#order_time').val(order.order_time);

    localStorage.setItem("order_id", order.order_id);
    $('#payment-button').prop('disabled', false);
    $('#refund_button').prop('disabled', false);

}

$(document).ready(function () {
    $('#payment_section').hide();
    $('#refund_section').hide();
});

$('#recovery_amount').on('input', function () {
    const pending = Number($('#amounttobe_paid').val()) || 0;
    const recovery = Number($(this).val()) || 0;
    $('#totalamount_paid').val(pending + recovery);
});

function resetOrderContext() {

    // Clear order info
    $('#order_date').val('');
    $('#order_time').val('');
    $('#refund_order_date').val('');
    $('#refund_order_time').val('');

    // Clear payment fields
    $('#grand_total').val('');
    $('#advance_amount').val('');
    $('#amounttobe_paid').val('');
    $('#recovery_amount').val(0);
    $('#totalamount_paid').val('');
    $('#paid_amount').val('');

    // Clear refund fields
    $('#refund_grand_total').val('');
    $('#refund_advance_amount').val('');
    $('#refund_amount').val('');
    $('#refund_pay_date').val('');
    $('#refund_pay_mode').val('');

    // Clear dynamic inputs
    $('#dynamicInput').html('');
    $('#dynamicInput1').html('');

    // Hide sections
    $('#payment_section').hide();
    $('#refund_section').hide();

    // Clear stored order id
    localStorage.removeItem("order_id");


    $('#payment-button').prop('disabled', true);
    $('#refund_button').prop('disabled', true);

}


// ------------------------- print invoice ---------------------------------


// for print
function prepareAndPrintInvoice() {
    console.log("Prepare and Print Invoice Called");
    const customerid = document.querySelector(".customer_id").dataset.cid;
    const addressid = selectedAddressId;
    // const customerid = document.getElementById("customer_id").value;
    // const addressid = .value;
    const orderdate = document.getElementById("order_date").value;
    const ordertime = document.getElementById("order_time").value;



    if (!customerid) {
        alert("Please select Customer");
        return;
    }


    const payload = {
        load: "loadinvoice",
        customerid,
        addressid,
        orderdate,
        ordertime
    };

    console.log("Prepare and Print Invoice Called payload:", payload);

    fetch("./webservices/invoicegenerate.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(payload)
    })
        .then(res => res.json())
        .then(response => {
            console.log("Prepare and Print Invoice Called response:", response);
            if (response.code !== 200) {
                alert(response.status || "Invoice generation failed");
                return;
            }

            generateTaxInvoice(response.data);
        })
        .catch(err => {
            console.error("Invoice Fetch Error:", err);
            alert("Server error");
        });
}

function generateTaxInvoice(data) {

    if (!data) {
        alert("No invoice data received");
        return;
    }

    // ✅ STORE window reference
    const printWindow = window.open(
        "invoicegenerate.html", // A4 print template
        "_blank",
        "width=794,height=1123"
    );

    printWindow.onload = function () {

        const d = printWindow.document;

        /* SAFETY CHECK */
        if (!d.getElementById("orgName")) {
            alert("Print template not loaded correctly");
            return;
        }

        /* ================= ORGANIZATION ================= */
        d.getElementById("orgName").innerText = data.organization.name;
        d.getElementById("orgAddress").innerText = data.organization.address;
        d.getElementById("orgGstin").innerText = data.organization.gstin;
        d.getElementById("orgCin").innerText = data.organization.cin;
        d.getElementById("orgPan").innerText = data.organization.pan;
        d.getElementById("orgPhone").innerText = data.organization.phone;
        d.getElementById("orgEmail").innerText = data.organization.email;

        d.getElementById("invoiceNo").innerText = data.invoice.invoice_no;
        d.getElementById("invoiceDate").innerText = data.invoice.invoice_date;

        /* ================= CUSTOMER ================= */
        d.getElementById("billName").innerText = data.customer.CustomerName;
        d.getElementById("billPhone").innerText = data.customer.Phone1;
        d.getElementById("billphno").innerText = data.customer.address_ph_number;
        d.getElementById("billAddress").innerText = data.customer.address;

        /* ================= ITEMS ================= */
        const tbody = d.getElementById("itemsBody");
        tbody.innerHTML = "";

        let totals = {
            qty: 0,
            amount: 0,
            cgst: 0,
            sgst: 0,
            igst: 0,
            grand: 0
        };
        let paidTotal = 0;

        data.orders.forEach((o, i) => {

            const baseAmount =
                Number(o.total_amount) + Number(o.services_amount);

            const cgst = baseAmount * data.tax.cgst / 100;
            const sgst = baseAmount * data.tax.sgst / 100;
            const igst = baseAmount * data.tax.igst / 100;

            const total = baseAmount + cgst + sgst + igst;

            totals.qty += Number(o.order_count);
            totals.amount += baseAmount;
            totals.cgst += cgst;
            totals.sgst += sgst;
            totals.igst += igst;
            totals.grand += total;

            paidTotal += Number(o.paid_amount);

            tbody.insertAdjacentHTML("beforeend", `
        <tr>
            <td>${i + 1}</td>
            <td>996337</td>
            <td>${o.order_date}</td>
            <td>${o.order_time}</td>
            <td class="right">${o.total_amount}</td>
            <td class="right">${o.services_amount}</td>
            <td class="right">${cgst.toFixed(2)}</td>
            <td class="right">${sgst.toFixed(2)}</td>
            <td class="right">${igst.toFixed(2)}</td>
            <td class="right">${total.toFixed(2)}</td>
        </tr>
    `);
        });


        /* ================= TOTALS ================= */
        // d.getElementById("tQty").innerText = totals.qty;
        // d.getElementById("tAmount").innerText = totals.amount.toFixed(2);
        // d.getElementById("tCgst").innerText = totals.cgst.toFixed(2);
        // d.getElementById("tSgst").innerText = totals.sgst.toFixed(2);
        // d.getElementById("tIgst").innerText = totals.igst.toFixed(2);
        // d.getElementById("tGrand").innerText = totals.grand.toFixed(2);

        d.getElementById("sumTotal").innerText = totals.grand.toFixed(2);
        // d.getElementById("paidAmount").innerText = paidTotal.toFixed(2);
        // d.getElementById("balanceAmount").innerText =
        //     (totals.grand - paidTotal).toFixed(2);

        /* ================= PRINT ================= */
        setTimeout(() => {
            printWindow.print();
            printWindow.onafterprint = () => printWindow.close();
        }, 500);
    };
}












