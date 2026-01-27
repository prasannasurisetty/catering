        const phone1Error = document.getElementById("phone1Error");
        var fields = {
            name: document.getElementById("name"),
            phone1: document.getElementById("phone1"),
            email: document.getElementById("email"),
        };
        const registerBtn = document.getElementById("registerBtn");
        const cancelBtn = document.getElementById("cancelBtn");
        const setInvalid = (el) => el.classList.add("error-border");
        const clearInvalid = (el) => el.classList.remove("error-border");


        // Phone restriction + live duplicate check
        fields.phone1.addEventListener("input", () => {
            fields.phone1.value = fields.phone1.value.replace(/[^0-9]/g, "").slice(0, 10);
            if (fields.phone1.value.length === 10) {
                checkDuplicatePhone1(fields.phone1.value);
            } else {
                clearInvalid(fields.phone1);
                phone1Error.textContent = "";
                registerBtn.disabled = false;
            }

        });




        $(function() {

            let addrCounter = 0;
            let usedFoodTypes = new Set();
            const fields = {
                name: $("#name"),
                phone1: $("#phone1"),
                email: $("#email")
            };



            $(document).ready(function() {
                $('#phone1').on('input', function() {
                    let value = $(this).val().replace(/\D/g, '');

                    if (value.length > 10) value = value.slice(0, 10);
                    $(this).val(value);

                    if (value.length > 0 && !/^[6-9]/.test(value)) {
                        alert("Phone number must start with 6, 7, 8, or 9.");
                        $(this).val('');
                    }

                });
            });

            document.getElementById("email").addEventListener("input", function() {
                // Allow only a-z, A-Z, 0-9, @, and .
                this.value = this.value.replace(/[^a-zA-Z0-9@.]/g, '');
            });

            document.getElementById("name").addEventListener("input", function() {
                this.value = this.value.replace(/[^A-Za-z\s]/g, '');
            });

        function makeAddressCard(index, address_id = null) {
                const $card = $(`
                 <div class="address-card" data-address-id="${address_id || ''}" data-addr-index="${index}">
                    <button class="remove-address">✖</button>
                    <div class="address-header"><strong>Address</strong></div>

                    <div class="form-group">
                      <label>House / Flat No.</label>
                      <input type="text" class="addr_house_no" maxlength="30" placeholder="Enter Here..."/>
                    </div>

                    <div class="form-group">
                      <label>Street</label>
                      <input type="text" class="addr_street" maxlength="30" placeholder="Enter Here..." />
                    </div>

                    <div class="form-group">
                      <label>Area</label>
                      <input type="text" class="addr_area" maxlength="30" placeholder="Enter Here..."/>
                    </div>

                    <div class="form-group">
                      <label>Landmark</label>
                      <input type="text" class="addr_landmark" maxlength="30" placeholder="Enter Here..." />
                    </div>

                    <div class="form-group">
                      <label>Pincode</label>
                      <input type="number" class="addr_pincode" id="addr_pincode" placeholder="Enter Here..." />
                    </div>

                    <div class="form-group">
                      <label>Location Link</label>
                      <input type="url" class="addr_map_link" placeholder="Enter Here..." />
                    </div>

                      <div class="form-group">
                      <label>Delivery Contact Number</label>
                      <input type="number" class="delivery_contact_no" maxlength="10" placeholder="Enter Here..."/>
                    </div>

                    <div class="form-group"">
                        <label>Monthly Food Order</label>
                        <input type="checkbox" class="monthly-checkbox" id="monthly-checkbox">   
                    </div>
                    
                  

                  </div>
                `);
                $card.find(".addr_pincode").on("input", function() {
                    this.value = this.value.slice(0, 6);
                });
                $card.find(".delivery-contact_no").on("input", function() {
                    let value = $(this).val().replace(/\D/g, '');

                    value = value.slice(0, 10);


                    if (value && !/^[6-9]/.test(value)) {
                        $(this).val('');
                        return alert("Phone number must start with 6, 7, 8, or 9.");
                    }
                    $(this).val(value);
                });

                return $card;
            }



            function addAddress(address_id = null) {
                const idx = addrCounter++;
                const $card = makeAddressCard(idx, address_id);
                $("#addressList").append($card);
            }

            function makeAddressCard(index, address_id = null) {
                const $card = $(`
             <div class="address-card" data-address-id="${address_id || ''}" data-addr-index="${index}">
            <button class="remove-address">✖</button>
            <div class="address-header"><strong>Address</strong></div>

            <div class="form-group">
                <label>House / Flat No.</label>
                <input type="text" class="addr_house_no" maxlength="30" placeholder="Enter Here..."/>
            </div>

            <div class="form-group">
                <label>Street</label>
                <input type="text" class="addr_street" maxlength="30" placeholder="Enter Here..." />
            </div>

            <div class="form-group">
                <label>Area</label>
                <input type="text" class="addr_area" maxlength="30" placeholder="Enter Here..."/>
            </div>

            <div class="form-group">
                <label>Landmark</label>
                <input type="text" class="addr_landmark" maxlength="30" placeholder="Enter Here..." />
            </div>

            <div class="form-group">
                <label>Pincode</label>
                <input type="number" class="addr_pincode" placeholder="Enter Here..." />
            </div>

            <div class="form-group">
                <label>Location Link</label>
                <input type="url" class="addr_map_link" placeholder="Enter Here..." />
            </div>

            <div class="form-group">
                <label>Delivery Contact Number</label>
                <input type="number" class="delivery_contact_no" maxlength="10" placeholder="Enter Here..."/>
            </div>

            <div class="form-group">
                <label>Monthly Food Order</label>
                <input type="checkbox" class="monthly-checkbox">
            </div>
        </div>
          `);

                // PINCODE LIMIT (6 digits)
                $card.find(".addr_pincode").on("input", function() {
                    this.value = this.value.slice(0, 6);
                });

                // PHONE NUMBER VALIDATION (correct selector!)
                $card.find(".delivery_contact_no").on("input", function() {
                    let value = $(this).val().replace(/\D/g, '');

                    value = value.slice(0, 10); // limit to 10 digits

                    if (value && !/^[6-9]/.test(value)) {
                        $(this).val('');
                        return alert("Phone number must start with 6, 7, 8, or 9.");
                    }

                    $(this).val(value);
                });

                return $card;
            }


            $(document).on("click", ".remove-address", function() {
                $(this).closest(".address-card").remove();
            });

            $("#addAddressBtn").on("click", function() {
                addAddress(false);
            });

            $("#cancelBtn").on("click", function() {
                document.getElementById('registerBtn').innerText = 'Register';
                $("#search_input").val("");
                fields.name.val("");
                fields.phone1.val("");
                fields.email.val("");
                $(".instruction-list").empty();
                usedFoodTypes.clear();
                $("#addressList").empty();
                addrCounter = 0;
                addAddress(true);
                $(this).prop("disabled", true);
            });


            function loadFoodTypes() {
                const payload = {
                    load: "type_instructions"
                };
                $.ajax({
                    type: "POST",
                    url: "webservices/userregistration.php",
                    data: JSON.stringify(payload),
                    contentType: "application/json",
                    dataType: "json",
                    success: function(data) {
                        const $dropdown = $(".food-type-dropdown");
                        $dropdown.empty().append(`<option value="">Select Food Type</option>`);
                        if (data?.data?.length) {
                            data.data.forEach(ft => {
                                $dropdown.append(`<option value="${ft.sno}">${ft.type}</option>`);
                            });
                        }
                    }
                });
            }

            $(document).on("click", ".add_instruction_btn", function() {
                const $parent = $(this).closest(".special-section");
                const typeId = $parent.find(".food-type-dropdown").val();
                const typeText = $parent.find(".food-type-dropdown option:selected").text();
                const inst = $parent.find(".instruction_input").val().trim();

                if (!typeId || !inst) {
                    alert("Select food type and enter instruction");
                    return;
                }

                // const exists = $(".instruction-chip").filter(function() {
                //     return $(this).data("id") == typeId;
                // });

                // if (exists.length > 0) {
                //     alert("Only one requirement can be added per food type");
                //     return;
                // }

                if (specialVar == 1 && editingChip) {

                    const exists = $(".instruction-chip").filter(function() {
                        return $(this).data("id") == typeId && this !== editingChip[0];
                    });

                    if (exists.length > 1) {
                        alert("Only one requirement can be added per food type");
                        return;
                    }

                    const oldKey = editingChip.data("id") + "::" + editingChip.data("requirement");
                    usedFoodTypes.delete(oldKey);
                    editingChip.attr("data-id", typeId);
                    editingChip.attr("data-requirement", inst);
                    editingChip.html(`<span><b>${typeText}</b></span>: ${inst}`);
                    usedFoodTypes.add(typeId + "::" + inst);
                    specialVar = 0;
                    editingChip = null;
                    $parent.find(".instruction_input").val("");
                    $parent.find(".food-type-dropdown").val("");

                    return;
                }


                for (let key of usedFoodTypes) {
                    if (key.startsWith(typeId + "::")) {
                        alert("Only one requirement can be added per food type");
                        return;
                    }
                }



                usedFoodTypes.add(typeId + "::" + inst);

                $(".instruction-list").append(`
                 <div class="instruction-chip" 
                     data-id="${typeId}" 
                     data-requirement="${inst}">
                     <strong><span>${typeText}</strong> :${inst}</span>
                     <span class="delete-chip">✖</span>
                  </div>
                  `);


                $parent.find(".instruction_input").val("");
                $parent.find(".food-type-dropdown").val("");
            });

            $(document).on("click", ".delete-chip", function() {
                const $chip = $(this).closest(".instruction-chip");
                const key = $chip.data("id") + "::" + $chip.data("requirement");

                usedFoodTypes.delete(key);
                $chip.remove();
            });

            $(document).on("click", ".clear_instructions_btn", function() {
                $(".instruction-list").empty();
                usedFoodTypes.clear();
            });

            loadFoodTypes();
            addAddress(true);
        });

        let searchinput = document.getElementById("search_input");
        searchinput.addEventListener("input", () => {
            let value = searchinput.value.trim();
            if (value === "") {
                document.querySelector(".recmd").style.display = "none";
                document.querySelector(".recmd").innerHTML = "";
                return;
            }
            var payload = {
                load: "search",
                searchvalue: value
            };
            $.ajax({
                type: "POST",
                url: "./webservices/register.php",
                data: JSON.stringify(payload),
                contentType: "application/json",
                dataType: "json",
                success: function(response) {
                    let recmd = document.querySelector(".recmd");
                    recmd.innerHTML = "";
                    if (response.data && response.data.length > 0) {
                        recmd.style.display = "flex";
                        response.data.forEach((itm) => {
                            let para = document.createElement("p");
                            para.classList.add("multiple_list_item");
                            para.innerHTML = `
                                <span>${itm.CustomerID} -</span>
                                <span>${itm.CustomerName} (</span>
                                <span>${itm.Phone1})</span>
                            `;
                            para.setAttribute("onclick", `fetchinfo(${itm.CustomerID}); closeRecmd();`);
                            recmd.appendChild(para);
                        });
                    } else {
                        recmd.style.display = "none";
                    }
                },
                error: function(err) {
                    console.log(err);
                    alert("Something went wrong");
                },
            });
        });

        function closeRecmd() {
            document.querySelector(".recmd").style.display = "none";
        }

        //   Live duplicate phone number check
        function checkDuplicatePhone1(phone1) {
            // console.log("Checking duplicate for:", phone1);

            const payload = {
                load: "check_phone1",
                phone1: phone1
            };

            $.ajax({
                type: "POST",
                url: "webservices/userregistration.php",
                data: JSON.stringify(payload),
                contentType: "application/json",
                dataType: "json",

                success: function(data) {
                    console.log("Response:", data);

                    if (!data.success) {
                        // Duplicate found
                        setInvalid(fields.phone1);
                        phone1Error.textContent = data.message;
                        registerBtn.disabled = true;
                    } else {
                        // No duplication
                        clearInvalid(fields.phone1);
                        phone1Error.textContent = "";
                        registerBtn.disabled = false;
                    }
                },

                error: function(error) {
                    console.error("Error checking duplicate phone:", error);
                }
            });
        }


        let specialVar = 0;
        let editingChip = null;
        $(document).on("click", ".instruction-chip", function() {

            specialVar = 1;
            editingChip = $(this);
            document.querySelector('.food-type-dropdown').value = $(this).data("id");
            document.querySelector('.instruction_input').value = $(this).data("requirement");

        });




        function getFoodInstructions() {
            let specialRequirements = [];

            $(".instruction-chip").each(function() {
                specialRequirements.push({
                    sno: $(this).attr("data-sno"),
                    foodtype_id: $(this).attr("data-id"),
                    requirement: $(this).attr("data-requirement")
                });
            });

            console.log('lalal', specialRequirements)

            return specialRequirements;
        }

        function fetchinfo(customerid) {

            document.getElementById('registerBtn').innerText = 'Update';

            console.log("1. fetching customer info function.....");
            document.getElementById('search_input').value = customerid;
            const payload = {
                load: "fetchinfo",
                customerid: customerid
            };

            console.log('', payload)

            $.ajax({
                type: "POST",
                url: "webservices/userregistration.php",
                data: JSON.stringify(payload),
                contentType: "application/json",
                dataType: "json",

                success: function(data) {
                    console.log("3. response", data);

                    if (data.status !== "success") {
                        console.log("Customer not found or no data");
                        return;
                    }


                    const c = data.customer;

                    $("#name").val(c.CustomerName || "");
                    $("#phone1").val(c.Phone1 || "");
                    $("#email").val(c.Email || "");


                    const reqList = document.querySelector(".instruction-list");
                    reqList.innerHTML = "";

                    if (Array.isArray(data.requirements)) {
                        data.requirements.forEach(req => {
                            const chip = document.createElement("div");
                            chip.className = "instruction-chip";
                            chip.dataset.sno = req.sno;
                            chip.dataset.id = req.food_type;
                            chip.dataset.requirement = req.requirement;
                            chip.innerHTML = `
                           
                        <span><b>${req.type}</b></span>:${req.requirement} <span class="delete-chip">✖</span>
                    `;
                            reqList.appendChild(chip);
                        });
                    }


                    const addressList = document.getElementById("addressList");
                    addressList.innerHTML = "";

                    if (Array.isArray(data.address)) {
                        data.address.forEach((addr, i) => {

                            const block = document.createElement("div");
                            // block.className = "address-block";

                            block.className = "address-card";
                            block.setAttribute("data-address-id", addr.aid ? addr.aid : "null");

                            // block.setAttribute("data-address-id", addr.aid);

                            block.innerHTML = `
                                    <div class="form-group">
                                        <label>Flat / House No</label>
                                        <input type="text" class="addr_house_no" value="${addr.flatno || ''}">
                                    </div>

                                    <div class="form-group">
                                        <label>Street</label>
                                        <input type="text" class="addr_street" value="${addr.street || ''}">
                                    </div>

                                    <div class="form-group">
                                        <label>Area</label>
                                        <input type="text" class="addr_area" value="${addr.area || ''}">
                                    </div>

                                    <div class="form-group">
                                        <label>Landmark</label>
                                        <input type="text" class="addr_landmark"  value="${addr.landmark || ''}">
                                    </div>

                                    <div class="form-group">
                                        <label>Pincode</label>
                                        <input type="text" class="addr_pincode" value="${addr.pincode || ''}">
                                    </div>

                                    <div class="form-group">
                                        <label>Map Link</label>
                                        <input type="text" class="addr_map_link" value="${addr.addresslink || ''}">
                                    </div>

                                    
                                     <div class="form-group">
                                      <label>Delivery Contact Number</label>
                                      <input type="text" class="delivery_contact_no" placeholder="Enter Here..."/>
                                     </div>

                                    <div class="form-group">
                                        <label>Monthly Food Order</label>
                                        <input type="checkbox" class="monthly-checkbox" ${addr.monthlysub == '1' ? 'checked' : ''}>
                                    </div>

                                `;

                            addressList.appendChild(block);

                            // Add separator between address blocks
                            if (i < data.address.length - 1) {
                                const hr = document.createElement("hr");
                                hr.className = "address-separator";
                                addressList.appendChild(hr);
                            }
                        });
                    }
                },

                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        }

        function getAddresses() {
            let addresses = [];

            $(".address-card").each(function() {

                let raw = $(this).attr("data-address-id");

                // convert invalid values to null
                let addrID = (raw && raw !== "null" && raw !== "true") ? raw : null;

                addresses.push({
                    address_id: addrID, // <--- now correct always
                    house_no: $(this).find(".addr_house_no").val() || "",
                    street: $(this).find(".addr_street").val() || "",
                    area: $(this).find(".addr_area").val() || "",
                    landmark: $(this).find(".addr_landmark").val() || "",
                    pincode: $(this).find(".addr_pincode").val() || "",
                    map_link: $(this).find(".addr_map_link").val() || "",
                    monthlysub: $(this).find(".monthly-checkbox").is(':checked') ? '1' : '0',
                    delivery_contact_no: $(this).find(".delivery_contact_no").val() || ""

                });
            });

            return addresses;
        }

        let redirectVariable = localStorage.getItem('redirectVariable');

        function saveCustomer() {
            let payload = {
                load: "save_customer",
                customer_id: document.querySelector("#search_input").value,
                name: fields.name.value.trim(),
                phone1: fields.phone1.value.trim(),
                email: fields.email.value.trim(),
                addresses: getAddresses(),
                specialreq: getFoodInstructions()
            };
            console.log("Payload:", payload);
            $.ajax({
                type: "POST",
                url: "webservices/userregistration.php",
                data: JSON.stringify(payload),
                contentType: "application/json",
                dataType: "json",
                success: function(res) {
                    if (res.success) {
                        alert(res.message);
                        if (redirectVariable === "1") {
                            let confirmMsg = confirm('Customer added! Want to go to Catering Page?');
                            if (confirmMsg) {
                                localStorage.setItem('redirectCid', res.newCustomerID);
                                localStorage.setItem('redirectVariable', "0");
                                location.href = 'catering.php';
                            }
                            localStorage.setItem('redirectVariable', "0");
                        }
                        console.log("new: ", res.newCustomerID)
                        fields.customer_id = res.customer_id;
                        if (res.address) {
                            loadCustomerAddresses(res.address);
                        }
                        $("#cancelBtn").click();

                    } else {
                        msg.textContent = data.message || "Registration failed!";
                        msg.className = "msg error";
                    }
                },
                error: function(error) {
                    console.error("error for inserting", error);
                }
            });
        }




        function loadCustomerAddresses(addresses) {
            $("#addressList").empty();
            addrCounter = 0;

            addresses.forEach(addr => {
                // const $card = makeAddressCard(addrCounter++, addr.aid);
                const $card = makeAddressCard(addrCounter++, addr.aid);


                $card.find(".addr_house_no").val(addr.flatno);
                $card.find(".addr_street").val(addr.street);
                $card.find(".addr_area").val(addr.area);
                $card.find(".addr_landmark").val(addr.landmark);
                // $card.find(".addr_city").val(addr.city);
                $card.find(".addr_pincode").val(addr.pincode);
                $card.find(".addr_map_link").val(addr.addresslink);
                $card.find(".monthly-checkbox").prop("checked", addr.monthlysub == "1");
                $card.find(".delivery_contact_no").val(addr.address_ph_number);


                $("#addressList").append($card);
            });
        }