function showToast(message, type = "success") {
    let bgColor = "#4CAF50"; // success

    if (type === "error") bgColor = "#f44336";
    if (type === "warning") bgColor = "#ff9800";

    Toastify({
        text: message,
        duration: 2000,
        gravity: "top",
        position: "right",
        backgroundColor: bgColor,
        close: false,
    }).showToast();
}

let selectedAddressId = null;
let checkedAddressIds = [];
let searchinput = document.getElementById("search_input");


searchinput.addEventListener("input", () => {
    var payload = {
        load: "search",
        searchvalue: searchinput.value,
    };
    $.ajax({
        type: "POST",
        url: "./webservices/register.php",
        data: JSON.stringify(payload),
        dataType: "json",
        success: function (response) {
            let recmd = document.querySelector(".recmd");
            recmd.innerHTML = "";
            if (response.data.length > 0) {
                recmd.style.display = "flex";
                response.data.forEach((itm) => {
                    let para = document.createElement("p");
                    para.setAttribute("class", "multiple_list_item");
                    para.setAttribute("onclick", `fetchbyid(${itm.CustomerID})`);
                    para.innerHTML = `<span>${itm.CustomerID} -</span>
                    <span>${itm.CustomerName} (</span><span>${itm.Phone1})</span>`;
                    recmd.appendChild(para);
                });

            } else {
                recmd.style.display = "none";
            }
        },
    });
});

function highlightAddress(aid) {
    selectedAddressId = aid;

    document.querySelectorAll(".address_block")
        .forEach(el => el.classList.remove("highlight-address"));

    document
        .querySelector(`.address_block[data-block-aid="${aid}"]`)
        .classList.add("highlight-address");

    // ✅ SHOW plate ordering after address is selected
    const plateSection = document.getElementById("plate_order_section");
    if (plateSection) plateSection.style.display = "flex";

    const serviceSection = document.getElementById("plate_preview");
    if (serviceSection) serviceSection.style.display = "flex";

    const plateinfo = document.getElementById("plate-info");
    if (plateinfo) plateinfo.style.display = "flex";

    fetchmenu(customerid, aid);

}



function triggermenudetails(customerid, orderdate, ordertime) {
    customerid = String(customerid).trim();
    orderdate = String(orderdate).trim();
    ordertime = String(ordertime).trim();



    // ✅ Populate inputs
    $('#order-date').val(orderdate);
    $('#order-time').val(ordertime);

    // ✅ Persist state
    localStorage.setItem("customerid", customerid);
    localStorage.setItem("orderdate", orderdate);
    localStorage.setItem("ordertime", ordertime);

    // ✅ Load customer → addresses → menu
    fetchbyid(customerid);
}

function autoFetchOnDateTimeChange() {
    const customerid = document.querySelector(".customer_id")?.dataset.cid;
    const addressid = selectedAddressId;

    const orderdate = $('#order-date').val();
    const ordertime = $('#order-time').val();

    if (!customerid || !addressid || !orderdate || !ordertime) return;

    fetchmenu(customerid, addressid);
}




function fetchbyid(sinput) {
    customerid = sinput;

    var load = "fetchbyid";

    var payload = {
        load: load,
        customerid: sinput,
    };
    $.ajax({
        type: "POST",
        url: "./webservices/register.php",
        data: JSON.stringify(payload),
        dataType: "json",
        success: function (response) {
            if (response.status === "Success") {
                document.querySelector(".recmd").style.display = "none";
                document.querySelector(".search_input").value = "";
                document.querySelector(".customer_id").textContent = `ID :${response.data[0].CustomerID}`;
                document.querySelector(".customer_id").dataset.cid = response.data[0].CustomerID;
                document.querySelector(".customer_name").textContent = `Name :${response.data[0].CustomerName}`;
                document.querySelector(".customer_name").setAttribute("title", response.data[0].CustomerName);
                document.querySelector(".customer_ph").textContent = `PH Number :${response.data[0].Phone1}`;




                const customer = response.data[0];

                const recmdEl = document.querySelector(".recmd");
                if (recmdEl) recmdEl.style.display = "none";

                const searchEl = document.querySelector(".search_input");
                if (searchEl) searchEl.value = "";

                const idEl = document.querySelector(".customer_id");
                const nameEl = document.querySelector(".customer_name");
                const phEl = document.querySelector(".customer_ph");

                if (idEl) {
                    idEl.textContent = `ID :${customer.CustomerID}`;
                    idEl.dataset.cid = customer.CustomerID;
                }
                if (nameEl) {
                    nameEl.textContent = `Name :${customer.CustomerName}`;
                    nameEl.setAttribute("title", customer.CustomerName);
                }
                if (phEl) {
                    phEl.textContent = `PH Number :${customer.Phone1}`;
                }

                // Load addresses for this customer
                loadAddress();



                document.querySelector(".catering-ordering").style.display = "flex";
                document.getElementById("addresses_container").style.display = "flex";

                // document.getElementById("menu-order").style.display = "none";

            } else {
                document.querySelector(".catering-ordering").style.display = "none";
                document.getElementById("addresses_container").style.display = "none";
                // document.getElementById("menu-order").style.display = "none";
            }
            return;

        },
    });
}



function addAddress() {
    let modal = document.getElementById("address_area");

    // Create modal only once
    if (!modal) {
        const html = `
              <div id="address_area" class="modal-overlay">
                 <div class="modal-box">
            <div class="modal-header">
              <h3>Address</h3>
              <span class="close-btn" id="closeadderss_area">&times;</span>
            </div>

            <div class="modal-body">
              <label>House No:</label>
              <input type="text" id="houseno" maxlength="20" placeholder="House No">

              <label>Street</label>
              <input type="text" id="street" maxlength="30" placeholder="Enter Street">

              <label>Area:</label>
              <input type="text" id="area" maxlength="30" placeholder="Enter Area">

              <label>Landmark:</label>
              <input type="text" id="landmark" maxlength="30" placeholder="Enter Landmark">

              <label>Phone Number:</label>
              <input type="text" id="ph_number" maxlength="10" placeholder="Enter Phone Number">
              <span id="ph_error" style="color:red; font-size:14px;"></span><br>

              <label>Pincode:</label>
              <input type="text" id="pincode" maxlength="6" placeholder="Enter Pin Code">

              <label>Address Link:</label>
              <input type="text" id="address_link" placeholder="Enter Address Link">

              <label class="setDefault">
                <input type="checkbox" id="default_address"> Set as default
              </label>
            </div>

            <div class="modal-footer">
              <button id="submitAddressBtn">Submit</button>
              <button id="cancelAddressBtn">Cancel</button>
            </div>
            </div>
             </div>`;

        document.body.insertAdjacentHTML("beforeend", html);

        modal = document.getElementById("address_area");

        document
            .getElementById("closeadderss_area")
            .addEventListener("click", closeAddressModal);
        document
            .getElementById("cancelAddressBtn")
            .addEventListener("click", closeAddressModal);

        // Close modal on clicking outside the box
        window.addEventListener("click", function (e) {
            if (e.target === modal) {
                closeAddressModal();
            }
        });
    }

    // Clear previous values
    document.getElementById("houseno").value = "";
    document.getElementById("street").value = "";
    document.getElementById("area").value = "";
    document.getElementById("landmark").value = "";
    document.getElementById("ph_number").value = "";
    document.getElementById("address_link").value = "";
    document.getElementById("pincode").value = "";
    document.getElementById("default_address").checked = false;
    const phError = document.getElementById("ph_error");
    if (phError) phError.textContent = "";

    modal.style.display = "flex";

    let btn = resetButtonEvents(modal.querySelector("#submitAddressBtn"));
    btn.innerText = "Submit";

    btn.onclick = function () {
        const cidEl = document.querySelector(".customer_id");
        const cid = cidEl ? cidEl.dataset.cid : null;

        if (!cid) {
            showToast("Please select a customer before adding address.", "warning");
            return;
        }

        const phone = (document.getElementById("ph_number").value || "").trim();
        if (!/^[6-9][0-9]{9}$/.test(phone)) {
            showToast("Please enter a valid 10-digit phone number starting with 6, 7, 8, or 9.", "warning");
            return;
        }

        const payload = {
            load: "addAddress",
            cid: cid,
            houseno: (document.getElementById("houseno").value || "").trim(),
            street: (document.getElementById("street").value || "").trim(),
            area: (document.getElementById("area").value || "").trim(),
            landmark: (document.getElementById("landmark").value || "").trim(),
            phone: phone,
            link: (document.getElementById("address_link").value || "").trim(),
            pincode: (document.getElementById("pincode").value || "").trim(),
            default: document.getElementById("default_address").checked ? 1 : 0,
        };
        $.ajax({
            type: "POST",
            url: "./webservices/catering.php",
            data: JSON.stringify(payload),
            contentType: "application/json",
            dataType: "json",
            success: function (response) {
                showToast(response.message || "Address added.", "success");
                loadAddress();
                closeAddressModal();
            },
        });
    };
}

function deleteAddress(aid) {
    if (!confirm("Are you sure you want to delete this address?")) return;

    var payload = {
        load: "deleteAddress",
        aid: aid,
    };



    $.ajax({
        type: "POST",
        url: "./webservices/catering.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (response) {
            if (response.status === "success") {
                showToast("Address deleted successfully!", "success");
                loadAddress();
            } else {
                showToast("Delete failed: " + response.message, "warning");
            }
        },
    });
}

function edit_address(address) {
    addAddress();

    document.getElementById("houseno").value = address.flatno || "";
    document.getElementById("street").value = address.street || "";
    document.getElementById("area").value = address.area || "";
    document.getElementById("landmark").value = address.landmark || "";
    document.getElementById("ph_number").value = address.address_ph_number || "";
    document.getElementById("address_link").value = address.addresslink || "";
    document.getElementById("pincode").value = address.pincode || "";

    let defaultCheckbox = document.getElementById("default_address");
    defaultCheckbox.checked = address.isDefault == 1;
    defaultCheckbox.onchange = function () {
        if (address.isDefault == 1 && !defaultCheckbox.checked) {
            showToast(
                "Cannot remove this address from default. Make another address default first.", "warning"
            );
            defaultCheckbox.checked = true;
        }
    };

    let btn = document.getElementById("submitAddressBtn");
    btn.innerText = "Update";
    btn = resetButtonEvents(btn);

    btn.onclick = function () {
        var payload = {
            load: "editAddress",
            aid: address?.aid,
            cid: document.querySelector(".customer_id").dataset.cid,
            houseno: document.getElementById("houseno").value,
            street: document.getElementById("street").value,
            area: document.getElementById("area").value,
            landmark: document.getElementById("landmark").value,
            phone: document.getElementById("ph_number").value,
            link: document.getElementById("address_link").value,
            pincode: document.getElementById("pincode").value,
            default: defaultCheckbox.checked ? 1 : 0,
        };
        $.ajax({
            type: "POST",
            url: "./webservices/catering.php",
            data: JSON.stringify(payload),
            contentType: "application/json",
            dataType: "json",
            success: function (response) {
                showToast(response.message, "success");
                closeAddressModal();
                loadAddress();
            },
        });
    };
}

// Reset button events helper
function resetButtonEvents(btn) {
    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);
    return newBtn;
}

function closeAddressModal() {
    const modal = document.getElementById("address_area");
    if (modal) modal.style.display = "none";
}

function loadAddress() {
    const cidEl = document.querySelector(".customer_id");
    const cid = cidEl ? cidEl.dataset.cid : null;

    if (!cid) {
        showToast("No customer selected, cannot load addresses.", "warning");
        return;
    }

    const payload = {
        load: "get_address",
        cid: cid,
    };

    $.ajax({
        type: "POST",
        url: "./webservices/addressapi.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",
        success: function (response) {

            if (response.success && Array.isArray(response.data)) {
                const container = document.getElementById("addresses_container");
                if (!container) return;

                container.style.display = "flex";
                container.innerHTML = `
              <div class="add_address_block" onclick="addAddress()"> + </div>
            `;

                selectedAddressId = null;

                response.data.forEach((address) => {
                    const isDefault = address.isDefault == 1;
                    if (isDefault) selectedAddressId = address.aid;

                    const block = `
                <div class="address_block ${isDefault ? "highlight-address" : ""}"
                     data-block-aid="${address.aid}">

                  

                  <div class="address_text">
                    ${isDefault ? `<span class="default-label">Default</span>` : ""}
                    <p>${address.flatno || ""}</p>
                    <p>${address.street || ""}</p>
                    <p>${address.area || ""}</p>
                    <p>${address.landmark || ""}</p>
                    <p>${address.address_ph_number || ""}</p>
                    <p>${address.pincode || ""}</p>
                    <p>${address.addresslink || ""}</p>

                  <p style="color:red;justify-self:end;display:flex;position: absolute;bottom: 8px;gap:10px;align-items:center;">                   
                  <i class="fa fa-pencil" style="cursor:pointer;" onclick='edit_address(${JSON.stringify(
                        address
                    )})'></i>                  
                  <i class="fa fa-trash" style="cursor:pointer;" onclick="deleteAddress(${address.aid
                        })"></i>
                    </p>
                  </div>
                </div>
              `;

                    container.insertAdjacentHTML("beforeend", block);
                });

                if (selectedAddressId) {
                    highlightAddress(selectedAddressId);
                    const cid = document.querySelector(".customer_id")?.dataset.cid;
                    if (cid) {
                        fetchmenu(cid, selectedAddressId);


                    }

                }

                // Rebuild checkedAddressIds from DOM
                checkedAddressIds = [];
                $("#addresses_container .address-checkbox:checked").each(function () {
                    const aid = parseInt($(this).data("aid"), 10);
                    if (!isNaN(aid)) checkedAddressIds.push(aid);
                });
                // Optional external function
                if (typeof autoLoadActiveFoodtype === "function") {
                    autoLoadActiveFoodtype();
                }
            } else {
                showToast("No address found", "warning");
            }
        },

    });
}

// ✅ HANDLE ADDRESS CLICK (DYNAMIC ELEMENTS)
$(document).on("click", ".address_block", function (e) {

    // Prevent edit / delete icons from triggering address select
    if ($(e.target).closest(".address-actions").length) return;

    const aid = $(this).data("block-aid");
    if (!aid) return;

    highlightAddress(aid);
});

function updateSummary() {
    const price = Number(document.getElementById("plate_price").value) || 0;
    const count = Number(document.getElementById("plate_count").value) || 0;

    const plateTotal = price * count;

    document.getElementById("total_amount").value = plateTotal;

    updateGrandTotal();
}

function updateGrandTotal() {
    let serviceTotal = 0;

    document.querySelectorAll(".service-cost").forEach(input => {
        serviceTotal += Number(input.value) || 0;
    });

    const plateTotal =
        Number(document.getElementById("total_amount").value) || 0;

    const grandTotal = plateTotal + serviceTotal;

    // ✅ AUTO UPDATE INPUT
    document.getElementById("grand_total").value = grandTotal;
}

function addServiceRow() {
    const container = document.getElementById("services_container");

    const row = document.createElement("div");
    row.className = "service-row";
    row.dataset.sno = ""; // empty = new service

    row.innerHTML = `
        <input type="text"
               placeholder="Enter Service Type"
               onblur="checkDuplicateService(this)">
        <input type="number"
               placeholder="Amount ₹"
               class="service-cost"
               oninput="updateGrandTotal()">
        <button type="button"
                class="remove-service"
                onclick="removeServiceRow(this)">
            <i class="fa fa-trash"></i>
        </button>
         `;

    container.appendChild(row);
}

function checkDuplicateService(input) {
    const value = input.value.trim().toLowerCase();
    if (!value) return;

    let count = 0;

    document.querySelectorAll("#services_container .service-row input[type='text']")
        .forEach(inp => {
            if (inp.value.trim().toLowerCase() === value) {
                count++;
            }
        });

    if (count > 1) {
        showToast("This service is already added.", "warning");
        input.value = "";
        input.focus();
    }
}

document.addEventListener("DOMContentLoaded", function () {
    addServiceRow(); // default one row
});


function removeServiceRow(btn) {
    const row = btn.closest(".service-row");
    if (!row) return;

    row.remove();          // 🔥 removed from DOM
    updateGrandTotal();
}

function getItemsFromTextarea() {
    const text = document.getElementById("item-names").value.trim();
    if (!text) return [];

    return text
        // split ONLY by comma or newline
        .split(/[\n,]+/)
        .map(item => item.trim())
        .filter(item => item.length > 0);
}

function validateServicesAmount() {
    let valid = true;
    let errorService = "";

    document.querySelectorAll("#services_container .service-row").forEach(row => {
        const name = row.querySelector("input[type='text']")?.value.trim();
        const cost = Number(row.querySelector(".service-cost")?.value) || 0;

        if (name && cost <= 0) {
            valid = false;
            errorService = name;
        }
    });

    if (!valid) {
        showToast(`Please enter amount for service: ${errorService}`, "warning");
        return false;
    }

    return true;
}


function saveCateringOrder() {
    const cidEl = document.querySelector(".customer_id");
    const customer_id = cidEl ? cidEl.dataset.cid : null;

    if (!customer_id) {
        showToast("Select customer first", "warning");
        return;
    }

    if (!selectedAddressId) {
        showToast("Select address first", "warning");
        return;
    }

    const orderDateEl = document.querySelector("input[type='date']");
    const orderTimeEl = document.querySelector("input[type='time']");

    const order_date = orderDateEl ? orderDateEl.value : "";
    const order_time = orderTimeEl ? orderTimeEl.value : "";

    if (!order_date || !order_time) {
        showToast("Select order date and time", "warning");
        return;
    }

    const plate_count = Number(document.getElementById("plate_count").value) || 0;
    const plate_cost = Number(document.getElementById("plate_price").value) || 0;
    const total_amount = Number(document.getElementById("total_amount").value) || 0;
    const grand_total = Number(document.getElementById("grand_total").value) || 0;
    const advance_amount = Number(document.getElementById("adv-amt").value) || 0;
    const pay_mode = document.getElementById("pay_mode").value || "";
    const remarks = document.getElementById("remarks-input").value;


    if (plate_count <= 0 || plate_cost <= 0) {
        showToast("Enter valid plate count and plate cost", "warning");
        return;
    }




    /* ================= FOOD ITEMS (supports vada-2 format) ================= */

    const fooditems = [];
    const itemsText = document.getElementById("item-names").value.trim();

    itemsText.split("\n").forEach(line => {
        const val = line.trim();
        if (val) {
            fooditems.push(val); // 🔥 store raw text
        }
    });


    if (!fooditems.length) {
        showToast("No valid food items found", "error");
        return;
    }

    /* ================= SERVICES (OPTIONAL) ================= */

    const services = [];

    document.querySelectorAll("#services_container .service-row").forEach(row => {
        const id = row.dataset.serviceId || null;
        const name = row.querySelector("input[type='text']")?.value.trim();
        const cost = Number(row.querySelector(".service-cost")?.value) || 0;

        if (name) {
            services.push({ id, name, cost });
        }
    });
    if (!validateServicesAmount()) {
        return;
    }

    if (advance_amount > 0 && !pay_mode) {
        showToast("Select payment mode for advance", "warning");
        return;
    }




    /* ================= PAYLOAD ================= */

    const payload = {
        load: "savemenu",
        customer_id: customer_id,
        address_id: selectedAddressId,
        order_date: order_date,
        order_time: order_time,
        plates_count: plate_count,
        plate_cost: plate_cost,
        total_amount: total_amount,
        grand_total: grand_total,
        fooditems: fooditems,
        services: services,
        advance_amount: advance_amount,   // ✅ NEW
        pay_mode: pay_mode,
        remarks: remarks
    };

    /* ================= AJAX ================= */

    $.ajax({
        type: "POST",
        url: "./webservices/catering.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (response) {
            if (response.status === "success") {
                showToast("Order saved successfully", "success");


                // RESET FORM
                document.getElementById("item-names").value = "";
                document.getElementById("plate_count").value = "";
                document.getElementById("plate_price").value = "";
                document.getElementById("total_amount").value = "";
                document.getElementById("grand_total").value = "";
                document.getElementById("services_container").innerHTML = "";
                document.getElementById("remarks-input").innerHTML = "";

            } else {
                showToast(response.message || "Failed to save order", "error");
            }
            fetchAllOrders();
        },
    });
}


function updateOrder() {

    const customerid = document.querySelector(".customer_id")?.dataset.cid;
    const addressid = selectedAddressId;
    const orderdate = $('#order-date').val();
    const ordertime = $('#order-time').val();

    if (!customerid || !addressid || !orderdate || !ordertime) {
        showToast("Missing required order details", "warning");
        return;
    }

    /* =============================
       🚫 DATE & TIME VALIDATION
    ============================= */

    const now = new Date();
    now.setSeconds(0, 0);

    const selectedDateTime = new Date(`${orderdate}T${ordertime}:00`);

    // ❌ past date or past time
    if (selectedDateTime < now) {
        showToast("Past date or past time cannot be updated", "warning");
        return;
    }

    // ❌ exact current time
    if (selectedDateTime.getTime() === now.getTime()) {
        showToast("Current time is not allowed. Select future time.", "warning");
        return;
    }

    /* =============================
       ✅ CONTINUE YOUR EXISTING CODE
    ============================= */

    const plate_count = Number($('#plate_count').val()) || 0;
    const plate_cost = Number($('#plate_price').val()) || 0;
    const total_amount = Number($('#total_amount').val()) || 0;
    const grand_total = Number($('#grand_total').val()) || 0;

    const fooditems = getItemsFromTextarea();
    const services = [];

    document.querySelectorAll("#services_container .service-row").forEach(row => {
        const sno = row.dataset.sno ? Number(row.dataset.sno) : null;
        const name = row.querySelector("input[type='text']")?.value.trim();
        const cost = Number(row.querySelector(".service-cost")?.value) || 0;

        if (name) {
            services.push({ sno, name, cost });
        }
    });
    // 🚨 SERVICE VALIDATION
    if (!validateServicesAmount()) {
        return;
    }


    const payload = {
        load: "updateorder",
        customerid,
        addressid,
        orderdate,
        ordertime,
        plates_count: plate_count,
        plate_cost,
        total_amount,
        grand_total,
        fooditems,
        services
    };

    $.ajax({
        type: "POST",
        url: "./webservices/catering.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (res) {
            if (res.status === "success") {
                showToast("Order updated successfully", "success");
                fetchAllOrders();
            } else {
                showToast(res.message || "Update failed", "error");
            }
        },
    });
}

document.addEventListener("DOMContentLoaded", function () {

    const saveBtn = document.getElementById("save-menu");

    if (!saveBtn) {
        showToast("Save button (#save-menu) not found", "error");
        return;
    }

    saveBtn.addEventListener("click", function () {

        const payload = {
            load: "checkorder",
            customerid: document.querySelector(".customer_id")?.dataset.cid,
            addressid: selectedAddressId,
            orderdate: $('#order-date').val(),
            ordertime: $('#order-time').val()
        };

        $.ajax({
            type: "POST",
            url: "./webservices/catering.php",
            data: JSON.stringify(payload),
            contentType: "application/json",
            dataType: "json",

            success: function (res) {
                if (res.exists) {
                    updateOrder();        // 🔁 UPDATE
                } else {
                    saveCateringOrder();  // 💾 SAVE
                }
            },
        });
    });

});

document.getElementById("cancel-menu").addEventListener("click", cancelOrder);
function cancelOrder() {
    const customerid = document.querySelector(".customer_id")?.dataset.cid;
    const addressid = selectedAddressId;
    var orderdate = $('#order-date').val();
    var ordertime = $('#order-time').val();


    if (!customerid || !addressid || !orderdate) {
        showToast("Missing order details", "warning");
        return;
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0); // normalize

    const selectedDate = new Date(orderdate);
    selectedDate.setHours(0, 0, 0, 0);

    if (selectedDate < today) {
        showToast("Past orders cannot be cancelled", "warning");
        return;
    }

    if (!confirm("Are you sure you want to cancel this order?")) {
        return;
    }

    const payload = {
        load: "cancelmenu",
        customerid: customerid,
        addressid: addressid,
        orderdate: orderdate,
        ordertime: ordertime,
    };

    $.ajax({
        type: "POST",
        url: "./webservices/catering.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (response) {
            if (response.status === "success") {
                showToast("Order cancelled successfully", "success");
                fetchmenu(customerid, addressid);
                clearMenuUI();
                fetchAllOrders();
                showCancelRefundUI();


            } else {
                showToast(response.message || "Failed to cancel order", "warning");
            }
        },
    });
}

// right side div 
function fetchAllOrders() {

    var payload = {
        load: "allorders"
    };

    $.ajax({
        type: "POST",
        url: "./webservices/catering.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (response) {

            const container = document.getElementById("ordersList");
            container.innerHTML = "";

            if (
                response.code !== 200 ||
                !Array.isArray(response.data) ||
                response.data.length === 0
            ) {
                container.innerHTML = "<p>No orders found</p>";
                return;
            }

            response.data.forEach(order => {
                const div = document.createElement("div");
                div.className = "order-card";
                div.onclick = function () {
                    triggermenudetails(
                        order.customer_id,
                        order.order_date,
                        order.order_time
                    );
                };



                div.innerHTML = `
                    <p><b>Date:</b> ${order.order_date}</p>
                    <p><b>Time:</b> ${order.order_time}</p>
                    <p><b>Customer ID:</b> ${order.customer_id}</p>
                    <p><b>Customer Name:</b> ${order.CustomerName}</p>
                    <p><b>Phone No:</b> ${order.address_ph_number}</p>
                    <p><b>Address:</b> ${order.full_address}</p>
                `;

                container.appendChild(div);
            });
        },
    });
}

// Load on page open
fetchAllOrders();

function fetchmenu(customerid, addressid) {


    // ✅ FIXED selectors
    var orderdate = $('#order-date').val();
    var ordertime = $('#order-time').val();

    // 🔒 HARD GUARD
    if (!customerid || !addressid || !orderdate || !ordertime) {
        showToast("Missing required data", "warning", {
            customerid, addressid, orderdate, ordertime
        });
        return;
    }

    var payload = {
        load: "fetchmenu",
        customerid: customerid,
        addressid: addressid,
        orderdate: orderdate,
        ordertime: ordertime
    };
    $.ajax({
        type: "POST",
        url: "./webservices/catering.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (response) {


            if (response.code !== 200 || !Array.isArray(response.data) || response.data.length === 0) {
                clearMenuUI();
                return;
            }

            const rows = response.data;

            /* ======================
               ITEMS (GROUP & COUNT)
            ====================== */
            // const foodMap = {};

            // rows.forEach(row => {
            //     const name = row.item_name;
            //     const qty = parseInt(row.item_qty) || 0;

            //     foodMap[name] = (foodMap[name] || 0) + qty;
            // });

            const textarea = document.getElementById("item-names");
            textarea.value = "";

            const uniqueItems = new Set();
            response.data.forEach(row => {
                if (row.item) uniqueItems.add(row.item);
            });

            uniqueItems.forEach(item => {
                textarea.value += item + "\n";
            });


            /* ======================
               SERVICES (UNIQUE)
            ====================== */
            const serviceMap = {};

            rows.forEach(row => {
                if (row.services_name && row.sno && !serviceMap[row.sno]) {
                    serviceMap[row.sno] = {
                        service_name: row.services_name,
                        service_cost: row.services_cost,
                        services_sno: row.sno   // ✅ stable primary key
                    };
                }
            });

            renderServices(Object.values(serviceMap));

            /* ======================
               PLATE INFO
            ====================== */
            const first = rows[0];

            document.getElementById("plate_count").value = first.order_count;
            document.getElementById("plate_price").value = first.plate_cost;
            document.getElementById("total_amount").value = first.total_amount;
            document.getElementById("remarks-input").value = first.order_remarks;


            updateSummary();
            updateGrandTotal();
            order_status = response.order_status;
            localStorage.setItem("orderstatus", order_status);
        },
    });
}

let order_status = null;
function autoFetchOnDateTimeChange() {
    const customerid = document.querySelector(".customer_id")?.dataset.cid;
    const addressid = selectedAddressId;

    const orderdate = $('#order-date').val();
    const ordertime = $('#order-time').val();

    // 🔒 Fetch ONLY when everything is selected
    if (!customerid || !addressid || !orderdate || !ordertime) {
        return;
    }

    fetchmenu(customerid, addressid);
}

// 🔁 Trigger when date or time changes
$(document).on('change', '#order-date, #order-time', function () {
    autoFetchOnDateTimeChange();
});

function renderServices(services) {
    const container = document.getElementById("services_container");
    container.innerHTML = "";

    services.forEach(srv => {
        const row = document.createElement("div");
        row.className = "service-row";

        // 🔥 THIS IS THE MOST IMPORTANT LINE
        row.dataset.sno = srv.services_sno;

        row.innerHTML = `
            <input type="text" value="${srv.service_name}">
            <input type="number" class="service-cost"
                   value="${srv.service_cost}"
                   oninput="updateGrandTotal()">
            <button type="button" class="remove-service"
                    onclick="removeServiceRow(this)">
                <i class="fa fa-trash"></i>
            </button>
            `;

        container.appendChild(row);
    });
}



function clearMenuUI() {

    // Food items
    const textarea = document.getElementById("item-names");
    if (textarea) textarea.value = "";

    // Plate info
    document.getElementById("plate_count").value = "";
    document.getElementById("plate_price").value = "";
    document.getElementById("total_amount").value = "";
    document.getElementById("grand_total").value = "";

    // Services
    const services = document.getElementById("services_container");
    if (services) services.innerHTML = "";

    // Optional: reset summary
    updateGrandTotal();

}




function setpaymentvariables() {
    // localstorage variables
    const grandtotal = Number(document.getElementById("grand_total").value) || 0;
    const orderdate = document.querySelector("input[type='date']").value;
    const ordertime = document.querySelector("input[type='time']").value;
    const customerid = document.querySelector(".customer_id")?.dataset.cid;


    const addressid =
        document.querySelector('.address_block.highlight-address')
            ?.getAttribute("data-block-aid");



    localStorage.setItem("customerid", customerid);
    localStorage.setItem("addressid", addressid);
    localStorage.setItem("orderdate", orderdate);
    localStorage.setItem("ordertime", ordertime);
    localStorage.setItem("grandtotal", grandtotal);
    window.location.href = "cateringservicespayments.php";


}





document.getElementById("item-names").addEventListener("input", function () {

    const raw = this.value.toLowerCase();

    // 🔥 Split ONLY by newline, comma, or dot
    const tokens = raw
        .split(/[\n,.]+/)
        .map(x => x.trim())
        .filter(Boolean);

    const seen = new Set();
    const duplicates = new Set();

    tokens.forEach(token => {

        // 🔥 Remove quantity ONLY if it is at the end (idly-3 → idly)
        const baseItem = token.replace(/-\d+$/, '').trim();

        if (seen.has(baseItem)) {
            duplicates.add(baseItem);
        }
        seen.add(baseItem);
    });

    const warningEl = document.getElementById("duplicate-warning");

    if (duplicates.size > 0) {
        warningEl.style.visibility = "visible";
        warningEl.textContent =
            "Duplicate item found: " + Array.from(duplicates).join(", ");
    } else {
        warningEl.style.visibility = "hidden";
        warningEl.textContent = "";
    }
});








function loadpaymode() {
    var payload = {
        load: "loadpaymode",
        sno: "",
        type: "type"
    };

    $.ajax({
        type: "POST",
        url: "./webservices/cateringservicespayments.php",
        data: JSON.stringify(payload),
        dataType: "json",
        contentType: "application/json",
        beforeSend: function () {
        },
        success: function (response) {


            let dropdown = document.getElementById("pay_mode");
            dropdown.innerHTML = ""; // Clear existing options

            let defaultOption = document.createElement('option');
            defaultOption.value = "";
            defaultOption.text = "Select paymode";
            // defaultOption.disabled = true;
            defaultOption.selected = true;
            dropdown.appendChild(defaultOption);

            if (response.status === "success" && response.data.length > 0) {
                response.data.forEach(x => {
                    let option = document.createElement('option');
                    option.value = x.sno;
                    option.text = x.type;
                    dropdown.appendChild(option);
                });
            } else {
                let noDataOption = document.createElement('option');
                noDataOption.value = "";
                noDataOption.text = "No types available";
                noDataOption.disabled = true;
                dropdown.appendChild(noDataOption);
            }
        },
    });
}
loadpaymode();


let redirectVariable = 0;

function setRedirectVariable() {
    redirectVariable = 1;
    localStorage.setItem('redirectVariable', redirectVariable);
}
let redirectCid = localStorage.getItem("redirectCid");

$(document).ready(function () {
    if (redirectCid && redirectCid !== "0") {
        fetchbyid(redirectCid);
        localStorage.setItem('redirectCid', "0");
    }
});





















