<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <link rel="stylesheet" type="text/css" href="css/catering.css">
    <script src="scriptfiles/catering.js" defer></script>
    <!-- Font Awesome (keep only one, latest) -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>




</head>

<body>
    <?php include "navbar.php"; ?>
    <div class="total">
        <div class="container">
            <div class="customer_search">

                <input type="text"
                    placeholder="Enter Here(Id/Name/PhoneNumber)"
                    class="search_input"
                    id="search_input"
                    maxlength="30">

                <div class="recmd"></div>

                <div class="customer_details">
                    <p class="customer_id"></p>
                    <p class="customer_name"></p>
                    <p class="customer_ph"></p>
                    <p class="register_button" onclick="setRedirectVariable()">
                        <a href="userregistration.php">
                            <i class="fa-regular fa-user" style="color:white"></i>
                        </a>
                    </p>
                </div>
            </div>

            <div class="catering-ordering">
                <div class="customer_delivery_addresses" id="addresses_container"></div>
                <div class="plate-info" id="plate-info" style="display:none">
                    <!-- <div class="plate-row">
                        <span id="heading-info">Order Info</span>

                    </div> -->
                    <div class="plate-row">
                        <label>Order Date:</label>
                        <input type="date" id="order-date">
                    </div>
                    <div class="plate-row">
                        <label>Order Time:</label>
                        <input type="time" id="order-time">
                    </div>

                    <div class="plate-row">
                        <label>No of Plates:</label>
                        <input type="number" id="plate_count" placeholder="0" oninput="updateSummary(); updateGrandTotal();">
                    </div>
                    <div class="plate-row">
                        <label>Plate Cost:</label>
                        <input type="number" id="plate_price" placeholder="0.00" oninput="updateSummary(); updateGrandTotal();">
                    </div>


                    <div class="plate-row">
                        <label>Total Amount:</label>
                        <input type="number" id="total_amount" placeholder="0.00" readonly>
                    </div>
                </div>
                <div class="order-menu">
                    <div class="plate-order section" id="plate_order_section" style="display:none;">
                        <span id="heading">Menu</span>
                        <div class="plate-menu">
                            <textarea id="item-names" placeholder="Enter Item Names Here...."></textarea>
                            <p id="duplicate-warning"></p>
                        </div>
                        <div class="remarks">
                            <textarea id="remarks-input" placeholder="Enter Remarks Here..." maxlength="100"></textarea>
                        </div>
                    </div>
                    <div class="plate_preview section" id="plate_preview" style="display:none;">
                        <div class="services-order" id="services_order_section">
                            <div class="add-services-text" onclick="addServiceRow()">
                                <i class="fa fa-plus"></i> Add Services
                            </div>

                            <div id="services_container">
                                <!-- Service rows will appear here -->
                            </div>





                        </div>

                        <div class="last-container">
                            <div class="grand">
                                <div class="grand-total">

                                </div>
                                <div class="grand-total">
                                    <label class="grand_total">Grand Total: </label>
                                    <input type="number" id="grand_total" placeholder="0.00">
                                </div>

                            </div>

                            <div class="plate-button">
                                <div class="grand-total">
                                    <label>Paymode:</label>
                                    <select id="pay_mode">
                                    </select>
                                </div>
                                <div class="grand-total">
                                    <label class="adv-amt">Advance: </label>
                                    <input type="number" id="adv-amt" placeholder="0.00">
                                </div>
                            </div>
                            <div class="buttons-total">
                                <button id="save-menu">Save Order</button>
                                <!-- <button id="set" onclick="setpaymentvariables();">Set</button> -->
                                <button id="cancel-menu">Cancel Order</button>
                            </div>

                        </div>



                    </div>

                </div>



            </div>
        </div>
        <div class="fixed-container">

            <div id="ordersList"></div>
        </div>

    </div>




</body>

</html>