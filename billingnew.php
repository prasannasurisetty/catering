<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Billing Page</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="css/billingnew.css">
    <script src="scriptfiles/billingnew.js" defer></script>
    <!-- <script src="scriptfiles/billing.js" defer></script> -->
          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

</head>

<body>
    <?php include "navbar.php"; ?>
    <div class="total">
        <div class="customer_search">
            <input type="" placeholder="Enter Here(Id/Name/PhoneNumber)" class="search_input" id="search_input" maxlength="30">
            <div class="recmd">
            </div>
            <div class="customer_details">
                <p class="customer_id"></p>
                <p class="customer_name"></p>
                <p class="customer_ph"></p>
            </div>
        </div>

        <div class="customer-paymentdata">
            <div class="container">
                <div class="customer_delivery_addresses" id="addresses_container"></div>
                <div class="divider">
                    <div class="customer-billdata subcontain" id="payment_section">
                        <center>
                            <h3 class="headings" style="color:#8D3A08;">Payment Details</h3>
                        </center>
                        <form class="form-bill">
                            <div class="form-group">
                                <div class="form-rows">
                                    <label>Order Date:</label>
                                    <input type="date" id="order_date" readonly>
                                </div>
                                <div class="form-rows">
                                    <label>Order Time:</label>
                                    <input type="time" id="order_time" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-rows">
                                    <label><b>Grand Total:</b></label>
                                    <input type="number" id="grand_total" placeholder="Total Amount" readonly>
                                </div>
                                <div class="form-rows">
                                    <label>Advance Amount:</label>
                                    <input type="number" id="advance_amount" placeholder="0" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-rows">
                                    <label>Amount to be Paid:</label>
                                    <input type="number" id="amounttobe_paid" placeholder="0" readonly>
                                </div>

                                <div class="form-rows">
                                    <label>Recovery Amount:</label>
                                    <input type="number" id="recovery_amount" value='0' min='0'>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-rows">
                                    <label>Total Amount to be Paid:</label>
                                    <input type="number" id="totalamount_paid" placeholder="0" readonly>
                                </div>
                                <div class="form-rows">
                                    <label>Paid Amount:</label>
                                    <input type="number" id="paid_amount" value='0' min='0'>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-rows">
                                    <label>Paymode:</label>
                                    <select id="pay_mode">
                                    </select>
                                </div>
                                <div class="form-rows">
                                    <div id="dynamicInput"></div>
                                </div>
                            </div>

                            <!-- <div class="balance-amount" onclick="openBalancePopup()">Click Here For Previous Month Amount</div> -->
                            <div class="form-group">
                                <div class="form-rows">
                                    <label>Pay Date:</label>
                                    <input type="date" id="pay_date">

                                </div>
                                <div class="form-rows">
                                    <button type="button" id="payment-button" onclick="savepayment();">Pay</button>
                                </div>
                            </div>
                        </form>

                    </div>

                    <div class="refund subcontain" id="refund_section">
                        <center>
                            <h3 class="headings" style="color:#8D3A08;">Refund Details</h3>
                        </center>
                        <form class="form-bill">
                               <div class="form-group">
                                <div class="form-rows">
                                    <label>Order Date:</label>
                                    <input type="date" id="refund_order_date" readonly>
                                </div>
                                <div class="form-rows">
                                    <label>Order Time:</label>
                                    <input type="time" id="refund_order_time" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-rows">
                                    <label><b>Grand Total:</b></label>
                                    <input type="number" id="refund_grand_total" readonly>
                                </div>

                                <div class="form-rows">
                                    <label>Advance Amount:</label>
                                    <input type="number" id="refund_advance_amount" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-rows">
                                    <label>Refund Amount:</label>
                                    <input type="number" id="refund_amount">
                                </div>
                                <div class="form-rows">
                             
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-rows">
                                    <label>Paymode:</label>
                                    <select id="refund_pay_mode"></select>
                                </div>
                                <div class="form-rows">
                                    <div id="dynamicInput1"></div>
                                </div>
                            </div>
                            <div class="form-group">

                                <div class="form-rows">
                                    <label>Pay Date:</label>
                                    <input type="date" id="refund_pay_date">
                                </div>

                                <div class="form-rows">
                                    <button type="button" id="refund_button" onclick="refund()">Refund</button>
                                </div>

                            </div>
                        </form>
                    </div>


                </div>

            </div>


            <div class="customer-orderdata">
                <table id="foodTypeTable" class="foodTypeTable">
                    <thead>
                        <tr>
                            <th>Order Date</th>
                            <th>Order Time</th>
                            <th>TotalAmount</th>
                            <th>AdvanceAmount</th>
                            <!-- <th></th> -->
                            <th>Pending</th>
                            <!-- <th>Paid Date</th> -->
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

            </div>
        </div>
    </div>



  



</body>

</html>