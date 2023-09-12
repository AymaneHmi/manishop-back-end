<?php

include '../../config/headers.php';

if (isset($postdata) && !empty($postdata)) {

    $response = array();

    $username_email = filter_var($postdata['username_email'], FILTER_SANITIZE_STRING);
    $password = filter_var($postdata['password'], FILTER_SANITIZE_STRING);

    if(empty($username_email) || empty($password)){
        echo json_encode(array('error' => 'email/username and password is required!'));
        exit;
    }

    $sql = "SELECT * FROM users WHERE role='user' AND (email = '$username_email' OR username= '$username_email')";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        http_response_code(500); // Internal Server Error
        echo json_encode(array('error' => 'Failed to query database'));
        exit;
    }

    if (mysqli_num_rows($result) == 1) {
        // Fetch the user data
        $admin_user = mysqli_fetch_assoc($result);
    
        // Check if the password is correct
        if (password_verify($password, $admin_user['password'])) {
            $password = '';
            // Password is correct, get user data
    
            $response = array(
                // 'user_id' => $admin_user['user_id'],
                'user_token' => $admin_user['user_token']
            );

            // setcookie("user_id", "12345", time() + 3600, "/", "localhost", true, true);
            
        } else {
            // Password is incorrect, send an error message
            // http_response_code(401);
            $response = array('error' => 'Incorrect email or password');
        }
    } else {
        // User with the provided email does not exist, send an error message
        // http_response_code(401); // Unauthorized
        $response = array('error' => 'Incorrect email or password');
    }

    echo json_encode($response);
}