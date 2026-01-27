<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="css/forgotpassword.css"> 
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- jQuery CDN -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="scriptfiles/forgotpassword.js" defer></script>
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


</body>

</html>