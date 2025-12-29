<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Login - HDN</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    :root {
      --color-primary: #8D3A08;
      --color-light: #D4B05A;
      --color-dark: #3A2615;
      --color-medium: #C28B42;
      --color-border: #8D3A08;
      --color-tab: #FBF5E5;
      --color-label: #3A2615;
      --color-button: #8D3A08;
      --color-blue: #007497;
      --soft-card: #F4E6C3;
      --muted-text: rgba(58, 38, 21, 0.55);
      --glass-bg: rgba(80, 48, 18, 0.05);
      --radius: 12px;
      --shadow: 0 8px 26px rgba(50, 30, 10, 0.07);
      --ease: cubic-bezier(0.2, 0.9, 0.3, 1);
      --card-gap: 10px;
    }

    * {
      font-family: "Calibri Light", Calibri, sans-serif;
    }

    body {
      background: #f5f6fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
    }

    .login-container {
      background: #fff;
      padding: 2rem 2.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
      width: 100%;
      max-width: 380px;
      position: relative;
    }

    .login-container h2 {
      margin-bottom: 1.3rem;
      color: var(--color-blue);
      text-align: center;
    }

    .form-group {
      margin-bottom: 1.1rem;
      position: relative;
    }

    label {
      display: block;
      margin-bottom: 0.35rem;
      color: #353b48;
      font-weight: 500;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 0.65rem 2.5rem 0.65rem 0.65rem;
      border: 1px solid #dcdde1;
      border-radius: 6px;
      font-size: 1rem;
      outline: none;
      transition: border 0.15s, box-shadow 0.15s;
      box-sizing: border-box;
    }

    input.error {
      border-color: #e84118 !important;
      box-shadow: 0 0 0 3px rgba(232, 65, 24, 0.06);
    }

    input:focus {
      border-color: #F3681E;
    }

    /* Password toggle icon */
    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 1.2rem;
      color: #888;
      cursor: pointer;
      display: none;
      user-select: none;
    }

    .toggle-password.active {
      color: #F3681E;
    }

    button {
      width: 100%;
      padding: 0.8rem;
      background: var(--color-dark);
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s ease-in-out;
      margin-top: 5%;
    }

    button:hover {
      background: var(--color-dark);
    }

    button:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }

    .forgot-password {
      text-align: center;
      margin-top: 1rem;
    }

    .forgot-password a {
      color: var(--color-dark);
      text-decoration: none;
      font-size: 0.9rem;
    }

    .forgot-password a:hover {
      text-decoration: underline;
    }

    .error-message {
      color: red;
      font-size: 0.85rem;
      margin-top: 0.35rem;
      height: 1em;
    }

    .success-message {
      color: #27ae60;
      font-weight: 600;
      text-align: center;
      margin-top: 0.8rem;
      height: 1.2em;
    }

    @media (max-width: 420px) {
      .login-container {
        padding: 1.2rem;
        max-width: 92%;
      }
    }
  </style>
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

  <script>
    (function() {
      const form = $('#loginForm');
      const usernameInput = $('#mobile_number');
      const passwordInput = $('#password');
      const usernameError = $('#mobile_error');
      const passwordError = $('#password_error');
      const successMessage = $('#success_message');
      const loginBtn = $('#loginBtn');
      const togglePassword = $('#togglePassword');

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      const mobileRegex = /^[6-9]\d{9}$/;
      const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_\-+=\[\]{};:'",.<>?/\\|`~]).{8,}$/;

      function debounce(fn, delay = 300) {
        let timer;
        return function(...args) {
          clearTimeout(timer);
          timer = setTimeout(() => fn.apply(this, args), delay);
        };
      }

      function detectInputType(value) {
        value = value.trim();
        if (value === '') return 'unknown';
        if (/^[0-9]/.test(value)) return 'mobile';
        return 'email';
      }

      function validateUsername(value) {
        value = value.trim();
        if (!value) return {
          valid: false,
          message: 'Please enter mobile number or email.'
        };
        const type = detectInputType(value);

        if (type === 'mobile') {
          if (!mobileRegex.test(value)) {
            return {
              valid: false,
              message: 'Enter a valid 10-digit mobile number starting with 6,7,8,9.'
            };
          }
        } else if (type === 'email') {
          if (!emailRegex.test(value)) {
            return {
              valid: false,
              message: 'Enter a valid email address.'
            };
          }
        }

        return {
          valid: true,
          message: ''
        };
      }

      function validatePassword(value) {
        value = value.trim();
        if (!value) {
          return {
            valid: false,
            message: 'Please enter your password.'
          };
        }
        if (!passwordRegex.test(value)) {
          return {
            valid: false,
            message: 'Password must have at least 8 chars, 1 uppercase, 1 number & 1 special char.'
          };
        }
        return {
          valid: true,
          message: ''
        };
      }

      function showValidation(input, errorElement, result) {
        if (result.valid) {
          input.removeClass('error');
          errorElement.text('');
        } else {
          input.addClass('error');
          errorElement.text(result.message);
        }
      }

      // Password toggle visibility
      passwordInput.on('input', function() {
        const val = $(this).val();
        if (val.length > 0) {
          togglePassword.fadeIn(200);
        } else {
          togglePassword.fadeOut(200);
        }
      });

      togglePassword.on('click', function() {
        const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);
        $(this).toggleClass('active');
        $(this).text(type === 'password' ? '👁️' : '🙈');
      });

      usernameInput.on('input', debounce(function() {
        let value = usernameInput.val().trim();
        const type = detectInputType(value);
        if (type === 'mobile') usernameInput.val(value.replace(/\D/g, ''));
        const result = validateUsername(usernameInput.val());
        showValidation(usernameInput, usernameError, result);
      }));

      passwordInput.on('input', debounce(function() {
        const result = validatePassword(passwordInput.val());
        showValidation(passwordInput, passwordError, result);
      }));

      form.on('submit', function(e) {
        e.preventDefault();
        successMessage.text('');

        const usernameVal = usernameInput.val().trim();
        const passwordVal = passwordInput.val().trim();

        const usernameCheck = validateUsername(usernameVal);
        const passwordCheck = validatePassword(passwordVal);

        showValidation(usernameInput, usernameError, usernameCheck);
        showValidation(passwordInput, passwordError, passwordCheck);

        if (!usernameCheck.valid || !passwordCheck.valid) return;

        loginBtn.prop('disabled', true).text('Logging in...');

        $.ajax({
          url: 'webservices/admin.php',
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({
            adminmobile: usernameVal,
            adminpass: passwordVal,
            load: 'adminloginbymobile'
          }),
          success: function(response) {
            let data;
            try {
              data = typeof response === 'string' ? JSON.parse(response) : response;
            } catch {
              passwordError.text('Invalid server response.');
              return;
            }

            if (data.code === '200') {
              alert('Login successful!');
              localStorage.setItem('adminmobile', usernameVal);
              if (data.data?.[0]?.adminid) {
                localStorage.setItem('adminid', data.data[0].adminid);
              }
              window.location.href = 'catering.php';
            } else {
              passwordError.text(data.message || 'Invalid username or password.');
              passwordInput.addClass('error');
            }
          },
          error: function() {
            passwordError.text('Server error. Please try again.');
            passwordInput.addClass('error');
          },
          complete: function() {
            loginBtn.prop('disabled', false).text('Login');
          }
        });
      });
    })();
  </script>
</body>

</html>