<?php

include '../../config/headers.php';

if (isset($postdata) && !empty($postdata)) {

    $response = array();

    $user_id = filter_var($postdata['user_id'], FILTER_SANITIZE_STRING);

    if(empty($user_id)) {
        echo json_encode(array('error' => 'user_id is requiered!'));
        exit;
    }

    $sql = "SELECT * FROM orders WHERE user_id='$user_id' ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        while ($order = mysqli_fetch_assoc($result)) {
            $post = array(
                'id' => $order['id'],
                'orderId' => $order['order_id'],
                'products' => $order['products'],
                'name' => $order['name'],
                'phone' => $order['phone'],
                'address' => $order['address'],
                'price' => floatval($order['price']),
                'status' => $order['status'],
                'createdAt' => $order['created_at']
            );
            array_push($response, $post);
        }
    } else {
        echo json_encode(array( 'error' => 'No order found.'));
        exit;
    }

    echo json_encode($response);
}