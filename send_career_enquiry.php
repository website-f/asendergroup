<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

$to_email = 'fitri@craveasia.com';
$website_name = 'ASENDER Career';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $fullName = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_STRING);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    // Validation
    if (empty($fullName) || empty($email) || empty($subject) || empty($message) || !isset($_FILES['resume'])) {
        header("Location: career-enquiry.html?status=error&message=Please fill in all required fields.");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: career-enquiry.html?status=error&message=Invalid email format.");
        exit;
    }

    // File validations
    $file = $_FILES['resume'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];

    if ($fileError != UPLOAD_ERR_OK) {
        header("Location: career-enquiry.html?status=error&message=Error uploading resume.");
        exit;
    }
    if ($fileType != 'application/pdf') {
        header("Location: career-enquiry.html?status=error&message=Please upload a PDF file.");
        exit;
    }
    if ($fileSize > 2 * 1024 * 1024) {
        header("Location: career-enquiry.html?status=error&message=File must be under 2MB.");
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // ✅ SMTP CONFIG
        $mail->isSMTP();
        $mail->Host       = 'mail.asendergroup.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@asendergroup.com';
        $mail->Password   = 'asendergroup';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // ✅ FROM / TO
        $mail->setFrom('no-reply@asendergroup.com', 'ASENDER Group');
        $mail->addAddress($to_email, $website_name);
        $mail->addReplyTo($email, $fullName);

        // ✅ ATTACH THE RESUME
        $mail->addAttachment($file['tmp_name'], $file['name']);

        // ✅ EMAIL CONTENT
        $mail->isHTML(false);
        $mail->Subject = "Career Application from ASENDER Group: $subject";
        $mail->Body    = "New career application received.\n\n"
                       . "Full Name: $fullName\n"
                       . "Email: $email\n"
                       . (!empty($phoneNumber) ? "Phone Number: $phoneNumber\n" : "")
                       . "Subject: $subject\n"
                       . "Message:\n$message\n";

        $mail->send();

        // ✅ SUCCESS REDIRECT
        header("Location: career-enquiry.html?status=success");
        exit;

    } catch (Exception $e) {
        error_log("Email sending failed. Mailer Error: {$mail->ErrorInfo}");
        header("Location: career-enquiry.html?status=error&message=Email failed to send.");
        exit;
    }
} else {
    header("Location: career-enquiry.html");
    exit;
}
