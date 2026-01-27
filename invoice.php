<!DOCTYPE html>
<html>

<head>
    <title>TAX INVOICE</title>
    <style>
        body {
            font-family: Arial;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
        }

        .right {
            text-align: center;
        }

        .center {
            text-align: center;
        }

        .no-border td {
            border: none;
        }

        tfoot td {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <h2 class="center">TAX INVOICE</h2>

    <!-- ORGANIZATION -->
    <table class="no-border">
        <tr>
            <td width="70%">
                <b>VEDYA HOSPITALITIES PRIVATE LIMITED</b><br>
                55/8/29, HB Colony Rd,<br>
                Beside Sri Venkateswara Swamy Temple,<br>
                KRM Colony, Madilpalem,<br>
                Visakhapatnam, Andhra Pradesh - 530013<br>
                GSTIN: 37ABCDE1234F1Z5<br>
                PAN: ABCDE1234F
            </td>

            <td width="30%">
                <b>Invoice No:</b> INV-001<br>
                <b>Invoice Date:</b> 05-01-2026<br>
            </td>
        </tr>
    </table>

    <hr>

    <!-- CUSTOMER -->
    <table class="no-border">
        <tr>
            <td width="50%">
                <b>Bill To:</b><br>
                Customer Name<br>
                Phone: 9XXXXXXXXX
            </td>

            <td width="50%">
                <b>Bill To:</b><br>
                55/8/29, HB Colony Rd,<br>
                Beside Sri Venkateswara Swamy Temple,<br>
                KRM Colony, Madilpalem<br>
            </td>
        </tr>
    </table>

    <!-- ITEMS TABLE -->
    <table id="itemsTable">
        <thead>
            <tr>
                <th>Sno</th>
                <th>SAC</th>
                <th>Order Date</th>
                <th>Item Name</th>
                <th>Qty</th>
                <th>Amount</th>
                <th>CGST<br>2.5%</th>
                <th>SGST<br>2.5%</th>
                <th>Discount</th>
                <th>Flat Rounded</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            <tr class="item-row"
                data-qty="10"
                data-amount="1000"
                data-cgst="25"
                data-sgst="25"
                data-discount="0"
                data-rounded="1050"
                data-total="1050">
                <td>1</td>
                <td>996337</td>
                <td>01-01-2026</td>
                <td>Idly</td>
                <td class="right">10</td>
                <td class="right">1000.00</td>
                <td class="right">25.00</td>
                <td class="right">25.00</td>
                <td class="right">0.00</td>
                <td class="right">1050.00</td>
                <td class="right">1050.00</td>
            </tr>

            <tr class="item-row"
                data-qty="10"
                data-amount="1000"
                data-cgst="25"
                data-sgst="25"
                data-discount="0"
                data-rounded="1050"
                data-total="1050">
                <td>2</td>
                <td>996337</td>
                <td>01-01-2026</td>
                <td>Meals</td>
                <td class="right">10</td>
                <td class="right">1000.00</td>
                <td class="right">25.00</td>
                <td class="right">25.00</td>
                <td class="right">0.00</td>
                <td class="right">1050.00</td>
                <td class="right">1050.00</td>
            </tr>
        </tbody>

        <!-- COLUMN TOTALS -->
        <tfoot>
            <tr>
                <td colspan="4" class="right">TOTAL</td>
                <td class="right" id="tQty"></td>
                <td class="right" id="tAmount"></td>
                <td class="right" id="tCgst"></td>
                <td class="right" id="tSgst"></td>
                <td class="right" id="tDiscount"></td>
                <td class="right" id="tRounded"></td>
                <td class="right" id="tGrand"></td>
            </tr>
        </tfoot>
    </table>

    <br><br>

    <table class="no-border" style="margin-top:40px;">
        <tr>
            <!-- LEFT : PAYMENT SUMMARY -->
            <td width="50%" valign="top">
                <table style="width:80%;">
                    <tr>
                        <td><b>Total Amount</b></td>
                        <td class="right"><b id="sumTotal">0.00</b></td>
                    </tr>
                    <tr>
                        <td><b>Paid Amount</b></td>
                        <td class="right" id="paidAmount">0.00</td>
                    </tr>
                    <tr>
                        <td><b>Balance Amount</b></td>
                        <td class="right"><b id="balanceAmount">0.00</b></td>
                    </tr>
                </table>
            </td>

            <!-- RIGHT : SIGNATURE -->
            <td width="50%" valign="bottom" class="right">
                For <b>VEDYA HOSPITALITIES PVT LTD</b><br><br><br>
                Company Seal & Authorized Signatory
            </td>
        </tr>
    </table>


    <!-- SCRIPT -->
    <script>
        let totals = {
            qty: 0,
            amount: 0,
            cgst: 0,
            sgst: 0,
            discount: 0,
            rounded: 0,
            grand: 0
        };

        document.querySelectorAll('.item-row').forEach(row => {
            totals.qty += Number(row.dataset.qty);
            totals.amount += Number(row.dataset.amount);
            totals.cgst += Number(row.dataset.cgst);
            totals.sgst += Number(row.dataset.sgst);
            totals.discount += Number(row.dataset.discount);
            totals.rounded += Number(row.dataset.rounded);
            totals.grand += Number(row.dataset.total);
        });

        tQty.innerText = totals.qty;
        tAmount.innerText = totals.amount.toFixed(2);
        tCgst.innerText = totals.cgst.toFixed(2);
        tSgst.innerText = totals.sgst.toFixed(2);
        tDiscount.innerText = totals.discount.toFixed(2);
        tRounded.innerText = totals.rounded.toFixed(2);
        tGrand.innerText = totals.grand.toFixed(2);



        /* ---- PAYMENT DETAILS ---- */
        const paidAmount = 1500; // ← change later from DB
        const totalAmount = totals.grand;
        const balanceAmount = totalAmount - paidAmount;

        document.getElementById('sumTotal').innerText = totalAmount.toFixed(2);
        document.getElementById('paidAmount').innerText = paidAmount.toFixed(2);
        document.getElementById('balanceAmount').innerText = balanceAmount.toFixed(2);
    </script>

</body>

</html>