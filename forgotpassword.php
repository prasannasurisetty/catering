<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
        }

        .container {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-container {
            background: white;
            padding: 2rem 2.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        .form h2 {
            text-align: center;
            color: #273c75;
            margin-bottom: 1.5rem;
        }

        .form input[type="email"] {
            width: 100%;
            padding: 0.7rem;
            margin-bottom: 1rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }

        .form button {
            width: 100%;
            padding: 0.8rem;
            background-color: var(--color-dark);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 1rem;
        }

        .form a {
            display: block;
            text-align: center;
            color: var(--color-dark);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            background: rgba(255, 255, 255, 0.8);
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
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
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

    <div class="container">
        <div class="form-container">
            <div class="form">
                <h2>Forgot Password</h2>
                <input type="email" id="email" placeholder="Enter your email" required />
                <button type="button" onclick="sendResetLink()">Send Reset Link</button>
                <a href="index.php">Back to signin</a>
            </div>
        </div>
    </div>

    <script>
        function sendResetLink() {
            const email = $('#email').val().trim();

            if (email === '') {
                alert('Please enter your email address.');
                return;
            }

            $('.loader-overlay').show();

            $.ajax({
                url: 'webservices/forgotpassword.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    load: 'send_reset_link',
                    email: email
                }),
                success: function(response) {
                    $('.loader-overlay').hide();
                    console.log("Server response:", response);

                    if (typeof response === "string") {
                        try {
                            response = JSON.parse(response);
                        } catch (e) {
                            alert("Server returned an invalid response.");
                            return;
                        }
                    }

                    const status = response.status?.toLowerCase?.() || '';
                    const code = response.code || '';

                    if (status === 'success' || code === '200') {
                        alert('✅ A reset link has been sent to your email.');
                        $('#email').val('');
                    } else {
                        alert(response.message || '⚠️ Email not found or failed to send link.');
                    }
                },
                error: function(xhr, status, error) {
                    $('.loader-overlay').hide();
                    console.error("AJAX error:", xhr.responseText);
                    alert('❌ Error occurred. Please try again later.');
                }
            });
        }
    </script>
</body>

</html>