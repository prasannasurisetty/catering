
let selectedAddressId = null;
let checkedAddressIds = [];
const searchinput = document.getElementById("search_input");
if (searchinput) {
    searchinput.addEventListener("input", () => {
        const value = searchinput.value.trim();

        // If empty, clear suggestions and skip request
        const recmd = document.querySelector(".recmd");
        if (!value) {
            if (recmd) {
                recmd.innerHTML = "";
                recmd.style.display = "none";
            }
            return;
        }

        const payload = {
            load: "search",
            searchvalue: value,
        };

        $.ajax({
            type: "POST",
            url: "./webservices/register.php",
            data: JSON.stringify(payload),
            dataType: "json",
            success: function (response) {
                if (!recmd) return;
                recmd.innerHTML = "";

                if (response.data && response.data.length > 0) {
                    recmd.style.display = "flex";
                    response.data.forEach((itm) => {
                        const para = document.createElement("p");
                        para.setAttribute("class", "multiple_list_item");
                        para.setAttribute("onclick", `fetchbyid(${itm.CustomerID})`);
                        para.innerHTML = `
                  <span>${itm.CustomerID}-</span>
                  <span>${itm.CustomerName} (</span>
                  <span>${itm.Phone1})</span>
                `;
                        recmd.appendChild(para);
                    });
                } else {
                    recmd.style.display = "none";
                }
            },
            error: function (err) {
                //console.log(err);
                alert("Something Wrong");
            },
        });
    });
}

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

}


function fetchbyid(sinput) {
    const payload = {
        load: "fetchbyid",
        customerid: sinput,
    };

    $.ajax({
        type: "POST",
        url: "./webservices/register.php",
        data: JSON.stringify(payload),
        dataType: "json",
        success: function (response) {
            if (response.status === "Success" && response.data && response.data[0]) {
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

                document.getElementById("menu-order").style.display = "none";

            } else {
                alert("NO DATA FOUND");
                document.querySelector(".catering-ordering").style.display = "none";
                document.getElementById("addresses_container").style.display = "none";
                document.getElementById("menu-order").style.display = "none";
            }
        },
        error: function (err) {
            alert("Something wrong");
            //console.log(err);
        },
    });
}

function deleteAddress(aid) {
    if (!confirm("Are you sure you want to delete this address?")) return;

    const payload = {
        load: "deleteAddress",
        aid: aid,
    };

    $.ajax({
        type: "POST",
        url: "./webservices/home.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",
        success: function (response) {
            if (response.status === "success") {
                alert("Address deleted successfully!");
                loadAddress();
            } else {
                alert("Delete failed: " + (response.message || "Unknown error"));
            }
        },
        error: function (err) {
            //console.log("Error deleting address:", err);
            alert("Error deleting address.");
        },
    });
}

function edit_address(address) {
    addAddress(); // show/create modal

    // Fill fields
    document.getElementById("houseno").value = address.flatno || "";
    document.getElementById("street").value = address.street || "";
    document.getElementById("area").value = address.area || "";
    document.getElementById("landmark").value = address.landmark || "";
    document.getElementById("ph_number").value = address.address_ph_number || "";
    document.getElementById("address_link").value = address.addresslink || "";
    document.getElementById("pincode").value = address.pincode || "";

    const defaultCheckbox = document.getElementById("default_address");
    defaultCheckbox.checked = address.isDefault == 1;

    // Prevent unchecking if it's currently default (until another is made default)
    defaultCheckbox.onchange = function () {
        if (address.isDefault == 1 && !defaultCheckbox.checked) {
            alert(
                "Cannot remove this address from default. Make another address default first."
            );
            defaultCheckbox.checked = true;
        }
    };

    let btn = document.getElementById("submitAddressBtn");
    btn.innerText = "Update";
    btn = resetButtonEvents(btn);

    btn.onclick = function () {
        const cidEl = document.querySelector(".customer_id");
        const cid = cidEl ? cidEl.dataset.cid : null;

        if (!cid) {
            alert("Please select a customer before updating address.");
            return;
        }

        const phone = (document.getElementById("ph_number").value || "").trim();
        if (!/^[6-9][0-9]{9}$/.test(phone)) {
            alert("Please enter a valid 10-digit phone number starting with 6, 7, 8, or 9.");
            return;
        }

        const payload = {
            load: "editAddress",
            aid: address?.aid,
            cid: cid,
            houseno: (document.getElementById("houseno").value || "").trim(),
            street: (document.getElementById("street").value || "").trim(),
            area: (document.getElementById("area").value || "").trim(),
            landmark: (document.getElementById("landmark").value || "").trim(),
            phone: phone,
            link: (document.getElementById("address_link").value || "").trim(),
            pincode: (document.getElementById("pincode").value || "").trim(),
            default: defaultCheckbox.checked ? 1 : 0,
        };

        // //console.log("Updated Address Payload:", payload);

        $.ajax({
            type: "POST",
            url: "./webservices/home.php",
            data: JSON.stringify(payload),
            contentType: "application/json",
            dataType: "json",
            success: function (response) {
                alert(response.message || "Address updated.");
                closeAddressModal();
                loadAddress();
            },
            error: function (err) {
                console.error("Error updating address:", err);
                alert("Error updating address.");
            },
        });
    };
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
            alert("Please select a customer before adding address.");
            return;
        }

        const phone = (document.getElementById("ph_number").value || "").trim();
        if (!/^[6-9][0-9]{9}$/.test(phone)) {
            alert("Please enter a valid 10-digit phone number starting with 6, 7, 8, or 9.");
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

        // //console.log("Address Submitted:", payload);

        $.ajax({
            type: "POST",
            url: "./webservices/home.php",
            data: JSON.stringify(payload),
            contentType: "application/json",
            dataType: "json",
            success: function (response) {
                alert(response.message || "Address added.");
                loadAddress();
                closeAddressModal();
            },
            error: function (err) {
                console.error("Error adding address:", err);
                alert("Error adding address.");
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

    // if (!cid) {
    //     console.warn("No customer selected, cannot load addresses.");
    //     return;
    // }

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

                    <p class="address-actions"
                       style="color:red;justify-self:end;display:flex;position:absolute;bottom:8px;gap:10px;align-items:center;">
                      <i class="fa fa-pencil edit-address"
                         style="cursor:pointer;"
                         data-address="${encodeURIComponent(JSON.stringify(address))}"></i>
                      <i class="fa fa-trash delete-address"
                         style="cursor:pointer;"
                         data-aid="${address.aid}"></i>
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
                // //console.log("Checked Address IDs (Init):", checkedAddressIds);

                // Optional external function
                if (typeof autoLoadActiveFoodtype === "function") {
                    autoLoadActiveFoodtype();
                }
            } else {
                alert("No address found");
            }
        },
        error: function (err) {
            //console.log(err);
            alert("Error loading address list.");
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

    row.innerHTML = `
        <input type="text" placeholder="Enter Service Type">
        <input type="number" placeholder="Amount ₹" class="service-cost" oninput="updateGrandTotal()">
        <button class="remove-service" onclick="removeServiceRow(this)">
            <i class="fa fa-trash"></i>
        </button>
        `;

    container.appendChild(row);
}
document.addEventListener("DOMContentLoaded", function () {
    addServiceRow(); // default one row
});

function removeServiceRow(btn) {
    btn.parentElement.remove();
    updateGrandTotal();
}

function getItemsFromTextarea() {
    const text = document.getElementById("item-names").value.trim();
    if (!text) return [];

    return text
        .split("\n")
        .map(line => line.trim())
        .filter(Boolean);
}

function saveCateringOrder() {
    const cidEl = document.querySelector(".customer_id");
    const customer_id = cidEl ? cidEl.dataset.cid : null;

    if (!customer_id) {
        alert("Select customer first");
        return;
    }

    if (!selectedAddressId) {
        alert("Select address first");
        return;
    }

    const orderDateEl = document.querySelector("input[type='date']");
    const orderTimeEl = document.querySelector("input[type='time']");

    const order_date = orderDateEl ? orderDateEl.value : "";
    const order_time = orderTimeEl ? orderTimeEl.value : "";

    if (!order_date || !order_time) {
        alert("Select order date and time");
        return;
    }

    const plate_count = Number(document.getElementById("plate_count").value) || 0;
    const plate_cost = Number(document.getElementById("plate_price").value) || 0;
    const total_amount = Number(document.getElementById("total_amount").value) || 0;
    const grand_total = Number(document.getElementById("grand_total").value) || 0;

    if (plate_count <= 0 || plate_cost <= 0) {
        alert("Enter valid plate count and plate cost");
        return;
    }

    /* ================= FOOD ITEMS (supports vada-2 format) ================= */

    const fooditems = [];
    const itemsText = document.getElementById("item-names").value.trim();

    if (!itemsText) {
        alert("Enter food items");
        return;
    }

    itemsText.split("\n").forEach(line => {
        const val = line.trim();
        if (!val) return;

        let name = val;
        let qty = 1;

        // Support format: item-qty (e.g., vada-2)
        if (val.includes("-")) {
            const parts = val.split("-");
            name = parts[0].trim();
            qty = parseInt(parts[1], 10) || 1;
        }

        fooditems.push({
            name: name,
            qty: qty
        });
    });

    if (!fooditems.length) {
        alert("No valid food items found");
        return;
    }

    /* ================= SERVICES (OPTIONAL) ================= */

    const services = [];
    document.querySelectorAll("#services_container .service-row").forEach(row => {
        const nameInput = row.querySelector("input[type='text']");
        const costInput = row.querySelector(".service-cost");

        const name = nameInput ? nameInput.value.trim() : "";
        const cost = costInput ? Number(costInput.value) : 0;

        if (name && cost > 0) {
            services.push({
                name: name,
                cost: cost
            });
        }
    });

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
        services: services
    };

    console.log("SAVE ORDER PAYLOAD:", payload);

    /* ================= AJAX ================= */

    $.ajax({
        type: "POST",
        url: "./webservices/catering.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function (response) {
            if (response.status === "success") {
                alert("Order saved successfully");

                // RESET FORM
                document.getElementById("item-names").value = "";
                document.getElementById("plate_count").value = "";
                document.getElementById("plate_price").value = "";
                document.getElementById("total_amount").value = "";
                document.getElementById("grand_total").value = "";
                document.getElementById("services_container").innerHTML = "";

            } else {
                alert(response.message || "Failed to save order");
            }
        },

        error: function (err) {
            console.error("Save order error:", err);
            alert("Server error while saving order");
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const saveBtn = document.getElementById("save-menu");
    if (saveBtn) {
        saveBtn.addEventListener("click", saveCateringOrder);
    }
});

document.getElementById("cancel-menu").addEventListener("click", cancelOrder);

function cancelOrder() {

    if (!confirm("Are you sure you want to cancel this order?")) {
        return;
    }

    const customerid = document.querySelector(".customer_id")?.dataset.cid;
    const addressid = selectedAddressId;
    var orderdate = $('#order-date').val();
    var ordertime = $('#order-time').val();


    if (!customerid || !addressid || !orderdate) {
        alert("Missing order details");
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
                alert("Order cancelled successfully");

                clearMenuUI();
                fetchAllOrders();
            } else {
                alert(response.message || "Failed to cancel order");
            }
        },

        error: function () {
            alert("Server error while cancelling order");
        }
    });
}




// right side div 

function fetchAllOrders() {

    var payload = {
        load: "allorders"
    }

    $.ajax({
        type: "POST",
        url: "./webservices/catering.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",
        success: function (response) {

            const container = document.getElementById("ordersList");
            container.innerHTML = "";

            if (response.code !== 200 || !response.data) {
                container.innerHTML = "<p>No orders found</p>";
                return;
            }

            response.data.forEach(order => {
                const div = document.createElement("div");
                div.className = "order-card";
                div.innerHTML = `
                    <h4>Order - ${order.order_id}</h4>
                    <p><b>Customer:</b> ${order.customer_id}</p>
                    <p><b>Address:</b> ${order.address_id}</p>
                    <p><b>Date:</b> ${order.order_date}</p>
                    <p><b>Total:</b> ₹${order.grand_total}</p>
                `;
                container.appendChild(div);
            });
        },
        error: function () {
            alert("Error loading orders");
        }
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
        console.warn("Missing required data", {
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
            const foodMap = {};

            rows.forEach(row => {
                const name = row.item_name;
                const qty = parseInt(row.item_qty) || 0;

                foodMap[name] = (foodMap[name] || 0) + qty;
            });

            const textarea = document.getElementById("item-names");
            textarea.value = "";

            Object.entries(foodMap).forEach(([name, qty]) => {
                textarea.value += `${name} (${qty})\n`;
            });

            /* ======================
               SERVICES (UNIQUE)
            ====================== */
            const serviceMap = {};

            rows.forEach(row => {
                if (row.services_name) {
                    serviceMap[row.services_name] = row.services_cost;
                }
            });

            renderServices(
                Object.entries(serviceMap).map(([name, cost]) => ({
                    service_name: name,
                    service_cost: cost
                }))
            );

            /* ======================
               PLATE INFO
            ====================== */
            const first = rows[0];

            document.getElementById("plate_count").value = first.order_count;
            document.getElementById("plate_price").value = first.plate_cost;
            document.getElementById("total_amount").value = first.total_amount;

            updateSummary();
            updateGrandTotal();
        },


        error: function () {
            alert("Server error");
        }
    });
}

function autoFetchOnDateTimeChange() {
    console.log("function runnninggggg");
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
function renderServices(services = []) {
    const container = document.getElementById("services_container");
    container.innerHTML = "";

    services.forEach(service => {
        const row = document.createElement("div");
        row.className = "service-row";

        row.innerHTML = `
            <input type="text" value="${service.service_name}" readonly>
            <input type="number" class="service-cost" value="${service.service_cost}" readonly>
            <button class="remove-service" disabled>
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

















