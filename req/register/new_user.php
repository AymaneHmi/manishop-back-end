<?php

include '../../config/headers.php';

if (isset($postdata) && !empty($postdata)) {

    $response = array();

    $username = filter_var($postdata['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($postdata['email'], FILTER_SANITIZE_STRING);
    $password = filter_var($postdata['password'], FILTER_SANITIZE_STRING);
    $number = filter_var($postdata['number'], FILTER_SANITIZE_STRING);
    $address = filter_var($postdata['address'], FILTER_SANITIZE_STRING);
    $city = filter_var($postdata['city'], FILTER_SANITIZE_STRING);
    $user_image = $postdata['image'];

    if(empty($username) || empty($email) || empty($password)) {
        $response = array('error' => 'Data is requiered');
        echo json_encode($response);
        exit;
    }

    if(!$conn) {
        $response = array('error' => 'Failed to connect to databse!');
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM users WHERE role = 'user' AND (email = '$email' OR username = '$username')";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 1){
        $response = array('error' => 'Account already exist!');
        echo json_encode($response);
        exit;
    }

    $role = 'user';
    $user_token = bin2hex(random_bytes(6));
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO users (username, email, password, role, user_token, number, city, address, email_verify) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssss", $username, $email, $password_hashed, $role, $user_token, $number, $address, $city);

    if(!$stmt->execute()){
        $response = array('error'  => 'could not insert data to database!');
        echo json_encode($response);
        exit;
    }

    $stmt->close();

    $user_id = $conn->insert_id;

    $query = "INSERT INTO cartlists (user_id) VALUES (?)";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("i", $user_id);

    if(!$stmt->execute()){
        $response = array('error'  => 'could not create a cartlist!');
        echo json_encode($response);
        exit;
    }
    
    $query = "INSERT INTO favoriteslists (user_id) VALUES (?)";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("i", $user_id);

    if(!$stmt->execute()){
        $response = array('error'  => 'could not create a cartlist!');
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    if(!empty($user_image)){
        list($type, $data) = explode(';', $user_image);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);

        $image_extension = '';
        if (strpos($type, 'image/png') !== false) {
            $image_extension = 'png';
        } elseif (strpos($type, 'image/jpeg') !== false) {
            $image_extension = 'jpg';
        } elseif (strpos($type, 'image/gif') !== false) {
            $image_extension = 'gif';
        }

        $filename = uniqid() . '.' . $image_extension;

        $uploadDirectory = '../../imgs/users/' . $filename;

        if(!file_put_contents($uploadDirectory, $data)) {
            $response = array( 'error' => 'Failed to upload image');
            echo json_encode($response);
            exit;
        }

        $sql = "UPDATE users SET user_image='$filename' WHERE user_id='$user_id'";

        if(!$conn->query($sql)) {
            $response = array( 'error' => 'Failed to update image row in database');
            echo json_encode($response);
            exit;
        }
    }    

    $response = array('success' => 'user created successfuly.');

    echo json_encode($response);
}