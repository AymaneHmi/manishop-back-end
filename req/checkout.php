<?php

require_once '../vendor/autoload.php';
include '../config/headers.php';

\Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($postdata['name'], FILTER_SANITIZE_STRING);
    $phone = filter_var($postdata['phone'], FILTER_SANITIZE_STRING);
    $address = filter_var($postdata['address'], FILTER_SANITIZE_STRING);
    $order_products = filter_var($postdata['order_products'], FILTER_SANITIZE_STRING);
    $cart_products = $postdata['products'];
    $products_ids = $postdata['products_ids'];
    $price = $postdata['price'];
    $user_id = filter_var($postdata['user_id'], FILTER_SANITIZE_STRING);

    if(empty($name) || empty($order_products) || empty($user_id) || empty($price) ){
        $response = array('error'  => 'Data is requiered!');
        echo json_encode($response);
        exit;
    }

    $status = "Stocked up";
    $products_ids = json_encode($products_ids);
    $order_code = strtoupper(bin2hex(random_bytes(6)));

    $query = "INSERT INTO Orders (name , phone, address, products, products_ids, price, user_id, status, order_id) VALUES (? , ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("sssssdiss", $name, $phone, $address, $order_products, $products_ids, $price, $user_id, $status, $order_code);

    if(!$stmt->execute()){
        $response = array('error'  => 'could not inset data to database!');
        echo json_encode($response);
        exit;
    }
    $order_id = $conn->insert_id;
    $stmt->close();

    $user_sql = "SELECT * FROM users WHERE user_id = '$user_id'";
    $user_result = mysqli_query($conn, $user_sql);

    if(mysqli_num_rows($user_result) == 0){
      http_response_code(401);
      exit;
    }

    $user = mysqli_fetch_assoc($user_result);

    $lineItems = [];

    foreach ($cart_products as $cartProduct) {
        $lineItems[] = [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $cartProduct['title'],
                ],
                'unit_amount' => $cartProduct['price'] * 100,
            ],
            'quantity' => $cartProduct['quantity'],
        ];
    }

    $checkout_session = \Stripe\Checkout\Session::create([
        'line_items' => $lineItems,
        'mode' => 'payment',
        'success_url' => getenv('FRONT_DOMAIN') . '?success=1',
        'cancel_url' => getenv('FRONT_DOMAIN') . '?canceled=1',
        'billing_address_collection' => 'required',
        'phone_number_collection' => [
            "enabled" => true,
        ],
        'customer_email' => $user['email'],
        'metadata' => [
            'orderId' => $order_id,
            'userId' => $user_id,
        ],
    ]);

    echo json_encode(['url' => $checkout_session->url]);
}