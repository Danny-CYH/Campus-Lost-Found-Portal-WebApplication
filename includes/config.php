<?php

// Database configuration (local settings)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'campus_lost_found');

// Database configuration (production settings)
// define('DB_HOST', 'localhost');
// define('DB_USER', 'dramranc_campuslostfound');
// define('DB_PASS', 'W8hj8HVF#aey');
// define('DB_NAME', 'dramranc_campuslostfound');

// Pusher Configuration
define('PUSHER_APP_ID', '2080314');
define('PUSHER_KEY', '585e3129f2fd92e29c0b');
define('PUSHER_SECRET', '4088efc496fa5c21a4f6');
define('PUSHER_CLUSTER', 'ap1');

define('SMTP_HOST', 'mail.dramran.com');
define('SMTP_PORT', 587); // Try 587 instead of 465
define('SMTP_USERNAME', 'campuslostfound@dramran.com');
define('SMTP_PASSWORD', 'LlB8fa8aQSBB');
define('SMTP_SECURE', 'tls'); // Use TLS instead of SSL for port 587
define('EMAIL_FROM', 'campuslostfound@dramran.com');
define('EMAIL_FROM_NAME', 'UUM Campus Lost & Found');
define('EMAIL_REPLY_TO', 'campuslostfound@dramran.com');
define('EMAIL_SUBJECT_PREFIX', 'UUM Find - ');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Create connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Google Maps API Key
define('GMAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY_HERE');

// Local Site configuration
//define('SITE_URL', 'http://localhost/Project');

// Production site configuration
define('SITE_URL', 'https://campuslostfound.dramran.com/');

// Simple email function using PHP's mail() with proper headers
function sendVerificationEmail($to, $username, $token)
{
    // PHPMailer paths - adjust based on your installation
    $phpmailer_path = __DIR__ . '/../PHPMailer/src/';

    try {
        require_once $phpmailer_path . 'Exception.php';
        require_once $phpmailer_path . 'PHPMailer.php';
        require_once $phpmailer_path . 'SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'mail.dramran.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'campuslostfound@dramran.com';
        $mail->Password = 'LlB8fa8aQSBB';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Disable SSL verification for testing
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom('campuslostfound@dramran.com', 'UUM Campus Lost & Found');
        $mail->addAddress($to, $username);
        $mail->addReplyTo('campuslostfound@dramran.com', 'UUM Campus Lost & Found');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your UUM Find Account';

        $verification_url = SITE_URL . '/api/verify_email.php?token=' . $token;

        $mail->Body = "
            <h2>Welcome to UUM Find, $username!</h2>
            <p>Please verify your email address by clicking the link below:</p>
            <p><a href='$verification_url' style='background: #006837; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>Verify Email Address</a></p>
            <p><strong>Link:</strong> $verification_url</p>
            <p>This link expires in 24 hours.</p>
        ";

        $mail->AltBody = "Verify your email: $verification_url";

        if ($mail->send()) {
            error_log("Email sent successfully to: $to");
            return true;
        }

    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $e->getMessage());
        return false;
    }
}

// Pure PHP Pusher function without Composer
function triggerPusher($channel, $event, $data)
{
    $appId = PUSHER_APP_ID;
    $appKey = PUSHER_KEY;
    $appSecret = PUSHER_SECRET;
    $cluster = PUSHER_CLUSTER;

    $url = "https://api-$cluster.pusher.com/apps/$appId/events";

    // Prepare payload
    $payload = json_encode([
        'name' => $event,
        'data' => json_encode($data), // data MUST be a JSON string
        'channel' => $channel
    ]);

    // Required Pusher authentication fields
    $authTimestamp = time();
    $authVersion = '1.0';
    $bodyMd5 = md5($payload);

    // Create query string
    $query = "auth_key=$appKey&auth_timestamp=$authTimestamp&auth_version=$authVersion&body_md5=$bodyMd5";

    // Create signature
    $stringToSign = "POST\n/apps/$appId/events\n$query";
    $authSignature = hash_hmac('sha256', $stringToSign, $appSecret);

    // Final URL with signature
    $signedUrl = "$url?$query&auth_signature=$authSignature";

    // cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $signedUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    error_log("Pusher Response: HTTP $status - $response - Error: $error");

    return $status === 200;
}

?>