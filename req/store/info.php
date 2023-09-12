<?php

include '../../config/headers.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $sql = "SELECT 
        COUNT(*) AS total_orders,
        SUM(price) AS total_price,
        SUM(CHAR_LENGTH(products_ids) - CHAR_LENGTH(REPLACE(products_ids, ',', '')) + 1) AS total_products
        FROM Orders
        WHERE is_paid = 1";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch the result
        $row = $result->fetch_assoc();
        
        // Get the total number of orders
        $totalOrders = $row['total_orders'];
        
        // Get the total price of paid orders
        $totalPrice = $row['total_price'];
        
        // Get the total number of products_ids elements
        $totalProducts = $row['total_products'];
        
        $response = array(
            'totalRevenue' => $totalPrice,
            'sales' => $totalOrders,
            'products' => $totalProducts
        );
        echo json_encode($response);
    } else {
        echo json_encode(array('error' => 'no order paid'));
    }
}