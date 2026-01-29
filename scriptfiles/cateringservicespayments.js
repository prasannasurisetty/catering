let alreadyPaidAmount = 0;
let utensils_id = null;


let customerid = localStorage.getItem('customerid');
let addressid = localStorage.getItem('addressid');
let orderdate = localStorage.getItem('orderdate');
let ordertime = localStorage.getItem('ordertime');
let order_id = localStorage.getItem('order_id');
let order_status = Number(localStorage.getItem('orderstatus'));



// console.log("printtt", customerid, addressid, orderdate, ordertime, order_status);


function applyOrderStatusLocks() {

    // 🔓 RESET FIRST
    $('.add-utensils-text').css({ 'pointer-events': '', 'opacity': '' });
    $('#utensils_container input').prop('disabled', false);
    $('#utensils_container i.fa-trash').css({ 'pointer-events': '', 'opacity': '' });
    $('#save-utensils-btn').prop('disabled', false).css('opacity', '');
    $('#delivered_time').prop('disabled', false);
    $('#delivered').prop('disabled', false).css('opacity', '');
    $('#order-cancelled-msg').remove();

    // 0 = cancelled
    if (order_status === 0) {

        /* ======================
           🔒 DISABLE UTENSILS
        ====================== */

        // Add utensil button
        $('.add-utensils-text')
            .css('pointer-events', 'none')
            .css('opacity', '0.5');

        // Utensil inputs
        $('#utensils_container input')
            .prop('disabled', true);

        // Trash icon
        $('#utensils_container i.fa-trash')
            .css('pointer-events', 'none')
            .css('opacity', '0.4');

        // Save utensils button
        $('#save-utensils-btn')
            .prop('disabled', true)
            .css('opacity', '0.6');

        /* ======================
           🔒 DISABLE DELIVERY
        ====================== */

        $('#delivered_time')
            .prop('disabled', true);

        $('#delivered')
            .prop('disabled', true)
            .css('opacity', '0.6');

        /* ======================
           🔒 OPTIONAL INFO MESSAGE
        ====================== */

        if (!$('#order-cancelled-msg').length) {
            $('.order-utensils').prepend(`
                <div id="order-cancelled-msg" style="
                    margin-bottom:10px;
                    padding:10px;
                    background:#fff3cd;
                    border:1px solid #ffecb5;
                    color:#664d03;
                    font-weight:600;
                    text-align:center;
                    border-radius:6px;">
                    ⚠️ Order is cancelled. Editing is disabled.
                </div>
            `);
        }
    }
}


function hasOrderContext() {
    // return customerid && addressid && orderdate && ordertime && order_id;
}

$(document).ready(function () {

    if (!hasOrderContext()) {


        $('.order-utensils').hide();
        $('#payment_section').hide();
        $('#refund_section').hide();


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
    applyOrderStatusLocks();
    togglePaymentOrRefundUI();
});


function recalculatePayment() {

    let amountToBePaid = Number($('#amounttobe_paid').val()) || 0;
    let recoveryAmount = Number($('#recovery_amount').val()) || 0;
    let paidAmount = Number($('#paid_amount').val()) || 0;

    // 1️⃣ Default total amount = amount to be paid
    let totalAmountPaid = amountToBePaid;

    // 2️⃣ Add recovery ONLY if entered
    if (recoveryAmount > 0) {
        totalAmountPaid += recoveryAmount;
    }

    $('#totalamount_paid').val(totalAmountPaid);

}

$('#recovery_amount, #paid_amount').on('input', function () {
    recalculatePayment();
});

function openPaymentForm() {

    $('.form-row').show();

    $('#payment_amount_row').hide();

    $('#recovery_amount').prop('readonly', false);
    $('button[onclick="savepayment()"]').prop('disabled', false).show();

    $('#payment-closed-msg').remove();
}

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

function paymenthistory() {


    var payload = {
        load: "paymenthistory",
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

            const paymenthistorydiv = document.querySelector('.payment-history');
            paymenthistorydiv.innerHTML = "";

            if (
                !response ||
                response.status !== "success" ||
                !Array.isArray(response.data) ||
                response.data.length === 0
            ) {
                paymenthistorydiv.innerHTML = "<p>No payment history found.</p>";
                return;
            }

            // ✅ ADD
            alreadyPaidAmount = 0;

            response.data.forEach((row, index) => {

                // ✅ ADD
                alreadyPaidAmount += Number(row.paid_amount) || 0;

                const div = document.createElement("div");
                div.className = "payment-history-row";

                div.innerHTML = `
            <span>${index + 1}.</span>
            <span><b>Paid Date:</b>${row.paid_date}</span>,
            <span><b>Paid Amount:</b>₹${row.paid_amount}</span>,
            <span><b>Pay Mode:</b>${row.pay_mode}</span>
           `;

                paymenthistorydiv.appendChild(div);
            });

            // ✅ ADD
            let totalamount = Number($('#total_amount').val()) || 0;
            // let balance = totalamount - alreadyPaidAmount;
            // $('#balance_amount').val(balance);
        },


        error: function (xhr, status, error) {
            console.error("Payment history error:", error);
            alert("Server error while fetching history");
        }
    });
}


function fetchbyid(customerid) {
    const payload = {
        load: "fetchbyid",
        customerid: customerid,
    };

    $.ajax({
        type: "POST",
        url: "./webservices/register.php",
        data: JSON.stringify(payload),
        dataType: "json",
        success: function (response) {
            if (response.status === "Success" && response.data && response.data[0]) {
                const customer = response.data[0];

                const recmdEl = document.querySelector(".recmd");
                if (recmdEl) recmdEl.style.display = "none";

                const searchEl = document.querySelector(".search_input");
                if (searchEl) searchEl.value = "";

                const idEl = document.querySelector(".customer_id");
                const nameEl = document.querySelector(".customer_name");
                const phEl = document.querySelector(".customer_ph");
                const odE1 = document.querySelector(".orderdate");
                const otE1 = document.querySelector(".ordertime");
                // const gtE1 = document.querySelector(".grandtotal");


                if (idEl) {
                    idEl.textContent = `ID :${customer.CustomerID}`;
                    idEl.dataset.cid = customer.CustomerID;
                }
                if (nameEl) {
                    nameEl.textContent = `Name :${customer.CustomerName}`;
                    nameEl.setAttribute("title", customer.CustomerName);
                }
                if (phEl) {
                    phEl.textContent = `PH Number :${customer.Phone1}`;
                }
                if (odE1) {
                    odE1.textContent = `Order Date : ${orderdate}`;
                }
                if (otE1) {
                    otE1.textContent = `Order Time : ${ordertime}`;
                }
                // if (gtE1) {
                //     gtE1.textContent = `Grand Total : ${grandtotal}`;
                // }


                // Load addresses for this customer
                // loadAddress();



                const co = document.querySelector(".catering-ordering");
                if (co) co.style.display = "flex";
                document.getElementById("addresses_container").style.display = "flex";

                document.querySelector(".menu-order").style.display = "flex";

            } else {
                alert("NO DATA FOUND");
                document.querySelector(".catering-ordering").style.display = "none";
                document.getElementById("addresses_container").style.display = "none";
                document.getElementById("menu-order").style.display = "none";
            }
        },
        error: function (err) {
            alert("Something wrong");

        },
    });
}

function fetchAllOrders() {

    var payload = {
        load: "allorders"
    };

    $.ajax({
        type: "POST",
        url: "./webservices/catering.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (response) {

            const container = document.getElementById("ordersList");
            container.innerHTML = "";

            if (
                response.code !== 200 ||
                !Array.isArray(response.data) ||
                response.data.length === 0
            ) {
                container.innerHTML = "<p>No orders found</p>";
                return;
            }


            response.data.forEach(order => {
                const div = document.createElement("div");
                div.className = "order-card";
                div.onclick = function () {
                    triggermenudetails(
                        order.customer_id,
                        order.order_date,
                        order.order_time,
                        order.order_status,
                        order.address_id,
                        order.order_id
                    );
                };


                div.innerHTML = `
                    <p><b>Date:</b> ${order.order_date}</p>
                    <p><b>Time:</b> ${order.order_time}</p>
                    <p><b>Customer ID:</b> ${order.customer_id}</p>
                    <p><b>Customer Name:</b> ${order.CustomerName}</p>
                    <p><b>Phone No:</b> ${order.address_ph_number}</p>
                    <p><b>Address:</b> ${order.full_address}</p>
                `;

                container.appendChild(div);
            });
        },

        error: function () {
            alert("Error loading orders");
        }
    });
}


fetchAllOrders();
fetchbyid(customerid);



function addUtensilRow(sno = null, name = "", issued = "", returned = "") {

    if (order_status === 0) {
        alert("Order is cancelled. Cannot add utensils.");
        return;
    }

    const container = document.getElementById("utensils_container");

    const row = document.createElement("div");
    row.className = "utensil-row";

    // 🔥 THIS IS THE KEY
    if (sno) row.dataset.sno = sno;

    row.innerHTML = `
      <input type="text"
       value="${name}"
       onblur="checkDuplicateUtensil(this)">
        <input type="number" value="${issued}">
        <input type="number" value="${returned}">
        <i class="fa fa-trash" id="remove-utensil"onclick="removeUtensilRow(this)"></i>
    `;

    container.appendChild(row);
}

function checkDuplicateUtensil(input) {
    const value = input.value.trim().toLowerCase();
    if (!value) return;

    let count = 0;

    document
        .querySelectorAll("#utensils_container .utensil-row input[type='text']")
        .forEach(inp => {
            if (inp.value.trim().toLowerCase() === value) {
                count++;
            }
        });

    if (count > 1) {
        alert("This utensil is already added.");
        input.value = "";
        input.focus();
    }
}




// default one row
document.addEventListener("DOMContentLoaded", function () {
    addUtensilRow();
});

function removeUtensilRow(el) {
    el.parentElement.remove();
}

function getNextUtensilsId() {
    let id = Number(localStorage.getItem("utensils_id_counter")) || 0;
    id += 1;
    localStorage.setItem("utensils_id_counter", id);
    return id;
}


function saveUtensils() {


    if (order_status === 0) {
        alert("Order is cancelled. Cannot save utensils.");
        return;
    }

    if (!order_id) {
        alert("Order ID missing");
        return;
    }

    if (!utensils_id) {
        utensils_id = getNextUtensilsId();
    }

    const utensils = [];

    document.querySelectorAll("#utensils_container .utensil-row").forEach(row => {

        const sno = row.dataset.sno ? Number(row.dataset.sno) : null;
        const inputs = row.querySelectorAll("input");

        const name = inputs[0].value.trim();
        const issued = Number(inputs[1].value) || 0;
        const returned = Number(inputs[2].value) || 0;

        if (!name || issued <= 0) return;

        utensils.push({
            sno: sno,
            utensils_name: name,
            issued_qty: issued,
            returned_qty: returned
        });
    });

    if (utensils.length === 0) {
        alert("No valid utensils to save");
        return;
    }

    const payload = {
        load: "addutensils",
        order_id: order_id,
        utensils_id: utensils_id,
        utensils: utensils
    };



    $.ajax({
        type: "POST",
        url: "./webservices/cateringservicespayments.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (res) {

            if (res.status === "success") {
                alert("Utensils saved successfully");
                loadIssuedUtensils(order_id, utensils_id);
                clearUtensilsUI();
            } else {
                alert(res.message || "Failed to save utensils");
            }
        },

        error: function () {
            alert("Server error while saving utensils");
        }
    });
}

document
    .getElementById("save-utensils-btn")
    .addEventListener("click", saveUtensils);

function loadIssuedUtensils(order_id, utensils_id) {

    const payload = {
        load: "loadissuedutensils",
        order_id: order_id,
        utensils_id: utensils_id
    };

    $.ajax({
        type: "POST",
        url: "./webservices/cateringservicespayments.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (res) {

            if (res.code !== 200) return;

            const container = document.getElementById("utensils_container");
            container.innerHTML = "";

            res.data.forEach(item => {

                const row = document.createElement("div");
                row.className = "utensil-row";
                row.dataset.sno = item.sno;

                row.innerHTML = `
                    <input type="text" value="${item.utensils_name}">
                    <input type="number" value="${item.issued_qty}">
                    <input type="number" value="${item.returned_qty}">
                    <button onclick="removeUtensilRow(this)">✖</button>
                `;

                container.appendChild(row);
            });
        }
    });
}

function clearUtensilsUI() {
    document.getElementById("utensils_container").innerHTML = "";
    addUtensilRow();
}


function autoLoadUtensils() {


    order_id = Number(order_id);
    if (!Number.isInteger(order_id) || order_id <= 0) return;

    const payload = {
        load: "fetchutensils",
        order_id: order_id
    };

    $.ajax({
        type: "POST",
        url: "./webservices/cateringservicespayments.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (res) {
            if (res.status !== "success") return;

            const container = document.getElementById("utensils_container");
            container.innerHTML = "";

            // 🔹 NO UTENSILS → RESET
            if (!res.utensils_id || res.data.length === 0) {
                console.log("No utensils for this order → reset");

                utensils_id = null;
                addUtensilRow();
                return;
            }

            // 🔹 UTENSILS EXIST
            utensils_id = res.utensils_id;

            res.data.forEach(item => {
                addUtensilRow(
                    item.sno,
                    item.utensils_name,
                    item.issued_qty,
                    item.returned_qty
                );
            });


        }
    });
}







function delieveredstatus(isDelivered = false) {

    // console.log("delieveredstatus");

    if (order_status === 0) {
        alert("Order is cancelled. Delivery not allowed.");
        return;
    }

    const deliveredtime = document.getElementById('delivered_time').value;

    if (!deliveredtime) {
        alert("Select out for delivery time");
        return;
    }

    var payload = {
        load: "deliveredstatus",
        customerid: customerid,
        addressid: addressid,
        orderdate: orderdate,
        ordertime: ordertime,
        deliveredtime: deliveredtime,
        delivered: isDelivered ? 1 : 0
    };

    // console.log("deliveredstatus payload", payload);

    $.ajax({
        type: "POST",
        url: "./webservices/cateringservicespayments.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (res) {
            // console.log("delivered status response", res);
            if (res.status === "success") {
                alert(isDelivered ? "Order Delivered" : "Marked Out For Delivery");
            } else {
                alert(res.message || "Failed to update delivery status");
            }

        },


        error: function () {
            alert("Server error");
        }
    });
}

function togglePaymentOrRefundUI() {

    const status = Number(order_status);

    if (status === 1) {
        // ✅ Active order → Payments
        $('#payment_section').css('display', 'block');
        $('#refund_section').css('display', 'none');
    }
    else if (status === 0) {
        // ❌ Cancelled order → Refund
        $('#payment_section').css('display', 'none');
        $('#refund_section').css('display', 'block');

        // Auto-fill refund values
        $('#refund_grand_total').val($('#grand_total').val());
        $('#refund_advance_amount').val($('#advance_amount').val());
        $('#refund_amount').val($('#advance_amount').val());
    }
}



function setpaymentvariables() {
    // localstorage variables
    const grandtotal = Number(document.getElementById("grand_total").value) || 0;
    const orderdate = document.querySelector("input[type='date']").value;
    const ordertime = document.querySelector("input[type='time']").value;
    const customerid = document.querySelector(".customer_id")?.dataset.cid;


    const addressid =
        document.querySelector('.address_block.highlight-address')
            ?.getAttribute("data-block-aid");



    localStorage.setItem("customerid", customerid);
    localStorage.setItem("addressid", addressid);
    localStorage.setItem("orderdate", orderdate);
    localStorage.setItem("ordertime", ordertime);
    localStorage.setItem("grandtotal", grandtotal);
    window.location.href = "catering.php";


    let redirectVariable = localStorage.getItem('orderstatus');
    if (redirectVariable) {
        let confirmMsg = confirm('Back to homepage');
        if (confirmMsg) {
            localStorage.setItem('orderstatus', null);
            localStorage.href = 'catering.php';
        }
        localStorage.setItem('orderstatus', null);
    }


}




function refund() {
    // console.log("refund function");

    refundamount = Number($('#refund_amount').val());
    refunddate = $('#refund_pay_date').val();
    refundpaymode = $('#refund_pay_mode').val();


    var payload = {
        load: "refund",
        customerid: customerid,
        addressid: addressid,
        refunddate: refunddate,
        refundamount: refundamount,
        refundpaymode: refundpaymode,
        orderdate: orderdate,
        ordertime: ordertime
    }
    // console.log("refund function payload", payload);
    $.ajax({
        type: "POST",
        url: "./webservices/cateringservicespayments.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",
        success: function (response) {
            // console.log("refund function response", response);

            if (response && response.status === "success") {
                alert("Refund Successful");
            }
            else {
                alert(response.message || "Payment Failed");
            }
        },
        error: function (xhr, status, error) {
            // console.log("Payment error:", error);
            alert("Error while processing payment");
        }
    })
}


function triggermenudetails(cid, odate, otime, status, aid, oid) {

    order_id = Number(oid);

    if (!Number.isInteger(order_id)) {
        console.error("❌ INVALID order_id received:", oid);
        return; // ⛔ stop here
    }

    // Update global state
    customerid = String(cid).trim();
    orderdate = String(odate).trim();
    ordertime = String(otime).trim().substring(0, 5);
    addressid = Number(aid);
    order_status = Number(status);

    // Persist
    localStorage.setItem("order_id", order_id);
    localStorage.setItem("customerid", customerid);
    localStorage.setItem("orderdate", orderdate);
    localStorage.setItem("ordertime", ordertime);
    localStorage.setItem("addressid", addressid);
    localStorage.setItem("orderstatus", order_status);

    // console.log("✅ Selected order_id:", order_id);

    $('#select-order-msg').remove();
    $('.order-utensils').show();
    $('#payment_section').show();

    $('.orderdate').text(`Order Date : ${orderdate}`);
    $('.ordertime').text(`Order Time : ${ordertime}`);

    fetchbyid(customerid);
    fetchTotalAmount();
    paymenthistory();
    autoLoadUtensils();
    applyOrderStatusLocks();
    togglePaymentOrRefundUI();
}



function fetchTotalAmount() {

    const payload = {
        load: "fetchtotalamount",
        customerid: customerid,
        addressid: addressid,
        orderdate: orderdate,
        ordertime: ordertime
    };
    // console.log("fetchamounttttt", payload);

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

        error: function () {
            alert("Error fetching total amount");
        }
    });
}



//--------------------- printing challans-------------

function generatechallansdata() {
    console.log("generate challans data function");

    var payload = {
        load: "loadchallan",
        orderdate,
        ordertime,
        customerid,
        addressid
    };

    console.log("generate challans payload", payload);

    fetch("./webservices/challan.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(payload)
    })
        .then(res => res.json())
        .then(response => {
            console.log("Prepare and Print challan response:", response);

            if (response.code !== 200) {
                alert(response.status || "challan generation failed");
                return;
            }

            // ✅ PASS FULL RESPONSE OBJECT
            generateprintchallan(response);
        })
        .catch(err => {
            console.error("Error loading challan", err);
            alert("Failed to load challan data");
        });
}


function generateprintchallan(data) {
    console.log("generateprintchallan called", data);

    if (!data) {
        alert("No challan data received");
        return;
    }

    const printWindow = window.open(
        "challan.html",
        "_blank",
        "width=794,height=1123"
    );

    printWindow.onload = function () {

        const d = printWindow.document;

        /* ================= SAFETY CHECK ================= */
        if (!d.querySelector(".org_name")) {
            alert("Print template not loaded correctly");
            return;
        }

        /* ================= ORGANIZATION ================= */
        d.querySelectorAll(".org_name").forEach(e => e.innerText = data.organization.name);
        d.querySelectorAll(".org_address").forEach(e => e.innerText = data.organization.address);
        d.querySelectorAll(".org_city").forEach(e => e.innerText = data.organization.city);
        d.querySelectorAll(".org_extra").forEach(e =>
            e.innerText = `Phone: ${data.organization.phone} | GST: ${data.organization.gstin}`
        );

        /* ================= DATE & TIME ================= */
        const now = new Date();
        const date = now.toLocaleDateString("en-GB");
        const time = now.toLocaleTimeString();

        /* ================= CUSTOMER ================= */
        d.getElementById("r_customer").innerText = data.customer.name;
        d.getElementById("r_phone").innerText = data.customer.phone;
        d.getElementById("r_address").innerText = data.customer.address;

        d.getElementById("u_customer").innerText = data.customer.name;
        d.getElementById("u_phone").innerText = data.customer.phone;
        d.getElementById("u_address").innerText = data.customer.address;

        /* ================= CHALLAN NUMBERS ================= */
        d.getElementById("r_challan_no").innerText = data.returnable.challan_no;
        d.getElementById("u_challan_no").innerText = data.unreturnable.challan_no;

        d.getElementById("r_date").innerText = date;
        d.getElementById("r_time").innerText = time;
        d.getElementById("u_date").innerText = date;
        d.getElementById("u_time").innerText = time;

        /* ================= RETURNABLE ITEMS ================= */
        const rBody = d.getElementById("returnable_items");
        rBody.innerHTML = "";

        let totalQty = 0;

        data.returnable.items.forEach((item, index) => {
            totalQty += parseInt(item.qty, 10);

            rBody.innerHTML += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.name}</td>
                    <td>${item.qty}</td>
                </tr>
            `;
        });

        d.getElementById("total_returnable_qty").innerText = totalQty;

        /* ================= UNRETURNABLE SERVICES ================= */
        const uBody = d.getElementById("unreturnable_items");
        uBody.innerHTML = "";

        let totalAmount = 0;

        data.unreturnable.services.forEach((service, index) => {
            totalAmount += parseFloat(service.amount);

            uBody.innerHTML += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${service.name}</td>
                    <td>₹ ${service.amount}</td>
                </tr>
            `;
        });

        d.getElementById("total_amount").innerText = totalAmount;

        /* ================= PRINT ================= */
        setTimeout(() => {
            printWindow.print();
            printWindow.onafterprint = () => printWindow.close();
        }, 500);
    };
}































