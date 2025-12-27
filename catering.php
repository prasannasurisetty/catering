<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Catering</title>
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
                <div class="order-menu">
                    <div class="plate-order section" id="plate_order_section" style="display:none;">

                        <span id="heading-info">Order Info</span>

                        <div class="plate-box">
                            <div class="plate-row">
                                <label>Date:</label>
                                <input type="date">
                            </div>
                            <div class="plate-row">
                                <label>Time:</label>
                                <input type="time">
                            </div>

                        </div>
                        <span id="heading-info">Items Info</span>

                        <div class="plate-menu">
                            <textarea id="item-names" placeholder="Enter Item Names Here...."></textarea>

                        </div>



                    </div>




                    <div class="plate_preview section" id="plate_preview">
                        <div>
                            <span id="heading-info">Plate Info</span>



                            <div class="plate-box">
                                <div class="plate-row">
                                    <label>No of Plates:</label>
                                    <input type="number" id="plate_count" oninput="updateSummary(); updateGrandTotal();">
                                </div>
                                <div class="plate-row">
                                    <label>Plate Cost:</label>
                                    <input type="number" id="plate_price" oninput="updateSummary(); updateGrandTotal();">
                                </div>
                            </div>
                            <div class="plate-box">
                                <div class="plate-row">
                                    <label>Total Amount:</label>
                                    <input type="number" id="total_amount" readonly>
                                </div>

                            </div>
                        </div>

                        <div class="services-order" id="services_order_section" style="display:none;">
                            <div class="plate-box">
                                <div class="plate-row">
                                    <span id="heading-info">Services Info</span>
                                </div>
                                <div class="plate-row">
                                    <button class="add-service-btn" onclick="addServiceRow()">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <div id="services_container">
                                <!-- Service rows will appear here -->
                            </div>


                        </div>

                        <div class="last-container">
                            <div class="grand-total">
                                <label class="grand_total">Grand Total: </label>
                                <input type="number" id="grand_total" readonly>
                            </div>
                            <div class="plate-button">
                                <button id="save-menu">Save Menu</button>
                            </div>
                        </div>



                    </div>

                </div>



            </div>
        </div>
        <div class="fixed-container">
            loading fixed container.............
        </div>
    </div>




</body>

</html>