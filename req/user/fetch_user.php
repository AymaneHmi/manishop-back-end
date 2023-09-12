<?php

include '../../config/headers.php';

if (isset($postdata) && !empty($postdata)) {

    $response = array();

    $user_token = filter_var($postdata['user_token'], FILTER_SANITIZE_STRING);

    if(empty($user_token)) {
        $response = array('error' => 'user token is requiered');
        json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM users WHERE role='user' AND user_token = '$user_token'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        http_response_code(500); // Internal Server Error
        echo json_encode(array('error' => 'Failed to query database'));
        exit;
    }

    if (mysqli_num_rows($result) == 1) {
        // Fetch the user data
        $user = mysqli_fetch_assoc($result);
    
        $response = array(
            'id' => $user['user_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'imageSrc' => $user['user_image'],
            'number' => $user['number'],
            'address' => $user['address'],
            'city' => $user['city'],
        );
            
    }

    echo json_encode($response);
}