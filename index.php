<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Login - HDN</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="css/index.css"> 
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>

<body>
  <div class="login-container">
    <h2>Login</h2>

    <form id="loginForm" method="post" action="">
      <div class="form-group">
        <label for="mobile_number">Mobile Number / Email</label>
        <input type="text" id="mobile_number" name="mobile_number" placeholder="Enter 10-digit mobile or email">
        <div id="mobile_error" class="error-message"></div>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Password">
        <span id="togglePassword" class="toggle-password">👁️</span>
        <div id="password_error" class="error-message"></div>
      </div>

      <button type="submit" id="loginBtn">Login</button>

      <div class="forgot-password">
        <a href="forgotpassword.php">Forgot Password?</a>
      </div>

      <div id="success_message" class="success-message"></div>
    </form>
  </div>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 <script src="scriptfiles/index.js" defer></script>

</body>

</html>