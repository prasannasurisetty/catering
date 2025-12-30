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
    <style>
        .payment-details {
            /* border: 2px solid blue; */
            border-radius: 5px;
            width: 100%;
            height: 75vh;
        }

        .payment-details {
            width: 100%;
            max-width: 380px;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 14px 16px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }

        /* Order Date */
        .payment-date {
            font-size: 13px;
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }

        /* Separator line */
        .payment-details hr {
            border: none;
            border-top: 1px dashed #ccc;
            margin: 8px 0;
        }


        .payment-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
        }

        .payment-row span:first-child {
            letter-spacing: 0.4px;
        }


        .payment-row span:last-child {
            font-weight: 600;
        }

        .payment-grand-total {
            display: flex;
            justify-content: space-between;
            font-size: 15px;
            font-weight: 700;
            color: #000;
            margin-top: 6px;
        }



        .container-2 {
            background: #ffffff;
            border: 1px solid #dcdcdc;
            border-radius: 6px;
            box-shadow: rgba(0, 0, 0, 0.08) 0px 2px 6px;
        }




        .container-2 input[type=number]::-webkit-inner-spin-button,
        .container-2 input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }




        .payment-details,
        .payment-history {
            background: #ffffff;
            border: 1px solid #dcdcdc;
            border-radius: 6px;
            padding: 16px;
            box-shadow: rgba(0, 0, 0, 0.08) 0px 2px 6px;
            font-size: 16px;
        }


        .payment-date {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .payment-details hr {
            border: none;
            border-top: 1px dashed #bbb;
            margin: 10px 0;
        }


        .payment-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 2px;
        }

        .payment-row span:first-child {
            font-weight: 600;
            letter-spacing: 0.6px;
            color: #222;
        }


        .payment-row span:last-child {
            font-weight: 600;
            color: #000;
        }


        .payment-grand-total {
            display: flex;
            justify-content: space-between;
            font-size: 15px;
            font-weight: 700;
            padding-top: 6px;
            color: #000;
        }

        .payment-grand-total span:last-child {
            color: #0a8a2a;
        }
    </style>

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
                <!-- <p class="register_button" onclick="setRedirectVariable()">
                    <a href="userregistration.php">
                        <i class="fa-regular fa-user" style="color:white"></i>
                    </a>
                </p> -->
            </div>


        </div>

        <div class="payment-container">
            <div class="container-1 contain">
                <div class="payment-heading">
                    <center>
                        <h2>Payment Summary</h2>
                    </center>
                </div>
                <div class="payment-details">

                </div>

            </div>
            <div class="container-2 contain">
                <div class="payment-heading">
                    <center>
                        <h2>Payment Details</h2>
                    </center>
                </div>
                <div class="form-group">
                    <div class="form-row">
                        <label><b>Total Amount:</b></label>
                        <input type="number" id="total_amount" placeholder="Total Amount">
                    </div>
                    <div class="form-row">
                        <label>Paid Amount:</label>
                        <input type="number" id="paid_amount" placeholder="0">
                    </div>
                    <div class="form-row">
                        <label>Balance Amount:</label>
                        <input type="number" id="balance_amount" placeholder="0">
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
            <div class="container-3 contain">
                <div class="payment-heading">
                    <center>
                        <h2>Payment History</h2>
                    </center>
                </div>
                <div class="payment-history"></div>
            </div>
        </div>

    </div>

    <script>
        var customerid = localStorage.getItem('customerid');
        var addressid = localStorage.getItem('addressid');
        var fetchdate = localStorage.getItem('fetchdate');
        console.log("printtt", customerid, addressid, fetchdate);

        $('#paid_amount').on('input', function() {
            let totalamount = Number($('#total_amount').val());
            let paidamount = Number($('#paid_amount').val());


            let balance = totalamount - paidamount;
            $('#balance_amount').val(balance);
        });



        function paymentdetails() {
            // console.log("1.paymentdetails function");

            var payload = {
                load: "paymentdetails",
                customerid: customerid,
                addressid: addressid,
                orderdate: fetchdate
            };

            // console.log("2.paymentdetails function payload", payload);

            $.ajax({
                type: "POST",
                url: "./webservices/cateringservicespayments.php",
                data: JSON.stringify(payload),
                contentType: "application/json",
                dataType: "json",
                success: function(response) {

                    const paymentdetailsdiv = document.querySelector(".payment-details");
                    paymentdetailsdiv.innerHTML = "";

                    if (response.status !== "success" || !Array.isArray(response.data) || response.data.length === 0) {
                        paymentdetailsdiv.innerHTML = "<p>No payment details found.</p>";
                        return;
                    }

                    let grandTotal = 0;
                    const orderDate = response.data[0].order_date;


                    const dateDiv = document.createElement("div");
                    dateDiv.className = "payment-date";
                    dateDiv.innerHTML = `<b>Order Date :</b> ${orderDate}`;
                    paymentdetailsdiv.appendChild(dateDiv);

                    paymentdetailsdiv.appendChild(document.createElement("hr"));

                    response.data.forEach(row => {
                        const amount = parseFloat(row.total_amount) || 0;
                        grandTotal += amount;

                        const rowDiv = document.createElement("div");
                        rowDiv.className = "payment-row";

                        rowDiv.innerHTML = `
                    <span><b>${row.type.toUpperCase()}</b></span>
                   <span>₹ ${amount.toFixed(2)}</span>
                    `;

                        paymentdetailsdiv.appendChild(rowDiv);
                    });

                    paymentdetailsdiv.appendChild(document.createElement("hr"));

                    // ✅ Grand Total
                    const totalDiv = document.createElement("div");
                    totalDiv.className = "payment-grand-total";
                    totalDiv.innerHTML = `<b>GRAND TOTAL :</b> ₹ ${grandTotal.toFixed(2)}`;

                    paymentdetailsdiv.appendChild(totalDiv);
                },


                error: function() {
                    alert("Server error while fetching details");
                }
            });
        }

        $(document).ready(function() {
            paymentdetails();
            paymenthistory();
            fetchbyid(customerid);
        });



        function savepayment() {
            // console.log("1.save function");

            if (
                !$('#paid_amount').val() ||
                !$('#pay_mode').val() ||
                !$('#pay_date').val()
            ) {
                alert("Please fill all payment fields");
                return;
            }

            var payload = {
                load: "savepayment",
                customerid: customerid,
                addressid: addressid,
                paidamount: $('#paid_amount').val(),
                paymode: $('#pay_mode').val(),
                paydate: $('#pay_date').val()
            };

            // console.log("2.save function payload", payload);

            $.ajax({
                type: "POST",
                url: "./webservices/cateringservicespayments.php",
                data: JSON.stringify(payload),
                contentType: "application/json",
                dataType: "json",

                success: function(response) {
                    // console.log("3.save function response", response);

                    if (response && response.status === "success") {
                        alert("Payment successful");
                    } else {
                        alert(response.message || "Payment failed");
                    }
                    paymenthistory();
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
                orderdate: fetchdate
            };

            // console.log("2.paymenthistory payload", payload);

            $.ajax({
                type: "POST",
                url: "./webservices/cateringservicespayments.php",
                data: JSON.stringify(payload),
                contentType: "application/json",
                dataType: "json",

                success: function(response) {
                    // console.log("3.paymenthistory response", response);

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


                    response.data.forEach((row, index) => {
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

                        // Load addresses for this customer
                        loadAddress();



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
    </script>
</body>

</html>