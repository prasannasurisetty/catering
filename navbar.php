<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home Delivery Dashboard</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f5f6fa;
    }

    /* Navbar styling */
    .navbar {
      background-color: #fafafa;

      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 20px;
      position: relative;
      height: 5vh;
      border-bottom: 1px solid #e5e7eb;
    }

    .logo {
      font-size: 20px;
      font-weight: bold;
      color: #111827;

    }

    /* Main menu */
    .menu {
      display: flex;
      align-items: center;
      gap: 25px;
      position: relative;
    }

    .menu-item {
      position: relative;
      cursor: pointer;
      font-weight: bold;
      color: #111827;
      padding: 5px;
      border-radius: 6px;
    }

    .menu-item:hover {
      background: #e5eefc;
      /* ✅ Hover background */
      color: #014AAC;
      /* ✅ Hover text color */
    }

    /* Submenu (dropdown) */
    .submenu {
      display: none;
      position: absolute;
      top: 15px;
      left: 0;
      background: white;
      color: #333;
      min-width: 180px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
      overflow: hidden;
      z-index: 1000;
    }

    .submenu li {
      list-style: none;
      padding: 10px 15px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .submenu li:hover {
      background: #e5eefc;
      /* ✅ Updated hover */
      color: #014AAC;
      /* ✅ Updated hover text */
    }

    .menu-item:hover .submenu {
      display: block;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-5px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Profile */
    .profile {
      position: relative;
      cursor: pointer;
    }

    .profile img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      border: 2px solid white;
    }

    .profile-dropdown {
      position: absolute;
      top: 55px;
      right: 0;
      background: white;
      color: #333;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      border-radius: 10px;
      width: 180px;
      display: none;
    }

    .profile-dropdown.active {
      display: block;
      animation: fadeIn 0.3s ease;
    }

    .profile-dropdown ul li:hover {
      background: #e5eefc;
      /* ✅ Updated hover */
      color: #014AAC;
      /* ✅ Updated hover text */
    }

    .logout {
      color: red;
      font-weight: bold;
    }

    .content {
      padding: 30px;
    }

    ul.submenu {
      padding: 0px;
    }

    a {
      color: black;
      text-decoration: none;
    }


    .profile-nav-container {
      position: relative;
      display: inline-block;
      margin-right: -36%;
      color: #111827;
    }

    .profile-nav {
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 8px 12px;
      border-radius: 4px;
      background: #f5f5f5;
      transition: background 0.2s;
    }

    .profile-nav:hover {
      background: #e5eefc;
      color: #014AAC;
    }

    .profile-subnav {
      display: none;
      position: absolute;
      top: 110%;
      right: 0;
      background: #fff;
      min-width: 140px;
      box-shadow: 0 2px 8px #0002;
      border-radius: 4px;
      z-index: 100;
      flex-direction: column;
    }

    .profile-subnav a {
      padding: 10px 16px;
      color: #333;
      text-decoration: none;
      display: block;
      border-bottom: 1px solid #eee;
      transition: background 0.2s;
    }

    .profile-subnav a:last-child {
      border-bottom: none;
    }

    .profile-subnav a:hover {
      background: #f0f0f0;
    }

    .fa-right-from-bracket {
      font-size: 26px;
    }

    .profile-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .45);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    .profile-box {
      position: relative;
      background: #fff;
      width: 340px;
      padding: 20px;
      border-radius: 10px;
      animation: fadein .25s;
      font-family: sans-serif;
    }

    @keyframes fadein {
      from {
        opacity: 0;
        transform: scale(.9);
      }

      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    .profile-avatar {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      background: #4566c7;
      color: #fff;
      font-size: 32px;
      line-height: 70px;
      margin: auto;
      text-align: center;
    }

    .profile-row {
      display: flex;
      justify-content: space-between;
      margin: 8px 0;
      font-size: 14px;
    }


    .profile-close {
      position: absolute;
      top: 8px;
      right: 12px;
      font-size: 18px;
      font-weight: bold;
      cursor: pointer;
      color: #555;
    }

    .profile-close:hover {
      color: #000;
    }

    .logo img {
      width: 65px;
    }
  </style>
</head>

<body>

  <div class="navbar">
    <div class="logo"><a href="catering.php"><img src="images/logo.png" alt="" srcset=""></a></div>
   



    <div class="menu">
      <div class="menu-item">
        Settings ▾
        <ul class="submenu">
          <li><a href="addfoodtype.php">Add Category</a></li>
          <li><a href="addcategory.php">Add Sub Category</a></li>
          <li><a href="additem.php">Add Items</a></li>
          <li><a href="addprices.php">Add Prices</a></li>
          <li><a href="adddeliveryboy.php">Add Delivery Boy</a></li>
          <li><a href="addoperator.php">Add Operator</a></li>
          <li><a href="selectprinter.php">Add Printer</a></li>
          <li><a href="menuscheduling.php">Menu scheduling</a></li>
              <li><a href="cateringservices.php">Catering Services</a></li>



        </ul>
      </div>

      <div class="menu-item">
        Billing 
        <!-- <ul class="submenu">
          <li><a href="kot.php">KOT</a></li>
            <li><a href="orderreview.php">Return Orders</a></li>
          <li><a href="billing.php">Billing</a></li>
          <li><a href="summary.php">Reports</a></li>
        </ul> -->
      </div>
    </div>

    <div class="profile-nav-container">
      <div class="profile-nav" id="profileNavBtn" onclick="toggleProfileNav()">
        <i class="fa-solid fa-user"></i>
        <i class="fa-solid fa-caret-down"></i>
      </div>
      <div class="profile-subnav" id="profileSubNav">
        <a href="#" onclick="showProfile()">Profile</a>
        <a href="#" onclick="logout()">Logout</a>
      </div>
    </div>



    <div class="profile-dialog">

    </div>
  </div>

</body>


<script>
  function toggleProfileNav() {
    const nav = document.getElementById('profileSubNav');
    nav.style.display = nav.style.display === 'block' ? 'none' : 'block';
    // Close on outside click
    document.addEventListener('click', function handler(e) {
      if (!e.target.closest('.profile-nav-container')) {
        nav.style.display = 'none';
        document.removeEventListener('click', handler);
      }
    });
  }



  function logout() {
    if (confirm("Are you sure you want to logout?")) {
      localStorage.removeItem("adminmobile");
      localStorage.removeItem("adminid");
      window.location.href = "index.php";
    }
  }








  function showProfile() {

    $.ajax({
      type: "POST",
      url: "./webservices/userregistration.php",
      data: JSON.stringify({
        load: 'getUser',
        adminid: localStorage.getItem('adminid')
      }),
      contentType: "application/json",
      dataType: "json",

      success: function(res) {

        const u = (res.data && res.data[0]) || null;

        let modal = document.getElementById("user_profile_modal");
        if (!modal) {
          document.body.insertAdjacentHTML("beforeend", `
  <div id="user_profile_modal" class="profile-overlay" style="display:none;">
    <div class="profile-box">
      <div class="profile-close" id="close_profile_btn">×</div>
      <div id="profile_content"></div>
    </div>
  </div>
`);


          document.getElementById("close_profile_btn").onclick = () =>
            modal.style.display = "none";

          modal = document.getElementById("user_profile_modal");
        }

        document.getElementById("profile_content").innerHTML = u ? `
        <div class="profile-avatar">${u.admin_name[0].toUpperCase()}</div>
        <h3 style="text-align:center;margin:10px 0 15px;">${u.admin_name}</h3>
        <div class="profile-row"><span>ID</span><span>${u.adminid}</span></div>
        <div class="profile-row"><span>Mobile</span><span>${u.admin_mobile}</span></div>
        <div class="profile-row"><span>Email</span><span>${u.admin_email}</span></div>
        <div class="profile-row"><span>Role</span><span>${u.userType}</span></div>
      ` : `<p style="text-align:center;">No profile found</p>`;

        modal.style.display = "flex";
      },

      error: e => alert("Unable to load profile")
    });

  }
</script>

</html>