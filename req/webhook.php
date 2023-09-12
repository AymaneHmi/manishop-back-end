<?php

require '../config/headers.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$stripe = new \Stripe\StripeClient(getenv('STRIPE_SECRET'));

$endpoint_secret = getenv('STRIPE_CLI_WEBHOOK');

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
  $event = \Stripe\Webhook::constructEvent(
    $payload, $sig_header, $endpoint_secret
  );
} catch(\UnexpectedValueException $e) {
  // Invalid payload
  http_response_code(400);
  exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
  // Invalid signature
  http_response_code(400);
  exit();
}

switch ($event->type) {
  case 'checkout.session.completed':
    $session = $event->data->object;
    
    $order_id = $session->metadata->orderId;
    $user_id = $session->metadata->userId;

    $user_sql = "SELECT * FROM users WHERE user_id = '$user_id'";
    $user_result = mysqli_query($conn, $user_sql);

    if(mysqli_num_rows($user_result) == 0){
      http_response_code(401);
      exit;
    }

    $user = mysqli_fetch_assoc($user_result);

    $address_components = [
      $session->customer_details->address->line1,
      $session->customer_details->address->line2,
      $session->customer_details->address->city,
      $session->customer_details->address->state,
      $session->customer_details->address->postal_code,
      $session->customer_details->address->country,
    ];

    $address = implode(', ', array_filter($address_components));

    $phone = $session->customer_details->phone;

    $update_query = "UPDATE Orders SET address = ?, phone = ?, is_paid='1' WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssi", $address, $phone, $order_id);

    if (!$update_stmt->execute()) {
      http_response_code(500);
      exit();
    }
    $sql = "SELECT * FROM CartLists WHERE user_id='$user_id'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 0){
      http_response_code(500);
    }

    $cart_list = mysqli_fetch_assoc($result);

    $list_id = $cart_list['list_id'];

    $delete = "DELETE FROM Carts WHERE list_id = ?";
    $stmt = mysqli_prepare($conn, $delete);
    mysqli_stmt_bind_param($stmt, "i", $list_id);
    if (!mysqli_stmt_execute($stmt)) {
      http_response_code(500);
      exit;
    }
    mysqli_stmt_close($stmt);

    $mail = new PHPMailer(true);
    
    $mail->isSMTP();                       
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;                       
    $mail->Username   = getenv('EMAIL');                     
    $mail->Password   = getenv('EMAIL_PASSWORD');                             
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;       
    $mail->Port       = 465;                                   

    $mail->addAddress($user['email'], $user['username']);
    $mail->isHTML(true);                                  
    $mail->Subject = 'order placed seccussfuly.';
    $mail->Body = '
      <html>
      <head>
          <style>
              /* Center the content horizontally */
              .center {
                text-align: center;
              }
          </style>
      </head>
      <body>
          <div class="center">
              <img src="/imgs/MS.svg" alt="Logo" width="100">
              <p>We would like to thank for placing an order.</p>
              <p class="verification-code">the order you placed is with these provided information:</p>
              <p>your order will be delivery within 24 hours</p>
              <p>Thank you for your confident.</p>
          </div>
      </body>
      </html>
    ';

    if(!$mail->send()) {
      $response = array('error' => 'Email not send!');
      echo json_encode($response);
      exit;
    }
  default:
}

http_response_code(200);