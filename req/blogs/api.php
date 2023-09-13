<?php

include '../../config/headers.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $headers = apache_request_headers();
    if (isset($headers['Authorization']) && $headers['Authorization'] === $api_token) {
        $sql = "SELECT * FROM blogs ORDER BY created_at DESC";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $response = array();
            while ($blog = mysqli_fetch_assoc($result)) {
                $user_id = $blog['user_id'];
                $user_sql = "SELECT * FROM users WHERE user_id='$user_id'";
                $user_result = mysqli_query($conn, $user_sql);
            
                if (!$user_result) {
                    http_response_code(500);
                    echo json_encode(array('error' => 'Failed to query database'));
                    exit;
                }
                $user = mysqli_fetch_assoc($user_result);

                $post = array(
                    'id' => $blog['blog_id'],
                    'title' => $blog['title'],
                    'description' => $blog['description'],
                    'image' => array($blog['image']),
                    'slug' => $blog['slug'],
                    'tags' => json_decode($blog['tags'], true),
                    'author' => $user['username'],
                    'createdAt' => $blog['created_at'],
                );
                array_push($response, $post);
            }
            echo json_encode($response);
        } else {
            echo json_encode(array('error' => 'No blog found.'));
            exit;
        }
    } else {
        header('HTTP/1.1 401 Unauthorized');
        echo 'Unauthorized';
    }
}
