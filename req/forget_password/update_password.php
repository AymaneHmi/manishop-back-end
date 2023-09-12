<?php

include '../../config/headers.php';

if (isset($postdata) && !empty($postdata)) {

    $response = array();

    $email = filter_var($postdata['email'], FILTER_SANITIZE_STRING);
    $password = filter_var($postdata['password'], FILTER_SANITIZE_STRING);

    if(empty($email) || empty($password)) {
        $response = array('error' => 'Data is requiered');
        echo json_encode($response);
        exit;
    }

    if(!$conn) {
        $response = array('error' => 'Failed to connect to databse!');
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM users WHERE role = 'user' AND email = '$email'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 0){
        $response = array('error' => 'Account is not exist!');
        echo json_encode($response);
        exit;
    }

    $password_hashed = password_hash($password, PASSWORD_DEFAULT); 
    
    $query = "UPDATE users SET password=? WHERE email=?";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("ss", $password_hashed, $email);

    if (!$stmt->execute()) {
        $response = array('error' => 'Could not update data in the database!');
        echo json_encode($response);
        exit;
    }

    $stmt->close();

    $response = array('success' => 'password updated successfuly.');

    echo json_encode($response);
}