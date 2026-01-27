<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Delivery Challan</title>
<style>
  body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f4f4;
    padding: 20px;
  }
  .challan {
    max-width: 900px;
    margin: auto;
    background: #fff;
    padding: 22px 26px;
    border: 1px solid #000;
    margin-bottom: 30px;
  }
  h2 {
    text-align: center;
    margin: 0 0 14px;
    text-decoration: underline;
  }
  .row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 14px;
  }
  .col {
    width: 48%;
  }
  .label {
    font-weight: bold;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    margin-top: 10px;
  }
  table th, table td {
    border: 1px solid #000;
    padding: 6px;
    text-align: center;
  }
  table th {
    background: #f0f0f0;
  }
  .footer {
    margin-top: 30px;
    display: flex;
    justify-content: space-between;
    font-size: 14px;
  }
  .stamp {
    border: 1px solid #000;
    padding: 14px;
    width: 230px;
    text-align: center;
  }
  .note {
    margin-top: 10px;
    font-size: 13px;
  }
</style>
</head>
<body>
<!-- RETURNED CHALLAN -->
<div class="challan">
  <h2>RETURNABLE DELIVERY CHALLAN</h2>
  <div class="row">
    <div class="col">
      <b>VEDYA HOSPITALITIES PVT. LTD.</b><br>
      55/8/29, HB Colony Rd, beside Sri Venkateswara Swamy Temple,<br>
      KRM Colony, Maddilapalem, Visakhapatnam – 530013
    </div>
    <div class="col">
      <b>Challan No:</b> RDC-001<br>
      <b>Date:</b> DD/MM/YYYY<br>
      <b>Time:</b> HH:MM
    </div>
  </div>
  <div class="row">
    <div class="col">
      <span class="label">Customer Name:</span> ___________<br>
      <span class="label">Phone:</span> XXXXXXXXXX
    </div>
    <div class="col">
      <span class="label">Address:</span><br>
      ___________________________<br>
      ___________________________
    </div>
  </div>
  <table>
    <thead>
      <tr>
        <th>S.No</th>
        <th>Utensil Name</th>
        <th>Issued Qty</th>
     
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Plates</td>
        <td>100</td>

      </tr>
      <tr>
        <td>2</td>
        <td>Glasses</td>
        <td>500</td>
   
      </tr>
      <tr>
        <td>3</td>
        <td>Spoons</td>
        <td>500</td>

      </tr>
    </tbody>
  </table>

  <div class="footer">
  
    <div class="stamp">
      Company Stamp<br><br>
      Authorized Signature
    </div>
       <div class="stamp">
      Company Stamp<br><br>
      Authorized Signature
    </div>
  </div>
</div>



<!-- UNRETURNED CHALLAN -->
<div class="challan">
  <h2>UNRETURNABLE DELIVERY CHALLAN</h2>
  <div class="row">
    <div class="col">
      <b>VEDYA HOSPITALITIES PVT. LTD.</b><br>
      55/8/29, HB Colony Rd, beside Sri Venkateswara Swamy Temple,<br>
      KRM Colony, Maddilapalem, Visakhapatnam – 530013
    </div>
    <div class="col">
      <b>Challan No:</b> UDC-001<br>
      <b>Date:</b> DD/MM/YYYY<br>
      <b>Time:</b> HH:MM
    </div>
  </div>
  <div class="row">
    <div class="col">
      <span class="label">Customer Name:</span> ___________<br>
      <span class="label">Phone:</span> XXXXXXXXXX
    </div>
    <div class="col">
      <span class="label">Address:</span><br>
      ___________________________<br>
      ___________________________
    </div>
  </div>
  <table>
    <thead>
      <tr>
        <th>S.No</th>
        <th>Service</th>
        <!-- <th>Description</th> -->
        <th>Amount (₹)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Delivery Boys</td>
        <!-- <td>Service Charges</td> -->
        <td>₹ 500</td>
      </tr>
      <tr>
        <td>2</td>
        <td>Transport Service</td>
        <!-- <td>Vehicle & Fuel</td> -->
        <td>₹ 500</td>
      </tr>
    </tbody>
  </table>
  <div class="note">
    <b>Total Amount Due:</b> ₹ 1,000<br>
    <!-- <b>Status:</b> Pending Recovery / Adjustment -->
  </div>
  <div class="footer">
    <!-- <div>
      Receiver Signature<br><br>
      ___________________
    </div> -->
    <div class="stamp">
      Company Stamp<br><br>
      Authorized Signature
    </div>
  </div>
</div>
</body>
</html>




