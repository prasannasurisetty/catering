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
    <script src="scriptfiles/cateringservicespayments.js" defer></script>

</head>

<body>
    <div class="container">
        <?php include "navbar.php"; ?>
        <div class="customer_search">

            <div class="customer_details">
                <p class="backpage" onclick="setpaymentvariables()" title="Go Back">
                    <i class="fa-solid fa-arrow-left"></i>
                </p>

                <p class="customer_id"></p>
                <p class="customer_name"></p>
                <p class="customer_ph"></p>
                <p class="orderdate"></p>
                <p class="ordertime"></p>
                <!-- <p class="grandtotal"></p> -->
            </div>


        </div>
        <div class="order-details">
            <div class="payment-container">
                <div class="order-utensils contain">
                    <div class="utensils-order">

                        <div class="add-utensils-text" onclick="addUtensilRow()">
                            <i class="fa fa-plus"></i> Add Utensil
                        </div>

                        <!-- SCROLL AREA -->
                        <div id="utensils_container">
                            <!-- utensil rows here -->
                        </div>

                        <!-- FIXED FOOTER -->
                        <div class="utensils-footer">
                            <button id="save-utensils-btn" class="save-utensils-btn">Save</button>

                        </div>

                    </div>
                    <div class="delivered-info">
                        <div class="grand">
                            <div class="grand-total">
                                <label class="delivered_time">Out For Delivery: </label>
                                <input type="time" id="delivered_time">
                            </div>
                            <div class="button-total">
                                <button id="delivered" onclick="delieveredstatus(true)">Delivered</button>

                            </div>
                        </div>

                    </div>



                </div>
                <div class="order-payments contain">
                    <div class="payment subcontain" id="payment_section">

                        <div class="form-group">
                            <div class="form-row">
                                <label><b>Grand Total:</b></label>
                                <input type="number" id="grand_total" placeholder="Total Amount" readonly>
                            </div>
                            <div class="form-row">
                                <label>Advance Amount:</label>
                                <input type="number" id="advance_amount" placeholder="0" readonly>
                            </div>
                            <div class="form-row">
                                <label>Amount to be Paid:</label>
                                <input type="number" id="amounttobe_paid" placeholder="0" readonly>
                            </div>
                            <div class="form-row">
                                <label>Recovery Amount:</label>
                                <input type="number" id="recovery_amount" value='0' min='0'>
                            </div>
                            <div class="form-row">
                                <label>Total Amount to be Paid:</label>
                                <input type="number" id="totalamount_paid" placeholder="0" readonly>
                            </div>
                            <div class="form-row">
                                <label>Paid Amount:</label>
                                <input type="number" id="paid_amount" value='0' min='0'>
                            </div>

                            <div class="form-row">
                                <label>Paymode:</label>
                                <select id="pay_mode">
                                </select>
                            </div>
                            <div class="form-row">
                                <label>Pay Date:</label>
                                <input type="date" id="pay_date">
                            </div>
                            <div class="form-row">
                                <button type="button" onclick="savepayment()">Pay</button>

                            </div>
                            <div class="form-row" id="payment_amount_row">
                                <label><b>Payment Amount:</b></label>
                                <input type="number" id="payment_amount" readonly>
                            </div>




                        </div>
                    </div>

                    <div class="refund subcontain" id="refund_section">
                        <div class="form-group">
                            <div class="form-row">
                                <label><b>Grand Total:</b></label>
                                <input type="number" id="refund_grand_total" readonly>
                            </div>

                            <div class="form-row">
                                <label>Advance Amount:</label>
                                <input type="number" id="refund_advance_amount" readonly>
                            </div>

                            <div class="form-row">
                                <label>Refund Amount:</label>
                                <input type="number" id="refund_amount">
                            </div>

                            <div class="form-row">
                                <label>Paymode:</label>
                                <select id="refund_pay_mode"></select>
                            </div>

                            <div class="form-row">
                                <label>Pay Date:</label>
                                <input type="date" id="refund_pay_date">
                            </div>

                            <div class="form-row">
                                <button type="button" onclick="refund()">Refund</button>
                            </div>

                        </div>
                    </div>



                </div>

            </div>
            <div class="fixed-container">
                <!-- <center> <h3>All Orders</h3> </center> -->
                <div id="ordersList"></div>
            </div>
        </div>

    </div>


</body>

</html>