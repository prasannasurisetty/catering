<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Billing Reports</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --color-primary: #8D3A08;
            --color-light: #D4B05A;
            --color-dark: #3A2615;
            --color-medium: #C28B42;
            --color-border: #8D3A08;
            --color-tab: #FBF5E5;
            --color-label: #3A2615;
            --color-button: #8D3A08;
            --color-blue: #007497;
            --soft-card: #F4E6C3;
            --muted-text: rgba(58, 38, 21, 0.55);
            --glass-bg: rgba(80, 48, 18, 0.05);
            --radius: 12px;
            --shadow: 0 8px 26px rgba(50, 30, 10, 0.07);
            --ease: cubic-bezier(0.2, 0.9, 0.3, 1);
            --card-gap: 10px;
            --primary: #8d3a08;
            --bg: #f4f5f7;
            --card: #ffffff;
            --border: #e0e0e0;
            --pending: #e53935;
        }

        * {
            font-family: "Calibri Light", Calibri, sans-serif;
        }

        .wrapper {
            /* margin:20px; */
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
                height: 87vh;
        }

        /* ========== TABS ========== */
        .tabs {
            display: flex;
            gap: 30px;
            border-bottom: 2px solid #eee;
        }

        .tabs button {
            background: none;
            border: none;
            padding: 12px 0;
            font-size: 16px;
            cursor: pointer;
            color: #666;
        }

        .tabs button.active {
            color: var(--color-primary);
            border-bottom: 3px solid var(--color-primary);
            font-weight: 600;
        }

        /* ========== FILTERS ========== */
        .filters {
            display: flex;
            gap: 350px;
            align-items: center;
            margin: 20px 0;
        }

        .search-box {
            /* flex: 1; */
            position: relative;
        }

        .search-box input {
            width: 250%;
            padding: 10px 12px 10px 36px;
            border: 1px solid var(--border);
            border-radius: 6px;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }

        .date-box {
            display: flex;
            gap: 10px;
        }

        .date-box input {
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 6px;
        }

        .apply-btn {
            padding: 10px 18px;
            background: var(--color-primary);
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        /* ========== CONTENT ========== */
        .contents {
            display: flex;
            gap: 20px;
        }

        /* ========== TABLE ========== */
        .table-wrap {
            flex: 3;
            overflow: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background:var(--color-medium);
            color : white;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: center;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #fafafa;
        }

        .pending {
            color: var(--pending);
            font-weight: 700;
        }

        /* ========== SUMMARY CARDS ========== */
        .summary {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .card {
            background: #fff;
            border-radius: 8px;
            padding: 18px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .08);
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .card i {
            font-size: 34px;
            color: var(--color-primary);
        }

        .card h3 {
            margin: 6px 0 0;
        }

        .pending-card i,
        .pending-card h3 {
            color: var(--pending);
        }

        /* ========== TAB CONTENT ========== */
        .tabcontent {
            display: none;
        }
    </style>
</head>

<body>
    <?php include "navbar.php"; ?>
    <div class="wrapper">

        <!-- Tabs -->
        <div class="tabs">
            <button class="tablinks active" onclick="openTab(event,'customer')">Customer Wise</button>
            <button class="tablinks" onclick="openTab(event,'payment')">Payments Wise</button>
        </div>

        <!-- Filters -->
        <div class="filters">
            <div class="search-box">
                <i class="fa fa-search"></i>
                <input type="text" placeholder="Search Customer / Order ID">
            </div>
            <div class="date-box">
                <input type="month">
                <!-- <input type="date"> -->
                <button class="apply-btn">Apply</button>
            </div>
        </div>

        <!-- CUSTOMER WISE -->
        <div class="contents tabcontent" id="customer" style="display:flex;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Sno</th>
                            <th>Customer ID</th>
                            <th>Address ID</th>
                            <th>No of Orders</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Pending Amount</th>
                            <th>Last Order Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>45</td>
                            <td>1</td>
                            <td>12</td>
                            <td>₹ 1,50,000</td>
                            <td>₹ 1,20,000</td>
                            <td class="pending">₹ 30,000</td>
                            <td>28/12/2025</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>49</td>
                            <td>17</td>
                            <td>8</td>
                            <td>₹ 90,000</td>
                            <td>₹ 75,000</td>
                            <td class="pending">₹ 15,000</td>
                            <td>5/1/2026</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="summary">
                <div class="card">
                    <i class="fa fa-clipboard-list"></i>
                    <div>
                        <p>Total Orders</p>
                        <h3>2</h3>
                    </div>
                </div>
                <div class="card">
                    <i class="fa fa-sack-dollar"></i>
                    <div>
                        <p>Total Revenue</p>
                        <h3>₹ 2,40,000</h3>
                    </div>
                </div>
                <div class="card pending-card">
                    <i class="fa fa-triangle-exclamation"></i>
                    <div>
                        <p>Total Pending</p>
                        <h3>₹ 45,000</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAYMENTS WISE -->
        <div class="contents tabcontent" id="payment">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Sno</th>
                            <th>Order ID</th>
                            <th>Customer ID</th>
                            <th>Payment Date</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Pending</th>
                            <th>Pay Mode</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>101</td>
                            <td>45</td>
                            <td>05/01/2026</td>
                            <td>₹ 1,300</td>
                            <td>₹ 700</td>
                            <td class="pending">₹ 600</td>
                            <td>Cash</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="summary">
                <div class="card">
                    <i class="fa fa-clipboard-list"></i>
                    <div>
                        <p>Total Orders</p>
                        <h3>1</h3>
                    </div>
                </div>
                <div class="card">
                    <i class="fa fa-sack-dollar"></i>
                    <div>
                        <p>Total Revenue</p>
                        <h3>1,300</h3>
                    </div>
                </div>
                <div class="card pending-card">
                    <i class="fa fa-triangle-exclamation"></i>
                    <div>
                        <p>Total Pending</p>
                        <h3>₹ 600</h3>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function openTab(evt, tabId) {
            document.querySelectorAll(".tabcontent").forEach(t => t.style.display = "none");
            document.querySelectorAll(".tablinks").forEach(b => b.classList.remove("active"));
            document.getElementById(tabId).style.display = "flex";
            evt.currentTarget.classList.add("active");
        }
    </script>

</body>

</html>