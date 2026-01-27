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



function sendResetLink() {
    const email = $('#email').val().trim();

    if (email === '') {
        showToast('Please enter your email address.',"warning");
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
        success: function (response) {
            $('.loader-overlay').hide();
            if (typeof response === "string") {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    showToast("Server returned an invalid response.","error");
                    return;
                }
            }

            const status = response.status?.toLowerCase?.() || '';
            const code = response.code || '';

            if (status === 'success' || code === '200') {
                showToast('✅ A reset link has been sent to your email.',"success");
                $('#email').val('');
            } else {
                showToast(response.message || '⚠️ Email not found or failed to send link.',"warning");
            }
        },
        error: function (xhr, status, error) {
            $('.loader-overlay').hide();
            showToast('❌ Error occurred. Please try again later.',"error");
        }
    });
}
