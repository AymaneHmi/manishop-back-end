<?php

include '../../config/headers.php';

if (isset($postdata) && !empty($postdata)) {

    $response = array();

    $user_token = filter_var($postdata['user_token'], FILTER_SANITIZE_STRING);

    $sql = "SELECT * FROM users WHERE role='admin' AND user_token = '$user_token'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(array('error' => 'Failed to query database'));
        exit;
    }

    if (mysqli_num_rows($result) == 1) {
        $admin_user = mysqli_fetch_assoc($result);
    
        $response = array(
            'id' => $admin_user['user_id'],
            'username' => $admin_user['username'],
            'email' => $admin_user['email'],
            'imageSrc' => $admin_user['user_image'],
            'number' => $admin_user['number'],
            'address' => $admin_user['address'],
            'city' => $admin_user['city'],
        );
            
    }


    echo json_encode($response);
}