<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';
include '../../config/headers.php';

if (isset($postdata) && !empty($postdata)) {

    $response = array();

    $username = filter_var($postdata['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($postdata['email'], FILTER_SANITIZE_STRING);

    if(empty($username) || empty($email)) {
        $response = array('error' => 'username and email are requiered');
        echo json_encode($response);
        exit;
    }

    if(!$conn) {
        $response = array('error' => 'Failed to connect to databse!');
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM users WHERE role = 'user' AND (email = '$email' OR username = '$username')";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 1){
        $response = array('error' => 'Account already exist!');
        echo json_encode($response);
        exit;
    }

    $verificationCode = bin2hex(random_bytes(8));

    $sql = "INSERT INTO verification_codes (email, code) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ss", $email, $verificationCode);

    if(!$stmt->execute()){
        $response = array('error'  => 'could not inset data to database!');
        echo json_encode($response);
        exit;
    }

    $stmt->close();

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('EMAIL');
    $mail->Password   = getenv('EMAIL_PASSWORD');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->addAddress($email, $username);
    $mail->isHTML(true);
    
    $mail->Subject = 'Verify Manishop account.';
    $mail->Body = '
        <html>
        <head>
            <style>
                /* Center the content horizontally */
                .center {
                    text-align: center;
                }
                /* Style the code to make it strong */
                .verification-code {
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="center">
                <img src="/imgs/MS.svg" alt="Logo" width="100">
                <p>Thank you for signing up with Manishop.</p>
                <p>Please verify your account by entering the following code:</p>
                <p class="verification-code">' . $verificationCode . '</p>
            </div>
        </body>
        </html>
    ';

    if(!$mail->send()) {
        $response = array('error' => 'Email not send!');
        echo json_encode($response);
        exit;
    }
    $response = array('success' => 'Email sent!');

    echo json_encode($response);
}