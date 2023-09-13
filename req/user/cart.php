<?php

include '../../config/headers.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_id = filter_var($postdata['product_id'], FILTER_SANITIZE_STRING);
    $user_id = filter_var($postdata['user_id'], FILTER_SANITIZE_STRING);
    $quantity = filter_var($postdata['quantity'], FILTER_SANITIZE_STRING);
    $size = filter_var($postdata['size'], FILTER_SANITIZE_STRING);

    if(empty($product_id) || empty($user_id)) {
        $response = array('error' => 'data is requiered');
        echo json_encode($response);
        exit;
    }
    if(empty($size)) {
        $size = null;
    }

    if(!$conn) {
        $response = array('error' => 'Failed to connect to databse!');
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM CartLists WHERE user_id='$user_id'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 0){
        $response = array('error' => 'no cart list for this user.');
        echo json_encode($response);
        exit;
    }

    $cart_list = mysqli_fetch_assoc($result);

    $list_id = $cart_list['list_id'];

    if (empty($size)) {
        $sql = "SELECT * FROM Carts WHERE list_id='$list_id' AND product_id='$product_id'";
    } else {
        $sql = "SELECT * FROM Carts WHERE list_id='$list_id' AND product_id='$product_id' AND size='$size'";
    }
    
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $response = array('error' => 'Item is already in the cart.');
        echo json_encode($response);
        exit;
    }

    $query = "INSERT INTO Carts (list_id, product_id, quantity, size) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("iiis", $list_id, $product_id, $quantity, $size);

    if(!$stmt->execute()){
        $response = array('error'  => 'could not insert data to database!');
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    $response = array('success' => 'Data inserted successfuly.');

    echo json_encode($response);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $user_id = $_GET['user_id'];

    if(empty($user_id)) {
        $response = array('error' => 'user is requiered');
        echo json_encode($response);
        exit;
    }

    if(!$conn){
        $response = array('error' => "can't connect to database!");
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM CartLists WHERE user_id='$user_id'";
    $result = mysqli_query($conn, $sql);

    if($result == 0){
        $response = array('error' => "no cart list with this user!");
        echo json_encode($response);
        exit;
    }

    $cart_list = mysqli_fetch_assoc($result);

    $list_id = $cart_list['list_id'];

    $sql = "SELECT * FROM Carts WHERE list_id='$list_id'";
    $result = mysqli_query($conn, $sql);

    if($result == 0){
        $response = array('error' => "no product cart with this user!");
        echo json_encode($response);
        exit;
    }

    while ($cart = mysqli_fetch_assoc($result)) {
        $product_id = $cart['product_id'];
    
        $productSql = "SELECT * FROM products WHERE product_id='$product_id'";
        $productResult = mysqli_query($conn, $productSql);
    
        $product = mysqli_fetch_assoc($productResult);
        $category_id = $product['category_id'];
    
        $categoryQuery = "SELECT * FROM categories WHERE category_id='$category_id'";
        $categoryResult = mysqli_query($conn, $categoryQuery);
        $category = mysqli_fetch_assoc($categoryResult);
    
        $post = array(
            'list_id' => $cart['list_id'],
            'cart_id' => $cart['cart_id'],
            'id' => $product_id,
            'title' => $product['title'],
            'price' => floatval($product['price']),
            'size' => $cart['size'],
            'quantity' => $cart['quantity'],
            'category' => $category['name'],
            'images' => json_decode($product['images'], true),
            'product_id' => $cart['product_id']
        );
        array_push($response, $post);
    }

    echo json_encode($response);
}

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {

    $cart_id = filter_var($postdata['cart_id'], FILTER_SANITIZE_STRING);
    $qty = filter_var($postdata['quantity'], FILTER_SANITIZE_STRING);

    if(empty($cart_id) || empty($qty)) {
        $response = array('error' => 'cart id and qty are requiered');
        echo json_encode($response);
        exit;
    }

    $query = "UPDATE Carts SET quantity=? WHERE cart_id=?";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("ii", $qty, $cart_id);

    if (!$stmt->execute()) {
        $response = array('error' => 'Could not update data in the database!');
        echo json_encode($response);
        exit;
    }

    $stmt->close();

    $response = array('success' => 'cart updated successfuly.');


    echo json_encode($response);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $cart_id = filter_var($postdata['cart_id'], FILTER_SANITIZE_STRING);

    if(empty($cart_id)) {
        $response = array('error' => 'cart id is requiered');
        echo json_encode($response);
        exit;
    }

    $delete = "DELETE FROM Carts WHERE cart_id = ?";
    $stmt = mysqli_prepare($conn, $delete);
    mysqli_stmt_bind_param($stmt, "i", $cart_id);
    if (!mysqli_stmt_execute($stmt)) {
        $response = array("error" => "cart not removed.");
        echo json_encode($response);
        exit;
    }
    mysqli_stmt_close($stmt);

    $response = array('success' => 'cart removed successfuly.');

    echo json_encode($response);
}