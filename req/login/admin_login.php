<?php

include '../../config/headers.php';

if (isset($postdata) && !empty($postdata)) {

    $response = array();

    $email = filter_var($postdata['email'], FILTER_SANITIZE_STRING);
    $password = filter_var($postdata['password'], FILTER_SANITIZE_STRING);

    if(empty($email) || empty($password)){
        echo json_encode(array('error' => 'email and password is required!'));
        exit;
    }

    $sql = "SELECT * FROM users WHERE role='admin' AND email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(array('error' => 'Failed to query database'));
        exit;
    }

    if (mysqli_num_rows($result) == 1) {
        $admin_user = mysqli_fetch_assoc($result);
    
        if (password_verify($password, $admin_user['password'])) {
            $password = '';
    
            $response = array(
                'user_token' => $admin_user['user_token']
            );
            
        } else {
            http_response_code(401);
            $response = array('error' => 'Incorrect email or password');
        }
    } else {
        http_response_code(401);
        $response = array('error' => 'Incorrect email or password');
    }

    echo json_encode($response);
}