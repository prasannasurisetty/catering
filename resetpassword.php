<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Reset Password</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f6fa;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .form-container {
      background: white;
      padding: 2rem 2.5rem;
      border-radius: 10px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.1);
      max-width: 400px;
      width: 100%;
      position: relative;
    }

    h2 {
      text-align: center;
      color: #273c75;
      margin-bottom: 1.5rem;
    }

    .input-wrapper {
      position: relative;
      margin-bottom: 1rem;
    }

    input[type="password"],
    input[type="text"] {
      width: 100%;
      padding: 0.7rem 2.2rem 0.7rem 0.7rem;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 1rem;
      box-sizing: border-box;
    }

    .toggle-password {
      position: absolute;
      right: 10px;
      top: 30%;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 1.1rem;
      color: #555;
      user-select: none;
    }

    .message {
      font-size: 0.85rem;
      margin-top: 0.25rem;
    }

    .message.success {
      color: green;
    }

    .message.error {
      color: red;
    }

    button {
      width: 100%;
      padding: 0.8rem;
      background-color: #F3681E;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: 0.5rem;
    }

    button:hover {
      background-color: #d85c16;
    }

    .loader-overlay {
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      background: rgba(255,255,255,0.8);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 999;
    }

    .loader {
      border: 6px solid #f3f3f3;
      border-top: 6px solid #F3681E;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg);}
      100% { transform: rotate(360deg);}
    }

    @media (max-width: 450px) {
      .form-container {
        padding: 1.2rem;
      }
    }
  </style>

  <!-- jQuery CDN -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
  <div class="loader-overlay">
    <div class="loader"></div>
  </div>

  <div class="form-container">
    <h2>Reset Your Password</h2>
    <div class="input-wrapper">
      <input type="password" id="new_password" placeholder="Enter new password" />
      <span class="toggle-password" onclick="toggleVisibility('new_password', this)">👁️</span>
      <div id="new_pass_msg" class="message"></div>
    </div>
    <div class="input-wrapper">
      <input type="password" id="confirm_password" placeholder="Confirm password" />
      <span class="toggle-password" onclick="toggleVisibility('confirm_password', this)">👁️</span>
      <div id="confirm_pass_msg" class="message"></div>
    </div>
    <button type="button" onclick="submitNewPassword()">Submit</button>
  </div>

  <script>
    function getQueryParam(name) {
      const urlParams = new URLSearchParams(window.location.search);
      return urlParams.get(name);
    }

    function toggleVisibility(id, el) {
      const input = document.getElementById(id);
      if (input.type === "password") {
        input.type = "text";
        el.textContent = "🙈";
      } else {
        input.type = "password";
        el.textContent = "👁️";
      }
    }

    function isValidPassword(password) {
      const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
      return regex.test(password);
    }

    function showMessage(id, msg, isValid) {
      const div = document.getElementById(id);
      div.innerText = msg;
      div.className = 'message ' + (isValid ? 'success' : 'error');
    }

    function validatePasswords() {
      const newPassword = $('#new_password').val().trim();
      const confirmPassword = $('#confirm_password').val().trim();

      if (newPassword === '') {
        showMessage('new_pass_msg', '', false);
      } else if (!isValidPassword(newPassword)) {
        showMessage('new_pass_msg', 'Must be 8+ chars, with uppercase, lowercase, number & special character.', false);
      } else {
        showMessage('new_pass_msg', 'Strong password ✅', true);
      }

      if (confirmPassword === '') {
        showMessage('confirm_pass_msg', '', false);
      } else if (confirmPassword !== newPassword) {
        showMessage('confirm_pass_msg', 'Passwords do not match ❌', false);
      } else {
        showMessage('confirm_pass_msg', 'Passwords match ✅', true);
      }
    }

    $('#new_password, #confirm_password').on('input', validatePasswords);

    function submitNewPassword() {
      const newPassword = $('#new_password').val().trim();
      const confirmPassword = $('#confirm_password').val().trim();
      const token = getQueryParam('token');
      const email = getQueryParam('email');

      if (!newPassword || !confirmPassword) {
        alert("Please fill in all fields.");
        return;
      }

      if (!isValidPassword(newPassword)) {
        alert("Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.");
        return;
      }

      if (newPassword !== confirmPassword) {
        alert("Passwords do not match.");
        return;
      }

      const result = confirm("Are you sure you want to reset your password?");
      if (!result) return;

      $('.loader-overlay').show();

      $.ajax({
        type: "POST",
        url: 'webservices/forgotpassword.php',
        contentType: "application/json",
        dataType: "json",
        data: JSON.stringify({
          load: "reset_password",
          token: token,
          email: email,
          password: newPassword
        }),
        success: function(response) {
          $('.loader-overlay').hide();
          console.log("Server response:", response);

          if (typeof response === "string") {
            try {
              response = JSON.parse(response);
            } catch {
              alert("Server returned an invalid response.");
              return;
            }
          }

          if (response.status?.toLowerCase() === "success") {
            alert("✅ Password has been reset successfully!");
            window.location.href = 'index.php';
          } else {
            alert(response.message || "⚠️ Failed to reset password.");
          }
        },
        error: function(xhr, status, error) {
          $('.loader-overlay').hide();
          console.error("AJAX error:", xhr.responseText);
          alert("❌ Something went wrong. Please try again later.");
        }
      });
    }
  </script>
</body>
</html>
