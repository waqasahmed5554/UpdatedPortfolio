<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes
require 'PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';

// DB connection
$host = "localhost";
$username = "root";
$password = "";
$database = "contact_me";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $name    = htmlspecialchars(trim($_POST["name"]));
    $email   = htmlspecialchars(trim($_POST["email"]));
    $subject = htmlspecialchars(trim($_POST["subject"]));
    $message = htmlspecialchars(trim($_POST["message"]));

    // Save to DB
    $stmt = $conn->prepare("INSERT INTO users (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $subject, $message);
    $dbSuccess = $stmt->execute();
    $stmt->close();

    // Send email via PHPMailer
    $mail = new PHPMailer(true);
    $emailSuccess = false;
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'waqasah652@gmail.com';  // Your Gmail address
        $mail->Password   = 'wims erph igjv npmh';   // Your Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom($email, $name);
        $mail->addAddress('waqasah652@gmail.com');   // Receiver email

        // Email content
        $mail->isHTML(false);
        $mail->Subject = $subject ?: 'WAQAS PORTFOLIO';
        $mail->Body    = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";

        $emailSuccess = $mail->send();
    } catch (Exception $e) {
        $emailSuccess = false;
    }

    // Determine redirect URL based on success
    if ($dbSuccess && $emailSuccess) {
        header("Location: index.html?status=success&message=Message+sent+and+saved+successfully!");
    } elseif ($dbSuccess && !$emailSuccess) {
        header("Location: index.html?status=error&message=Message+saved+but+email+failed+to+send");
    } else {
        header("Location: index.html?status=error&message=Failed+to+save+message");
    }
    exit();
} else {
    header("Location: index.html?status=error&message=Invalid+request");
    exit();
}
?>