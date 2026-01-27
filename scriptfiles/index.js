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


(function () {
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
        return function (...args) {
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
    passwordInput.on('input', function () {
        const val = $(this).val();
        if (val.length > 0) {
            togglePassword.fadeIn(200);
        } else {
            togglePassword.fadeOut(200);
        }
    });

    togglePassword.on('click', function () {
        const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);
        $(this).toggleClass('active');
        $(this).text(type === 'password' ? '👁️' : '🙈');
    });

    usernameInput.on('input', debounce(function () {
        let value = usernameInput.val().trim();
        const type = detectInputType(value);
        if (type === 'mobile') usernameInput.val(value.replace(/\D/g, ''));
        const result = validateUsername(usernameInput.val());
        showValidation(usernameInput, usernameError, result);
    }));

    passwordInput.on('input', debounce(function () {
        const result = validatePassword(passwordInput.val());
        showValidation(passwordInput, passwordError, result);
    }));

    form.on('submit', function (e) {
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
            success: function (response) {
                let data;
                try {
                    data = typeof response === 'string' ? JSON.parse(response) : response;
                } catch {
                    passwordError.text('Invalid server response.');
                    return;
                }

                if (data.code === '200') {
                    showToast('Login successful!', "success");
                    localStorage.setItem('adminmobile', usernameVal);
                    if (data.data?.[0]?.adminid) {
                        localStorage.setItem('adminid', data.data[0].adminid);
                    }
                    setTimeout(() => {
                        window.location.href = "catering.php";
                    }, 1000); // 1 second
                }
             else {
                passwordError.text(data.message || 'Invalid username or password.');
                passwordInput.addClass('error');
            }
        },
            error: function () {
                passwordError.text('Server error. Please try again.');
                passwordInput.addClass('error');
            },
            complete: function () {
                loginBtn.prop('disabled', false).text('Login');
            }
        });
});
    }) ();
