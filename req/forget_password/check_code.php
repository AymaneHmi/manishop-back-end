<?php

include '../../config/headers.php';

if (isset($postdata) && !empty($postdata)) {

    $response = array();

    $email = filter_var($postdata['email'], FILTER_SANITIZE_STRING);
    $code = filter_var($postdata['code'], FILTER_SANITIZE_STRING);

    if(empty($code) || empty($email)) {
        $response = array('error' => 'code and email are requiered');
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM verification_codes WHERE (email = '$email' AND code = '$code')";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 0){
        $response = array('error' => 'code is not valid!');
        echo json_encode($response);
        exit;
    }
    
    $delete = "DELETE FROM verification_codes WHERE code = ?";
    $stmt = mysqli_prepare($conn, $delete);
    mysqli_stmt_bind_param($stmt, "s", $code);
    if (!mysqli_stmt_execute($stmt)) {
        // Error
        $response = array("error" => "code is not deleted");
        echo json_encode($response);
        exit;
    }
    mysqli_stmt_close($stmt);

    $response = array('success' => 'email verified.');
    
    echo json_encode($response);
}