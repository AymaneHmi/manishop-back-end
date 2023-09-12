<?php

include '../../config/headers.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = filter_var($postdata['user_id'], FILTER_SANITIZE_STRING);

    if(empty($user_id)) {
        $response = array('error' => 'data is requiered');
        echo json_encode($response);
        exit;
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

    $delete = "DELETE FROM Carts WHERE list_id = ?";
    $stmt = mysqli_prepare($conn, $delete);
    mysqli_stmt_bind_param($stmt, "i", $list_id);
    if (!mysqli_stmt_execute($stmt)) {
        // Error
        $response = array("error" => "cart not reseted.");
        echo json_encode($response);
        exit;
    }
    mysqli_stmt_close($stmt);

    $response = array('success' => 'cart reset successfuly.');
}