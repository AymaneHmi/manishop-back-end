<?php

include '../../config/headers.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $headers = apache_request_headers();
    if (isset($headers['Authorization']) && $headers['Authorization'] === $api_token) {
        $sql = "SELECT * FROM products WHERE available=TRUE";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $response = array();
            while ($product = mysqli_fetch_assoc($result)) {
                $category_id = $product['category_id'];
                $subcategory_id = $product['subcategory_id'];

                $categoryQuery = "SELECT * FROM categories WHERE category_id='$category_id'";
                $categoryResult = mysqli_query($conn, $categoryQuery);
                $category = mysqli_fetch_assoc($categoryResult);
                $subcategoryQuery = "SELECT * FROM subcategories WHERE subcategory_id='$subcategory_id'";
                $subcategoryResult = mysqli_query($conn, $subcategoryQuery);
                $subcategory = mysqli_fetch_assoc($subcategoryResult);
                // Build response array
                $post = array(
                    'id' => $product['product_id'],
                    'subcategory' => $subcategory['name'],
                    'category' => $category['name'],
                    'slug' => $product['slug'],
                    'price' => $product['price'],
                    'title' => $product['title'],
                    'description' => $product['description'],
                    'images' => json_decode($product['images']),
                    'sizes' => json_decode($subcategory['size']),
                    'available' => ($product['available'] === '1' ? true : false),
                    'createdAt' => $product['created_at']
                );
                array_push($response, $post);
            }
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            echo json_encode(array('error' => 'No category found.'));
            exit;
        }
    } else {
        // Unauthorized
        header('HTTP/1.1 401 Unauthorized');
        echo 'Unauthorized';
    }
}
