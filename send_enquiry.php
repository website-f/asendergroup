<?php
// Include the PHPMailer autoloader files.
// IMPORTANT: Adjust these paths if your PHPMailer library is in a different location.
// For example, if you uploaded the entire PHPMailer-master.zip and extracted it,
// the paths might be like 'PHPMailer-master/src/Exception.php'.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php'; // Only needed if you're sending via SMTP

// Define the recipient email address
$to_email = 'fitri@craveasia.com'; // IMPORTANT: Change this to your actual admin email address
$website_name = 'ASENDER Website'; // Your website name

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $fullName = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $companyName = filter_input(INPUT_POST, 'companyName', FILTER_SANITIZE_STRING);
    $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_STRING);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    // Basic validation
    if (empty($fullName) || empty($email) || empty($subject) || empty($message)) {
        header("Location: service-enquiry.html?status=error&message=Please fill in all required fields.");
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: service-enquiry.html?status=error&message=Invalid email format.");
        exit;
    }

    // Create a new PHPMailer instance
    $mail = new PHPMailer(true); // Passing `true` enables exceptions for detailed error reporting

    try {
    $mail = new PHPMailer(true);

    // ✅ SMTP CONFIGURATION
    $mail->isSMTP();
    $mail->Host       = 'mail.asendergroup.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'no-reply@asendergroup.com';
    $mail->Password   = 'asendergroup';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
    $mail->Port       = 465;

    // ✅ SENDER / RECIPIENT
    $mail->setFrom('no-reply@asendergroup.com', 'ASENDER Group');
    $mail->addAddress('admin@asendergroup.com', 'Admin');
    $mail->addReplyTo($email, $fullName);

    // ✅ EMAIL CONTENT
    $mail->isHTML(false);
    $mail->Subject = "Service Enquiry from ASENDER Group: $subject";
    $mail->Body    = "You have received a new service enquiry from ASENDER Group.\n\n"
                    . "Full Name: $fullName\n"
                    . "Email: $email\n"
                    . (!empty($companyName) ? "Company Name: $companyName\n" : "")
                    . (!empty($phoneNumber) ? "Phone Number: $phoneNumber\n" : "")
                    . "Subject: $subject\n"
                    . "Message:\n$message\n";

    $mail->send();

    header("Location: service-enquiry.html?status=success");
    exit;

} catch (Exception $e) {
    error_log("Email sending failed. Mailer Error: {$mail->ErrorInfo}");
    $error_message = "Failed to send email.";
    header("Location: service-enquiry.html?status=error&message=$error_message");
    exit;
}

} else {
    // If accessed directly without a POST request, redirect to the form page
    header("Location: service-enquiry.html");
    exit;
}
?>
