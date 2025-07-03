<?php
// Include the PHPMailer autoloader files.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php'; // Only needed if you're sending via SMTP

// Define the recipient email address for partnership enquiries
$to_email = 'admin@asendergroup.com'; // IMPORTANT: Change this to your actual admin email address for partnerships
$website_name = 'ASENDER Website'; // Your website name

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $fullName = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $companyName = filter_input(INPUT_POST, 'companyName', FILTER_SANITIZE_STRING);
    $organizationType = filter_input(INPUT_POST, 'organizationType', FILTER_SANITIZE_STRING);
    $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_STRING);
    $partnershipInterest = filter_input(INPUT_POST, 'partnershipInterest', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    // Basic validation
    if (empty($fullName) || empty($email) || empty($companyName) || empty($organizationType) || empty($partnershipInterest) || empty($message)) {
        header("Location: partnership-enquiry.html?status=error&message=Please fill in all required fields.");
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: partnership-enquiry.html?status=error&message=Invalid email format.");
        exit;
    }

    // Create a new PHPMailer instance
    $mail = new PHPMailer(true); // Passing `true` enables exceptions for detailed error reporting

    try {
        // --- SMTP SERVER SETTINGS (CONFIGURED FOR CPANEL) ---
        // You MUST uncomment and configure these settings for reliable email sending.
        // Get these details from your web hosting provider or your email service (e.g., Google Workspace, Outlook, your cPanel email settings).

        $mail->isSMTP();
        $mail->Host       = 'mail.asendergroup.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@asendergroup.com';
        $mail->Password   = 'asendergroup';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
        $mail->Port       = 465;
    

        // OR if cPanel says "STARTTLS on port 587":
        // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        // $mail->Port       = 587;

        // OR if cPanel gives an "unencrypted" port (e.g., 25 or 26) with no encryption type:
        // $mail->SMTPSecure = false; // No encryption
        // $mail->SMTPAutoTLS = false; // Disable TLS even if server offers
        // $mail->Port       = 25; // Or 26, etc. (Less secure, but might be necessary for some hosts)


        // Recipients
        $mail->setFrom($email, $fullName); // Sender's email and name (from the form)
        $mail->addAddress($to_email, $website_name); // Add recipient (your admin email)
        $mail->addReplyTo($email, $fullName); // Add a reply-to address so you can reply directly to the sender

        // Content
        $mail->isHTML(false); // Set email format to plain text
        $mail->Subject = "Partnership Enquiry from " . $website_name . ": " . $partnershipInterest;
        $email_body    = "You have received a new partnership enquiry from your website.\n\n";
        $email_body   .= "Full Name: " . $fullName . "\n";
        $email_body   .= "Email: " . $email . "\n";
        $email_body   .= "Organization Name: " . $companyName . "\n";
        $email_body   .= "Organization Type: " . $organizationType . "\n";
        if (!empty($phoneNumber)) {
            $email_body .= "Phone Number: " . $phoneNumber . "\n";
        }
        $email_body   .= "Partnership Interest / Area: " . $partnershipInterest . "\n";
        $email_body   .= "Message / Proposal:\n" . $message . "\n";
        $mail->Body = $email_body;

        $mail->send();
        // Redirect on success
        header("Location: partnership-enquiry.html?status=success");
        exit;
    } catch (Exception $e) {
        // Log the detailed error for server-side debugging.
        error_log("Partnership enquiry email sending failed. Mailer Error: {$mail->ErrorInfo}", 0);

        // Redirect with a more informative error message for client-side debugging
        $error_message = "Failed to send partnership enquiry. Error: " . urlencode($mail->ErrorInfo);
        header("Location: partnership-enquiry.html?status=error&message=" . $error_message);
        exit;
    }
} else {
    // If accessed directly without a POST request, redirect to the form page
    header("Location: partnership-enquiry.html");
    exit;
}
?>
