<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Payments & Utensils</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f6f7fb;
        }

        /* HEADER */
        .header {
            background: #7a3b00;
            color: #fff;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            margin: 0;
            font-size: 20px;
        }

        /* ORDER SUMMARY */
        .order-summary {
            background: #fff;
            margin: 20px;
            padding: 15px;
            border-radius: 8px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .summary-item {
            font-size: 14px;
        }

        .summary-item span {
            font-weight: bold;
        }

        /* MAIN LAYOUT */
        .main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px;
        }

        .card {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .card h3 {
            margin-top: 0;
            color: #7a3b00;
        }

        /* INPUTS */
        .form-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        input, select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        textarea {
            resize: none;
        }

        /* BUTTONS */
        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-add {
            background: #2e7d32;
            color: #fff;
        }

        .btn-delete {
            background: #d32f2f;
            color: #fff;
        }

        .btn-save {
            background: #7a3b00;
            color: #fff;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 14px;
            text-align: center;
        }

        table th {
            background: #f0e6dc;
        }

        .pending {
            color: red;
            font-weight: bold;
        }

        .ok {
            color: green;
            font-weight: bold;
        }

        /* FOOTER */
        .footer {
            background: #fff;
            margin: 20px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <h2>Payments & Utensils</h2>
    <div>Order ID: <b>#1023</b></div>
</div>

<!-- ORDER SUMMARY -->
<div class="order-summary">
    <div class="summary-item"><span>Customer:</span> C S Rao</div>
    <div class="summary-item"><span>Phone:</span> 9731472299</div>
    <div class="summary-item"><span>Order Date:</span> 29-12-2025</div>
    <div class="summary-item"><span>Address:</span> KRM Colony</div>
    <div class="summary-item"><span>Grand Total:</span> ₹25,000</div>
    <div class="summary-item"><span>Status:</span> Partial Paid</div>
</div>

<!-- MAIN CONTENT -->
<div class="main">

    <!-- PAYMENTS -->
    <div class="card">
        <h3>💰 Payments</h3>

        <div class="form-row">
            <input type="date">
            <select>
                <option>Cash</option>
                <option>UPI</option>
                <option>Card</option>
                <option>Bank</option>
            </select>
        </div>

        <div class="form-row">
            <input type="text" placeholder="Reference No">
            <input type="number" placeholder="Amount">
        </div>

        <textarea rows="2" placeholder="Notes"></textarea><br><br>

        <button class="btn btn-add">Add Payment</button>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Mode</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>29-12-2025</td>
                    <td>Cash</td>
                    <td>₹10,000</td>
                    <td><button class="btn btn-delete">Delete</button></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- UTENSILS -->
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

<!-- FOOTER -->
<div class="footer">
    <div>
        <b>Balance Amount:</b> ₹15,000 |
        <b>Utensils:</b> Pending
    </div>
    <div>
        <button class="btn btn-save">Close Order</button>
    </div>
</div>

</body>
</html>
