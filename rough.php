<!DOCTYPE html>

<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Catering Services Payments</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel=" stylesheet" type="text/css" href="css/cateringservicespayments.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="scriptfiles/cateringservices.js" defer></script>

</head>

<body>
    <div class="container">
        <?php include "navbar.php"; ?>
        <div class="customer_search">

            <div class="customer_details">
                <p class="backpage"><i class="fa-solid fa-arrow-left" style="color:white;font-weight:bold;"></i></p>
                <p class="customer_id"></p>
                <p class="customer_name"></p>
                <p class="customer_ph"></p>
                <p class="orderdate"></p>
                <p class="ordertime"></p>
                <p class="grandtotal"></p>

                <!-- <p class="register_button" onclick="setRedirectVariable()">
                    <a href="userregistration.php">
                        <i class="fa-regular fa-user" style="color:white"></i>
                    </a>
                </p> -->
            </div>


        </div>
        <div class="order-details">
            <div class="payment-container">
                <div class="order-payments contain">
                    <div class="payment subcontain">
                        <div class="form-group">
                            <div class="form-row">
                                <label><b>Total Amount:</b></label>
                                <input type="number" id="total_amount" placeholder="Total Amount" readonly>
                            </div>
                            <div class="form-row">
                                <label>Paid Amount:</label>
                                <input type="number" id="paid_amount" placeholder="0">
                            </div>
                            <div class="form-row">
                                <label>Balance Amount:</label>
                                <input type="number" id="balance_amount" placeholder="0" readonly>
                            </div>
                            <div class="form-row">
                                <label>Paymode:</label>
                                <select id="pay_mode">
                                    <option value="">Select Type</option>
                                    <option value="1">Cash</option>
                                    <option value="2">Card</option>
                                    <option value="3">UPI</option>

                                </select>
                            </div>
                            <div class="form-row">
                                <label>Pay Date:</label>
                                <input type="date" id="pay_date">
                            </div>
                            <div class="form-row">
                                <button type="button" onclick="savepayment()">Pay</button>

                            </div>

                        </div>
                    </div>
                    <div class="payment-history subcontain">

                    </div>
                </div>
                <div class="order-utensils contain">
                    <div class="card">
                        <h3>🍽️ Utensils</h3>

                        <table>
                            <thead>
                                <tr>
                                    <th>Utensil</th>
                                    <th>Issued</th>
                                    <th>Returned</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Plates</td>
                                    <td>200</td>
                                    <td>180</td>
                                    <td class="pending">20 Pending</td>
                                </tr>
                                <tr>
                                    <td>Glasses</td>
                                    <td>200</td>
                                    <td>200</td>
                                    <td class="ok">Returned</td>
                                </tr>
                                <tr>
                                    <td>Buckets</td>
                                    <td>5</td>
                                    <td>4</td>
                                    <td class="pending">1 Pending</td>
                                </tr>
                            </tbody>
                        </table>

                        <br>
                        <button class="btn btn-save">Save Utensils</button>
                    </div>

                </div>
            </div>
            <div class="fixed-container">
                <!-- <center> <h3>All Orders</h3> </center> -->
                <div id="ordersList"></div>
            </div>
        </div>

    </div>

    <script>
        let alreadyPaidAmount = 0;

        var customerid = localStorage.getItem('customerid');
        var addressid = localStorage.getItem('addressid');
        var orderdate = localStorage.getItem('orderdate');
        var ordertime = localStorage.getItem('ordertime');
        var grandtotal = localStorage.getItem('grandtotal');

        console.log("printtt", customerid, addressid, orderdate, ordertime, grandtotal);


        $(document).ready(function() {

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
        });

        $('#paid_amount').on('input', function() {
            let totalamount = Number($('#total_amount').val()) || 0;
            let paidamount = Number($(this).val()) || 0;

            let balance = totalamount - (alreadyPaidAmount + paidamount);

            // ❌ Do not allow negative balance
            if (balance < 0) {
                balance = 0;
            }

            $('#balance_amount').val(balance);
        });


        function fetchTotalAmount() {
            console.log("1");
            const payload = {
                load: "fetchtotalamount",
                customerid: customerid,
                addressid: addressid,
                orderdate: orderdate,
                ordertime: ordertime
            };
            console.log("1 payload", payload);

            $.ajax({
                type: "POST",
                url: "./webservices/cateringservicespayments.php",
                data: JSON.stringify(payload),
                contentType: "application/json",
                dataType: "json",
                success: function(response) {
                    console.log("1 response", response);
                    if (response.status === "success") {

                        // ✅ FIXED KEY NAME
                        let totalAmount = Number(response.totalamount) || 0;

                        $('#total_amount').val(totalAmount);

                        // let balance = totalAmount - alreadyPaidAmount;
                        // $('#balance_amount').val(balance);
                    }
                },
                error: function() {
                    alert("Error fetching total amount");
                }
            });
        }





        // function paymentdetails() {


        //     var payload = {
        //         load: "paymentdetails",
        //         customerid: customerid,
        //         addressid: addressid,
        //         orderdate: orderdate,
        //         ordertime : ordertime
        //     };



        //     $.ajax({
        //         type: "POST",
        //         url: "./webservices/cateringservicespayments.php",
        //         data: JSON.stringify(payload),
        //         contentType: "application/json",
        //         dataType: "json",
        //         success: function(response) {

        //             const paymentdetailsdiv = document.querySelector(".payment-details");
        //             paymentdetailsdiv.innerHTML = "";

        //             if (response.status !== "success" || !Array.isArray(response.data) || response.data.length === 0) {
        //                 paymentdetailsdiv.innerHTML = "<p>No payment details found.</p>";
        //                 return;
        //             }

        //             let grandTotal = 0;
        //             const orderDate = response.data[0].order_date;


        //             const dateDiv = document.createElement("div");
        //             dateDiv.className = "payment-date";
        //             dateDiv.innerHTML = `<b>Order Date :</b> ${orderDate}`;
        //             paymentdetailsdiv.appendChild(dateDiv);

        //             paymentdetailsdiv.appendChild(document.createElement("hr"));

        //             response.data.forEach(row => {
        //                 const amount = parseFloat(row.total_amount) || 0;
        //                 grandTotal += amount;

        //                 const rowDiv = document.createElement("div");
        //                 rowDiv.className = "payment-row";

        //                 rowDiv.innerHTML = `
        //             <span><b>${row.type.toUpperCase()}</b></span>
        //            <span>₹ ${amount.toFixed(2)}</span>
        //             `;

        //                 paymentdetailsdiv.appendChild(rowDiv);
        //             });

        //             paymentdetailsdiv.appendChild(document.createElement("hr"));


        //             const totalDiv = document.createElement("div");
        //             totalDiv.className = "payment-grand-total";
        //             totalDiv.innerHTML = `<b>GRAND TOTAL :</b> ₹ ${grandTotal.toFixed(2)}`;

        //             paymentdetailsdiv.appendChild(totalDiv);
        //         },


        //         error: function() {
        //             alert("Server error while fetching details");
        //         }
        //     });
        // }

        $(document).ready(function() {
            // paymentdetails();
            paymenthistory();
            fetchbyid(customerid);
        });




        function savepayment() {
            console.log("1.save payment function");

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
                paidamount: Number($('#paid_amount').val()),
                paymode: $('#pay_mode').val(),
                paydate: $('#pay_date').val()
            };
            console.log("1.save payment function payload", payload);

            $.ajax({
                type: "POST",
                url: "./webservices/cateringservicespayments.php",
                data: JSON.stringify(payload),
                contentType: "application/json",
                dataType: "json",

                success: function(response) {
                    console.log("1.save payment function response", response);

                    if (response && response.status === "success") {

                        alert("Payment successful");

                        // ✅ UPDATE TOTALS
                        $('#total_amount').val(response.grand_total ?? $('#total_amount').val());
                        $('#paid_amount').val("");
                        // $('#balance_amount').val(
                        //     Number($('#total_amount').val()) - (response.paid_amount ?? 0)
                        // );

                        // 🔁 REFRESH HISTORY
                        paymenthistory();
                        fetchTotalAmount();

                    } else {
                        alert(response.message || "Payment failed");
                    }
                },

                error: function(xhr, status, error) {
                    console.error("Payment error:", error);
                    alert("Error while processing payment");
                }
            });
        }



        function paymenthistory() {
            // console.log("1.paymenthistory");

            var payload = {
                load: "paymenthistory",
                customerid: customerid,
                addressid: addressid,
                orderdate: orderdate
            };

            // console.log("2.paymenthistory payload", payload);

            $.ajax({
                type: "POST",
                url: "./webservices/cateringservicespayments.php",
                data: JSON.stringify(payload),
                contentType: "application/json",
                dataType: "json",

                success: function(response) {

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


                error: function(xhr, status, error) {
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
                success: function(response) {
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



                        document.querySelector(".catering-ordering").style.display = "flex";
                        document.getElementById("addresses_container").style.display = "flex";

                        document.querySelector(".menu-order").style.display = "flex";

                    } else {
                        alert("NO DATA FOUND");
                        document.querySelector(".catering-ordering").style.display = "none";
                        document.getElementById("addresses_container").style.display = "none";
                        document.getElementById("menu-order").style.display = "none";
                    }
                },
                error: function(err) {
                    alert("Something wrong");
                    //console.log(err);
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
                success: function(response) {

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
                error: function() {
                    alert("Error loading orders");
                }
            });
        }



        // Load on page open
        fetchAllOrders();
        fetchbyid(customerid);
    </script>
</body>

</html>