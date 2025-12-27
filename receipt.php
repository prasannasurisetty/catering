<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Receipt</title>
    <link rel="stylesheet" href="receipt.css">
    <style>
        body {
            background: #f4f4f4;
            font-family: Arial, sans-serif;
        }

        .receipt-container {
            width: 380px;
            background: #fff;
            margin: 30px auto;
            padding: 20px;
            border: 1px solid #ddd;
        }

        .receipt-header {
            text-align: center;
        }

        .receipt-header h2 {
            margin: 0;
        }

        .receipt-meta {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-top: 10px;
        }

        .receipt-details p {
            margin: 6px 0;
            font-size: 14px;
            align-items: center;
        }

        .address {
            margin-left: 10px;
        }

        .receipt-amount {
            text-align: center;
        }

        .receipt-amount h1 {
            margin: 10px 0;
        }

        .receipt-footer {
            text-align: center;
            font-size: 12px;
            color: #555;
        }

        .company-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .company-header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .company-header p {
            margin: 2px 0;
            font-size: 12px;
            color: #444;
        }

        .single-line-address {
            display: inline-block;
            max-width: 100%;
            white-space: nowrap;
            /* 👈 forces single line */
            overflow: hidden;
            /* hides extra text */
            text-overflow: ellipsis;
            /* shows ... if long */
            vertical-align: top;
        }

        .single-line-total {
            display: inline-block;
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>

<body>

    <div class="receipt-container">
        <div class="receipt-header company-header">
            <h2>VEDYA HOSPITALITIES PRIVATE LIMITED</h2>
            <p>Floor-1, #55-8-29/2, KRM Colony, Seethamadara</p>
            <p>Visakhapatnam, Andhra Pradesh, India - 530013</p>
            <p><b>Phno : XXXXXXXXXX</b></p>
        </div>


        <div class="receipt-meta">
            <div>
                <strong>Receipt No:</strong>
                <span id="receiptNo"></span>
            </div>
            <div>
                <strong>Date:</strong>
                <span id="receiptDate"></span>
            </div>
        </div>

        <!-- <hr> -->

        <div class="receipt-details">
            <p><strong>Customer Name:</strong> <span id="customerName"></span></p>
            <p><strong>Phone No:</strong> <span id="phoneNo"></span></p>
            <p>
                <strong>Address:</strong>
                <span class="single-line-address" id="address">
                    123, Green Park Road, Near City Hospital, Madhapur, Hyderabad - 500081
                </span>
            </p>
            <p>
                <strong>Total:</strong>
                <span class="single-line-total" id="totalAmount">
                    ₹ 1100.00
                </span>
            </p>


        </div>

        <!-- <hr> -->
<!-- 
        <div class="receipt-amount">
            <h3>Amount</h3>
            <h1>₹ <span id="amount"></span></h1>
        </div> -->

        <!-- <hr> -->

        <!-- <div class="receipt-footer">
            <p>Thank you for your business!</p>
        </div> -->
    </div>

    <script src="receipt.js"></script>
    <script>
        // Dynamic values (example – replace with backend values)
        document.getElementById("receiptNo").textContent = "VED"+Date.now();

        const today = new Date();
        document.getElementById("receiptDate").textContent =
            today.toLocaleDateString("en-GB"); // DD/MM/YYYY

        document.getElementById("customerName").textContent = "C S Rao";
        document.getElementById("phoneNo").textContent = "9731472299";
        document.getElementById("address").textContent =
            "6-21-12 East Point Colony Vizag";

        document.getElementById("amount").textContent = "11,000";
    </script>
</body>

</html>