<?php

include '../../config/headers.php';

if (isset($postdata) && !empty($postdata)) {

    $response = array();

    $name = filter_var($postdata['name'], FILTER_SANITIZE_STRING);
    $phone = filter_var($postdata['phone'], FILTER_SANITIZE_STRING);
    $address = filter_var($postdata['address'], FILTER_SANITIZE_STRING);
    $products = filter_var($postdata['products'], FILTER_SANITIZE_STRING);
    $products_ids = $postdata['products_ids'];
    $price = $postdata['price'];
    $user_id = filter_var($postdata['user_id'], FILTER_SANITIZE_STRING);
    $status = filter_var($postdata['status'], FILTER_SANITIZE_STRING);
    $is_paid = $postdata['isPaid'];

    if(empty($name) || empty($phone) || empty($address) || empty($products) || empty($products_ids) || empty($user_id) || empty($price) ){
        $response = array('error'  => 'Data is requiered!');
        echo json_encode($response);
        exit;
    }

    $is_paid = $is_paid ? 1 : 0 ;
    $products_ids = json_encode($products_ids);
    $order_id = strtoupper(bin2hex(random_bytes(6)));

    $query = "INSERT INTO Orders (name , phone, address, products, products_ids, price, user_id, status, is_paid, order_id) VALUES (? , ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("sssssdisis", $name, $phone, $address, $products, $products_ids, $price, $user_id, $status, $is_paid, $order_id);

    if(!$stmt->execute()){
        $response = array('error'  => 'could not inset data to database!');
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    $response = array('success' => 'order placed seccussfuly');

    echo json_encode($response);
}