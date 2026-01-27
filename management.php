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
    <style>
        #returnable_challan,
        #unreturnable_challan {
            width: 13px;
            height: 13px;
            border: 1px solid red;
        }
    </style>

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




                </div>
                <div class="order-payments contain">

                    <div class="delivered-info">
                        <div class="grand">
                            <div class="grand-total">
                                <label class="returnable_challan">Returnable Challan: </label>
                                <input type="checkbox" id="returnable_challan">
                            </div>
                            <div class="grand-total">
                                <label class="unreturnable_challan">Unreturnable Challan: </label>
                                <input type="checkbox" id="unreturnable_challan">
                            </div>
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



            </div>
            <div class="fixed-container">
                <!-- <center> <h3>All Orders</h3> </center> -->
                <div id="ordersList"></div>
            </div>
        </div>

    </div>


</body>

</html>