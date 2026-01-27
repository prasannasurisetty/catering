<!DOCTYPE html>
<html>

<head>
  <title>Organization</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <style>
    body {
      font-family: Arial;
      background: #f4f4f4;
    }

    .container {
      display: flex;
      gap: 20px;
      padding: 20px;
    }

    .box {
      background: #fff;
      padding: 15px;
      border-radius: 5px;
    }

    input {
      width: 100%;
      padding: 6px;
      margin: 5px 0;
    }

    button {
      padding: 6px 12px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th,
    td {
      border: 1px solid #ccc;
      padding: 6px;
    }
  </style>
</head>

<body>

  <div class="container">

    <!-- FORM -->
    <div class="box" style="width:40%">
      <h3>Organization Details</h3>

      <input type="hidden" id="id">

      <label>Organization Name</label>
      <input type="text" id="organization_name">

      <label>Legal Type</label>
      <input type="text" id="legal_type">

      <label>PAN</label>
      <input type="text" id="pan">

      <label>CIN</label>
      <input type="text" id="cin">

      <label>GSTIN</label>
      <input type="text" id="gstin">

      <button onclick="saveOrganization()">Save</button>
      <button onclick="clearOrgForm()">Cancel</button>
    </div>

    <!-- TABLE -->
    <div class="box" style="width:60%">
      <table>
        <thead>
          <tr>
            <th>Organization</th>
            <th>Legal Type</th>
            <th>CIN</th>
            <th>PAN</th>
            <th>GSTIN</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="orgData"></tbody>
      </table>
    </div>

  </div>

  <script>
    $(document).ready(function() {
      fetchOrganization();
    });

    function saveOrganization() {

      var payload = {
        load: "saveOrganization",
        id: $("#id").val(),
        organization_name: $("#organization_name").val(),
        legal_type: $("#legal_type").val(),
        pan: $("#pan").val(),
        cin: $("#cin").val(),
        gstin: $("#gstin").val()
      };

      console.log("saveOrganization payload", payload);

      $.ajax({
        type: "POST",
      url: "./webservices/orgregistration.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function(response) {

          if (response.code !== 200) {
            alert(response.message || "Save failed");
            return;
          }

          clearOrgForm();
          fetchOrganization();
        },

        error: function() {
          alert("Server error");
        }
      });
    }


    function fetchOrganization() {

      var payload = {
        load: "fetchOrganization"
      };

      console.log("fetchOrganization payload", payload);

      $.ajax({
        type: "POST",
        url: "./webservices/orgregistration.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function(response) {

          if (response.code !== 200 || !Array.isArray(response.data)) {
            $("#orgData").html("");
            return;
          }

          let html = "";

          response.data.forEach(row => {
            html += `
          <tr>
            <td>${row.organization_name}</td>
            <td>${row.legal_type}</td>
            <td>${row.cin}</td>
            <td>${row.pan}</td>
            <td>${row.gstin}</td>
            <td>
              <button onclick="editOrganization(${row.id})">Edit</button>
              <button onclick="toggleOrgStatus(${row.id})">
                ${row.status == 1 ? 'Active' : 'Inactive'}
              </button>
            </td>
          </tr>
        `;
          });

          $("#orgData").html(html);
        },

        error: function() {
          alert("Server error");
        }
      });
    }



    function editOrganization(id) {

      var payload = {
        load: "getOrganization",
        id: id
      };

      console.log("getOrganization payload", payload);

      $.ajax({
        type: "POST",
        url: "./webservices/orgregistration.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function(response) {

          if (response.code !== 200 || !response.data) return;

          const o = response.data;

          $("#id").val(o.id);
          $("#organization_name").val(o.organization_name);
          $("#legal_type").val(o.legal_type);
          $("#pan").val(o.pan);
          $("#cin").val(o.cin);
          $("#gstin").val(o.gstin);
        }
      });
    }


    function toggleOrgStatus(id) {

      var payload = {
        load: "toggleOrgStatus",
        id: id
      };

      $.ajax({
        type: "POST",
        url: "./webservices/orgregistration.php",
        data: JSON.stringify(payload),
        contentType: "application/json",
        dataType: "json",

        success: function() {
          fetchOrganization();
        }
      });
    }


    function clearOrgForm() {
      $("#id").val("");
      $("#organization_name").val("");
      $("#legal_type").val("");
      $("#pan").val("");
      $("#cin").val("");
      $("#gstin").val("");
    }
  </script>
</body>

</html>