<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Customer Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    
    <link rel="stylesheet" type="text/css" href="css/userregistration.css">
    <script src="scriptfiles/userregistration.js" defer></script>

  
</head>

<body>
    <div class="container">
        <div class="header-box">
            <h2>Register Account</h2>
            <a class="home-icon" href="catering.php">
                <i class="fa-regular fa-house" id="home-icon"></i>
            </a>
        </div>

        <div class="layout">

            <!-- LEFT PANEL -->
            <div class="left">
                <input type="text" placeholder="Enter Here (Id/Name/PhoneNumber) " class="search_input" id="search_input">


                <div class="recmd"></div>


                <!-- CUSTOMER INFO -->
                <div class="customer-info">
                    <h3>Customer Info</h3>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="name" maxlength="50" placeholder="Enter full name" />
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" id="phone1" maxlength="10" placeholder="10-digit phone" />
                        <div id="phone1Error" class="error"></div>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="email" placeholder="optional email" />
                    </div>
                </div>

                <hr>

                <!-- SPECIAL REQUIREMENTS -->
                <div class="special-section">
                    <h3>Special Requirements</h3>

                    <div class="form-group">
                        <label>Food Type</label>
                        <select class="food-type-dropdown">
                            <option value="">Loading...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Instruction</label>
                        <input type="text" class="instruction_input" maxlength="50" placeholder="Enter instruction" />
                    </div>

                    <div class="controls" style="display:flex; gap:8px;">
                        <button class="btn add_instruction_btn">Add Instruction</button>
                    </div>

                    <div class="instruction-list"></div>
                </div>
            </div>

            <!-- RIGHT PANEL -->
            <div class="right">
                <div class="right-sub">
                    <h3>Delivery Address</h3>
                    <button class="btn" id="addAddressBtn"><i class="fa fa-plus"></i> Add Address</button>
                </div>

                <div class="address-list" id="addressList"></div>


            </div>
        </div>

        <div class="bottom-buttons">
            <button class="btn" id="registerBtn" onclick="saveCustomer()">Register</button>
            <button class="btn secondary" id="cancelBtn">Cancel</button>
        </div>
    </div>





</body>

</html>