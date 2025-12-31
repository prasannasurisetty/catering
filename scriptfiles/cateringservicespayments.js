let alreadyPaidAmount = 0;
let utensils_id = null;


var customerid = localStorage.getItem('customerid');
var addressid = localStorage.getItem('addressid');
var orderdate = localStorage.getItem('orderdate');
var ordertime = localStorage.getItem('ordertime');
var grandtotal = localStorage.getItem('grandtotal');

console.log("printtt", customerid, addressid, orderdate, ordertime, grandtotal);


$(document).ready(function () {

    if (!customerid || !addressid || !orderdate || !ordertime) {
        alert("Invalid order context. Please go back and select order again.");
        return;
    }

    // ✅ SET TOTAL AMOUNT FROM LOCALSTORAGE
    // $('#total_amount').val(grandtotal);
    fetchTotalAmount();

    // paymentdetails();
    paymenthistory();
    fetchbyid(customerid);
    autoLoadUtensils();


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

    // 3️⃣ Balance calculation
    let balance = totalAmountPaid - paidAmount;
    if (balance < 0) balance = 0;

    $('#balance_amount').val(balance);
}

$('#recovery_amount, #paid_amount').on('input', function () {
    recalculatePayment();
});


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

            let grandTotal = Number(response.grand_total) || 0;
            let paidAmount = Number(response.paid_amount) || 0;
            let recovery = Number(response.recovery_amount) || 0;
            let balance = Number(response.amounttobe_paid) || 0;

            $('#grand_total').val(grandTotal);
            $('#advance_amount').val(paidAmount);
            $('#amounttobe_paid').val(balance);
            $('#recovery_amount').val(recovery);
            $('#payment_amount').val(paidAmount);


            // 🔒 PAYMENT CLOSED
            if (response.payment_status == 1) {
                closePaymentForm();
            }
            // 🔓 PAYMENT OPEN
            else {
                openPaymentForm();
                recalculatePayment();
            }
        },

        error: function () {
            alert("Error fetching total amount");
        }
    });
}
$(document).ready(function () {

    // Hide payment amount until payment is closed
    $('#payment_amount_row').hide();
});


function openPaymentForm() {

    $('.form-row').show();

    // Always hide advance amount
    // $('#advance_amount').closest('.form-row').hide();

    // ❌ Hide payment amount when payment is OPEN
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













function savepayment() {
    console.log("Save payments");

    if (
        !$('#paid_amount').val() ||
        !$('#pay_mode').val() ||
        !$('#pay_date').val()
    ) {
        alert("Please fill all payment fields");
        return;
    }

    const payload = {
        load: "savepayment",
        customerid: customerid,
        addressid: addressid,
        orderdate: orderdate, // ✅ REQUIRED
        ordertime: ordertime, // ✅ REQUIRED
        recoveryamt: Number($('#recovery_amount').val()),
        paidamount: Number($('#paid_amount').val()),
        paymode: $('#pay_mode').val(),
        paydate: $('#pay_date').val()
    };
    console.log("Save payments payload", payload);

    $.ajax({
        type: "POST",
        url: "./webservices/cateringservicespayments.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (response) {
            console.log("Save payments response", response);

            if (response && response.status === "success") {

                alert("Payment successful");

                // ✅ UPDATE TOTALS
                $('#total_amount').val(response.grand_total ?? $('#total_amount').val());
                $('#paid_amount').val("");
                // 🔁 REFRESH HISTORY
                paymenthistory();
                fetchTotalAmount();

            } else {
                alert(response.message || "Payment failed");
            }
        },

        error: function (xhr, status, error) {
            console.error("Payment error:", error);
            alert("Error while processing payment");
        }
    });
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
                const gtE1 = document.querySelector(".grandtotal");


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
                if (gtE1) {
                    gtE1.textContent = `Grand Total : ${grandtotal}`;
                }


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
    }

    $.ajax({
        type: "POST",
        url: "./webservices/catering.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",
        success: function (response) {

            const container = document.getElementById("ordersList");
            container.innerHTML = "";

            if (response.code !== 200 || !response.data) {
                container.innerHTML = "<p>No orders found</p>";
                return;
            }

            response.data.forEach(order => {
                const div = document.createElement("div");
                div.className = "order-card";
                div.innerHTML = `
                    <h4>Order - ${order.order_id}</h4>
                    <p><b>Customer:</b> ${order.customer_id}</p>
                    <p><b>Address:</b> ${order.address_id}</p>
                    <p><b>Date:</b> ${order.order_date}</p>
                    <p><b>Total:</b> ₹${order.grand_total}</p>
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



function addUtensilRow(name = "", issued = "", returned = "") {

    const container = document.getElementById("utensils_container");

    const row = document.createElement("div");
    row.className = "utensil-row";

    row.innerHTML = `
            <input type="text" placeholder="Item Name" value="${name}">
            <input type="number" placeholder="Issued Qty" value="${issued}">
            <input type="number" placeholder="Returned Qty" value="${returned}">
           <i class="fa fa-trash" id="remove-utensil" onclick="removeUtensilRow(this)"></i>
           `;

    container.appendChild(row);
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
    console.log('1.utensils function');

    if (!utensils_id) {
        utensils_id = getNextUtensilsId(); // or Date.now()
    }

    const utensils = [];

    document.querySelectorAll("#utensils_container .utensil-row").forEach(row => {
        const inputs = row.querySelectorAll("input");

        const name = inputs[0].value.trim();
        const issued = Number(inputs[1].value) || 0;
        const returned = Number(inputs[2].value) || 0;

        if (!name || issued <= 0) return;

        utensils.push({
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
        customerid: customerid,
        addressid: addressid,
        orderdate: orderdate,
        ordertime: ordertime,
        utensils_id: utensils_id,
        utensils: utensils
    };

    console.log('1.utensils payload', payload);

    $.ajax({
        type: "POST",
        url: "./webservices/cateringservicespayments.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (res) {
            console.log('1.utensils res', res);
            if (res.status === "success") {
                alert("Utensils saved successfully");
                loadIssuedUtensils(customerid, addressid, utensils_id);
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

document.getElementById("save-utensils-btn")
    .addEventListener("click", saveUtensils);

function clearUtensilsUI() {
    document.getElementById("utensils_container").innerHTML = "";
    addUtensilRow();
}


function autoLoadUtensils() {
    console.log("auto load");

    const payload = {
        load: "fetchutensils",
        customerid: customerid,
        addressid: addressid,
        orderdate: orderdate,
        ordertime: ordertime
    };
    console.log("auto load payload", payload);

    $.ajax({
        type: "POST",
        url: "./webservices/cateringservicespayments.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (res) {
            console.log("auto load res", res);

            if (res.status !== "success") return;

            // 🔹 No utensils issued yet
            if (!res.utensils_id || res.data.length === 0) {
                console.log("No utensils issued yet");
                return;
            }

            // 🔹 Store billing_id globally (important for save/update)
            utensils_id = res.utensils_id;

            const container = document.getElementById("utensils_container");
            container.innerHTML = "";

            res.data.forEach(item => {
                addUtensilRow(
                    item.utensils_name,
                    item.issued_qty,
                    item.returned_qty
                );
            });

            // 🔒 Lock issued qty
            document
                .querySelectorAll("#utensils_container .utensil-row input:nth-child(2)")
                .forEach(inp => inp.readOnly = true);
        }
    });
}


function loadpaymode() {
    // console.log("1.loadpaymode...");

    var payload = {
        load: "loadpaymode",
        sno: "",
        type: "type"
    };

    $.ajax({
        type: "POST",
        url: "./webservices/cateringservicespayments.php",
        data: JSON.stringify(payload),
        dataType: "json",
        contentType: "application/json",
        beforeSend: function () {
            // console.log("Fetching food types...");
        },
        success: function (response) {
            // console.log("3.load paymode Response received:", response);

            let dropdown = document.getElementById("pay_mode");
            dropdown.innerHTML = ""; // Clear existing options

            let defaultOption = document.createElement('option');
            defaultOption.value = "";
            defaultOption.text = "Select paymode";
            // defaultOption.disabled = true;
            defaultOption.selected = true;
            dropdown.appendChild(defaultOption);

            if (response.status === "success" && response.data.length > 0) {
                response.data.forEach(x => {
                    let option = document.createElement('option');
                    option.value = x.sno;
                    option.text = x.type;
                    dropdown.appendChild(option);
                });
            } else {
                console.warn("No paymode found.");
                let noDataOption = document.createElement('option');
                noDataOption.value = "";
                noDataOption.text = "No types available";
                noDataOption.disabled = true;
                dropdown.appendChild(noDataOption);
            }
        },
        error: function (err) {
            console.error("Error loading paymode:", err);
            alert("Failed to load  paymode. Please try again later.");
        }
    });
}
loadpaymode();



function delieveredstatus(isDelivered = false) {

    console.log("delieveredstatus");

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

    console.log("deliveredstatus payload", payload);

    $.ajax({
        type: "POST",
        url: "./webservices/cateringservicespayments.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (res) {
            console.log("delivered status response", res);
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
