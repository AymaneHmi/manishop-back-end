<?php

include '../../config/headers.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $headers = apache_request_headers();
    if (isset($headers['Authorization']) && $headers['Authorization'] === $api_token) {
        $sql = "SELECT * FROM categories ORDER BY created_at DESC";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $response = array();
            while ($category = mysqli_fetch_assoc($result)) {
                $post = array(
                    'id' => $category['category_id'],
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'image' => array($category['image']),
                    'createdAt' => $category['created_at'],
                );
                array_push($response, $post);
            }
            echo json_encode($response);
        } else {
            echo json_encode(array('error' => 'No category found.'));
            exit;
        }
    } else {
        header('HTTP/1.1 401 Unauthorized');
        echo 'Unauthorized';
    }
}
