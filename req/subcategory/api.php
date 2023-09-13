<?php

include '../../config/headers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $headers = apache_request_headers();
    if (isset($headers['Authorization']) && $headers['Authorization'] === $api_token) {
        $response = array();

        $subcategoriesQuery = "SELECT * FROM subcategories ORDER BY created_at DESC";
        $subcategoriesResult = mysqli_query($conn, $subcategoriesQuery);

        if (mysqli_num_rows($subcategoriesResult) > 0) {
            while ($subcategory = mysqli_fetch_assoc($subcategoriesResult)) {
                $category_id = $subcategory['category_id'];

                $categoryQuery = "SELECT * FROM categories WHERE category_id='$category_id'";
                $categoryResult = mysqli_query($conn, $categoryQuery);
                $category = mysqli_fetch_assoc($categoryResult);

                $post = array(
                    'id' => $subcategory['subcategory_id'],
                    'category_id' => $category['category_id'],
                    'category' => $category['name'],
                    'name' => $subcategory['name'],
                    'sizes' => json_decode($subcategory['size'], true),
                    'createdAt' => $subcategory['created_at'],
                );

                $response[] = $post;
            }

            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            echo json_encode(array('error' => 'No subcategories found.'));
        }
    } else {
        header('HTTP/1.1 401 Unauthorized');
        echo 'Unauthorized';
    }
}
