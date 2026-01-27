<!DOCTYPE html>

<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <style>
        /* =========================
   GLOBAL
========================= */
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        .challan {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border: 1px solid #000;
            color: #000;
        }

        /* =========================
   HEADER
========================= */
        .challan h2 {
            text-align: center;
            margin: 0 0 20px;
            font-size: 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* =========================
   ROW & COLUMNS
========================= */
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .col {
            width: 48%;
            font-size: 14px;
            line-height: 1.5;
        }

        .label {
            font-weight: bold;
        }

        /* =========================
   TABLE
========================= */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }

        table thead th {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            background: #f0f0f0;
        }

        table tbody td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        table tbody td:nth-child(2) {
            text-align: left;
        }

        /* =========================
   FOOTER / SIGNATURE
========================= */
        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }

        .stamp {
            width: 45%;
            text-align: center;
            font-size: 14px;
        }

        .stamp::before {
            content: "";
            display: block;
            height: 60px;
        }

        /* =========================
   PRINT STYLES
========================= */
        @media print {
            body {
                background: none;
                padding: 0;
            }

            .challan {
                border: 1px solid #000;
                box-shadow: none;
                margin: 0;
                width: 100%;
            }

            button {
                display: none;
            }

            @page {
                size: A4;
                margin: 12mm;
            }
        }
    </style>
</head>

<body>
    <!-- RETURNED CHALLAN -->
    <div class="challan" id="challan">
        <h2>RETURNABLE DELIVERY CHALLAN</h2>

        <div class="row">
            <div class="col">
                <b id="companyName"></b><br>
                <span id="companyAddress"></span>
            </div>
            <div class="col">
                <b>Challan No:</b> <span id="challanNo"></span><br>
                <b>Date:</b> <span id="challanDate"></span><br>
                <b>Time:</b> <span id="challanTime"></span>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <span class="label">Customer Name:</span> <span id="customerName"></span><br>
                <span class="label">Phone:</span> <span id="customerPhone"></span>
            </div>
            <div class="col">
                <span class="label">Address:</span>
                <span id="customerAddress"></span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Utensil Name</th>
                    <th>Issued Qty</th>
                </tr>
            </thead>
            <tbody id="itemsTableBody">
                <!-- dynamic rows -->
            </tbody>
        </table>

        <div class="footer">
            <div class="stamp">
                Company Stamp<br><br>
                Authorized Signature
            </div>
            <div class="stamp">
                Receiver Signature<br><br>
                Authorized Signature
            </div>
        </div>
    </div>

    <button onclick="printChallan()">Print Challan</button>


    <script>
        function loadReturnableChallan() {

            // if (!order_id) {
            //     alert("Please select an order first");
            //     return;
            // }

            const payload = {
                load: "loadreturnablechallan",
                customerid: 46,
                addressid: 10,
                orderid: 2 
            };

            console.log("Returnable Challan Payload", payload);

            $.ajax({
                type: "POST",
                url: "./webservices/returnablechallan.php",
                data: JSON.stringify(payload),
                contentType: "application/json",
                dataType: "json",

                success: function(response) {

                    if (response.code !== 200) {
                        alert(response.status || "Failed to load challan");
                        return;
                    }

                    // populate challan
                    loadChallan(response.data);

                    // optional auto print
                    setTimeout(printChallan, 300);
                },

                error: function() {
                    alert("Error loading returnable challan");
                }
            });
        }

        loadReturnableChallan();

        function loadChallan(data) {

    // Company
    document.getElementById("companyName").innerText = data.company.name;
    document.getElementById("companyAddress").innerText = data.company.address;

    // Challan info
    document.getElementById("challanNo").innerText = data.challan_no;
    document.getElementById("challanDate").innerText = data.date;
    document.getElementById("challanTime").innerText = data.time;

    // Customer
    document.getElementById("customerName").innerText = data.customer.name;
    document.getElementById("customerPhone").innerText = data.customer.phone;
    document.getElementById("customerAddress").innerText = data.customer.address;

    // Items
    const tbody = document.getElementById("itemsTableBody");
    tbody.innerHTML = "";

    if (!data.items || data.items.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="3" style="text-align:center;">
                    No utensils issued for this order
                </td>
            </tr>
        `;
        return;
    }

    data.items.forEach((item, index) => {
        tbody.insertAdjacentHTML("beforeend", `
            <tr>
                <td>${index + 1}</td>
                <td>${item.name}</td>
                <td>${item.qty}</td>
            </tr>
        `);
    });
}



        function printChallan() {
            const printContent = document.getElementById("challan").innerHTML;
            const originalContent = document.body.innerHTML;

            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
        }



    </script>
</body>

</html>