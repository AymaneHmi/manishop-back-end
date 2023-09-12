<?php

include '../../config/headers.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $headers = apache_request_headers();
    if (isset($headers['Authorization']) && $headers['Authorization'] === $api_token) {
        $sql = "SELECT * FROM orders ORDER BY created_at DESC";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $response = array();
            while ($order = mysqli_fetch_assoc($result)) {
                // Build response array
                $post = array(
                    'id' => $order['id'],
                    'orderId' => $order['order_id'],
                    'products' => $order['products'],
                    'name' => $order['name'],
                    'phone' => $order['phone'],
                    'address' => $order['address'],
                    'price' => floatval($order['price']),
                    'status' => $order['status'],
                    'isPaid' => ($order['is_paid'] === '1' ? true : false),
                    'createdAt' => $order['created_at']
                );
                array_push($response, $post);
            }
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            echo json_encode(array( 'error' => 'No order found.'));
            exit;
        }
    } else {
        // Unauthorized
        header('HTTP/1.1 401 Unauthorized');
        echo 'Unauthorized';
    }
}
