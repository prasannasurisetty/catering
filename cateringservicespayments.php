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
                <!-- <p class="backpage"><i class="fa-solid fa-arrow-left" style="color:white;font-weight:bold;"></i></p> -->
                <p class="customer_id"></p>
                <p class="customer_name"></p>
                <p class="customer_ph"></p>
                <p class="orderdate"></p>
                <p class="ordertime"></p>
                <p class="grandtotal"></p>
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