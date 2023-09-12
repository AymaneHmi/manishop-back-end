<?php

include '../../config/headers.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = filter_var($postdata['product_id'], FILTER_SANITIZE_STRING);
    $user_id = filter_var($postdata['user_id'], FILTER_SANITIZE_STRING);

    if(empty($product_id) || empty($user_id)) {
        $response = array('error' => 'data is requiered');
        echo json_encode($response);
        exit;
    }

    if(!$conn) {
        $response = array('error' => 'Failed to connect to databse!');
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM FavoritesLists WHERE user_id='$user_id'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 0){
        $response = array('error' => 'no favorites list for this user.');
        echo json_encode($response);
        exit;
    }

    $favorite_list = mysqli_fetch_assoc($result);

    $list_id = $favorite_list['list_id'];

    $sql = "SELECT * FROM Favorites WHERE list_id='$list_id' AND product_id='$product_id'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 1){
        $response = array('error' => 'the product is favorited before.');
        echo json_encode($response);
        exit;
    }

    $query = "INSERT INTO Favorites (list_id , product_id) VALUES (? , ?)";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("ii", $list_id, $product_id);

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

    $sql = "SELECT * FROM favoriteslists WHERE user_id='$user_id'";
    $result = mysqli_query($conn, $sql);

    if($result == 0){
        $response = array('error' => "no favorites list with this user!");
        echo json_encode($response);
        exit;
    }

    $favorite_list = mysqli_fetch_assoc($result);

    $list_id = $favorite_list['list_id'];

    $sql = "SELECT * FROM favorites WHERE list_id='$list_id'";
    $result = mysqli_query($conn, $sql);

    if($result == 0){
        $response = array('error' => "no favorites with this user!");
        echo json_encode($response);
        exit;
    }

    while ($favorite = mysqli_fetch_assoc($result)) {

        $post = array(
            'list_id' => $favorite['list_id'],
            'product_id' => $favorite['product_id']
        );
        array_push($response, $post);
    }


    echo json_encode($response);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $product_id = filter_var($postdata['product_id'], FILTER_SANITIZE_STRING);
    $user_id = filter_var($postdata['user_id'], FILTER_SANITIZE_STRING);

    if(empty($product_id) || empty($user_id)) {
        $response = array('error' => 'data is requiered');
        echo json_encode($response);
        exit;
    }

    if(!$conn) {
        $response = array('error' => 'Failed to connect to databse!');
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM FavoritesLists WHERE user_id='$user_id'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 0){
        $response = array('error' => 'no favorites list for this user.');
        echo json_encode($response);
        exit;
    }

    $favorite_list = mysqli_fetch_assoc($result);

    $list_id = $favorite_list['list_id'];

    $sql = "SELECT * FROM Favorites WHERE list_id='$list_id' AND product_id='$product_id'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 0){
        $response = array('error' => 'the product is already defavorited.');
        echo json_encode($response);
        exit;
    }

    $query = "DELETE FROM Favorites WHERE list_id=? AND product_id=?";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("ii", $list_id, $product_id);

    if(!$stmt->execute()){
        $response = array('error'  => 'could not insert data to database!');
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    $response = array('success' => 'Data inserted successfuly.');

    echo json_encode($response);
}